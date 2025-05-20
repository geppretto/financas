<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Http\Request;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;

class ReceitaController extends Controller
{
    public function create()
    {
        return view('receitas.create');
    }

    public function store(Request $request)
    {

        Receita::create([
            'descricao' => $request->descricao,
            'valor' => $request->valor,
            'data' => $request->data,
            'user_id' => Auth::id()
        ]);


        return redirect('/resumo')->with('success', 'Receita cadastrada com sucesso!');
    }
    public function marcarComoPago(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $mes = $request->input('mes') ?? now()->format('Y-m');
            $pago = $request->boolean('pago');

            $receita = Receita::where('id', $id)->where('user_id', $userId)->firstOrFail();

            if ($receita->data) {
                // Receita com data => grava direto no campo "pago"
                $receita->pago = $pago;
                $receita->save();
            } else {
                // Receita mensal => usa a tabela de pagamentos mensais
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
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
