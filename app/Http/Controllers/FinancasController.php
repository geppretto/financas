<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinancasController extends Controller
{
   public function resumo()
{
    $mesAtual = Carbon::now()->format('m');
    $anoAtual = Carbon::now()->format('Y');

    $receitas = Receita::whereMonth('data', $mesAtual)->whereYear('data', $anoAtual)->get();
    $despesas = Despesa::whereMonth('data', $mesAtual)->whereYear('data', $anoAtual)->get();

    $totalReceitas = $receitas->sum('valor');
    $totalDespesas = $despesas->sum('valor');
    $saldo = $totalReceitas - $totalDespesas;

    return view('resumo', compact('receitas', 'despesas', 'totalReceitas', 'totalDespesas', 'saldo'));
}
}
