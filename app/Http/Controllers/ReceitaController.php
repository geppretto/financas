<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Http\Request;
use App\Models\PagamentoMensal;
use Illuminate\Support\Facades\Auth;

class ReceitaController extends Controller
{
    public function create() {
    return view('receitas.create');
}

public function store(Request $request) {
    dd($request->all());
    $request->validate([
        'descricao' => 'required|string|max:255',
        'valor' => 'required|numeric',
        'data' => 'required|date',
    ]);

    Receita::create($request->all());

    return redirect('/resumo')->with('success', 'Receita cadastrada com sucesso!');
}
public function marcarComoPago(Request $request, $id)
{
    try {
        $userId = auth()->id();
        $mes = now()->format('Y-m'); // ou use o que vier via request

        $pagamento = PagamentoMensal::updateOrCreate(
            [
                'user_id' => $userId,
                'receita_id' => $id,
                'mes' => $mes,
            ],
            [
                'pago' => $request->input('pago', false),
            ]
        );

        return response()->json(['success' => true, 'pago' => $pagamento->pago]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
