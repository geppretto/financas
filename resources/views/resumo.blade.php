@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
        <div>
            <div class="badge-soft d-inline-flex mb-2">{{ strtoupper($user->name) }}</div>
            <h1 class="page-title mb-1">Resumo financeiro</h1>
            <p class="text-muted mb-0">Acompanhe receitas, despesas e pagamentos de {{ $mes }}/{{ $ano }}.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 action-bar">
            <a href="{{ route('receitas.create') }}" class="btn btn-success">Nova receita</a>
            <a href="{{ route('despesas.create') }}" class="btn btn-danger">Nova despesa</a>
            <a href="{{ route('categories.index') }}" class="btn btn-primary">Categorias</a>
        </div>
    </div>

    <form method="GET" action="{{ route('resumo') }}" class="panel p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-md-3 col-lg-2">
                <label for="mes" class="form-label">Mês</label>
                <select name="mes" id="mes" class="form-select">
                    @for ($i = 1; $i <= 12; $i++)
                        @php $mesOption = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                        <option value="{{ $mesOption }}" {{ $mes == $mesOption ? 'selected' : '' }}>
                            {{ $mesOption }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label for="ano" class="form-label">Ano</label>
                <select name="ano" id="ano" class="form-select">
                    @for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                        <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <div id="resumo" class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card metric metric-success">
                <div class="card-header">Total recebido</div>
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <strong class="fs-4 valor-mascarado d-none">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</strong>
                    <strong class="fs-4 valor-visivel">••••••</strong>
                    <button type="button" class="btn btn-sm btn-light toggle-valor">Mostrar</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric metric-danger">
                <div class="card-header">Total gasto</div>
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <strong class="fs-4 valor-mascarado d-none">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</strong>
                    <strong class="fs-4 valor-visivel">••••••</strong>
                    <button type="button" class="btn btn-sm btn-light toggle-valor">Mostrar</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric {{ $saldo >= 0 ? 'metric-brand' : 'metric-warning' }}">
                <div class="card-header">Saldo</div>
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <strong class="fs-4 valor-mascarado d-none">R$ {{ number_format($saldo, 2, ',', '.') }}</strong>
                    <strong class="fs-4 valor-visivel">••••••</strong>
                    <button type="button" class="btn btn-sm btn-light toggle-valor">Mostrar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="graficos" class="panel mb-4 overflow-hidden">
        <div class="panel-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h2 class="h5 mb-1">Gráficos e leitura do ano</h2>
                <p class="text-muted mb-0">Comparativo mensal de entradas, saídas, saldo e categorias de {{ $ano }}.</p>
            </div>
            <span class="badge-soft">{{ $ano }}</span>
        </div>

        <div class="p-3 p-lg-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="insight-card">
                        <div class="insight-label mb-2">Maior gasto</div>
                        <div class="h4 mb-1">{{ $mesMaiorGasto['label'] }}</div>
                        <div class="text-muted">R$ {{ number_format($mesMaiorGasto['despesas'], 2, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="insight-card">
                        <div class="insight-label mb-2">Menor gasto</div>
                        <div class="h4 mb-1">{{ $mesMenorGasto['label'] }}</div>
                        <div class="text-muted">R$ {{ number_format($mesMenorGasto['despesas'], 2, ',', '.') }}</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="insight-card">
                        <div class="insight-label mb-2">Receitas pendentes</div>
                        <div class="h4 mb-1">R$ {{ number_format($receitasPendentes, 2, ',', '.') }}</div>
                        <div class="text-muted">no mês filtrado</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="insight-card">
                        <div class="insight-label mb-2">Despesas pendentes</div>
                        <div class="h4 mb-1">R$ {{ number_format($despesasPendentes, 2, ',', '.') }}</div>
                        <div class="text-muted">no mês filtrado</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="h6 mb-0">Entradas, saídas e saldo por mês</h3>
                            <span class="text-muted small">Ano {{ $ano }}</span>
                        </div>
                        <div class="chart-box">
                            <canvas id="fluxoMensalChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="h6 mb-0">Gasto por categoria</h3>
                            <span class="text-muted small">{{ $mes }}/{{ $ano }}</span>
                        </div>
                        <div class="chart-box">
                            <canvas id="categoriasChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="h6 mb-0">Quanto foi gasto em cada mês</h3>
                            <span class="text-muted small">Saídas</span>
                        </div>
                        <div class="chart-box">
                            <canvas id="gastosMensaisChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                            <h3 class="h6 mb-0">Ranking de categorias</h3>
                            <span class="text-muted small">{{ $categoryStats->count() }}</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categoryStats as $category)
                                        <tr>
                                            <td>{{ $category['categoria'] }}</td>
                                            <td class="text-end">R$ {{ number_format($category['total'], 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">Sem gastos por categoria neste mês.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="receitas" class="panel mb-4 overflow-hidden">
        <div class="panel-header d-flex justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 mb-1">Receitas</h2>
                <p class="text-muted mb-0">Entradas pontuais e receitas mensais.</p>
            </div>
            <span class="badge-soft">{{ $receitas->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Pago</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receitas as $receita)
                        @php
                            $pago = $receita->data ? $receita->pago : (bool) ($receitasPagas[$receita->id] ?? false);
                        @endphp
                        <tr class="{{ $pago ? 'is-paid' : 'is-open' }}">
                            <td>{{ $receita->data ? $receita->data->format('d/m/Y') : 'Mensal' }}</td>
                            <td>{{ $receita->descricao ?? '-' }}</td>
                            <td class="text-end">R$ {{ number_format((float) $receita->valor, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input marcar-pago"
                                    data-id="{{ $receita->id }}"
                                    data-type="receitas"
                                    {{ $pago ? 'checked' : '' }}
                                >
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('receitas.edit', $receita->id) }}">Editar</a>
                                    <form action="{{ route('receitas.destroy', $receita->id) }}" method="POST" onsubmit="return confirm('Excluir esta receita?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nenhuma receita encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="despesas" class="panel overflow-hidden">
        <div class="panel-header d-flex justify-content-between align-items-center gap-3">
            <div>
                <h2 class="h5 mb-1">Despesas</h2>
                <p class="text-muted mb-0">Saídas lançadas no mês selecionado.</p>
            </div>
            <span class="badge-soft">{{ $despesas->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Categoria</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Pago</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($despesas as $despesa)
                        <tr class="{{ $despesa->pago ? 'is-paid' : 'is-open' }}">
                            <td>{{ $despesa->data ? $despesa->data->format('d/m/Y') : 'Mensal' }}</td>
                            <td>{{ $despesa->descricao ?? '-' }}</td>
                            <td>{{ $despesa->category->name ?? '-' }}</td>
                            <td class="text-end">R$ {{ number_format((float) $despesa->valor, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input marcar-pago"
                                    data-id="{{ $despesa->id }}"
                                    data-type="despesas"
                                    {{ $despesa->pago ? 'checked' : '' }}
                                >
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('despesas.edit', $despesa->id) }}">Editar</a>
                                    <form action="{{ route('despesas.destroy', $despesa->id) }}" method="POST" onsubmit="return confirm('Excluir esta despesa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma despesa encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const chartData = @json($chartData);
            const money = (value) => new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value || 0);

            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.dataset.label}: ${money(context.parsed.y ?? context.parsed)}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => money(value)
                        }
                    }
                }
            };

            const fluxoCanvas = document.getElementById('fluxoMensalChart');
            const gastosCanvas = document.getElementById('gastosMensaisChart');
            const categoriasCanvas = document.getElementById('categoriasChart');

            if (fluxoCanvas && window.Chart) {
                new Chart(fluxoCanvas, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [
                            {
                                label: 'Entradas',
                                data: chartData.receitas,
                                borderColor: '#237a57',
                                backgroundColor: 'rgba(35, 122, 87, 0.12)',
                                tension: 0.35,
                                fill: true
                            },
                            {
                                label: 'Saídas',
                                data: chartData.despesas,
                                borderColor: '#c2414b',
                                backgroundColor: 'rgba(194, 65, 75, 0.10)',
                                tension: 0.35,
                                fill: true
                            },
                            {
                                label: 'Saldo',
                                data: chartData.saldo,
                                borderColor: '#1f7a6b',
                                backgroundColor: 'rgba(31, 122, 107, 0.08)',
                                tension: 0.35
                            }
                        ]
                    },
                    options: chartDefaults
                });
            }

            if (gastosCanvas && window.Chart) {
                new Chart(gastosCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Gastos',
                            data: chartData.despesas,
                            backgroundColor: '#c2414b',
                            borderRadius: 6
                        }]
                    },
                    options: chartDefaults
                });
            }

            if (categoriasCanvas && window.Chart) {
                new Chart(categoriasCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.categoriasLabels.length ? chartData.categoriasLabels : ['Sem gastos'],
                        datasets: [{
                            label: 'Categorias',
                            data: chartData.categoriasValores.length ? chartData.categoriasValores : [1],
                            backgroundColor: ['#1f7a6b', '#c2414b', '#a16207', '#2563eb', '#7c3aed', '#0891b2', '#475569']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${money(context.parsed)}`
                                }
                            }
                        }
                    }
                });
            }

            document.querySelectorAll('.marcar-pago').forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const id = this.dataset.id;
                    const type = this.dataset.type;
                    const pago = this.checked;
                    const mes = document.getElementById('mes')?.value;
                    const ano = document.getElementById('ano')?.value;
                    const periodo = `${ano}-${mes}`;
                    const linha = this.closest('tr');

                    if (!csrfToken || !id || !type || !linha) {
                        return;
                    }

                    this.disabled = true;

                    fetch(`{{ url('/') }}/${type}/${id}/pagar`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ pago, mes: periodo })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.error || 'Erro ao atualizar pagamento.');
                            }

                            linha.classList.toggle('is-paid', pago);
                            linha.classList.toggle('is-open', !pago);
                        })
                        .catch(error => {
                            this.checked = !pago;
                            alert(error.message);
                        })
                        .finally(() => {
                            this.disabled = false;
                        });
                });
            });

            document.querySelectorAll('.toggle-valor').forEach(botao => {
                botao.addEventListener('click', function () {
                    const container = this.closest('.card-body');
                    const visivel = container.querySelector('.valor-visivel');
                    const mascarado = container.querySelector('.valor-mascarado');
                    const mostrando = !mascarado.classList.contains('d-none');

                    visivel.classList.toggle('d-none');
                    mascarado.classList.toggle('d-none');
                    this.textContent = mostrando ? 'Mostrar' : 'Ocultar';
                });
            });
        });
    </script>
@endsection
