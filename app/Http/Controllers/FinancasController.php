<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use App\Models\Receita;
use App\Models\User;
use Illuminate\Http\Request;
use \Carbon\Carbon;

class FinancasController extends Controller
{
    public function resumo(Request $request)
    {
        $userId = Auth::id();
        $user = User::findOrFail($userId);

        $mes = $request->input('mes') ?? now()->format('m');
        $ano = $request->input('ano') ?? now()->format('Y');
        $mesAno = "$ano-$mes";

        // Pagamentos mensais: receitas e despesas
        $pagamentosReceitas = PagamentoMensal::where('mes', $mesAno)
            ->where('user_id', $userId)
            ->whereNotNull('receita_id')
            ->pluck('pago', 'receita_id')
            ->toArray();

        $pagamentosDespesas = PagamentoMensal::where('mes', $mesAno)
            ->where('user_id', $userId)
            ->whereNotNull('despesa_id')
            ->pluck('pago', 'despesa_id')
            ->toArray();

        $salario = Receita::where('id', '1')->where('user_id', $userId)->first();
        $despesasSempre = Despesa::where('id', '4')->where('user_id', $userId)->first();

        $salarioPago = PagamentoMensal::where('mes', $mesAno)
            ->where('user_id', $userId)
            ->where('receita_id', $salario?->id)
            ->value('pago') ?? false;

        $despesaSemprePago = PagamentoMensal::where('mes', $mesAno)
            ->where('user_id', $userId)
            ->where('despesa_id', $despesasSempre?->id)
            ->value('pago') ?? false;

        $receitas = Receita::whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->where('user_id', $userId)
            ->get();

        $receitasComData = $receitas->keyBy('id')->mapWithKeys(function ($r) {
            return [$r->id => $r->pago];
        })->toArray();

        $despesas = Despesa::whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->where('user_id', $userId)
            ->get();
        $despesasComData = $despesas->keyBy('id')->mapWithKeys(function ($r) {
            return [$r->id => $r->pago];
        })->toArray();

        $totalReceitas = $receitas->sum('valor');
        $totalDespesas = $despesas->sum('valor');

        $salarioAll = ($salario->valor ?? 0) + $totalReceitas;
        $despesasAll = ($despesasSempre->valor ?? 0) + $totalDespesas;

        $saldo = $salarioAll - $despesasAll;
        // dd($despesas);

        return view('resumo', compact(
            'receitas',
            'despesas',
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'mes',
            'ano',
            'salario',
            'despesasSempre',
            'salarioAll',
            'despesasAll',
            'user',
            'pagamentosReceitas',
            'pagamentosDespesas',
            'salarioPago',
            'despesaSemprePago',
            'receitasComData',
            'despesasComData',
        ));
    }
}
