<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Receita;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;

class ReceitaController extends Controller
{
    private function categoriasReceita()
    {
        return Category::where('tipo', 'receita')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    private function regraCategoria(): array
    {
        return [
            'nullable',
            Rule::exists('categories', 'id')->where(fn ($q) => $q->where('tipo', 'receita')->where('user_id', Auth::id())),
        ];
    }

    private function regrasReceita(Request $request): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'recorrente' => ['nullable', 'boolean'],
            'data' => [Rule::requiredIf(!$request->boolean('recorrente')), 'nullable', 'date'],
            'conta' => ['nullable', 'string', 'max:100'],
            'category_id' => $this->regraCategoria(),
        ];
    }

    private function dadosFormulario(?Receita $receita = null): array
    {
        $userId = Auth::id();
        $mes = now()->format('m');
        $ano = now()->format('Y');

        return [
            'receita' => $receita,
            'categories' => $this->categoriasReceita(),
            'contas' => Receita::where('user_id', $userId)->whereNotNull('conta')->distinct()->orderBy('conta')->pluck('conta'),
            'ultimasReceitas' => Receita::where('user_id', $userId)
                ->with('category')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get(),
            'totalPrevistoMes' => (float) Receita::where('user_id', $userId)->doMes($mes, $ano)->sum('valor'),
            'mes' => $mes,
            'ano' => $ano,
        ];
    }

    public function create()
    {
        return view('receitas.create', $this->dadosFormulario());
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->regrasReceita($request));

        Receita::create([
            'descricao' => $validated['descricao'],
            'valor' => $validated['valor'],
            'conta' => $validated['conta'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'data' => $request->boolean('recorrente') ? null : ($validated['data'] ?? null),
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('receitas.create')->with('success', 'Receita cadastrada com sucesso!');
    }

    public function edit($id)
    {
        $receita = Receita::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('receitas.create', $this->dadosFormulario($receita));
    }

    public function update(Request $request, $id)
    {
        $receita = Receita::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate($this->regrasReceita($request));

        $receita->update([
            'descricao' => $validated['descricao'],
            'valor' => $validated['valor'],
            'conta' => $validated['conta'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'data' => $request->boolean('recorrente') ? null : ($validated['data'] ?? null),
        ]);

        return redirect()->route('resumo')->with('success', 'Receita atualizada com sucesso!');
    }

    public function marcarComoPago(Request $request, $id)
{
    try {
        $userId = auth()->id();

        // esperado: "YYYY-MM"
        $mes  = $request->input('mes') ?: now()->format('Y-m');
        $pago = $request->boolean('pago');

        $receita = Receita::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if (!empty($receita->data)) {
            // Receita com data => grava direto na coluna "pago"
            $receita->pago = $pago;
            $receita->save();

            // limpa registro mensal desse mês pra não conflitar (opcional)
            PagamentoMensal::where('user_id', $userId)
                ->where('receita_id', $id)
                ->where('mes', $mes)
                ->delete();

            $source = 'receitas.pago';
        } else {
            // Receita mensal (sem data) => grava em PagamentoMensal
            PagamentoMensal::updateOrCreate(
                [
                    'user_id' => $userId,
                    'receita_id' => $id,
                    'mes' => $mes,
                ],
                [
                    'pago' => $pago,
                ]
            );

            $source = 'pagamento_mensals.pago';
        }

        return response()->json([
            'success' => true,
            'pago'    => $pago,
            'source'  => $source,
            'mes'     => $mes,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function destroy($id)
    {
        $userId = Auth::id();
        $receita = Receita::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        PagamentoMensal::where('user_id', $userId)
            ->where('receita_id', $receita->id)
            ->delete();

        $receita->delete();

        return redirect()->route('resumo')->with('success', 'Receita excluída com sucesso!');
    }
}
