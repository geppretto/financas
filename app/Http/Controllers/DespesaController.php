<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use App\Models\Receita;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DespesaController extends Controller
{
    public function create()
    {
        $data = now();
        $userId = Auth::id();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where('tipo', 'despesa')->where(function ($query) {
            $query->where('user_id', Auth::id())->orWhereNull('user_id');
        })->orderBy('name')->get();

        $despesasMes = Despesa::where('user_id', $userId)
            ->doMes($mes, $ano)
            ->with('category')
            ->get();

        $gastoMes = (float) $despesasMes->sum('valor');
        $receitaMes = (float) Receita::where('user_id', $userId)->doMes($mes, $ano)->sum('valor');
        $disponivelMes = $receitaMes - $gastoMes;
        $pctUsado = $receitaMes > 0 ? ($gastoMes / $receitaMes) * 100 : ($gastoMes > 0 ? 100 : 0);

        $gastosPorCategoria = $despesasMes
            ->groupBy(fn ($despesa) => $despesa->category->name ?? 'Sem categoria')
            ->map(fn ($items) => (float) $items->sum('valor'))
            ->sortDesc();

        $maioresGastos = $gastosPorCategoria->take(3);

        $ultimasDespesas = Despesa::where('user_id', $userId)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $origens = $this->origensDoUsuario();

        return view('despesas.create', compact(
            'origens',
            'mes',
            'ano',
            'categories',
            'gastoMes',
            'receitaMes',
            'disponivelMes',
            'pctUsado',
            'gastosPorCategoria',
            'maioresGastos',
            'ultimasDespesas'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tipo', 'despesa')->where(fn ($q) => $q->where('user_id', Auth::id())->orWhereNull('user_id'))),
            ],
            'quantidade_parcelas' => ['nullable', 'integer', 'min:2', 'max:120'],
            'parcelas_iguais' => ['nullable', 'boolean'],
            'pago' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'origem' => ['nullable', 'string', 'max:100'],
            'recorrente' => ['nullable', 'boolean'],
            'parcelas_pagas' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);

        $pago = (bool) ($validated['pago'] ?? false);
        $notas = $validated['notas'] ?? null;
        $origem = isset($validated['origem']) ? trim($validated['origem']) : null;
        $origem = $origem !== '' ? $origem : null;

        if ($request->boolean('recorrente')) {
            // Lançada uma vez: repete todo mês no mesmo dia, a partir da data informada.
            // O pagamento é controlado mês a mês (pagamentos_mensais).
            $despesa = Despesa::create([
                'descricao' => $validated['descricao'],
                'valor' => $validated['valor'],
                'data' => $validated['data'],
                'category_id' => $validated['category_id'] ?? null,
                'notas' => $notas,
                'origem' => $origem,
                'recorrente' => true,
                'pago' => false,
                'user_id' => Auth::id(),
            ]);

            if ($pago) {
                PagamentoMensal::create([
                    'user_id' => Auth::id(),
                    'despesa_id' => $despesa->id,
                    'mes' => Carbon::parse($validated['data'])->format('Y-m'),
                    'pago' => true,
                ]);
            }
        } elseif ($request->has('tem_parcelas')) {
            $request->validate([
                'quantidade_parcelas' => ['required', 'integer', 'min:2', 'max:120'],
                'parcelas_iguais' => ['required', 'boolean'],
            ]);

            $quantidadeParcelas = (int) $validated['quantidade_parcelas'];
            // As N primeiras parcelas entram como pagas; as demais ficam pendentes.
            $parcelasPagas = min(max((int) ($validated['parcelas_pagas'] ?? 0), 0), $quantidadeParcelas);

            if ($request->parcelas_iguais == 1) {
                $valorParcela = $validated['valor'];
                $dataInicial = Carbon::parse($validated['data']);

                DB::transaction(function () use ($quantidadeParcelas, $validated, $valorParcela, $dataInicial, $parcelasPagas, $notas, $origem) {
                    for ($i = 0; $i < $quantidadeParcelas; $i++) {
                        Despesa::create([
                            'descricao' => $validated['descricao'] . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                            'valor' => $valorParcela,
                            // addMonthsNoOverflow: 31/01 + 1 mês = 28/02, não 03/03
                            'data' => $dataInicial->copy()->addMonthsNoOverflow($i)->toDateString(),
                            'category_id' => $validated['category_id'] ?? null,
                            'pago' => $i < $parcelasPagas,
                            'notas' => $notas,
                'origem' => $origem,
                            'user_id' => Auth::id()
                        ]);
                    }
                });

            } else {
                $request->validate([
                    'valor_parcela' => ['required', 'array', 'size:' . $quantidadeParcelas],
                    'valor_parcela.*' => ['required', 'numeric', 'min:0.01'],
                    'data_parcela' => ['required', 'array', 'size:' . $quantidadeParcelas],
                    'data_parcela.*' => ['required', 'date'],
                ]);

                DB::transaction(function () use ($quantidadeParcelas, $validated, $request, $parcelasPagas, $notas, $origem) {
                    for ($i = 0; $i < $quantidadeParcelas; $i++) {
                        Despesa::create([
                            'descricao' => $validated['descricao'] . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                            'valor' => $request->valor_parcela[$i],
                            'data' => $request->data_parcela[$i],
                            'category_id' => $validated['category_id'] ?? null,
                            'pago' => $i < $parcelasPagas,
                            'notas' => $notas,
                'origem' => $origem,
                            'user_id' => Auth::id()
                        ]);
                    }
                });
            }
        } else {
            Despesa::create([
                'descricao' => $validated['descricao'],
                'valor' => $validated['valor'],
                'data' => $validated['data'],
                'category_id' => $validated['category_id'] ?? null,
                'pago' => $pago,
                'notas' => $notas,
                'origem' => $origem,
                'user_id' => Auth::id()

            ]);
        }

        return redirect()->route('despesas.create')->with('success', 'Despesa(s) cadastrada(s) com sucesso!');
    }
    public function marcarComoPago(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $pago = $request->boolean('pago');

            $despesa = Despesa::where('id', $id)->where('user_id', $userId)->firstOrFail();

            if ($despesa->recorrente) {
                $mes = $request->input('mes');
                if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
                    $mes = now()->format('Y-m');
                }

                PagamentoMensal::updateOrCreate(
                    ['user_id' => $userId, 'despesa_id' => $despesa->id, 'mes' => $mes],
                    ['pago' => $pago]
                );
            } else {
                $despesa->pago = $pago;
                $despesa->save();
            }

            return response()->json(['success' => true, 'pago' => $pago]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function edit($id)
    {
        $despesa = Despesa::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

        $data = $despesa->data ?? now();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where('tipo', 'despesa')->where(function ($query) {
            $query->where('user_id', auth()->id())->orWhereNull('user_id');
        })->orderBy('name')->get();

        $serie = $this->parcelasDaSerie($despesa);
        $serieTotal = $serie->count();

        $despesas = $serieTotal > 1
            ? $serie
            : Despesa::where('user_id', auth()->id())->where('category_id', $despesa->category_id)->get();

        $seriePagas = $serie->where('pago', true)->count();
        $totalDeclarado = preg_match('/\(Parcela \d+\/(\d+)\)$/', (string) $despesa->descricao, $mt) ? (int) $mt[1] : $serieTotal;

        $origens = $this->origensDoUsuario();

        return view('despesas.edit', compact('despesa', 'mes', 'ano', 'categories', 'despesas', 'serieTotal', 'seriePagas', 'origens', 'totalDeclarado'));
    }

    /**
     * Altera o total de parcelas de uma compra: cria as que faltam (a partir da última,
     * uma por mês, pendentes) ou remove as excedentes (somente se estiverem pendentes) e
     * renumera todas para "(Parcela n/novoTotal)". Retorna mensagem de erro ou null.
     */
    private function redimensionarSerie(Despesa $despesa, int $novoTotal): ?string
    {
        $serie = $this->parcelasDaSerie($despesa);

        if ($serie->count() < 2) {
            return 'Não foi possível identificar as parcelas desta compra.';
        }

        $numero = fn (Despesa $d) => (int) (preg_match('/\(Parcela (\d+)\//', (string) $d->descricao, $x) ? $x[1] : 0);
        $base = preg_replace('/ \(Parcela \d+\/\d+\)$/', '', (string) $despesa->descricao);

        $excedentes = $serie->filter(fn (Despesa $d) => $numero($d) > $novoTotal);

        if ($excedentes->contains(fn (Despesa $d) => $d->pago)) {
            return 'Há parcelas pagas entre as que seriam removidas. Desmarque-as como pagas antes de reduzir o total.';
        }

        DB::transaction(function () use ($serie, $excedentes, $novoTotal, $numero, $base) {
            foreach ($excedentes as $parcela) {
                PagamentoMensal::where('despesa_id', $parcela->id)->delete();
                $parcela->delete();
            }

            $restantes = $serie->reject(fn (Despesa $d) => $numero($d) > $novoTotal);
            $ultima = $restantes->sortBy($numero)->last();
            $ultimoNumero = $ultima ? $numero($ultima) : 0;

            // Acrescenta as parcelas que faltam, copiando a última e avançando um mês a cada uma
            for ($n = $ultimoNumero + 1; $n <= $novoTotal; $n++) {
                Despesa::create([
                    'descricao' => $base . ' (Parcela ' . $n . '/' . $novoTotal . ')',
                    'valor' => $ultima->valor,
                    'data' => $ultima->data->copy()->addMonthsNoOverflow($n - $ultimoNumero)->toDateString(),
                    'category_id' => $ultima->category_id,
                    'origem' => $ultima->origem,
                    'notas' => $ultima->notas,
                    'pago' => false,
                    'recorrente' => false,
                    'user_id' => $ultima->user_id,
                ]);
            }

            foreach ($restantes as $parcela) {
                $parcela->update(['descricao' => $base . ' (Parcela ' . $numero($parcela) . '/' . $novoTotal . ')']);
            }
        });

        return null;
    }

    private function origensDoUsuario()
    {
        return Despesa::where('user_id', Auth::id())
            ->whereNotNull('origem')
            ->distinct()
            ->orderBy('origem')
            ->pluck('origem');
    }

    /**
     * Parcelas da mesma compra, identificadas pelo padrão "Nome (Parcela n/N)".
     */
    private function parcelasDaSerie(Despesa $despesa)
    {
        if ($despesa->recorrente || $despesa->data === null
            || !preg_match('/^(.*) \(Parcela (\d+)\/(\d+)\)$/u', (string) $despesa->descricao, $m)) {
            return collect();
        }

        [, $base, $numeroAtual, $total] = $m;
        $numeroAtual = (int) $numeroAtual;
        $like = addcslashes($base, '%_\\') . ' (Parcela %/' . $total . ')';

        // Candidatas: mesmo nome-base e total (compras diferentes podem ter exatamente o mesmo nome)
        $candidatas = Despesa::where('user_id', $despesa->user_id)
            ->where('recorrente', false)
            ->where('descricao', 'like', $like)
            ->get()
            ->map(function (Despesa $d) use ($base, $total) {
                if (preg_match('/^(.*) \(Parcela (\d+)\/(\d+)\)$/u', (string) $d->descricao, $x)
                    && $x[1] === $base && $x[3] === $total) {
                    return ['n' => (int) $x[2], 'd' => $d];
                }

                return null;
            })
            ->filter();

        // Para cada número de parcela escolhe a candidata da mesma compra: parcelas criadas juntas têm
        // ids consecutivos (id da editada + diferença de número). Se não houver, usa categoria,
        // data mais próxima da esperada (um mês por parcela) e id mais próximo.
        $serie = collect();
        foreach ($candidatas->groupBy('n') as $numero => $itens) {
            $numero = (int) $numero;
            $grupo = $itens->pluck('d');
            $idEsperado = $despesa->id + ($numero - $numeroAtual);
            $esperada = $despesa->data->copy()->addMonthsNoOverflow($numero - $numeroAtual);

            $escolhida = $numero === $numeroAtual
                ? $grupo->firstWhere('id', $despesa->id)
                : ($grupo->firstWhere('id', $idEsperado)
                    ?? $grupo->sortBy(fn (Despesa $d) => [
                        $d->category_id === $despesa->category_id ? 0 : 1,
                        abs((int) $d->data->diffInDays($esperada, false)),
                        abs($d->id - $idEsperado),
                    ])->first());

            if ($escolhida) {
                $serie->push(['n' => $numero, 'd' => $escolhida]);
            }
        }

        return $serie->sortBy('n')->pluck('d')->values();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('tipo', 'despesa')->where(fn ($q) => $q->where('user_id', Auth::id())->orWhereNull('user_id'))),
            ],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'recorrencia_fim' => ['nullable', 'date', 'after_or_equal:data'],
            'parcelas_pagas' => ['nullable', 'integer', 'min:0', 'max:120'],
            'origem' => ['nullable', 'string', 'max:100'],
            'total_parcelas' => ['nullable', 'integer', 'min:2', 'max:120'],
        ]);

        $validated['origem'] = isset($validated['origem']) && trim($validated['origem']) !== '' ? trim($validated['origem']) : null;

        $despesa = Despesa::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Alterar o total de parcelas da compra (ex.: de 6 para 8)
        $novoTotal = isset($validated['total_parcelas']) && $validated['total_parcelas'] !== '' ? (int) $validated['total_parcelas'] : null;
        $totalMudou = false;

        if ($novoTotal !== null
            && preg_match('/^(.*) \(Parcela (\d+)\/(\d+)\)$/u', (string) $despesa->descricao, $mp)
            && (int) $mp[3] !== $novoTotal
        ) {
            $erro = $this->redimensionarSerie($despesa, $novoTotal);

            if ($erro !== null) {
                return back()->withInput()->withErrors(['total_parcelas' => $erro]);
            }

            $despesa->refresh();
            $totalMudou = true;

            // o nome enviado no formulário ainda traz o total antigo
            $validated['descricao'] = preg_replace('/\(Parcela (\d+)\/\d+\)$/', '(Parcela $1/' . $novoTotal . ')', $validated['descricao']);
        }

        $serieCompleta = $this->parcelasDaSerie($despesa);
        $sequencia = $request->boolean('sequencia_mensal');
        $serie = ($request->boolean('aplicar_serie') || $sequencia) ? $serieCompleta : collect();

        // Quantas parcelas da compra estão pagas: independente de "aplicar a todas"
        $pagasInformadas = ($serieCompleta->count() > 1 && isset($validated['parcelas_pagas']) && $validated['parcelas_pagas'] !== '')
            ? min((int) $validated['parcelas_pagas'], $serieCompleta->count())
            : null;

        DB::transaction(function () use ($despesa, $validated, $serie, $serieCompleta, $pagasInformadas, $sequencia) {
            if ($pagasInformadas !== null && $pagasInformadas !== $serieCompleta->where('pago', true)->count()) {
                foreach ($serieCompleta->values() as $indice => $parcela) {
                    $parcela->update(['pago' => $indice < $pagasInformadas]);
                }

                // mantém o modelo carregado em sincronia para o update abaixo
                $despesa->pago = $serieCompleta->values()->search(fn ($p) => $p->id === $despesa->id) < $pagasInformadas;
            }

            if ($serie->count() > 1) {
                $this->atualizarSerie($despesa, $serie->map(fn ($p) => $p->fresh()), $validated, $sequencia);

                return;
            }

            $despesa->update([
                'descricao' => $validated['descricao'],
                'category_id' => $validated['category_id'] ?? null,
                'origem' => $validated['origem'],
                'valor' => $validated['valor'],
                'data' => $validated['data'],
                'notas' => $validated['notas'] ?? null,
                'recorrencia_fim' => $despesa->recorrente ? ($validated['recorrencia_fim'] ?? null) : null,
            ]);
        });

        $mensagem = $serie->count() > 1
            ? 'Despesa e demais parcelas atualizadas com sucesso!'
            : ($pagasInformadas !== null ? 'Despesa atualizada! Parcelas pagas: ' . $pagasInformadas . ' de ' . $serieCompleta->count() . '.' : 'Despesa atualizada com sucesso!');

        if ($totalMudou) {
            $mensagem = 'Compra atualizada: agora com ' . $novoTotal . ' parcelas. ' . $mensagem;
        }

        return redirect()->route('despesas.edit', $despesa->id)->with('success', $mensagem);
    }

    /**
     * Aplica a edição a todas as parcelas da compra: nome, categoria, notas,
     * deslocamento de datas (em meses) e valor (só nas parcelas que tinham o mesmo valor).
     * O status de pagamento de cada parcela é preservado.
     */
    private function atualizarSerie(Despesa $atual, $serie, array $validated, bool $sequencia = false): void
    {
        $dataAntiga = $atual->data->copy();
        $dataNova = Carbon::parse($validated['data']);
        $valorAntigo = (float) $atual->valor;

        $meses = ($dataNova->year - $dataAntiga->year) * 12 + ($dataNova->month - $dataAntiga->month);
        $mudouDia = $dataNova->day !== $dataAntiga->day;

        // Nome-base: se o usuário manteve o sufixo "(Parcela n/N)", usa o que vem antes dele
        $base = preg_match('/^(.*) \(Parcela \d+\/\d+\)$/u', $validated['descricao'], $m)
            ? $m[1]
            : $validated['descricao'];

        preg_match('/\(Parcela (\d+)\//', (string) $atual->descricao, $n);
        $numeroAtual = (int) ($n[1] ?? 1);

        foreach ($serie as $parcela) {
            preg_match('/\(Parcela (\d+)\/(\d+)\)$/', (string) $parcela->descricao, $x);

            if ($sequencia) {
                // Uma parcela por mês, ancorada na parcela editada
                $data = $dataNova->copy()->addMonthsNoOverflow((int) $x[1] - $numeroAtual);
            } else {
                $data = $parcela->data->copy()->addMonthsNoOverflow($meses);
                if ($mudouDia) {
                    $data->day(min($dataNova->day, $data->daysInMonth));
                }
            }

            $dados = [
                'descricao' => $base . ' (Parcela ' . $x[1] . '/' . $x[2] . ')',
                'category_id' => $validated['category_id'] ?? null,
                'origem' => $validated['origem'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'data' => $parcela->is($atual) ? $dataNova->toDateString() : $data->toDateString(),
            ];

            if (abs((float) $parcela->valor - $valorAntigo) < 0.005) {
                $dados['valor'] = $validated['valor'];
            }

            $parcela->update($dados);
        }
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $despesa = Despesa::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        PagamentoMensal::where('user_id', $userId)
            ->where('despesa_id', $despesa->id)
            ->delete();

        $despesa->delete();

        return redirect()->route('resumo')->with('success', 'Despesa excluída com sucesso!');
    }
}
