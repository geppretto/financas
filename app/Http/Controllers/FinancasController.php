<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Despesa;
use App\Models\PagamentoMensal;
use App\Models\Receita;
use App\Models\User;
use Carbon\Carbon;
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

        $mesAutomatico = false;

        if (!$request->filled('mes') && !$request->filled('ano')) {
            // Sem filtro: abre no mês atual; se todas as contas dele já foram pagas, avança.
            $referencia = $this->proximoMesComPendencias($userId, now()->startOfMonth());
            $mesAutomatico = !$referencia->isSameMonth(now());
            $mes = $referencia->format('m');
            $ano = $referencia->format('Y');
        } else {
            $mes = $validated['mes'] ?? now()->format('m');
            $ano = $validated['ano'] ?? now()->format('Y');
        }
        $mesAno = "{$ano}-{$mes}";

        $totalReceitas = Receita::where('user_id', $userId)
            ->doMes($mes, $ano)
            ->sum('valor');

        $pagamentosDespesas = PagamentoMensal::where('user_id', $userId)
            ->where('mes', $mesAno)
            ->whereNotNull('despesa_id')
            ->pluck('pago', 'despesa_id');

        $despesas = Despesa::where('user_id', $userId)
            ->doMes($mes, $ano)
            ->with('category')
            ->get()
            ->each(function (Despesa $despesa) use ($pagamentosDespesas, $mes, $ano) {
                $despesa->data_exibida = $despesa->dataNoMes((int) $ano, (int) $mes);
                $despesa->pago_mes = $despesa->recorrente
                    ? (bool) ($pagamentosDespesas[$despesa->id] ?? false)
                    : (bool) $despesa->pago;
            })
            // Pendentes primeiro (mais próximas do vencimento no topo); pagas depois
            ->sortBy([
                fn ($a, $b) => (int) $a->pago_mes <=> (int) $b->pago_mes,
                fn ($a, $b) => $a->data_exibida <=> $b->data_exibida,
                fn ($a, $b) => $a->id <=> $b->id,
            ])
            ->values();

        $totalDespesas = (float) $despesas->sum('valor');

        $saldo = $totalReceitas - $totalDespesas;

        $receitas = Receita::where('user_id', $userId)
            ->doMes($mes, $ano)
            ->orderByRaw('data is null')
            ->orderBy('data')
            ->get();

        $receitasPagas = PagamentoMensal::where('user_id', $userId)
            ->where('mes', $mesAno)
            ->whereNotNull('receita_id')
            ->pluck('pago', 'receita_id');

        $receitasMensais = Receita::where('user_id', $userId)
            ->whereNull('data')
            ->get();

        $receitasAno = Receita::where('user_id', $userId)
            ->whereNotNull('data')
            ->whereYear('data', $ano)
            ->get();

        $despesasAno = Despesa::where('user_id', $userId)
            ->where(function ($q) use ($ano) {
                $q->where('recorrente', true)->orWhereYear('data', $ano);
            })
            ->get();

        $nomesMeses = [
            'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
            'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez',
        ];

        $monthlyStats = collect(range(1, 12))->map(function ($month) use ($nomesMeses, $receitasAno, $despesasAno, $receitasMensais, $ano) {
            $receitasMes = $receitasAno
                ->filter(fn ($receita) => (int) $receita->data->format('m') === $month)
                ->sum('valor')
                + $receitasMensais
                    ->filter(fn ($receita) => $receita->vigenteNoMes((int) $ano, $month))
                    ->sum('valor');

            $despesasMes = $despesasAno
                ->filter(fn ($despesa) => $despesa->ocorreNoMes((int) $ano, $month))
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

        $origemStats = $despesas
            ->groupBy(fn ($despesa) => $despesa->origem ?: 'Sem origem')
            ->map(fn ($items, $name) => [
                'origem' => $name,
                'total' => (float) $items->sum('valor'),
            ])
            ->sortByDesc('total')
            ->values();

        $despesasPagas = $despesas->where('pago_mes', true)->sum('valor');
        $despesasPendentes = max($totalDespesas - $despesasPagas, 0);

        $receitasPagasTotal = $receitas->sum(function ($receita) use ($receitasPagas) {
            $pago = $receita->data ? $receita->pago : (bool) ($receitasPagas[$receita->id] ?? false);

            return $pago ? (float) $receita->valor : 0;
        });

        $receitasPendentes = max($totalReceitas - $receitasPagasTotal, 0);

        $nomesMesesCompletos = [
            'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
            'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
        ];

        $mesAnterior = Carbon::create((int) $ano, (int) $mes, 1)->subMonthNoOverflow();
        $totalReceitasAnterior = (float) Receita::where('user_id', $userId)
            ->doMes($mesAnterior->format('m'), $mesAnterior->format('Y'))
            ->sum('valor');

        $variacaoReceitas = $totalReceitasAnterior > 0
            ? (($totalReceitas - $totalReceitasAnterior) / $totalReceitasAnterior) * 100
            : null;

        $pctDespesasReceita = $totalReceitas > 0 ? ($totalDespesas / $totalReceitas) * 100 : ($totalDespesas > 0 ? 100 : 0);
        $taxaPoupanca = $totalReceitas > 0 ? ($saldo / $totalReceitas) * 100 : 0;
        $pctReceitasLiquidado = $totalReceitas > 0 ? ($receitasPagasTotal / $totalReceitas) * 100 : 0;
        $saldoRealizado = $receitasPagasTotal - $despesasPagas;

        $receitasPendentesCount = $receitas->filter(
            fn ($receita) => !($receita->data ? $receita->pago : (bool) ($receitasPagas[$receita->id] ?? false))
        )->count();
        $despesasPendentesCount = $despesas->where('pago_mes', false)->count();

        $mesesComGasto = $monthlyStats->filter(fn ($m) => $m['despesas'] > 0);
        $mediaGastoMensal = $mesesComGasto->isEmpty() ? 0 : (float) $mesesComGasto->avg('despesas');

        $chartData = [
            'labels' => $monthlyStats->pluck('label'),
            'receitas' => $monthlyStats->pluck('receitas'),
            'despesas' => $monthlyStats->pluck('despesas'),
            'saldo' => $monthlyStats->pluck('saldo'),
            'categoriasLabels' => $categoryStats->pluck('categoria'),
            'categoriasValores' => $categoryStats->pluck('total'),
            'origemLabels' => $origemStats->pluck('origem'),
            'origemValores' => $origemStats->pluck('total'),
            'mesSelecionado' => (int) $mes - 1,
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
            'origemStats',
            'despesasPagas',
            'despesasPendentes',
            'receitasPagasTotal',
            'receitasPendentes',
            'nomesMesesCompletos',
            'mesAutomatico',
            'mesAnterior',
            'variacaoReceitas',
            'pctDespesasReceita',
            'taxaPoupanca',
            'pctReceitasLiquidado',
            'saldoRealizado',
            'receitasPendentesCount',
            'despesasPendentesCount',
            'mediaGastoMensal',
            'chartData'
        ));
    }

    /**
     * Avança do mês informado enquanto ele tiver despesas e todas estiverem pagas
     * (limite de 12 meses à frente).
     */
    private function proximoMesComPendencias(int $userId, Carbon $mes): Carbon
    {
        for ($i = 0; $i < 12; $i++) {
            $despesas = Despesa::where('user_id', $userId)
                ->doMes($mes->format('m'), $mes->format('Y'))
                ->get();

            if ($despesas->isEmpty()) {
                break;
            }

            $pagamentos = PagamentoMensal::where('user_id', $userId)
                ->where('mes', $mes->format('Y-m'))
                ->whereNotNull('despesa_id')
                ->pluck('pago', 'despesa_id');

            $temPendente = $despesas->contains(function (Despesa $despesa) use ($pagamentos) {
                $pago = $despesa->recorrente
                    ? (bool) ($pagamentos[$despesa->id] ?? false)
                    : (bool) $despesa->pago;

                return !$pago;
            });

            if ($temPendente) {
                break;
            }

            $mes = $mes->copy()->addMonthNoOverflow();
        }

        return $mes;
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
