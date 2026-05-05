<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use App\Models\Receita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FinancasController extends Controller
{
    public function resumo(Request $request)
    {
        $validated = $request->validate([
            'mes' => ['nullable', 'date_format:m'],
            'ano' => ['nullable', 'digits:4'],
        ]);

        $userId = Auth::id();
        $user = User::findOrFail($userId);

        $mes = $validated['mes'] ?? now()->format('m');
        $ano = $validated['ano'] ?? now()->format('Y');
        $mesAno = "{$ano}-{$mes}";

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

        $receitas = Receita::where('user_id', $userId)
            ->where(function ($q) use ($mes, $ano) {
                $q->where(function ($q2) use ($mes, $ano) {
                    $q2->whereMonth('data', $mes)
                        ->whereYear('data', $ano);
                })->orWhereNull('data');
            })
            ->orderByRaw('data is null')
            ->orderBy('data')
            ->get();

        $receitasPagas = PagamentoMensal::where('user_id', $userId)
            ->where('mes', $mesAno)
            ->whereNotNull('receita_id')
            ->pluck('pago', 'receita_id');

        $despesas = Despesa::whereMonth('data', $mes)
            ->whereYear('data', $ano)
            ->with('category')
            ->where('user_id', $userId)
            ->orderBy('data')
            ->get();

        $receitasMensaisTotal = Receita::where('user_id', $userId)
            ->whereNull('data')
            ->sum('valor');

        $receitasAno = Receita::where('user_id', $userId)
            ->whereNotNull('data')
            ->whereYear('data', $ano)
            ->get();

        $despesasAno = Despesa::where('user_id', $userId)
            ->whereYear('data', $ano)
            ->with('category')
            ->get();

        $nomesMeses = [
            'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
            'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez',
        ];

        $monthlyStats = collect(range(1, 12))->map(function ($month) use ($nomesMeses, $receitasAno, $despesasAno, $receitasMensaisTotal) {
            $receitasMes = $receitasAno
                ->filter(fn ($receita) => (int) $receita->data->format('m') === $month)
                ->sum('valor') + $receitasMensaisTotal;

            $despesasMes = $despesasAno
                ->filter(fn ($despesa) => (int) $despesa->data->format('m') === $month)
                ->sum('valor');

            return [
                'mes' => str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'label' => $nomesMeses[$month - 1],
                'receitas' => (float) $receitasMes,
                'despesas' => (float) $despesasMes,
                'saldo' => (float) ($receitasMes - $despesasMes),
            ];
        });

        $mesMaiorGasto = $this->monthByExpense($monthlyStats, 'desc');
        $mesMenorGasto = $this->monthByExpense($monthlyStats, 'asc');

        $categoryStats = $despesas
            ->groupBy(fn ($despesa) => $despesa->category->name ?? 'Sem categoria')
            ->map(fn ($items, $name) => [
                'categoria' => $name,
                'total' => (float) $items->sum('valor'),
            ])
            ->sortByDesc('total')
            ->values();

        $despesasPagas = $despesas->where('pago', true)->sum('valor');
        $despesasPendentes = max($totalDespesas - $despesasPagas, 0);

        $receitasPagasTotal = $receitas->sum(function ($receita) use ($receitasPagas) {
            $pago = $receita->data ? $receita->pago : (bool) ($receitasPagas[$receita->id] ?? false);

            return $pago ? (float) $receita->valor : 0;
        });

        $receitasPendentes = max($totalReceitas - $receitasPagasTotal, 0);

        $chartData = [
            'labels' => $monthlyStats->pluck('label'),
            'receitas' => $monthlyStats->pluck('receitas'),
            'despesas' => $monthlyStats->pluck('despesas'),
            'saldo' => $monthlyStats->pluck('saldo'),
            'categoriasLabels' => $categoryStats->pluck('categoria'),
            'categoriasValores' => $categoryStats->pluck('total'),
        ];

        return view('resumo', compact(
            'user',
            'totalReceitas',
            'totalDespesas',
            'mes',
            'ano',
            'mesAno',
            'receitas',
            'receitasPagas',
            'saldo',
            'despesas',
            'monthlyStats',
            'mesMaiorGasto',
            'mesMenorGasto',
            'categoryStats',
            'despesasPagas',
            'despesasPendentes',
            'receitasPagasTotal',
            'receitasPendentes',
            'chartData'
        ));
    }

    private function monthByExpense(Collection $monthlyStats, string $direction): array
    {
        $monthsWithExpenses = $monthlyStats->filter(fn ($month) => $month['despesas'] > 0);

        if ($monthsWithExpenses->isEmpty()) {
            return [
                'label' => '-',
                'despesas' => 0,
            ];
        }

        return $direction === 'asc'
            ? $monthsWithExpenses->sortBy('despesas')->first()
            : $monthsWithExpenses->sortByDesc('despesas')->first();
    }
}
