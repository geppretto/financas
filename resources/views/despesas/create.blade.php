@extends('layouts.app')

@section('content_class', 'content-wide')
@section('nav_active', 'saidas')

@php
    $brl = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $mesesNomes = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $mesNome = $mesesNomes[(int) $mes - 1];
    $pctBarra = min(max($pctUsado, 0), 100);
    $coresTop = ['#b91c1c', '#0a1222', '#4b5568'];
    $totalGastos = max($gastosPorCategoria->sum(), 0.01);
    $totalTop = max($maioresGastos->sum(), 0.01);
@endphp

@push('styles')
<style>
    .nd { color: #0a1222; }
    .nd .mono { font-family: 'JetBrains Mono', monospace; }
    .nd svg.lucide, .nd [data-lucide] { width: 18px; height: 18px; }
    .nd-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.75rem; }
    .nd-crumb { display: flex; align-items: center; gap: .6rem; font-size: .85rem; color: #1e293b; margin-bottom: .6rem; }
    .nd-crumb a { color: inherit; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem; font-weight: 500; }
    .nd-crumb .sep { color: #94a3b8; }
    .nd-tag-red { font-family: 'JetBrains Mono', monospace; font-size: .68rem; background: #fde8ea; color: #b91c1c; border-radius: 6px; padding: .2rem .55rem; }
    .nd-title { font-size: 2.3rem; font-weight: 500; letter-spacing: -0.035em; margin: 0; line-height: 1.15; }
    .nd-sub { color: #334155; margin: .4rem 0 0; font-size: .95rem; }
    .nd-budget { display: inline-flex; align-items: center; gap: .6rem; background: #e8f0fe; border-radius: 8px; padding: .55rem .9rem; font-family: 'JetBrains Mono', monospace; font-size: .72rem; color: #1e293b; }
    .nd-budget i { width: 8px; height: 8px; border-radius: 50%; background: #b91c1c; }

    .nd-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .nd-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, .05); border: 1px solid #eef1f6; }
    .nd-form { padding: 3rem 3.1rem 2.2rem; }

    .nd-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e8f0fe; margin-bottom: 1.6rem; flex-wrap: wrap; }
    .nd-head-left { display: flex; align-items: center; gap: .9rem; }
    .nd-ico-red { width: 40px; height: 40px; border-radius: 10px; background: #fde8ea; color: #dc2626; display: grid; place-items: center; }
    .nd-head h2 { font-size: 1.25rem; font-weight: 500; letter-spacing: -0.02em; margin: 0; }
    .nd-head p { margin: 0; color: #334155; font-size: .85rem; }
    .nd-chip { font-family: 'JetBrains Mono', monospace; font-size: .68rem; background: #eaf1fe; color: #334155; border-radius: 6px; padding: .35rem .7rem; }

    .nd-label { display: block; font-size: .95rem; font-weight: 500; color: #0a1222; margin-bottom: .6rem; }
    .nd-label .req { color: #dc2626; }
    .nd-field { background: #fff; border: 0; border-radius: 10px; box-shadow: 0 1px 3px rgba(15, 23, 42, .07); display: flex; align-items: center; gap: .6rem; padding: 0 1rem; min-height: 52px; }
    .nd-field:focus-within { box-shadow: 0 0 0 2px #93b4f5; }
    .nd-field input, .nd-field select, .nd-field textarea { border: 0; outline: 0; background: transparent; width: 100%; font-size: 1rem; color: #0a1222; padding: .7rem 0; font-family: inherit; }
    .nd-field select { appearance: none; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230a1222' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right center; padding-right: 1.6rem; }
    .nd-field .prefix { font-family: 'JetBrains Mono', monospace; font-size: .85rem; color: #475569; }
    .nd-field .valor-input { font-family: 'JetBrains Mono', monospace; font-weight: 500; font-size: 1.1rem; }
    .nd-field > svg { color: #475569; flex: 0 0 auto; }
    .nd-field.area { align-items: flex-start; padding-top: .4rem; }
    .nd-grid2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.4rem; margin-top: 1.7rem; }
    .nd-grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.4rem; margin-top: 1.7rem; }
    .nd-invalid { color: #b91c1c; font-size: .8rem; margin-top: .35rem; }

    .nd-parcelas { margin-top: 1.7rem; background: #eaf1fe; border-radius: 12px; padding: 1.1rem 1.3rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .nd-parcelas label { display: flex; align-items: center; gap: .8rem; margin: 0; font-size: 1.05rem; font-weight: 500; cursor: pointer; }
    .nd-parcelas input[type=checkbox] { width: 20px; height: 20px; accent-color: #0a1222; border-radius: 4px; }
    .nd-parcelas .hint { font-family: 'JetBrains Mono', monospace; font-size: .7rem; color: #334155; letter-spacing: .02em; }
    .nd-parcelas-box { margin-top: .75rem; padding: 1.1rem; border-radius: 12px; background: #f6f9ff; }
    .nd-parcelas-box .nd-label { font-size: .85rem; }

    .nd-status { margin-top: 1.7rem; padding: 1.2rem 1.3rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; box-shadow: 0 1px 3px rgba(15, 23, 42, .05); }
    .nd-status h4 { font-size: 1.05rem; font-weight: 500; margin: 0; }
    .nd-status p { margin: .1rem 0 0; font-size: .85rem; color: #334155; }
    .nd-seg { display: inline-flex; background: #e1ebfc; border-radius: 10px; padding: 5px; gap: 4px; }
    .nd-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .nd-seg label { display: inline-flex; align-items: center; gap: .5rem; padding: .55rem 1rem; border-radius: 8px; font-size: .95rem; color: #334155; cursor: pointer; margin: 0; }
    .nd-seg label svg { width: 18px; height: 18px; }
    .nd-seg input:checked + label { background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
    .nd-seg input[value="1"]:checked + label { color: #059669; }
    .nd-seg input[value="0"]:checked + label { color: #a16207; }

    .nd-notes { margin-top: 1.7rem; }
    .nd-notes-head { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .6rem; }
    .nd-notes-head .nd-label { margin: 0; }
    .nd-notes-head .mono { font-size: .7rem; color: #334155; letter-spacing: .04em; }
    .nd-actions { margin-top: 1.7rem; padding-top: 1.1rem; border-top: 1px solid #e8edf5; display: flex; justify-content: flex-end; gap: .75rem; flex-wrap: wrap; }
    .nd-btn { border: 0; border-radius: 10px; padding: .85rem 1.5rem; font-size: 1rem; font-weight: 500; display: inline-flex; align-items: center; gap: .6rem; text-decoration: none; cursor: pointer; }
    .nd-btn.light { background: #e1ebfc; color: #0a1222; }
    .nd-btn.red { background: #b91c1c; color: #fff; padding: .85rem 1.7rem; box-shadow: 0 2px 6px rgba(185, 28, 28, .35); }
    .nd-btn.red:hover { background: #991b1b; color: #fff; }

    .nd-overview { margin-top: 1.5rem; padding: 1.2rem 1.25rem 1.25rem; }
    .nd-overview-head { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: .72rem; letter-spacing: .12em; margin-bottom: .9rem; gap: 1rem; flex-wrap: wrap; }
    .nd-overview-head .r { letter-spacing: .02em; font-weight: 500; }
    .nd-overview-body { background: #eef1f5; border-radius: 10px; padding: 1.4rem 1.5rem; min-height: 200px; display: grid; gap: .9rem; align-content: start; }
    .nd-bar-row { display: grid; grid-template-columns: 150px 1fr 110px; gap: 1rem; align-items: center; font-size: .85rem; }
    .nd-bar-row .track { height: 8px; border-radius: 999px; background: #dbe3ef; overflow: hidden; }
    .nd-bar-row .track span { display: block; height: 100%; background: #b91c1c; border-radius: 999px; }
    .nd-bar-row .v { font-family: 'JetBrains Mono', monospace; font-size: .75rem; text-align: right; }

    .nd-side { display: grid; gap: 1.5rem; }
    .nd-side-card { padding: 1.9rem 1.9rem; }
    .nd-side-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.4rem; }
    .nd-side-title h3 { font-size: 1.3rem; font-weight: 500; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .6rem; }
    .nd-side-title .mono { font-size: .72rem; color: #475569; letter-spacing: .06em; }
    .nd-acc { display: flex; justify-content: space-between; align-items: baseline; font-family: 'JetBrains Mono', monospace; font-size: .85rem; letter-spacing: .06em; }
    .nd-acc strong { font-size: 1.35rem; color: #b91c1c; font-weight: 600; letter-spacing: 0; }
    .nd-acc strong.ok { color: #059669; }
    .nd-progress { height: 12px; background: #dde7f9; border-radius: 999px; overflow: hidden; margin: .5rem 0 .55rem; }
    .nd-progress span { display: block; height: 100%; background: #b91c1c; border-radius: 999px; }
    .nd-progress-foot { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: .72rem; color: #334155; letter-spacing: .04em; }
    .nd-tip { margin-top: 1.4rem; background: #e8f0fe; border-radius: 12px; padding: 1rem 1.1rem; display: flex; gap: .8rem; }
    .nd-tip svg { color: #334155; flex: 0 0 auto; margin-top: .15rem; }
    .nd-tip strong { font-weight: 500; font-size: 1rem; display: block; }
    .nd-tip p { margin: .2rem 0 0; font-size: .9rem; color: #334155; line-height: 1.45; }

    .nd-donut-row { display: flex; align-items: center; gap: 1.6rem; }
    .nd-donut { position: relative; width: 112px; height: 112px; flex: 0 0 112px; }
    .nd-donut-center { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; pointer-events: none; }
    .nd-donut-center strong { font-size: .8rem; font-weight: 600; }
    .nd-donut-center span { font-family: 'JetBrains Mono', monospace; font-size: .55rem; color: #475569; }
    .nd-legend { flex: 1; display: grid; gap: .55rem; font-size: .9rem; }
    .nd-legend div { display: flex; align-items: center; justify-content: space-between; gap: .6rem; }
    .nd-legend .l { display: flex; align-items: center; gap: .55rem; }
    .nd-legend i { width: 9px; height: 9px; border-radius: 50%; }
    .nd-legend .p { font-size: .85rem; color: #0a1222; }

    .nd-last-item { display: flex; align-items: center; gap: .9rem; padding: .7rem 0; border-bottom: 1px solid #eef1f6; }
    .nd-last-item:last-child { border-bottom: 0; }
    .nd-last-ico { width: 44px; height: 44px; border-radius: 10px; background: #e8f0fe; color: #0a1222; display: grid; place-items: center; flex: 0 0 auto; }
    .nd-last-main { flex: 1; min-width: 0; }
    .nd-last-main .n { font-size: .95rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .nd-last-main .m { font-family: 'JetBrains Mono', monospace; font-size: .7rem; color: #334155; letter-spacing: .03em; }
    .nd-last-val { font-family: 'JetBrains Mono', monospace; font-size: .85rem; color: #b91c1c; white-space: nowrap; }

    @media (max-width: 1100px) {
        .nd-layout { grid-template-columns: 1fr; }
        .nd-form { padding: 1.5rem; }
        .nd-grid3, .nd-grid2 { grid-template-columns: 1fr; }
        .nd-bar-row { grid-template-columns: 110px 1fr 90px; }
    }

    @media (max-width: 640px) {
        .nd-title { font-size: 1.8rem; }
        .nd-form { padding: 1.1rem; }
        .nd-head { flex-direction: column; align-items: flex-start; }
        .nd-status { flex-direction: column; align-items: stretch; }
        .nd-seg { display: flex; }
        .nd-seg label { flex: 1; justify-content: center; padding: .6rem .4rem; }
        .nd-parcelas { flex-direction: column; align-items: flex-start; }
        .nd-actions { flex-direction: column-reverse; }
        .nd-actions .nd-btn { justify-content: center; width: 100%; }
        .nd-bar-row { grid-template-columns: 90px 1fr 80px; gap: .5rem; font-size: .78rem; }
        .nd-side-card { padding: 1.25rem; }
        .nd-donut-row { flex-direction: column; align-items: flex-start; }
        .nd-budget { font-size: .66rem; }
    }
</style>
@endpush

@section('content')
<div class="nd">
    <div class="nd-top">
        <div>
            <div class="nd-crumb">
                <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}#despesas"><i data-lucide="arrow-left"></i> Voltar para Saídas</a>
                <span class="sep">/</span>
                <span class="nd-tag-red">Débito em Conta</span>
            </div>
            <h1 class="nd-title">Nova despesa</h1>
            <p class="nd-sub">Lance uma saída simples ou divida em parcelas recorrentes.</p>
        </div>
        <span class="nd-budget"><i></i> Gasto {{ $mesNome }}: {{ number_format($pctUsado, 0, ',', '.') }}% da receita</span>
    </div>

    <div class="nd-layout">
        <div>
            <div class="nd-card nd-form">
                <form action="{{ route('despesas.store') }}" method="POST">
                    @csrf

                    <div class="nd-head">
                        <div class="nd-head-left">
                            <span class="nd-ico-red"><i data-lucide="banknote"></i></span>
                            <div>
                                <h2>Dados da despesa</h2>
                                <p>Preencha as informações essenciais para a conciliação patrimonial.</p>
                            </div>
                        </div>
                        <span class="nd-chip">Campos obrigatórios *</span>
                    </div>

                    <div>
                        <label for="descricao" class="nd-label">Descrição da saída <span class="req">*</span></label>
                        <div class="nd-field">
                            <i data-lucide="text-cursor-input"></i>
                            <input type="text" id="descricao" name="descricao" value="{{ old('descricao') }}" maxlength="255" placeholder="Ex.: Supermercado" required>
                        </div>
                        @error('descricao')<div class="nd-invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="nd-grid2">
                        <div>
                            <label for="category_id" class="nd-label">Categoria</label>
                            <div class="nd-field">
                                <select id="category_id" name="category_id">
                                    <option value="">Sem categoria</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('category_id')<div class="nd-invalid">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="origem" class="nd-label">Origem</label>
                            <div class="nd-field">
                                <input type="text" id="origem" name="origem" maxlength="100" list="origensLista" autocomplete="off"
                                       placeholder="Ex: Shopee, Nubank, Jeitto" value="{{ old('origem') }}">
                                <datalist id="origensLista">
                                    @foreach ($origens as $origemSugestao)
                                        <option value="{{ $origemSugestao }}">
                                    @endforeach
                                </datalist>
                            </div>
                            <div class="form-text mt-1" style="font-size:.75rem;color:#475569">De onde vem o gasto. A categoria diz o <strong>tipo</strong> (Empréstimo, Crédito); a origem diz <strong>qual</strong>.</div>
                            @error('origem')<div class="nd-invalid">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="nd-grid2">
                        <div>
                            <label for="valor" class="nd-label">Valor (R$) <span class="req">*</span></label>
                            <div class="nd-field">
                                <span class="prefix">R$</span>
                                <input type="number" step="0.01" min="0.01" class="valor-input" id="valor" name="valor" value="{{ old('valor') }}" placeholder="0,00" required>
                            </div>
                            <div class="form-text mt-1" style="font-size:.75rem;color:#475569">Em parcelas iguais, é o valor de <strong>cada</strong> parcela.</div>
                            @error('valor')<div class="nd-invalid">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="data" class="nd-label">Data de Competência <span class="req">*</span></label>
                            <div class="nd-field">
                                <input type="date" id="data" name="data" value="{{ old('data', now()->toDateString()) }}" required>
                            </div>
                            @error('data')<div class="nd-invalid">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="nd-parcelas">
                        <label for="tem_parcelas">
                            <input type="checkbox" id="tem_parcelas" name="tem_parcelas" {{ old('tem_parcelas') ? 'checked' : '' }}>
                            Dividir em parcelas
                        </label>
                        <span class="hint">Despesa a prazo / Recorrente</span>
                    </div>

                    <div class="nd-parcelas" style="margin-top:.75rem">
                        <label for="recorrente">
                            <input type="checkbox" id="recorrente" name="recorrente" value="1" {{ old('recorrente') ? 'checked' : '' }}>
                            Despesa recorrente (todo mês)
                        </label>
                        <span class="hint" id="recorrenteHint">Assinaturas, aluguel, mensalidades</span>
                    </div>

                    <div id="parcelas_opcoes" class="nd-parcelas-box {{ old('tem_parcelas') ? '' : 'd-none' }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="quantidade_parcelas" class="nd-label">Quantidade de parcelas</label>
                                <div class="nd-field">
                                    <input type="number" id="quantidade_parcelas" name="quantidade_parcelas" min="2" max="120" value="{{ old('quantidade_parcelas') }}">
                                </div>
                                @error('quantidade_parcelas')<div class="nd-invalid">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label for="parcelas_iguais" class="nd-label">Parcelas com o mesmo valor?</label>
                                <div class="nd-field">
                                    <select id="parcelas_iguais" name="parcelas_iguais">
                                        <option value="">Selecione</option>
                                        <option value="1" {{ old('parcelas_iguais') === '1' ? 'selected' : '' }}>Sim</option>
                                        <option value="0" {{ old('parcelas_iguais') === '0' ? 'selected' : '' }}>Não</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="parcelas_pagas" class="nd-label">Parcelas já pagas</label>
                                <div class="nd-field">
                                    <input type="number" id="parcelas_pagas" name="parcelas_pagas" min="0" max="120" value="{{ old('parcelas_pagas', 0) }}">
                                </div>
                                <div class="form-text mt-1" style="font-size:.75rem;color:#475569">As primeiras parcelas, em ordem, entram como pagas; as demais ficam pendentes.</div>
                                @error('parcelas_pagas')<div class="nd-invalid">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div id="parcelas_diferentes_campos" class="nd-parcelas-box d-none">
                        <div class="alert alert-info mb-3">Informe o valor e a data de cada parcela.</div>
                        <div id="parcelas_campos" class="row g-3"></div>
                    </div>

                    <div class="nd-status" id="statusLiquidacao">
                        <div>
                            <h4>Status da liquidação</h4>
                            <p>Marque como pago se o valor já foi debitado do saldo real.</p>
                        </div>
                        <div class="nd-seg" role="radiogroup" aria-label="Status da liquidação">
                            <input type="radio" name="pago" id="pago_sim" value="1" {{ old('pago') === '1' ? 'checked' : '' }}>
                            <label for="pago_sim"><i data-lucide="circle-check"></i> Já foi pago</label>
                            <input type="radio" name="pago" id="pago_nao" value="0" {{ old('pago', '0') === '0' ? 'checked' : '' }}>
                            <label for="pago_nao"><i data-lucide="clock"></i> Pendente</label>
                        </div>
                    </div>

                    <div class="nd-notes">
                        <div class="nd-notes-head">
                            <label for="notas" class="nd-label">Notas adicionais (opcional)</label>
                            <span class="mono">Tags ou comprovantes</span>
                        </div>
                        <div class="nd-field area">
                            <textarea id="notas" name="notas" rows="2" maxlength="1000" placeholder="Ex: Compra parcelada no cartão Nubank Black; reembolsável pela empresa em até 15 dias...">{{ old('notas') }}</textarea>
                        </div>
                        @error('notas')<div class="nd-invalid">{{ $message }}</div>@enderror
                    </div>

                    <div class="nd-actions">
                        <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="nd-btn light">Cancelar</a>
                        <button type="submit" class="nd-btn red"><i data-lucide="circle-minus"></i> Salvar despesa</button>
                    </div>
                </form>
            </div>

            <div class="nd-card nd-overview">
                <div class="nd-overview-head">
                    <span>VISÃO GERAL DOS GASTOS CADASTRADOS</span>
                    <span class="r">{{ $mesNome }} {{ $ano }} • {{ $brl($gastoMes) }}</span>
                </div>
                <div class="nd-overview-body">
                    @forelse ($gastosPorCategoria->take(6) as $nome => $total)
                        <div class="nd-bar-row">
                            <span>{{ $nome }}</span>
                            <div class="track"><span style="width: {{ min(($total / $totalGastos) * 100, 100) }}%"></span></div>
                            <span class="v">{{ $brl($total) }}</span>
                        </div>
                    @empty
                        <div class="text-muted" style="font-size:.9rem">Nenhuma despesa cadastrada em {{ $mesNome }}. Os gastos aparecerão aqui assim que forem salvos.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="nd-side">
            <div class="nd-card nd-side-card">
                <div class="nd-side-title">
                    <h3><i data-lucide="chart-pie" style="width:22px;height:22px"></i> Teto do Mês</h3>
                    <span class="mono">{{ $mesNome }} / {{ $ano }}</span>
                </div>
                <div class="nd-acc"><span>Gasto Acumulado:</span><strong>{{ $brl($gastoMes) }}</strong></div>
                <div class="nd-progress"><span style="width: {{ $pctBarra }}%"></span></div>
                <div class="nd-progress-foot">
                    <span>Meta: {{ $brl($receitaMes) }}</span>
                    <span>Disponível: {{ $brl(max($disponivelMes, 0)) }}</span>
                </div>
                <div class="nd-tip">
                    <i data-lucide="lightbulb"></i>
                    <div>
                        <strong>Dica de Gestão</strong>
                        <p>Ao parcelar despesas em cartão de crédito, lembre-se de provisionar as parcelas futuras nos próximos meses.</p>
                    </div>
                </div>
            </div>

            <div class="nd-card nd-side-card">
                <div class="nd-side-title"><h3>Maiores Gastos no Mês</h3></div>
                <div class="nd-donut-row">
                    <div class="nd-donut">
                        <canvas id="maioresGastosChart"></canvas>
                        <div class="nd-donut-center">
                            <strong>{{ number_format($pctUsado, 0, ',', '.') }}%</strong>
                            <span>USADO</span>
                        </div>
                    </div>
                    <div class="nd-legend">
                        @forelse ($maioresGastos as $nome => $total)
                            <div>
                                <span class="l"><i style="background: {{ $coresTop[$loop->index] }}"></i>{{ $nome }}</span>
                                <span class="p">{{ number_format(($total / $totalGastos) * 100, 0) }}%</span>
                            </div>
                        @empty
                            <span class="text-muted" style="font-size:.85rem">Sem gastos neste mês.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="nd-card nd-side-card">
                <div class="nd-side-title">
                    <h3>Últimas despesas</h3>
                    <a href="{{ route('resumo') }}#despesas" class="text-decoration-none" style="font-size:.9rem;color:#0a1222">Ver todas</a>
                </div>
                @forelse ($ultimasDespesas as $item)
                    @php
                        $quando = $item->created_at;
                        $rotulo = $quando
                            ? ($quando->isToday() ? 'Hoje, ' . $quando->format('H:i') : ($quando->isYesterday() ? 'Ontem, ' . $quando->format('H:i') : $quando->format('d/m/Y')))
                            : '-';
                    @endphp
                    <div class="nd-last-item">
                        <span class="nd-last-ico"><i data-lucide="receipt-text"></i></span>
                        <div class="nd-last-main">
                            <div class="n">{{ $item->descricao }}</div>
                            <div class="m">{{ $rotulo }} • {{ $item->category->name ?? 'Sem categoria' }}</div>
                        </div>
                        <span class="nd-last-val">- {{ $brl($item->valor) }}</span>
                    </div>
                @empty
                    <div class="text-muted" style="font-size:.9rem">Nenhuma despesa cadastrada ainda.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Rosca "Maiores gastos"
    const canvas = document.getElementById('maioresGastosChart');
    if (canvas && window.Chart) {
        const valores = @json($maioresGastos->values());
        const cores = @json($coresTop);
        const usado = {{ round(min(max($pctUsado, 0), 100), 2) }};
        const temDados = valores.length > 0;
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: temDados ? @json($maioresGastos->keys()) : ['Sem gastos'],
                datasets: [{
                    data: temDados ? valores : [1],
                    backgroundColor: temDados ? cores : ['#e8edf5'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                plugins: { legend: { display: false }, tooltip: { enabled: temDados } },
            },
        });
    }

    // Parcelas
    const temParcelas = document.getElementById('tem_parcelas');
    const opcoesParcelas = document.getElementById('parcelas_opcoes');
    const parcelasIguais = document.getElementById('parcelas_iguais');
    const parcelasDiferentesCampos = document.getElementById('parcelas_diferentes_campos');
    const parcelasCamposContainer = document.getElementById('parcelas_campos');
    const quantidadeParcelasInput = document.getElementById('quantidade_parcelas');

    const recorrente = document.getElementById('recorrente');
    const recorrenteHint = document.getElementById('recorrenteHint');
    const dataInput = document.getElementById('data');
    const dicaPadrao = recorrenteHint.textContent;

    const atualizarRecorrente = () => {
        if (recorrente.checked) {
            temParcelas.checked = false;
            temParcelas.dispatchEvent(new Event('change'));
            const d = dataInput.value ? new Date(dataInput.value + 'T00:00:00') : null;
            recorrenteHint.textContent = d
                ? `Repete todo dia ${d.getDate()}, a partir de ${d.toLocaleDateString('pt-BR')}`
                : 'Escolha a data do primeiro vencimento';
        } else {
            recorrenteHint.textContent = dicaPadrao;
        }
    };
    recorrente.addEventListener('change', atualizarRecorrente);
    dataInput.addEventListener('change', atualizarRecorrente);
    atualizarRecorrente();

    const statusLiquidacao = document.getElementById('statusLiquidacao');
    const parcelasPagas = document.getElementById('parcelas_pagas');
    const limitarPagas = () => {
        const q = parseInt(quantidadeParcelasInput.value);
        if (Number.isInteger(q) && q >= 2) {
            parcelasPagas.max = q;
            if (parseInt(parcelasPagas.value) > q) parcelasPagas.value = q;
        }
    };
    quantidadeParcelasInput.addEventListener('input', limitarPagas);

    // Com parcelas, o status é definido por parcela (campo "Parcelas já pagas")
    const atualizarStatus = () => {
        statusLiquidacao.style.display = temParcelas.checked ? 'none' : '';
    };
    temParcelas.addEventListener('change', atualizarStatus);
    atualizarStatus();

    temParcelas.addEventListener('change', function () {
        if (this.checked && recorrente.checked) {
            recorrente.checked = false;
            atualizarRecorrente();
        }
        opcoesParcelas.classList.toggle('d-none', !this.checked);
        if (!this.checked) {
            parcelasDiferentesCampos.classList.add('d-none');
            parcelasCamposContainer.innerHTML = '';
        }
    });

    parcelasIguais.addEventListener('change', function () {
        if (this.value === '0') {
            gerarCamposParcelas();
        } else {
            parcelasDiferentesCampos.classList.add('d-none');
            parcelasCamposContainer.innerHTML = '';
        }
    });

    quantidadeParcelasInput.addEventListener('input', function () {
        if (parcelasIguais.value === '0') {
            gerarCamposParcelas();
        }
    });

    function gerarCamposParcelas() {
        const quantidade = parseInt(quantidadeParcelasInput.value);
        parcelasCamposContainer.innerHTML = '';

        if (!Number.isInteger(quantidade) || quantidade < 2) {
            parcelasDiferentesCampos.classList.add('d-none');
            return;
        }

        for (let i = 0; i < quantidade; i++) {
            const div = document.createElement('div');
            div.classList.add('col-md-6');
            div.innerHTML = `
                <div class="nd-field d-block p-3">
                    <h3 class="h6 mb-3">Parcela ${i + 1}</h3>
                    <label class="nd-label" style="font-size:.85rem">Valor (R$)</label>
                    <input type="number" step="0.01" min="0.01" name="valor_parcela[]" class="mb-3" style="border-bottom:1px solid #e2e8f0" required>
                    <label class="nd-label" style="font-size:.85rem">Data</label>
                    <input type="date" name="data_parcela[]" style="border-bottom:1px solid #e2e8f0" required>
                </div>
            `;
            parcelasCamposContainer.appendChild(div);
        }

        parcelasDiferentesCampos.classList.remove('d-none');
    }

    // Reabre os campos de parcelas diferentes após erro de validação
    if (temParcelas.checked && parcelasIguais.value === '0') {
        gerarCamposParcelas();
    }
});
</script>
@endsection
