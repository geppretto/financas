<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DespesaController extends Controller
{
    public function create()
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where(function ($query) {
            $query->where('user_id', Auth::id())->orWhereNull('user_id');
        })->orderBy('name')->get();

        return view('despesas.create', compact('mes', 'ano', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())->orWhereNull('user_id')),
            ],
            'quantidade_parcelas' => ['nullable', 'integer', 'min:2', 'max:120'],
            'parcelas_iguais' => ['nullable', 'boolean'],
        ]);

        if ($request->has('tem_parcelas')) {
            $request->validate([
                'quantidade_parcelas' => ['required', 'integer', 'min:2', 'max:120'],
                'parcelas_iguais' => ['required', 'boolean'],
            ]);

            $quantidadeParcelas = (int) $validated['quantidade_parcelas'];

            if ($request->parcelas_iguais == 1) {
                $valorParcela = $validated['valor'];
                $dataInicial = $validated['data'];

                for ($i = 0; $i < $quantidadeParcelas; $i++) {
                    Despesa::create([
                        'descricao' => $validated['descricao'] . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                        'valor' => $valorParcela,
                        'data' => date('Y-m-d', strtotime("+$i month", strtotime($dataInicial))),
                        'category_id' => $validated['category_id'] ?? null,
                        'user_id' => Auth::id()
                    ]);
                }

            } else {
                $request->validate([
                    'valor_parcela' => ['required', 'array', 'size:' . $quantidadeParcelas],
                    'valor_parcela.*' => ['required', 'numeric', 'min:0.01'],
                    'data_parcela' => ['required', 'array', 'size:' . $quantidadeParcelas],
                    'data_parcela.*' => ['required', 'date'],
                ]);

                for ($i = 0; $i < $quantidadeParcelas; $i++) {
                    Despesa::create([
                        'descricao' => $validated['descricao'] . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                        'valor' => $request->valor_parcela[$i],
                        'data' => $request->data_parcela[$i],
                        'category_id' => $validated['category_id'] ?? null,
                        'user_id' => Auth::id()
                    ]);
                }
            }
        } else {
            Despesa::create([
                'descricao' => $validated['descricao'],
                'valor' => $validated['valor'],
                'data' => $validated['data'],
                'category_id' => $validated['category_id'] ?? null,
                'user_id' => Auth::id()

            ]);
        }

        return redirect('/resumo')->with('success', 'Despesa(s) cadastrada(s) com sucesso!');
    }
    public function marcarComoPago(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $mes = $request->input('mes') ?? now()->format('Y-m');
            $pago = $request->boolean('pago');

            $despesa = Despesa::where('id', $id)->where('user_id', $userId)->firstOrFail();

            if ($despesa->data) {
                $despesa->pago = $pago;
                $despesa->save();
            } else {
                PagamentoMensal::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'despesa_id' => $id,
                        'mes' => $mes,
                    ],
                    [
                        'pago' => $pago,
                    ]
                );
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function edit($id)
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::where(function ($query) {
            $query->where('user_id', auth()->id())->orWhereNull('user_id');
        })->orderBy('name')->get();

        $despesa = Despesa::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $despesas = Despesa::where('user_id', auth()->id())->where('category_id', $despesa->category_id)->get();

        return view('despesas.edit', compact('despesa', 'mes', 'ano', 'categories', 'despesas'));
    }
    public function update(Request $request, $id)
    {
        $data = now()->addMonth();
        $mes = $data->format('m');
        $ano = $data->format('Y');

        $validated = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())->orWhereNull('user_id')),
            ],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
        ]);

        $despesa = Despesa::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $despesas = Despesa::where('user_id', auth()->id())->where('category_id', $despesa->category_id)->get();
        $despesa->update([
            'descricao' => $validated['descricao'],
            'category_id' => $validated['category_id'] ?? null,
            'valor' => $validated['valor'],
            'data' => $validated['data'],
        ]);

        return redirect()->route('despesas.edit', [
            'id' => $despesa->id,
            'mes' => $mes,
            'ano' => $ano,
            'despesas' => $despesas,
        ])->with('success', 'Despesa atualizada com sucesso!');
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
