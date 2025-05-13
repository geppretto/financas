<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\Receita;
use Illuminate\Http\Request;
use \Carbon\Carbon;

class FinancasController extends Controller
{
public function resumo(Request $request)
{
    $mes = $request->input('mes') ?? now()->format('m');
    $ano = $request->input('ano') ?? now()->format('Y');

    $receitas = Receita::whereMonth('data', $mes)->whereYear('data', $ano)->get();
    $despesas = Despesa::whereMonth('data', $mes)->whereYear('data', $ano)->get();

    $totalReceitas = $receitas->sum('valor');
    $totalDespesas = $despesas->sum('valor');
    $saldo = $totalReceitas - $totalDespesas;

    return view('resumo', compact('receitas', 'despesas', 'totalReceitas', 'totalDespesas', 'saldo', 'mes', 'ano'));
}
}
