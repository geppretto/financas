<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DespesaController extends Controller
{
    public function create()
    {
        $data = now()->addMonth();

        $mes = $data->format('m');
        $ano = $data->format('Y');
        $categories = Category::all();

        return view('despesas.create', compact('mes', 'ano', 'categories'));
    }

    public function store(Request $request)
    {
        // Validação básica
        $request->validate([
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric',
            'data' => 'required|date',
        ]);

        // Verifica se tem parcelas
        if ($request->has('tem_parcelas')) {
            $quantidadeParcelas = intval($request->quantidade_parcelas);

            // Se for mesmo valor em todas
            if ($request->parcelas_iguais == 1) {
                $valorParcela = $request->valor;
                $dataInicial = $request->data;

                for ($i = 0; $i < $quantidadeParcelas; $i++) {
                    Despesa::create([
                        'descricao' => $request->descricao . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                        'valor' => $valorParcela,
                        'data' => date('Y-m-d', strtotime("+$i month", strtotime($dataInicial))),
                        'user_id' => Auth::id()
                    ]);
                }

                // Parcelas com valores e datas diferentes
            } else {
                $request->validate([
                    'valor_parcela' => 'required|array',
                    'valor_parcela.*' => 'required|numeric',
                    'data_parcela' => 'required|array',
                    'data_parcela.*' => 'required|date',
                ]);

                for ($i = 0; $i < $quantidadeParcelas; $i++) {
                    Despesa::create([
                        'descricao' => $request->descricao . " (Parcela " . ($i + 1) . "/" . $quantidadeParcelas . ")",
                        'valor' => $request->valor_parcela[$i],
                        'data' => $request->data_parcela[$i],
                        'user_id' => Auth::id()
                    ]);
                }
            }
        } else {
            // Despesa única (sem parcelas)
            Despesa::create([
                'descricao' => $request->descricao,
                'valor' => $request->valor,
                'data' => $request->data,
                'category_id' => $request->category_id,
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

            $receita = Despesa::where('id', $id)->where('user_id', $userId)->firstOrFail();

            if ($receita->data) {
                // Receita com data => grava direto no campo "pago"
                $receita->pago = $pago;
                $receita->save();
            } else {
                // Receita mensal => usa a tabela de pagamentos mensais
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
        $categories = Category::where('user_id', auth()->id())->get();

        $despesa = Despesa::find($id);
        $despesas = Despesa::where('user_id', auth()->id())->where('category_id', $despesa->category_id)->get();

        return view('despesas.edit', compact('despesa', 'mes', 'ano', 'categories', 'despesas'));
    }
    public function update(Request $request, $id)
    {
        $data = now()->addMonth();
        $mes = $data->format('m');
        $ano = $data->format('Y');

        $despesa = Despesa::findOrFail($id);
        $despesas = Despesa::where('user_id', auth()->id())->where('category_id', $despesa->category_id)->get();
        $despesa->update([
            'descricao' => $request->descricao,
            'category_id' => $request->category_id,
            'valor' => $request->valor,
            'data' => $request->data,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('despesas.edit', [
            'id' => $despesa->id,
            'mes' => $mes,
            'ano' => $ano,
            'despesas' => $despesas,
        ])->with('success', 'Despesa atualizada com sucesso!');
    }
}
