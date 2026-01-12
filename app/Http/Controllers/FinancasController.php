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

        $totalReceitas = Receita::where('user_id', $userId)
    ->where(function ($q) use ($mes, $ano) {
        $q->where(function ($q2) use ($mes, $ano) {
            $q2->whereMonth('data', $mes)
               ->whereYear('data', $ano);
        })
        ->orWhereNull('data');
    })
    ->sum('valor');

        $totalDespesas = Despesa::whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->where('user_id', $userId)
            ->sum('valor');

        $saldo = $totalReceitas - $totalDespesas;

        $receitas = Receita::whereMonth('data', $mes)->whereYear('data', $ano)->where('user_id', $userId)->orWhereNull('data')->get();

        // dd($receitas);
        $despesas = Despesa::whereMonth('data', $mes)->whereYear('data', $ano)->where('user_id', $userId)->get();

        return view('resumo', compact(
            'user',
            'totalReceitas',
            'totalDespesas',
            'mes',
            'ano',
            'receitas',
            'saldo',
            'despesas'
        ));
    }
}
