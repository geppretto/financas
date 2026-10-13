@extends('layouts.app')

@section('nav_active', 'entradas')

@php
    $isEdit = $receita !== null;
    $brl = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $mesesNomes = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $mesesCurtos = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
    $mesNome = $mesesNomes[(int) $mes - 1];
    $recorrente = old('_token') !== null
        ? (bool) old('recorrente')
        : ($isEdit && $receita->data === null);
    $dataValor = old('_token') !== null
        ? old('data')
        : ($isEdit ? optional($receita->data)->format('Y-m-d') : now()->toDateString());
    $formatarData = fn ($d) => $d ? $d->format('d') . ' ' . $mesesCurtos[(int) $d->format('m') - 1] . ' ' . $d->format('Y') : 'Mensal';
@endphp

@push('styles')
<style>
    .nr { color: #0a1222; max-width: 1024px; margin: 0 auto; }
    .nr .mono { font-family: 'JetBrains Mono', monospace; }
    .nr svg.lucide, .nr [data-lucide] { width: 18px; height: 18px; }
    .nr-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.6rem; }
    .nr-crumb { display: flex; align-items: center; gap: .5rem; font-family: 'JetBrains Mono', monospace; font-size: .66rem; letter-spacing: .06em; text-transform: uppercase; color: #475569; margin-bottom: .35rem; }
    .nr-crumb .tag { background: #e2f6ec; color: #059669; border-radius: 999px; padding: .2rem .6rem; display: inline-flex; align-items: center; gap: .35rem; }
    .nr-crumb .tag::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: #34d399; }
    .nr-title { font-size: 2.6rem; font-weight: 500; letter-spacing: -0.04em; margin: 0; line-height: 1.1; }
    .nr-sub { color: #334155; margin: .35rem 0 0; font-size: .95rem; }
    .nr-back { background: #fff; border: 1px solid #eef1f6; border-radius: 10px; padding: .7rem 1.1rem; display: inline-flex; align-items: center; gap: .6rem; font-size: .9rem; font-weight: 500; color: #0a1222; text-decoration: none; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .nr-back:hover { background: #f8fafc; color: #0a1222; }

    .nr-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .nr-card { background: #fff; border-radius: 16px; border: 1px solid #eef1f6; box-shadow: 0 1px 3px rgba(15, 23, 42, .05); overflow: hidden; }
    .nr-head { background: #f4f7fc; padding: 1.4rem 2.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .nr-head-left { display: flex; align-items: center; gap: 1rem; }
    .nr-head-ico { width: 40px; height: 40px; border-radius: 10px; background: #34d399; color: #064e3b; display: grid; place-items: center; }
    .nr-head h2 { font-size: 1.5rem; font-weight: 500; letter-spacing: -0.03em; margin: 0; line-height: 1.1; }
    .nr-head p { margin: .2rem 0 0; font-family: 'JetBrains Mono', monospace; font-size: .72rem; color: #334155; }
    .nr-brl { background: #fff; color: #059669; font-family: 'JetBrains Mono', monospace; font-size: .68rem; font-weight: 600; border-radius: 999px; padding: .35rem .8rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .06); }

    .nr-body { padding: 2.2rem 2.5rem 2.6rem; }
    .nr-lblrow { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .55rem; gap: .5rem; }
    .nr-lbl { font-size: 1.1rem; font-weight: 500; letter-spacing: -0.015em; margin: 0; }
    .nr-hint { font-family: 'JetBrains Mono', monospace; font-size: .66rem; color: #334155; letter-spacing: .03em; white-space: nowrap; }
    .nr-hint.green { color: #059669; font-weight: 600; }
    .nr-field { background: #eef3fc; border-radius: 10px; display: flex; align-items: center; gap: .7rem; padding: 0 1rem; min-height: 44px; }
    .nr-field:focus-within { box-shadow: 0 0 0 2px #93b4f5; }
    .nr-field svg { color: #334155; flex: 0 0 auto; }
    .nr-field input, .nr-field select { border: 0; outline: 0; background: transparent; width: 100%; font-size: .93rem; color: #0a1222; padding: .6rem 0; font-family: inherit; }
    .nr-field input::placeholder { color: #334155; }
    .nr-field select { appearance: none; background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230a1222' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right center; padding-right: 1.5rem; }
    .nr-field.money { min-height: 52px; }
    .nr-field.money .cur { color: #059669; font-weight: 700; font-size: .85rem; }
    .nr-field.money input { font-family: 'JetBrains Mono', monospace; font-size: 1.45rem; font-weight: 700; letter-spacing: .02em; padding: .35rem 0; }
    .nr-field input:disabled { opacity: .45; }
    .nr-help { font-size: .85rem; color: #334155; margin: .5rem 0 0; }
    .nr-err { color: #b91c1c; font-size: .8rem; margin-top: .3rem; }
    .nr-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.8rem; }
    .nr-block { margin-top: 0; }

    .nr-rec { margin-top: 1.8rem; background: #eaf1fe; border-radius: 12px; padding: 1.1rem 1.2rem; display: flex; align-items: center; gap: 1rem; }
    .nr-rec-ico { width: 32px; height: 32px; border-radius: 8px; background: #fff; color: #059669; display: grid; place-items: center; flex: 0 0 auto; }
    .nr-rec-ico svg { width: 16px; height: 16px; }
    .nr-rec-text { flex: 1; }
    .nr-rec-text strong { font-size: 1.1rem; font-weight: 500; letter-spacing: -0.015em; display: block; line-height: 1.2; }
    .nr-rec-text span { font-size: .85rem; color: #334155; }
    .nr-switch { position: relative; width: 52px; height: 28px; flex: 0 0 auto; }
    .nr-switch input { position: absolute; inset: 0; opacity: 0; cursor: pointer; margin: 0; z-index: 2; width: 100%; height: 100%; }
    .nr-switch .track { position: absolute; inset: 0; border-radius: 999px; background: #c5d3ea; transition: background .15s; }
    .nr-switch .knob { position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; transition: transform .15s; box-shadow: 0 1px 3px rgba(15, 23, 42, .25); }
    .nr-switch input:checked ~ .track { background: #059669; }
    .nr-switch input:checked ~ .knob { transform: translateX(24px); }

    .nr-actions { margin-top: 2.2rem; display: flex; justify-content: flex-end; gap: .6rem; flex-wrap: wrap; }
    .nr-btn { border: 0; border-radius: 10px; padding: .9rem 1.7rem; font-size: 1.05rem; font-weight: 500; display: inline-flex; align-items: center; gap: .6rem; text-decoration: none; }
    .nr-btn.light { background: #e1ebfc; color: #1e293b; }
    .nr-btn.light:hover { background: #d4e2fb; color: #0a1222; }
    .nr-btn.dark { background: #0a1222; color: #fff; padding: .9rem 2.2rem; }
    .nr-btn.dark:hover { background: #16213a; }

    .nr-side { display: grid; gap: 1.5rem; }
    .nr-ref { padding: 1rem 1rem 1rem; }
    .nr-ref-head { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: .66rem; letter-spacing: .1em; margin-bottom: .7rem; }
    .nr-ref-head .r { color: #059669; font-weight: 600; letter-spacing: .02em; }
    .nr-ref-body { border-radius: 10px; min-height: 165px; background: linear-gradient(180deg, #1b2740 0%, #0a1222 100%); color: #fff; padding: 1.1rem; display: flex; flex-direction: column; justify-content: flex-end; gap: .6rem; }
    .nr-ref-body .big { font-family: 'JetBrains Mono', monospace; font-size: 1.5rem; font-weight: 700; }
    .nr-ref-body p { margin: 0; font-size: .9rem; color: #cbd5e1; }
    .nr-last { background: #fff; }
    .nr-last-head { background: #f4f7fc; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; }
    .nr-last-head h3 { font-size: 1.15rem; font-weight: 500; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .6rem; }
    .nr-last-head h3 svg { color: #059669; }
    .nr-last-head a { font-family: 'JetBrains Mono', monospace; font-size: .68rem; color: #334155; text-decoration: none; }
    .nr-item { display: flex; align-items: center; gap: .85rem; padding: 1rem 1.25rem; }
    .nr-item-ico { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; flex: 0 0 auto; }
    .nr-item-ico svg { width: 16px; height: 16px; }
    .nr-item-main { flex: 1; min-width: 0; }
    .nr-item-top { display: flex; justify-content: space-between; gap: .5rem; font-size: .85rem; }
    .nr-item-top .n { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .nr-item-top .v { font-family: 'JetBrains Mono', monospace; font-size: .75rem; color: #059669; white-space: nowrap; }
    .nr-item-bot { display: flex; justify-content: space-between; margin-top: .2rem; font-family: 'JetBrains Mono', monospace; font-size: .62rem; color: #475569; }
    .nr-total { background: #f4f7fc; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; }
    .nr-total strong { font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; }
    .nr-tip { background: #e8f0fe; border-radius: 12px; padding: 1.1rem 1.2rem; display: flex; gap: .8rem; font-size: .85rem; color: #334155; line-height: 1.6; }
    .nr-tip svg { flex: 0 0 auto; margin-top: .15rem; color: #334155; }
    .nr-tip b { color: #0a1222; font-weight: 600; }

    @media (max-width: 1000px) {
        .nr-layout { grid-template-columns: 1fr; }
        .nr-row { grid-template-columns: 1fr; }
        .nr-body, .nr-head { padding-left: 1.25rem; padding-right: 1.25rem; }
    }

    @media (max-width: 640px) {
        .nr-title { font-size: 2rem; }
        .nr-top { flex-direction: column; }
        .nr-back { align-self: flex-start; }
        .nr-body { padding: 1.25rem; }
        .nr-head { padding: 1rem 1.25rem; }
        .nr-head h2 { font-size: 1.25rem; }
        .nr-rec { flex-wrap: wrap; }
        .nr-actions { flex-direction: column-reverse; }
        .nr-actions .nr-btn { justify-content: center; width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="nr">
    <div class="nr-top">
        <div>
            <div class="nr-crumb">
                <span class="tag">Entrada financeira</span>
                <span>/ Crédito</span>
            </div>
            <h1 class="nr-title">{{ $isEdit ? 'Editar receita' : 'Nova receita' }}</h1>
            <p class="nr-sub">Cadastre uma entrada pontual ou deixe a data vazia para uma receita mensal contínua.</p>
        </div>
        <a href="{{ route('resumo') }}" class="nr-back"><i data-lucide="arrow-left"></i> Voltar para resumo</a>
    </div>

    <div class="nr-layout">
        <div class="nr-card">
            <div class="nr-head">
                <div class="nr-head-left">
                    <span class="nr-head-ico"><i data-lucide="circle-plus"></i></span>
                    <div>
                        <h2>Dados da receita</h2>
                        <p>Lançamento em conta corrente ou carteira ativa</p>
                    </div>
                </div>
                <span class="nr-brl">BRL (R$)</span>
            </div>

            <form class="nr-body" method="POST" action="{{ $isEdit ? route('receitas.update', $receita->id) : route('receitas.store') }}">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <div class="nr-block">
                    <div class="nr-lblrow">
                        <label for="descricao" class="nr-lbl">Descrição da receita</label>
                        <span class="nr-hint">Obrigatório</span>
                    </div>
                    <div class="nr-field">
                        <i data-lucide="file-text"></i>
                        <input type="text" id="descricao" name="descricao" maxlength="255" required
                               placeholder="Ex: Salário mensal, Freelance, Dividendos, Aluguel..."
                               value="{{ old('descricao', $receita->descricao ?? '') }}">
                    </div>
                    <p class="nr-help">Insira um identificador intuitivo para relatórios e busca no fluxo de caixa.</p>
                    @error('descricao')<div class="nr-err">{{ $message }}</div>@enderror
                </div>

                <div class="nr-row">
                    <div>
                        <div class="nr-lblrow">
                            <label for="valor" class="nr-lbl">Valor previsto</label>
                            <span class="nr-hint green">Crédito (+)</span>
                        </div>
                        <div class="nr-field money">
                            <span class="cur">R$</span>
                            <input type="number" id="valor" name="valor" step="0.01" min="0.01" required placeholder="0,00"
                                   value="{{ old('valor', $receita->valor ?? '') }}">
                        </div>
                        <p class="nr-help">Valor líquido a ser compensado.</p>
                        @error('valor')<div class="nr-err">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <div class="nr-lblrow">
                            <label for="data" class="nr-lbl">Data de crédito</label>
                            <span class="nr-hint">Vazio = Recorrente</span>
                        </div>
                        <div class="nr-field">
                            <i data-lucide="calendar"></i>
                            <input type="date" id="data" name="data" value="{{ $recorrente ? '' : $dataValor }}" {{ $recorrente ? 'disabled' : '' }}>
                        </div>
                        <p class="nr-help">Selecione o dia do pagamento ou faturamento.</p>
                        @error('data')<div class="nr-err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="nr-row">
                    <div>
                        <div class="nr-lblrow"><label for="conta" class="nr-lbl">Conta de destino</label></div>
                        <div class="nr-field">
                            <i data-lucide="landmark"></i>
                            <input type="text" id="conta" name="conta" maxlength="100" list="contasLista" autocomplete="off"
                                   placeholder="Ex: Itaú, Nubank, Carteira" value="{{ old('conta', $receita->conta ?? '') }}">
                            <datalist id="contasLista">
                                @foreach ($contas as $conta)
                                    <option value="{{ $conta }}">
                                @endforeach
                            </datalist>
                        </div>
                        @error('conta')<div class="nr-err">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <div class="nr-lblrow"><label for="category_id" class="nr-lbl">Categoria</label></div>
                        <div class="nr-field">
                            <i data-lucide="tag"></i>
                            <select id="category_id" name="category_id">
                                <option value="">Sem categoria</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ (string) old('category_id', $receita->category_id ?? '') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('category_id')<div class="nr-err">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="nr-rec">
                    <span class="nr-rec-ico"><i data-lucide="refresh-cw"></i></span>
                    <div class="nr-rec-text">
                        <strong>Receita recorrente / fixa mensal</strong>
                        <span>Lançar automaticamente este montante todo início de ciclo mensal.</span>
                    </div>
                    <label class="nr-switch mb-0">
                        <input type="checkbox" name="recorrente" id="recorrente" value="1" {{ $recorrente ? 'checked' : '' }}>
                        <span class="track"></span>
                        <span class="knob"></span>
                    </label>
                </div>

                <div class="nr-actions">
                    <a href="{{ route('resumo') }}" class="nr-btn light">Cancelar</a>
                    <button type="submit" class="nr-btn dark"><i data-lucide="circle-check"></i> {{ $isEdit ? 'Salvar alterações' : 'Salvar receita' }}</button>
                </div>
            </form>
        </div>

        <aside class="nr-side">
            <div class="nr-card nr-ref">
                <div class="nr-ref-head"><span>VISÃO DO MÊS</span><span class="r">{{ $mesNome }} / {{ $ano }}</span></div>
                <div class="nr-ref-body">
                    <div class="big">{{ $brl($totalPrevistoMes) }}</div>
                    <p>Total de receitas previstas para o mês, somando entradas pontuais e recorrentes.</p>
                </div>
            </div>

            <div class="nr-card nr-last">
                <div class="nr-last-head">
                    <h3><i data-lucide="history"></i> Últimas {{ max($ultimasReceitas->count(), 3) }} receitas</h3>
                    <a href="{{ route('resumo') }}#receitas">Ver todas</a>
                </div>
                @forelse ($ultimasReceitas as $item)
                    @php $cor = $item->category->cor ?? '#2563eb'; @endphp
                    <div class="nr-item">
                        <span class="nr-item-ico" style="background: {{ $cor }}1f; color: {{ $cor }}"><i data-lucide="{{ $item->category->icone ?? 'banknote' }}"></i></span>
                        <div class="nr-item-main">
                            <div class="nr-item-top">
                                <span class="n">{{ $item->descricao }}</span>
                                <span class="v">+ {{ $brl($item->valor) }}</span>
                            </div>
                            <div class="nr-item-bot">
                                <span>{{ $formatarData($item->data) }}{{ $item->conta ? ' • ' . $item->conta : '' }}</span>
                                <span>{{ $item->data ? 'Pontual' : 'Fixa' }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="nr-item text-muted" style="font-size:.88rem">Nenhuma receita cadastrada ainda.</div>
                @endforelse
                <div class="nr-total">
                    <span>Total previsto no mês</span>
                    <strong>{{ $brl($totalPrevistoMes) }}</strong>
                </div>
            </div>

            <div class="nr-tip">
                <i data-lucide="lightbulb"></i>
                <div><b>Dica de gestão:</b> Entradas marcadas como recorrentes alimentam a projeção do gráfico de balanço futuro pelos próximos 12 meses.</div>
            </div>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('recorrente');
    const data = document.getElementById('data');
    const ultimaData = { valor: data.value || new Date().toISOString().slice(0, 10) };

    toggle.addEventListener('change', function () {
        if (this.checked) {
            if (data.value) ultimaData.valor = data.value;
            data.value = '';
            data.disabled = true;
        } else {
            data.disabled = false;
            data.value = ultimaData.valor;
        }
    });
});
</script>
@endsection
