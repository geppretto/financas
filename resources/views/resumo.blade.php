@extends('layouts.app')

@section('content_class', 'content-wide')

@php
    $brl = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $mesNome = $nomesMesesCompletos[(int) $mes - 1];
    $mesAnteriorNome = $nomesMesesCompletos[(int) $mesAnterior->format('m') - 1];
    $pctGasto = min(max($pctDespesasReceita, 0), 100);
    $coresCategorias = ['#0a1222', '#4b5568', '#22a56b', '#d3e2fb', '#9bb3d9', '#f59e0b', '#e11d48'];
    $totalCategorias = max($categoryStats->sum('total'), 0.01);
    $temDespesas = $despesas->count() > 0;
    $despesasAbertas = $despesas->where('pago', false);
@endphp

@push('styles')
<style>
    .rs { color: #0a1222; }
    .rs .mono { font-family: 'JetBrains Mono', monospace; }
    .rs-card {
        background: #fff;
        border: 1px solid #e9edf4;
        border-radius: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
    }
    .rs-toolbar {
        display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
        padding: 1rem 1rem; margin-bottom: 1.5rem;
    }
    .rs-toolbar-left { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .rs-hello {
        display: inline-flex; align-items: center; gap: .5rem;
        background: #f1f5f9; border-radius: 999px; padding: .5rem .9rem; font-size: .82rem; font-weight: 600;
    }
    .rs-hello .dot { width: 8px; height: 8px; border-radius: 50%; background: #34d399; }
    .rs-hello .mono { font-size: .68rem; font-weight: 400; color: #64748b; }
    .rs-fiscal { display: inline-flex; align-items: center; gap: .5rem; font-size: .7rem; color: #475569; padding-left: 1rem; border-left: 1px solid #e2e8f0; }
    .rs-fiscal svg { width: 14px; height: 14px; }
    .rs-filter { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .rs-select {
        appearance: none; border: 0; background: #f1f5f9 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right .7rem center;
        border-radius: 10px; padding: .6rem 2.1rem .6rem .9rem; font-size: .85rem; font-weight: 500; color: #0a1222;
    }
    .rs-btn-dark {
        display: inline-flex; align-items: center; gap: .5rem; border: 0; background: #0a1222; color: #fff;
        border-radius: 10px; padding: .6rem 1.1rem; font-size: .85rem; font-weight: 600;
    }
    .rs-btn-icon {
        border: 1px solid #e2e8f0; background: #fff; color: #334155; border-radius: 10px; width: 40px; height: 40px;
        display: grid; place-items: center;
    }
    .rs svg.lucide, .rs [data-lucide] { width: 16px; height: 16px; }

    .rs-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .rs-kpi { padding: 1.5rem; }
    .rs-kpi-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .rs-kpi-title { display: flex; align-items: center; gap: .6rem; font-size: .85rem; font-weight: 600; color: #1e293b; }
    .rs-ico {
        width: 32px; height: 32px; border-radius: 8px; display: grid; place-items: center; flex: 0 0 auto;
    }
    .rs-ico.blue { background: #eaf1fe; color: #2563eb; }
    .rs-ico.red { background: #fde8ea; color: #dc2626; }
    .rs-ico.green { background: #e2f6ec; color: #16a34a; }
    .rs-ico.slate { background: #eef2f8; color: #334155; }
    .rs-kpi-value { font-size: 2.6rem; font-weight: 700; letter-spacing: -0.035em; line-height: 1.1; }
    .rs-kpi-value.green { color: #16a36a; }
    .rs-kpi-sub { display: flex; align-items: center; gap: .6rem; margin-top: .7rem; font-size: .82rem; color: #334155; min-height: 24px; }
    .rs-chip { font-family: 'JetBrains Mono', monospace; font-size: .68rem; font-weight: 600; border-radius: 6px; padding: .2rem .5rem; display: inline-flex; align-items: center; gap: .3rem; }
    .rs-chip.green { background: #e2f6ec; color: #15803d; }
    .rs-chip.blue { background: #e8f0fe; color: #1d4ed8; }
    .rs-chip.red { background: #fde8ea; color: #b91c1c; }
    .rs-chip.slate { background: #eef2f8; color: #334155; }
    .rs-chip svg { width: 12px; height: 12px; }
    .rs-kpi-foot { display: flex; justify-content: space-between; margin-top: 1.1rem; font-family: 'JetBrains Mono', monospace; font-size: .68rem; color: #64748b; }
    .rs-kpi-foot .strong { color: #0a1222; font-weight: 700; }
    .rs-kpi-foot .ok { color: #16a34a; font-weight: 600; }
    .rs-meta { font-family: 'JetBrains Mono', monospace; font-size: .68rem; color: #64748b; }
    .rs-bar { height: 6px; border-radius: 999px; background: #dbe7fb; margin-top: 1.4rem; overflow: hidden; }
    .rs-bar > span { display: block; height: 100%; background: #0a1222; border-radius: 999px; }
    .rs-eye { background: none; border: 0; color: #334155; padding: 0; }
    .rs-secret.is-hidden { filter: blur(9px); user-select: none; }

    .rs-section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .rs-section-title h2 { font-size: 1.35rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .6rem; }

    .rs-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .rs-insight { padding: 1rem; display: flex; align-items: center; gap: 1rem; }
    .rs-insight .rs-ico { width: 44px; height: 44px; border-radius: 12px; }
    .rs-insight .label { font-size: .8rem; color: #475569; }
    .rs-insight .value { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em; margin: .1rem 0; }
    .rs-insight .hint { font-family: 'JetBrains Mono', monospace; font-size: .65rem; }
    .rs-insight .hint.red { color: #b91c1c; }
    .rs-insight .hint.green { color: #16a34a; }
    .rs-insight .hint.slate { color: #64748b; }

    .rs-panel { padding: 1.5rem; }
    .rs-panel-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; }
    .rs-panel-head h3 { font-size: 1.3rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
    .rs-panel-head .sub { font-size: .85rem; color: #475569; margin-top: .15rem; }
    .rs-tag { font-family: 'JetBrains Mono', monospace; font-size: .6rem; background: #eef2f8; color: #475569; border-radius: 5px; padding: .15rem .45rem; vertical-align: middle; margin-left: .5rem; }
    .rs-legend { display: flex; gap: 1rem; font-family: 'JetBrains Mono', monospace; font-size: .7rem; font-weight: 600; color: #334155; }
    .rs-legend i { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .4rem; }
    .rs-chart-lg { position: relative; height: 300px; margin-top: 1.25rem; }

    .rs-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
    .rs-donut-wrap { display: flex; align-items: center; gap: 2rem; margin-top: 1.5rem; min-height: 180px; }
    .rs-donut { position: relative; width: 170px; height: 170px; flex: 0 0 170px; }
    .rs-donut-center { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; pointer-events: none; }
    .rs-donut-center strong { font-size: 1.15rem; line-height: 1; }
    .rs-donut-center span { font-family: 'JetBrains Mono', monospace; font-size: .65rem; color: #475569; }
    .rs-cat-list { flex: 1; display: grid; gap: .7rem; font-size: .85rem; }
    .rs-cat-row { display: grid; grid-template-columns: 1fr auto 44px; align-items: center; gap: .9rem; }
    .rs-cat-row .name { display: flex; align-items: center; gap: .6rem; font-weight: 500; }
    .rs-cat-row .name i { width: 10px; height: 10px; border-radius: 50%; flex: 0 0 auto; }
    .rs-cat-row .val { font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 600; }
    .rs-cat-row .pct { font-family: 'JetBrains Mono', monospace; font-size: .7rem; color: #64748b; text-align: right; }
    .rs-aviso { padding: .8rem 1.1rem; margin: -0.75rem 0 1.25rem; background: #eaf1fe; border-color: #dbe7fd; font-size: .85rem; }
    .rs-aviso a { margin-left: .4rem; }
    .rs-tabs { display: inline-flex; background: #eef2f8; border-radius: 8px; padding: 3px; gap: 2px; }
    .rs-tabs button { border: 0; background: transparent; font-size: .72rem; font-weight: 600; color: #475569; padding: .3rem .65rem; border-radius: 6px; }
    .rs-tabs button.active { background: #fff; color: #0a1222; box-shadow: 0 1px 2px rgba(15, 23, 42, .12); }
    .rs-chart-sm { position: relative; height: 190px; margin-top: 1.5rem; }
    .rs-chart-foot { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: .68rem; color: #475569; margin-top: .6rem; gap: 1rem; flex-wrap: wrap; }

    .rs-table-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .rs-table-head h3 { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .7rem; }
    .rs-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .86rem; }
    .rs-table thead th {
        background: #f4f6fa; font-family: 'JetBrains Mono', monospace; font-size: .66rem; font-weight: 500; color: #475569;
        padding: .7rem .75rem; white-space: nowrap;
    }
    .rs-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .rs-table thead th:last-child { border-radius: 0 10px 10px 0; }
    .rs-table tbody td { padding: .95rem .75rem; border-bottom: 1px solid #f1f4f9; vertical-align: middle; }
    .rs-table tbody tr:last-child td { border-bottom: 0; }
    .rs-table .desc { display: flex; align-items: center; gap: .6rem; font-weight: 600; }
    .rs-table .desc svg { color: #16a34a; width: 16px; height: 16px; flex: 0 0 auto; }
    .rs-table .desc.out svg { color: #dc2626; }
    .rs-table .desc small { display: block; font-weight: 400; font-size: .7rem; color: #64748b; }
    .rs-table .num { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: .8rem; text-align: right; white-space: nowrap; }
    .rs-table .act { text-align: right; white-space: nowrap; }
    .rs-table .act a, .rs-table .act button { background: none; border: 0; color: #334155; padding: .25rem; display: inline-grid; place-items: center; }
    .rs-table .act a:hover { color: #2563eb; }
    .rs-table .act button:hover { color: #dc2626; }
    .rs-status {
        border: 0; display: inline-flex; align-items: center; gap: .35rem; font-family: 'JetBrains Mono', monospace;
        font-size: .68rem; font-weight: 600; border-radius: 999px; padding: .3rem .65rem;
    }
    .rs-status svg { width: 12px; height: 12px; }
    .rs-status.pago { background: #e2f6ec; color: #15803d; }
    .rs-status.pend { background: #fff4d6; color: #a16207; }
    .rs-status { cursor: pointer; margin: 0; white-space: nowrap; min-width: 104px; justify-content: flex-start; }
    .rs-table td:nth-child(3) { white-space: nowrap; }
    .rs-status input { width: 15px; height: 15px; margin: 0; accent-color: #15803d; cursor: pointer; }
    .rs-status input:disabled { cursor: wait; }
    .rs-table tr.urg-azul td { background: #f1f6ff; }
    .rs-table tr.urg-amarelo td { background: #fff8dc; }
    .rs-table tr.urg-vermelho td { background: #fdecee; }
    .rs-table tr.urg-ok td { background: #f2fbf6; }
    .rs-table tr.urg-azul td:first-child, .rs-table tr.urg-amarelo td:first-child,
    .rs-table tr.urg-vermelho td:first-child, .rs-table tr.urg-ok td:first-child { border-radius: 10px 0 0 10px; }
    .rs-table tr.urg-azul td:last-child, .rs-table tr.urg-amarelo td:last-child,
    .rs-table tr.urg-vermelho td:last-child, .rs-table tr.urg-ok td:last-child { border-radius: 0 10px 10px 0; }
    .rs-table tr.urg-vermelho .desc small { color: #b91c1c; font-weight: 600; }
    .rs-table tr.urg-amarelo .desc small { color: #a16207; font-weight: 600; }
    .rs-table tr.urg-azul td, .rs-table tr.urg-amarelo td, .rs-table tr.urg-vermelho td, .rs-table tr.urg-ok td { border-bottom: 0; }
    .rs-table tr.linha-despesa + tr.linha-despesa td { border-top: 6px solid #fff; }
    .rs-table-box { min-height: 250px; }
    /* Mostra ~5 linhas por vez; o restante rola dentro do card */
    .rs-scroll { max-height: 430px; overflow-y: auto; overflow-x: auto; padding-right: 4px; scrollbar-width: thin; scrollbar-color: #c9d3e3 transparent; }
    .rs-scroll::-webkit-scrollbar { width: 8px; height: 8px; }
    .rs-scroll::-webkit-scrollbar-thumb { background: #c9d3e3; border-radius: 999px; }
    .rs-scroll thead th { position: sticky; top: 0; z-index: 2; }
    .rs-empty {
        background: #f8fafc; border-radius: 14px; min-height: 250px; display: grid; place-content: center;
        text-align: center; padding: 2rem; gap: .35rem; justify-items: center;
    }
    .rs-empty .badge-ico { width: 56px; height: 56px; border-radius: 14px; background: #fff; display: grid; place-items: center; color: #16a34a; box-shadow: 0 6px 18px rgba(15, 23, 42, .06); margin-bottom: .6rem; }
    .rs-empty .badge-ico svg { width: 26px; height: 26px; }
    .rs-empty strong { font-size: 1.05rem; }
    .rs-empty p { font-size: .82rem; color: #475569; max-width: 340px; margin: 0; }
    .rs-empty button { margin-top: .8rem; border: 0; background: #e8f0fe; color: #1d4ed8; font-size: .8rem; font-weight: 600; border-radius: 8px; padding: .5rem .9rem; display: inline-flex; gap: .4rem; align-items: center; }
    .rs-table-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 1.1rem; font-family: 'JetBrains Mono', monospace; font-size: .7rem; color: #475569; }
    .rs-table-foot a { color: #0a1222; text-decoration: none; font-weight: 600; display: inline-flex; gap: .35rem; align-items: center; }
    .rs-table-foot a.green { color: #16a34a; }
    .rs-table-foot a svg { width: 13px; height: 13px; }

    @media (max-width: 1100px) {
        .rs-grid-3, .rs-grid-2 { grid-template-columns: 1fr; }
        .rs-grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .rs-grid-4 { grid-template-columns: 1fr; }
        .rs-donut-wrap { flex-direction: column; align-items: stretch; }
        .rs-donut { align-self: center; }
        .rs-kpi-value { font-size: 2.1rem; }
        .rs-fiscal { border-left: 0; padding-left: 0; }
    }
    @media print {
        .topbar, .app-footer, .rs-filter, .act { display: none !important; }
    }

    @media (max-width: 640px) {
        .rs-toolbar { padding: .85rem; }
        .rs-toolbar-left, .rs-filter { width: 100%; }
        .rs-fiscal { display: none; }
        .rs-filter .rs-select { flex: 1 1 40%; min-width: 0; }
        .rs-filter .rs-btn-dark { flex: 1 1 auto; justify-content: center; }
        .rs-kpi { padding: 1.1rem; }
        .rs-kpi-value { font-size: 1.9rem; }
        .rs-kpi-sub { flex-wrap: wrap; }
        .rs-kpi-foot { flex-direction: column; gap: .35rem; }
        .rs-section-title { flex-direction: column; align-items: flex-start; gap: .25rem; }
        .rs-section-title h2 { font-size: 1.15rem; }
        .rs-panel { padding: 1rem; }
        .rs-panel-head h3 { font-size: 1.1rem; }
        .rs-legend { flex-wrap: wrap; gap: .6rem; }
        .rs-chart-lg { height: 230px; }
        .rs-chart-sm { height: 170px; }
        .rs-chart-foot { flex-direction: column; gap: .25rem; }
        .rs-cat-row { grid-template-columns: 1fr auto 38px; gap: .5rem; }
        .rs-insight { padding: .85rem; }

        /* Tabelas viram cartões */
        .rs-scroll { max-height: 520px; padding-right: 0; }
        .rs-table, .rs-table tbody { display: block; width: 100%; }
        .rs-table thead { display: none; }
        .rs-table tbody tr {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: .35rem .75rem;
            padding: .8rem .85rem;
            margin-bottom: .5rem;
            border-radius: 12px;
            background: #f8fafc;
        }
        .rs-table tbody tr td {
            padding: 0; border: 0; background: transparent !important; border-radius: 0 !important;
            display: block;
        }
        .rs-table tr.linha-despesa + tr.linha-despesa td { border-top: 0; }
        .rs-table tr.urg-azul { background: #f1f6ff; }
        .rs-table tr.urg-amarelo { background: #fff8dc; }
        .rs-table tr.urg-vermelho { background: #fdecee; }
        .rs-table tr.urg-ok { background: #f2fbf6; }
        /* 1 descrição | 4 valor | 2 categoria/data | 3 status | 5 ações */
        .rs-table td:nth-child(1) { grid-column: 1; grid-row: 1; min-width: 0; }
        .rs-table td:nth-child(4) { grid-column: 2; grid-row: 1; align-self: start; }
        .rs-table td:nth-child(2) { grid-column: 1; grid-row: 2; font-size: .8rem; color: #475569; align-self: center; }
        .rs-table td:nth-child(3) { grid-column: 2; grid-row: 2; justify-self: end; }
        .rs-table td:nth-child(5) { grid-column: 1 / -1; grid-row: 3; text-align: right; }
        .rs-table .desc { align-items: flex-start; }
        .rs-table-foot { flex-direction: column; align-items: flex-start; gap: .6rem; }
        .rs-empty { min-height: 180px; }
    }
</style>
@endpush

@section('content')
<div class="rs">
    {{-- Barra de saudação e filtros --}}
    <form method="GET" action="{{ route('resumo') }}" class="rs-card rs-toolbar">
        <div class="rs-toolbar-left">
            <span class="rs-hello">
                <span class="dot"></span>
                Olá, {{ $user->name }}
                <span class="mono">Workspace Ativo</span>
            </span>
            <span class="rs-fiscal mono"><i data-lucide="calendar"></i> Exercício Fiscal Corrente</span>
        </div>

        <div class="rs-filter">
            <select name="mes" id="mes" class="rs-select" aria-label="Mês">
                @foreach ($nomesMesesCompletos as $i => $nome)
                    @php $opt = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT); @endphp
                    <option value="{{ $opt }}" {{ $mes == $opt ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
            <select name="ano" id="ano" class="rs-select" aria-label="Ano">
                @for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                    <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="rs-btn-dark"><i data-lucide="sliders-horizontal"></i> Filtrar</button>
            <button type="button" class="rs-btn-icon" onclick="window.print()" title="Imprimir / salvar em PDF"><i data-lucide="download"></i></button>
        </div>
    </form>

    @if ($mesAutomatico)
        <div class="rs-card rs-aviso">
            <i data-lucide="circle-check" style="color:#15803d"></i>
            Todas as contas do mês atual estão pagas — exibindo <strong>{{ $mesNome }} de {{ $ano }}</strong>.
            <a href="{{ route('resumo', ['mes' => now()->format('m'), 'ano' => now()->format('Y')]) }}" style="margin-left:.4rem">Ver mês atual</a>
        </div>
    @endif

    {{-- KPIs --}}
    <div id="resumo" class="rs-grid-3">
        <div class="rs-card rs-kpi">
            <div class="rs-kpi-head">
                <div class="rs-kpi-title"><span class="rs-ico blue"><i data-lucide="arrow-down"></i></span> Total Recebido</div>
                <button type="button" class="rs-eye" id="toggleValores" title="Mostrar/ocultar valores"><i data-lucide="eye"></i></button>
            </div>
            <div class="rs-kpi-value rs-secret">{{ $brl($totalReceitas) }}</div>
            <div class="rs-kpi-sub">
                @if ($variacaoReceitas !== null)
                    <span class="rs-chip {{ $variacaoReceitas >= 0 ? 'green' : 'red' }}">
                        <i data-lucide="{{ $variacaoReceitas >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                        {{ $variacaoReceitas >= 0 ? '+' : '' }}{{ number_format($variacaoReceitas, 0, ',', '.') }}% mês ant.
                    </span>
                    <span>vs. {{ $mesAnteriorNome }}</span>
                @else
                    <span class="rs-meta">Sem dados em {{ $mesAnteriorNome }} para comparar</span>
                @endif
            </div>
            <div class="rs-kpi-foot">
                <span>Previsão final: {{ $brl($totalReceitas) }}</span>
                <span class="ok">{{ number_format($pctReceitasLiquidado, 0, ',', '.') }}% liquidado</span>
            </div>
        </div>

        <div class="rs-card rs-kpi">
            <div class="rs-kpi-head">
                <div class="rs-kpi-title"><span class="rs-ico red"><i data-lucide="arrow-up"></i></span> Total Gasto</div>
                <span class="rs-meta rs-secret">Receita: {{ $brl($totalReceitas) }}</span>
            </div>
            <div class="rs-kpi-value rs-secret">{{ $brl($totalDespesas) }}</div>
            <div class="rs-kpi-sub">
                @if ($totalDespesas <= $totalReceitas)
                    <span class="rs-chip blue"><i data-lucide="circle-check"></i> Dentro da receita</span>
                @else
                    <span class="rs-chip red"><i data-lucide="circle-alert"></i> Acima da receita</span>
                @endif
                <span>{{ number_format($pctDespesasReceita, 1, ',', '.') }}% da receita</span>
            </div>
            <div class="rs-bar"><span style="width: {{ $pctGasto }}%"></span></div>
        </div>

        <div class="rs-card rs-kpi">
            <div class="rs-kpi-head">
                <div class="rs-kpi-title"><span class="rs-ico green"><i data-lucide="wallet"></i></span> Saldo Atual</div>
                <span class="rs-chip {{ $saldo >= 0 ? 'green' : 'red' }}">{{ $saldo >= 0 ? 'Positivo' : 'Negativo' }}</span>
            </div>
            <div class="rs-kpi-value rs-secret {{ $saldo >= 0 ? 'green' : '' }}" @if($saldo < 0) style="color:#dc2626" @endif>{{ $brl($saldo) }}</div>
            <div class="rs-kpi-sub" style="color: {{ $saldo >= 0 ? '#16a34a' : '#dc2626' }}; font-weight: 500;">
                <i data-lucide="{{ $saldo >= 0 ? 'piggy-bank' : 'triangle-alert' }}"></i>
                {{ $saldo >= 0 ? 'Disponível para aplicações' : 'Gastos acima das receitas' }}
            </div>
            <div class="rs-kpi-foot">
                <span>Saldo realizado: {{ $brl($saldoRealizado) }}</span>
                <span class="strong">Taxa Poupança: {{ number_format($taxaPoupanca, 1, ',', '.') }}%</span>
            </div>
        </div>
    </div>

    {{-- Leitura do ano --}}
    <div id="graficos" class="rs-section-title">
        <h2><i data-lucide="chart-line"></i> Gráficos e leitura do ano {{ $ano }}</h2>
        <span class="rs-meta">Consolidado em {{ now()->format('d/m/Y') }}</span>
    </div>

    <div class="rs-grid-4">
        <div class="rs-card rs-insight">
            <span class="rs-ico red"><i data-lucide="trending-up"></i></span>
            <div>
                <div class="label">Maior gasto registrado</div>
                <div class="value">{{ $mesMaiorGasto['label'] }} • {{ $brl($mesMaiorGasto['despesas']) }}</div>
                <div class="hint red">Pico de despesas</div>
            </div>
        </div>
        <div class="rs-card rs-insight">
            <span class="rs-ico green"><i data-lucide="trending-down"></i></span>
            <div>
                <div class="label">Menor gasto registrado</div>
                <div class="value">{{ $mesMenorGasto['label'] }} • {{ $brl($mesMenorGasto['despesas']) }}</div>
                <div class="hint green">Mês de menor consumo</div>
            </div>
        </div>
        <div class="rs-card rs-insight">
            <span class="rs-ico slate"><i data-lucide="clock"></i></span>
            <div>
                <div class="label">Receitas pendentes</div>
                <div class="value">{{ $brl($receitasPendentes) }}</div>
                <div class="hint slate">{{ $receitasPendentesCount }} {{ $receitasPendentesCount === 1 ? 'entrada prevista' : 'entradas previstas' }}</div>
            </div>
        </div>
        <div class="rs-card rs-insight">
            <span class="rs-ico green"><i data-lucide="circle-check"></i></span>
            <div>
                <div class="label">Despesas pendentes</div>
                <div class="value">{{ $brl($despesasPendentes) }}</div>
                @if ($despesasPendentesCount === 0)
                    <div class="hint green">Tudo quitado</div>
                @else
                    <div class="hint red">{{ $despesasPendentesCount }} {{ $despesasPendentesCount === 1 ? 'conta em aberto' : 'contas em aberto' }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Evolução mensal --}}
    <div class="rs-card rs-panel">
        <div class="rs-panel-head">
            <div>
                <h3>Evolução Mensal: Jan a Dez {{ $ano }} <span class="rs-tag">Anual</span></h3>
                <div class="sub">Comparativo de fluxo de caixa entre receitas, despesas e margem líquida retida</div>
            </div>
            <div class="rs-legend">
                <span><i style="background:#16a36a"></i>Entradas</span>
                <span><i style="background:#e11d48"></i>Saídas</span>
                <span><i style="background:#0a1222"></i>Saldo</span>
            </div>
        </div>
        <div class="rs-chart-lg"><canvas id="fluxoMensalChart"></canvas></div>
    </div>

    <div class="rs-grid-2">
        {{-- Categorias --}}
        <div class="rs-card rs-panel">
            <div class="rs-panel-head">
                <div>
                    <h3>Gasto por <span id="agrupamentoTitulo">Categoria</span></h3>
                    <div class="sub">Distribuição percentual em {{ $mesNome }} {{ $ano }}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <span class="rs-meta">Total: {{ $brl($totalDespesas) }}</span>
                    <div class="rs-tabs" role="tablist">
                        <button type="button" class="active" data-agrupar="categoria">Categoria</button>
                        <button type="button" data-agrupar="origem">Origem</button>
                    </div>
                </div>
            </div>
            <div class="rs-donut-wrap">
                <div class="rs-donut">
                    <canvas id="categoriasChart"></canvas>
                    <div class="rs-donut-center">
                        <strong id="donutQtd">{{ $categoryStats->count() }}</strong>
                        <span>Grupos</span>
                    </div>
                </div>
                <div class="rs-cat-list" id="legendaCategoria">
                    @forelse ($categoryStats as $i => $cat)
                        <div class="rs-cat-row">
                            <span class="name"><i style="background: {{ $coresCategorias[$i % count($coresCategorias)] }}"></i>{{ $cat['categoria'] }}</span>
                            <span class="val">{{ $brl($cat['total']) }}</span>
                            <span class="pct">{{ number_format(($cat['total'] / $totalCategorias) * 100, 0) }}%</span>
                        </div>
                    @empty
                        <div class="text-muted">Sem gastos por categoria neste mês.</div>
                    @endforelse
                </div>
                <div class="rs-cat-list" id="legendaOrigem" style="display:none">
                    @php $totalOrigens = max($origemStats->sum('total'), 0.01); @endphp
                    @forelse ($origemStats as $i => $org)
                        <div class="rs-cat-row">
                            <span class="name"><i style="background: {{ $coresCategorias[$i % count($coresCategorias)] }}"></i>{{ $org['origem'] }}</span>
                            <span class="val">{{ $brl($org['total']) }}</span>
                            <span class="pct">{{ number_format(($org['total'] / $totalOrigens) * 100, 0) }}%</span>
                        </div>
                    @empty
                        <div class="text-muted">Sem gastos neste mês.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Gasto por mês --}}
        <div class="rs-card rs-panel">
            <div class="rs-panel-head">
                <div>
                    <h3>Gasto em Cada Mês</h3>
                    <div class="sub">Série histórica de despesas mensais em R$</div>
                </div>
                <span class="rs-meta" style="color:#16a34a;font-weight:600">Média: {{ $brl($mediaGastoMensal) }}</span>
            </div>
            <div class="rs-chart-sm"><canvas id="gastosMensaisChart"></canvas></div>
            <div class="rs-chart-foot">
                <span>Menor: {{ $mesMenorGasto['label'] }} ({{ $brl($mesMenorGasto['despesas']) }})</span>
                <span>Mês Selecionado: {{ $mesNome }} ({{ $brl($totalDespesas) }})</span>
            </div>
        </div>
    </div>

    {{-- Tabelas --}}
    <div class="rs-grid-2">
        <div id="receitas" class="rs-card rs-panel d-flex flex-column">
            <div class="rs-table-head">
                <h3><span class="rs-ico slate"><i data-lucide="banknote"></i></span> Tabela de Receitas</h3>
                <span class="rs-meta">{{ $receitas->count() }} {{ $receitas->count() === 1 ? 'registro' : 'registros' }}</span>
            </div>

            <div class="rs-table-box rs-scroll flex-grow-1">
                @if ($receitas->isEmpty())
                    <div class="rs-empty">
                        <span class="badge-ico"><i data-lucide="inbox"></i></span>
                        <strong>Nenhuma receita neste mês</strong>
                        <p>Cadastre uma entrada para acompanhar o fluxo de {{ $mesNome }} de {{ $ano }}.</p>
                    </div>
                @else
                    <table class="rs-table">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th style="text-align:right">Valor</th>
                                <th style="text-align:right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receitas as $receita)
                                @php $pago = $receita->data ? $receita->pago : (bool) ($receitasPagas[$receita->id] ?? false); @endphp
                                <tr>
                                    <td><span class="desc"><i data-lucide="briefcase"></i>{{ $receita->descricao ?? '-' }}</span></td>
                                    <td class="mono" style="font-size:.75rem">{{ $receita->data ? $receita->data->format('d/m/Y') : 'Mensal' }}</td>
                                    <td>
                                        <label class="rs-status {{ $pago ? 'pago' : 'pend' }}" title="Marque quando o valor for recebido">
                                            <input type="checkbox" class="marcar-pago" {{ $pago ? 'checked' : '' }}
                                                   data-id="{{ $receita->id }}" data-type="receitas" data-pago="{{ $pago ? 1 : 0 }}">
                                            <span class="label-status">{{ $pago ? 'Recebida' : 'Pendente' }}</span>
                                        </label>
                                    </td>
                                    <td class="num">{{ $brl($receita->valor) }}</td>
                                    <td class="act">
                                        <a href="{{ route('receitas.edit', $receita->id) }}" title="Editar"><i data-lucide="pencil"></i></a>
                                        <form action="{{ route('receitas.destroy', $receita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta receita?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Excluir"><i data-lucide="trash-2"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="rs-table-foot">
                <span>Total liquidado: {{ $brl($receitasPagasTotal) }}</span>
                <a href="{{ route('receitas.create') }}" class="green"><i data-lucide="plus"></i> Adicionar entrada</a>
            </div>
        </div>

        <div id="despesas" class="rs-card rs-panel d-flex flex-column">
            <div class="rs-table-head">
                <h3><span class="rs-ico slate"><i data-lucide="receipt"></i></span> Tabela de Despesas</h3>
                <span class="rs-meta" style="{{ $despesasPendentesCount === 0 ? 'color:#16a34a' : 'color:#b91c1c' }}">
                    {{ $despesasPendentesCount }} {{ $despesasPendentesCount === 1 ? 'pendência' : 'pendências' }}
                </span>
            </div>

            <div class="rs-table-box rs-scroll flex-grow-1">
                @if (!$temDespesas)
                    <div class="rs-empty">
                        <span class="badge-ico"><i data-lucide="inbox"></i></span>
                        <strong>Nenhuma despesa lançada</strong>
                        <p>Não há saídas registradas para {{ $mesNome }} de {{ $ano }}.</p>
                    </div>
                @else
                    <table id="despesasTabela" class="rs-table">
                        <thead>
                            <tr>
                                <th>Descrição</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th style="text-align:right">Valor</th>
                                <th style="text-align:right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($despesas as $despesa)
                                @php
                                    $vence = $despesa->data_exibida ? $despesa->data_exibida->copy()->startOfDay() : null;
                                    $dias = $vence ? (int) now()->startOfDay()->diffInDays($vence, false) : null;
                                    if ($despesa->pago_mes) {
                                        $urg = 'urg-ok'; $urgTexto = 'paga';
                                    } elseif ($dias === null) {
                                        $urg = 'urg-azul'; $urgTexto = '';
                                    } elseif ($dias < 0) {
                                        $urg = 'urg-vermelho'; $urgTexto = 'atrasada há ' . abs($dias) . ($dias === -1 ? ' dia' : ' dias');
                                    } elseif ($dias === 0) {
                                        $urg = 'urg-vermelho'; $urgTexto = 'vence hoje';
                                    } elseif ($dias <= 5) {
                                        $urg = 'urg-amarelo'; $urgTexto = 'vence em ' . $dias . ($dias === 1 ? ' dia' : ' dias');
                                    } else {
                                        $urg = 'urg-azul'; $urgTexto = 'vence em ' . $dias . ' dias';
                                    }
                                @endphp
                                <tr class="linha-despesa {{ $urg }}" data-pago="{{ $despesa->pago_mes ? 1 : 0 }}">
                                    <td>
                                        <span class="desc out"><i data-lucide="receipt-text"></i>
                                            <span>{{ $despesa->descricao ?? '-' }}<small>{{ $despesa->data_exibida ? $despesa->data_exibida->format('d/m/Y') : '-' }}@if($despesa->recorrente) • recorrente @endif @if($urgTexto) • {{ $urgTexto }} @endif</small></span>
                                        </span>
                                    </td>
                                    <td>{{ $despesa->category->name ?? '-' }}@if($despesa->origem)<small class="d-block" style="color:#64748b;font-size:.7rem">{{ $despesa->origem }}</small>@endif</td>
                                    <td>
                                        <label class="rs-status {{ $despesa->pago_mes ? 'pago' : 'pend' }}" title="Marque quando a conta for paga">
                                            <input type="checkbox" class="marcar-pago" {{ $despesa->pago_mes ? 'checked' : '' }}
                                                   data-id="{{ $despesa->id }}" data-type="despesas" data-pago="{{ $despesa->pago_mes ? 1 : 0 }}">
                                            <span class="label-status">{{ $despesa->pago_mes ? 'Paga' : 'Pendente' }}</span>
                                        </label>
                                    </td>
                                    <td class="num">{{ $brl($despesa->valor) }}</td>
                                    <td class="act">
                                        <a href="{{ route('despesas.edit', $despesa->id) }}" title="Editar"><i data-lucide="pencil"></i></a>
                                        <form action="{{ route('despesas.destroy', $despesa->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta despesa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Excluir"><i data-lucide="trash-2"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="rs-table-foot">
                <span>Status da carteira: {{ $saldo >= 0 ? 'Saudável' : 'Atenção' }}</span>
                <span class="d-flex gap-3 align-items-center">
                    <a href="{{ route('despesas.create') }}"><i data-lucide="plus"></i> Lançar nova despesa</a>
                </span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const chartData = @json($chartData);
    const mesesCompletos = @json($nomesMesesCompletos);
    const selecionado = chartData.mesSelecionado;
    const money = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
    const mono = { family: "'JetBrains Mono', monospace", size: 10 };

    // Cores dos gráficos conforme o tema (a troca recarrega a página para redesenhar)
    const dark = document.documentElement.dataset.theme === 'dark';
    const ink = dark ? '#e6edf7' : '#0a1222';
    const gridColor = dark ? '#22304a' : '#eef1f6';
    const barOutra = dark ? '#2a3a57' : '#d7e2f4';
    const corSemDados = dark ? '#22304a' : '#e8edf5';
    window.addEventListener('themechange', () => window.location.reload());

    if (window.Chart) {
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = dark ? '#9fb0c8' : '#64748b';
    }

    // Evolução mensal
    const fluxo = document.getElementById('fluxoMensalChart');
    if (fluxo && window.Chart) {
        const ctx = fluxo.getContext('2d');
        const grad = (rgb) => {
            const g = ctx.createLinearGradient(0, 0, 0, 300);
            g.addColorStop(0, `rgba(${rgb}, 0.28)`);
            g.addColorStop(1, `rgba(${rgb}, 0)`);
            return g;
        };
        const pontos = (cor) => chartData.labels.map((_, i) => (i === selecionado ? 5 : 0));
        const mk = (label, data, cor, rgb, fill) => ({
            label, data, borderColor: cor, borderWidth: 2.5, tension: 0.4,
            fill: fill ? true : false, backgroundColor: fill ? grad(rgb) : undefined,
            pointRadius: pontos(), pointHoverRadius: 6, pointBackgroundColor: cor, pointBorderColor: cor,
        });
        new Chart(fluxo, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    mk('Entradas', chartData.receitas, '#16a36a', '22, 163, 106', true),
                    mk('Saídas', chartData.despesas, '#c0161c', '192, 22, 28', true),
                    mk('Saldo', chartData.saldo, ink, '10, 18, 34', false),
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${money(c.parsed.y)}` } },
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: mono } },
                    y: {
                        border: { display: false },
                        grid: { color: gridColor, borderDash: [4, 4] },
                        ticks: { display: false },
                    },
                },
            },
        });
    }

    // Categorias
    const cat = document.getElementById('categoriasChart');
    if (cat && window.Chart) {
        const cores = @json($coresCategorias).map((c) => (c === '#0a1222' ? ink : c));
        const grupos = {
            categoria: { labels: chartData.categoriasLabels, valores: chartData.categoriasValores },
            origem: { labels: chartData.origemLabels, valores: chartData.origemValores },
        };
        const dadosDonut = (g) => {
            const tem = grupos[g].valores.length > 0;
            return {
                labels: tem ? grupos[g].labels : ['Sem gastos'],
                data: tem ? grupos[g].valores : [1],
                cores: tem ? cores : [corSemDados],
                tem,
            };
        };
        const inicial = dadosDonut('categoria');
        const donut = new Chart(cat, {
            type: 'doughnut',
            data: {
                labels: inicial.labels,
                datasets: [{ data: inicial.data, backgroundColor: inicial.cores, borderWidth: 0 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (c) => `${c.label}: ${money(c.parsed)}` } },
                },
            },
        });

        document.querySelectorAll('.rs-tabs button').forEach((botao) => {
            botao.addEventListener('click', () => {
                const g = botao.dataset.agrupar;
                const d = dadosDonut(g);
                donut.data.labels = d.labels;
                donut.data.datasets[0].data = d.data;
                donut.data.datasets[0].backgroundColor = d.cores;
                donut.update();

                document.querySelectorAll('.rs-tabs button').forEach((b) => b.classList.toggle('active', b === botao));
                document.getElementById('legendaCategoria').style.display = g === 'categoria' ? '' : 'none';
                document.getElementById('legendaOrigem').style.display = g === 'origem' ? '' : 'none';
                document.getElementById('agrupamentoTitulo').textContent = g === 'categoria' ? 'Categoria' : 'Origem';
                document.getElementById('donutQtd').textContent = d.tem ? grupos[g].valores.length : 0;
            });
        });
    }

    // Gasto por mês
    const gastos = document.getElementById('gastosMensaisChart');
    if (gastos && window.Chart) {
        new Chart(gastos, {
            type: 'bar',
            data: {
                labels: chartData.labels.map((l) => l.charAt(0)),
                datasets: [{
                    label: 'Gastos',
                    data: chartData.despesas,
                    backgroundColor: chartData.labels.map((_, i) => (i === selecionado ? ink : barOutra)),
                    borderRadius: 6,
                    maxBarThickness: 22,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => mesesCompletos[items[0].dataIndex],
                            label: (c) => money(c.parsed.y),
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false }, border: { display: false },
                        ticks: { font: (c) => ({ ...mono, weight: c.index === selecionado ? '700' : '400' }) },
                    },
                    y: { display: false, beginAtZero: true },
                },
            },
        });
    }

    // Mostrar/ocultar valores
    const eye = document.getElementById('toggleValores');
    eye?.addEventListener('click', () => {
        document.querySelectorAll('.rs-secret').forEach((el) => el.classList.toggle('is-hidden'));
    });

    // Marcar como pago
    document.querySelectorAll('.marcar-pago').forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const pago = this.dataset.pago !== '1';
            const periodo = `${document.getElementById('ano').value}-${document.getElementById('mes').value}`;

            if (!csrfToken || !id || !type) return;

            this.disabled = true;

            fetch(`{{ url('/') }}/${type}/${id}/pagar`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ pago, mes: periodo }),
            })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) throw new Error(data.error || 'Erro ao atualizar pagamento.');
                    // recarrega para atualizar totais, contadores e gráficos
                    window.location.reload();
                })
                .catch((err) => {
                    alert(err.message);
                    this.checked = this.dataset.pago === '1';
                    this.disabled = false;
                });
        });
    });
});
</script>
@endsection
