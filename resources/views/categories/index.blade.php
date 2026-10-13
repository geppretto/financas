@extends('layouts.app')

@section('content_class', 'content-wide')
@section('nav_active', 'categorias')

@php
    $brl = fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
    $brlInt = fn ($v) => 'R$ ' . number_format((float) $v, 0, ',', '.');
    $mesesNomes = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $mesNome = $mesesNomes[(int) $mes - 1];
    $isEdit = $editing !== null;
    $tipoAtual = old('tipo', $editing->tipo ?? 'despesa');
    $iconeAtual = old('icone', $editing->icone ?? $icones[0]);
    $corAtual = old('cor', $editing->cor ?? $cores[0]);
    $tetoAtual = old('teto_mensal', ($editing && $editing->teto_mensal) ? $editing->teto_mensal : '');
@endphp

@push('styles')
<style>
    .ct { color: #0a1222; }
    .ct .mono { font-family: 'JetBrains Mono', monospace; }
    .ct svg.lucide, .ct [data-lucide] { width: 18px; height: 18px; }
    .ct-top { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .ct-crumb { font-family: 'JetBrains Mono', monospace; font-size: .68rem; letter-spacing: .08em; color: #475569; text-transform: uppercase; margin-bottom: .35rem; }
    .ct-crumb strong { color: #0a1222; font-weight: 600; }
    .ct-title { font-size: 2.3rem; font-weight: 500; letter-spacing: -0.035em; margin: 0; line-height: 1.15; }
    .ct-sub { color: #334155; margin: .5rem 0 0; font-size: .95rem; max-width: 640px; line-height: 1.45; }
    .ct-top-right { display: flex; align-items: center; gap: .6rem; }
    .ct-teto { background: #fff; border: 1px solid #eef1f6; border-radius: 10px; padding: .6rem 1rem; font-family: 'JetBrains Mono', monospace; font-size: .72rem; color: #334155; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .ct-teto strong { color: #0a1222; font-size: .78rem; margin-left: .35rem; }
    .ct-export { background: #fff; border: 1px solid #eef1f6; border-radius: 10px; padding: .6rem 1rem; font-size: .88rem; font-weight: 500; color: #0a1222; text-decoration: none; display: inline-flex; align-items: center; gap: .5rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
    .ct-export:hover { background: #f8fafc; color: #0a1222; }

    .ct-layout { display: grid; grid-template-columns: minmax(0, 4fr) minmax(0, 7fr); gap: 1.5rem; align-items: start; }
    .ct-card { background: #fff; border: 1px solid #eef1f6; border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, .05); }

    .ct-form { padding: 1.5rem; }
    .ct-form-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.6rem; }
    .ct-form-head h2 { font-size: 1.3rem; font-weight: 500; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .55rem; }
    .ct-mode { font-family: 'JetBrains Mono', monospace; font-size: .62rem; letter-spacing: .06em; background: #e2f6ec; color: #059669; border-radius: 5px; padding: .25rem .5rem; }
    .ct-mode.edit { background: #fff4d6; color: #a16207; }
    .ct-lbl { display: flex; justify-content: space-between; font-size: .68rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #1e293b; margin: 1.2rem 0 .5rem; }
    .ct-lbl .mono { font-weight: 400; letter-spacing: .02em; text-transform: none; color: #475569; }
    .ct-input { width: 100%; background: #e8f0fe; border: 0; border-radius: 10px; padding: .85rem 1rem; font-size: .95rem; color: #0a1222; outline: 0; font-family: inherit; }
    .ct-input:focus { box-shadow: 0 0 0 2px #93b4f5; }
    .ct-input::placeholder { color: #334155; }
    .ct-input-wrap { position: relative; }
    .ct-input-wrap svg { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #475569; }
    .ct-money { display: flex; align-items: center; gap: .5rem; background: #e8f0fe; border-radius: 10px; padding: 0 1rem; }
    .ct-money:focus-within { box-shadow: 0 0 0 2px #93b4f5; }
    .ct-money span { font-family: 'JetBrains Mono', monospace; color: #475569; font-size: .85rem; }
    .ct-money input { border: 0; background: transparent; outline: 0; width: 100%; padding: .85rem 0; font-family: 'JetBrains Mono', monospace; font-size: .9rem; }
    .ct-help { font-size: .82rem; color: #334155; margin: .55rem 0 0; }
    .ct-err { color: #b91c1c; font-size: .78rem; margin-top: .3rem; }

    .ct-tipo { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
    .ct-tipo input, .ct-icons input, .ct-colors input { position: absolute; opacity: 0; pointer-events: none; }
    .ct-tipo label { display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .7rem; border-radius: 8px; background: #e8f0fe; font-size: .9rem; font-weight: 500; cursor: pointer; margin: 0; }
    .ct-tipo label svg { width: 15px; height: 15px; }
    .ct-tipo input:checked + label { background: #0a1222; color: #fff; }

    .ct-icons { background: #e8f0fe; border-radius: 12px; padding: .5rem; display: grid; grid-template-columns: repeat(6, 1fr); gap: .35rem; }
    .ct-icons label { height: 44px; display: grid; place-items: center; border-radius: 8px; cursor: pointer; color: #0a1222; margin: 0; }
    .ct-icons input:checked + label { background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
    .ct-colors { background: #e8f0fe; border-radius: 12px; padding: .75rem 1rem; display: flex; gap: .7rem; flex-wrap: wrap; }
    .ct-colors label { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; margin: 0; border: 2px solid transparent; box-sizing: border-box; }
    .ct-colors input:checked + label { box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0a1222; }

    .ct-actions { display: flex; gap: .6rem; margin-top: 1.4rem; }
    .ct-save { flex: 1; background: #0a1222; color: #fff; border: 0; border-radius: 10px; padding: .85rem 1rem; font-weight: 500; font-size: .92rem; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; }
    .ct-save:hover { background: #16213a; }
    .ct-clear { background: #e8f0fe; color: #1e293b; border: 0; border-radius: 10px; padding: .85rem 1.3rem; font-weight: 500; font-size: .92rem; text-decoration: none; }
    .ct-clear:hover { background: #dbe7fd; color: #0a1222; }

    .ct-auto { margin-top: 1.5rem; padding: 1rem; display: flex; gap: 1rem; align-items: center; }
    .ct-auto-ico { width: 80px; height: 80px; border-radius: 10px; background: #eef1f5; display: grid; place-items: center; color: #059669; flex: 0 0 auto; }
    .ct-auto-ico svg { width: 34px; height: 34px; }
    .ct-auto .k { font-family: 'JetBrains Mono', monospace; font-size: .66rem; letter-spacing: .08em; color: #059669; text-transform: uppercase; }
    .ct-auto h3 { font-size: 1.15rem; font-weight: 500; margin: .15rem 0; letter-spacing: -0.02em; }
    .ct-auto p { margin: 0; font-size: .88rem; color: #334155; line-height: 1.4; }

    .ct-list { padding: 1.5rem; }
    .ct-list-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
    .ct-list-head h2 { font-size: 1.3rem; font-weight: 500; letter-spacing: -0.02em; margin: 0; display: flex; align-items: center; gap: .6rem; }
    .ct-count { font-family: 'JetBrains Mono', monospace; font-size: .68rem; background: #e8f0fe; color: #1e293b; font-weight: 600; border-radius: 999px; padding: .25rem .6rem; }
    .ct-search { position: relative; width: 256px; max-width: 100%; }
    .ct-search svg { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: #334155; width: 16px; height: 16px; }
    .ct-search input { width: 100%; background: #e8f0fe; border: 0; border-radius: 10px; padding: .7rem 1rem .7rem 2.5rem; font-size: .85rem; outline: 0; }

    .ct-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .ct-table thead th { background: #f4f6fa; font-size: .66rem; letter-spacing: .06em; font-weight: 600; color: #334155; text-transform: uppercase; padding: .75rem .8rem; white-space: nowrap; }
    .ct-table thead th:first-child { border-radius: 8px 0 0 8px; }
    .ct-table thead th:last-child { border-radius: 0 8px 8px 0; }
    .ct-table td { padding: .75rem .8rem; vertical-align: middle; }
    .ct-name { display: flex; align-items: center; gap: .9rem; }
    .ct-ico { width: 40px; height: 40px; border-radius: 10px; display: grid; place-items: center; flex: 0 0 auto; }
    .ct-name .n { font-size: 1rem; font-weight: 500; letter-spacing: -0.01em; }
    .ct-name .s { font-family: 'JetBrains Mono', monospace; font-size: .64rem; color: #475569; letter-spacing: .02em; }
    .ct-num { text-align: center; font-size: .9rem; }
    .ct-total { text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: .85rem; white-space: nowrap; }
    .ct-meta { min-width: 150px; }
    .ct-meta .row1 { display: flex; justify-content: space-between; font-family: 'JetBrains Mono', monospace; font-size: .62rem; color: #475569; margin-bottom: .35rem; }
    .ct-meta .row1 b { font-weight: 700; }
    .ct-track { height: 5px; background: #dbe7fb; border-radius: 999px; overflow: hidden; }
    .ct-track span { display: block; height: 100%; border-radius: 999px; }
    .ct-acts { text-align: right; white-space: nowrap; }
    .ct-acts a, .ct-acts button { background: none; border: 0; color: #334155; padding: .35rem; display: inline-grid; place-items: center; }
    .ct-acts a:hover { color: #2563eb; }
    .ct-acts button:hover { color: #dc2626; }
    .ct-empty { text-align: center; color: #475569; padding: 2.5rem 1rem !important; font-size: .9rem; }
    .ct-foot { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-top: 1.5rem; padding-top: 1rem; font-size: .88rem; }
    .ct-foot .mono { font-size: .7rem; color: #475569; }
    .ct-foot .ok { color: #059669; font-weight: 600; }
    .ct-foot .warn { color: #b91c1c; font-weight: 600; }
    .ct-foot .ok::before, .ct-foot .warn::before { content: '●'; margin-right: .3rem; font-size: .6rem; }

    @media (max-width: 1100px) {
        .ct-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .ct-table thead { display: none; }
        .ct-table, .ct-table tbody, .ct-table tr, .ct-table td { display: block; width: 100%; }
        .ct-table tr { border-bottom: 1px solid #eef1f6; padding: .5rem 0; }
        .ct-num, .ct-total, .ct-acts { text-align: left; }
    }

    @media (max-width: 640px) {
        .ct-title { font-size: 1.8rem; }
        .ct-top-right { width: 100%; justify-content: space-between; }
        .ct-form, .ct-list { padding: 1rem; }
        .ct-search { width: 100%; }
        .ct-list-head { flex-direction: column; align-items: stretch; }
        .ct-auto { flex-direction: column; align-items: flex-start; }
        .ct-table tbody tr {
            display: grid; grid-template-columns: 1fr 1fr; gap: .5rem .75rem;
            padding: .85rem; margin-bottom: .6rem; border-radius: 12px; background: #f8fafc;
        }
        .ct-table td { padding: 0; }
        .ct-table td:nth-child(1) { grid-column: 1 / -1; }
        .ct-table td:nth-child(2)::before { content: 'Registros: '; color: #475569; font-size: .75rem; }
        .ct-table td:nth-child(3)::before { content: 'Total: '; color: #475569; font-size: .75rem; font-family: inherit; font-weight: 400; }
        .ct-table td:nth-child(2), .ct-table td:nth-child(3) { text-align: left; }
        .ct-table td:nth-child(4) { grid-column: 1 / -1; }
        .ct-table td:nth-child(5) { grid-column: 1 / -1; text-align: right; }
        .ct-meta { min-width: 0; }
    }
</style>
@endpush

@section('content')
<div class="ct">
    <div class="ct-top">
        <div>
            <div class="ct-crumb">Centros de custo / <strong>Classificação</strong></div>
            <h1 class="ct-title">Categorias</h1>
            <p class="ct-sub">Crie, edite e organize os grupos e centros de custo usados nas suas despesas e receitas com limites orçamentários calculados em tempo real.</p>
        </div>
        <div class="ct-top-right">
            <span class="ct-teto">Teto Consolidado: <strong>{{ $brl($tetoConsolidado) }}</strong></span>
            <a href="{{ route('categories.export') }}" class="ct-export"><i data-lucide="download"></i> Exportar</a>
        </div>
    </div>

    <div class="ct-layout">
        <div>
            {{-- Formulário --}}
            <div class="ct-card ct-form">
                <form id="categoriaForm" method="POST" action="{{ $isEdit ? route('categories.update', $editing->id) : route('categories.store') }}">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="ct-form-head">
                        <h2><i data-lucide="bookmark-plus" style="width:20px;height:20px"></i> {{ $isEdit ? 'Editar categoria' : 'Nova categoria' }}</h2>
                        <span class="ct-mode {{ $isEdit ? 'edit' : '' }}">{{ $isEdit ? 'MODO EDIÇÃO' : 'MODO CRIAÇÃO' }}</span>
                    </div>

                    <div class="ct-lbl" style="margin-top:0"><span>Nome da categoria</span></div>
                    <div class="ct-input-wrap">
                        <input type="text" name="name" class="ct-input" maxlength="255" required
                               placeholder="Ex: Assinaturas, Mercado, Viagem..." value="{{ old('name', $editing->name ?? '') }}">
                        <i data-lucide="tag"></i>
                    </div>
                    @error('name')<div class="ct-err">{{ $message }}</div>@enderror

                    <div class="ct-lbl"><span>Tipo de movimentação</span></div>
                    <div class="ct-tipo">
                        <input type="radio" name="tipo" id="tipo_despesa" value="despesa" {{ $tipoAtual === 'despesa' ? 'checked' : '' }}>
                        <label for="tipo_despesa"><i data-lucide="arrow-up-right"></i> Despesa</label>
                        <input type="radio" name="tipo" id="tipo_receita" value="receita" {{ $tipoAtual === 'receita' ? 'checked' : '' }}>
                        <label for="tipo_receita"><i data-lucide="arrow-down-left"></i> Receita</label>
                    </div>
                    @error('tipo')<div class="ct-err">{{ $message }}</div>@enderror

                    <div class="ct-lbl"><span>Ícone de identificação</span><span class="mono" id="iconeNome">{{ $iconeAtual }}</span></div>
                    <div class="ct-icons">
                        @foreach ($icones as $icone)
                            <input type="radio" name="icone" id="icone_{{ $icone }}" value="{{ $icone }}" {{ $iconeAtual === $icone ? 'checked' : '' }}>
                            <label for="icone_{{ $icone }}" title="{{ $icone }}"><i data-lucide="{{ $icone }}"></i></label>
                        @endforeach
                    </div>
                    @error('icone')<div class="ct-err">{{ $message }}</div>@enderror

                    <div class="ct-lbl"><span>Paleta cromática</span></div>
                    <div class="ct-colors">
                        @foreach ($cores as $cor)
                            <input type="radio" name="cor" id="cor_{{ ltrim($cor, '#') }}" value="{{ $cor }}" {{ $corAtual === $cor ? 'checked' : '' }}>
                            <label for="cor_{{ ltrim($cor, '#') }}" style="background: {{ $cor }}" title="{{ $cor }}"></label>
                        @endforeach
                    </div>
                    @error('cor')<div class="ct-err">{{ $message }}</div>@enderror

                    <div class="ct-lbl"><span>Orçamento teto mensal (R$)</span><span class="mono">OPCIONAL</span></div>
                    <div class="ct-money">
                        <span>R$</span>
                        <input type="number" name="teto_mensal" step="0.01" min="0" placeholder="0,00" value="{{ $tetoAtual }}">
                    </div>
                    @error('teto_mensal')<div class="ct-err">{{ $message }}</div>@enderror
                    <p class="ct-help">Defina para monitorar alertas de consumo e relatórios preditivos.</p>

                    <div class="ct-actions">
                        <button type="submit" class="ct-save"><i data-lucide="circle-check"></i> {{ $isEdit ? 'Salvar alterações' : 'Salvar categoria' }}</button>
                        @if ($isEdit)
                            <a href="{{ route('categories.index') }}" class="ct-clear">Cancelar</a>
                        @else
                            <button type="reset" class="ct-clear" id="limparForm">Limpar</button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="ct-card ct-auto">
                <span class="ct-auto-ico"><i data-lucide="chart-pie"></i></span>
                <div>
                    <div class="k">Controle orçamentário</div>
                    <h3>Distribuição Automática</h3>
                    <p>Suas categorias organizam todas as entradas e saídas nas visões de Gráficos e Balanço mensal.</p>
                </div>
            </div>
        </div>

        {{-- Lista --}}
        <div class="ct-card ct-list">
            <div class="ct-list-head">
                <h2>Minhas categorias <span class="ct-count">{{ $categories->count() }} {{ $categories->count() === 1 ? 'ativa' : 'ativas' }}</span></h2>
                <label class="ct-search mb-0">
                    <i data-lucide="search"></i>
                    <input type="search" id="buscaCategoria" placeholder="Buscar categoria..." autocomplete="off">
                </label>
            </div>

            <div class="table-responsive">
                <table class="ct-table">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th style="text-align:center">Registros</th>
                            <th style="text-align:right">Total acumulado</th>
                            <th>Consumo / Meta</th>
                            <th style="text-align:right">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="listaCategorias">
                        @forelse ($categories as $category)
                            @php
                                $pct = $category->percentual;
                                $largura = $pct === null ? 0 : min($pct, 100);
                            @endphp
                            <tr data-nome="{{ \Illuminate\Support\Str::lower($category->name) }}">
                                <td>
                                    <div class="ct-name">
                                        <span class="ct-ico" style="background: {{ $category->cor }}1f; color: {{ $category->cor }}"><i data-lucide="{{ $category->icone }}"></i></span>
                                        <div>
                                            <div class="n">{{ $category->name }}</div>
                                            <div class="s">{{ $category->tipo === 'receita' ? 'Receitas' : 'Despesas' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="ct-num">{{ $category->registros }}</td>
                                <td class="ct-total">{{ $brl($category->total) }}</td>
                                <td class="ct-meta">
                                    @if ($pct !== null)
                                        <div class="row1">
                                            <span>Teto: {{ $brlInt($category->teto_mensal) }}</span>
                                            <b style="color: {{ $category->cor }}">{{ number_format($pct, 0) }}%</b>
                                        </div>
                                        <div class="ct-track"><span style="width: {{ $largura }}%; background: {{ $category->cor }}"></span></div>
                                    @else
                                        <div class="row1"><span>Sem teto definido</span></div>
                                        <div class="ct-track"></div>
                                    @endif
                                </td>
                                <td class="ct-acts">
                                    <a href="{{ route('categories.edit', $category->id) }}" title="Editar"><i data-lucide="pencil"></i></a>
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Excluir esta categoria? Os lançamentos serão mantidos sem categoria.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Excluir"><i data-lucide="trash-2"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ct-empty">Nenhuma categoria criada ainda. Use o formulário ao lado para começar.</td></tr>
                        @endforelse
                        <tr id="semResultado" style="display:none"><td colspan="5" class="ct-empty">Nenhuma categoria encontrada para a busca.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="ct-foot">
                <span><span id="mostrando">{{ $categories->count() }}</span> de {{ $categories->count() }} categorias <span class="mono">• valores de {{ $mesNome }}/{{ $ano }}</span></span>
                @if ($orcamentoMedio !== null)
                    <span class="mono">Orçamento médio: {{ number_format($orcamentoMedio, 1, ',', '.') }}%
                        <span class="{{ $orcamentoMedio <= 100 ? 'ok' : 'warn' }}">Status {{ $orcamentoMedio <= 100 ? 'Saudável' : 'Acima do teto' }}</span>
                    </span>
                @else
                    <span class="mono">Defina tetos para acompanhar o orçamento médio</span>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Nome do ícone selecionado
    const nomeIcone = document.getElementById('iconeNome');
    document.querySelectorAll('input[name="icone"]').forEach((r) => {
        r.addEventListener('change', () => { nomeIcone.textContent = r.value; });
    });

    // Limpar: volta ao estado padrão do formulário
    document.getElementById('limparForm')?.addEventListener('click', () => {
        setTimeout(() => {
            const marcado = document.querySelector('input[name="icone"]:checked');
            nomeIcone.textContent = marcado ? marcado.value : '';
        }, 0);
    });

    // Busca
    const busca = document.getElementById('buscaCategoria');
    const linhas = [...document.querySelectorAll('#listaCategorias tr[data-nome]')];
    const semResultado = document.getElementById('semResultado');
    const mostrando = document.getElementById('mostrando');

    busca?.addEventListener('input', () => {
        const termo = busca.value.trim().toLowerCase();
        let visiveis = 0;
        linhas.forEach((l) => {
            const ok = l.dataset.nome.includes(termo);
            l.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });
        mostrando.textContent = visiveis;
        if (semResultado) semResultado.style.display = linhas.length > 0 && visiveis === 0 ? '' : 'none';
    });
});
</script>
@endsection
