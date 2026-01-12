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
}
