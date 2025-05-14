<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use Illuminate\Http\Request;

class DespesaController extends Controller
{
    public function create()
    {
        return view('despesas.create');
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
                    ]);
                }
            }

        } else {
            // Despesa única (sem parcelas)
            Despesa::create([
                'descricao' => $request->descricao,
                'valor' => $request->valor,
                'data' => $request->data,
            ]);
        }

        return redirect('/resumo')->with('success', 'Despesa(s) cadastrada(s) com sucesso!');
    }
}
