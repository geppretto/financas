@extends('layouts.app')

@section('title', 'Relatórios - Controle Financeiro')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Relatório Anual</h1>

    <!-- Year Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="flex items-center gap-4">
            <label class="text-sm font-medium text-gray-700">Ano:</label>
            <select name="year" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </form>
    </div>

    <!-- Annual Summary -->
    @php
        $totalEntradasAnual = array_sum(array_column($monthlyData, 'entradas'));
        $totalSaidasAnual = array_sum(array_column($monthlyData, 'saidas'));
        $saldoAnual = $totalEntradasAnual - $totalSaidasAnual;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Entradas {{ $year }}</p>
                    <p class="text-3xl font-bold">R$ {{ number_format($totalEntradasAnual, 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-trend-up text-4xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Total Saídas {{ $year }}</p>
                    <p class="text-3xl font-bold">R$ {{ number_format($totalSaidasAnual, 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-trend-down text-4xl text-red-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Saldo Anual {{ $year }}</p>
                    <p class="text-3xl font-bold">R$ {{ number_format($saldoAnual, 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-chart-line text-4xl text-blue-200"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Report Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Relatório Mensal - {{ $year }}</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entradas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saídas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">% Economia</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $meses = [
                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                    ];
                @endphp
                
                @foreach($monthlyData as $month => $data)
                    @php
                        $economia = $data['entradas'] > 0 ? (($data['saldo'] / $data['entradas']) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">{{ $meses[$month] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-green-600 font-semibold">
                                <i class="fas fa-arrow-up"></i> R$ {{ number_format($data['entradas'], 2, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-red-600 font-semibold">
                                <i class="fas fa-arrow-down"></i> R$ {{ number_format($data['saidas'], 2, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold {{ $data['saldo'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                R$ {{ number_format($data['saldo'], 2, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2" style="max-width: 100px;">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min(abs($economia), 100) }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $economia >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($economia, 1) }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr class="font-bold">
                    <td class="px-6 py-4 text-sm text-gray-900">TOTAL</td>
                    <td class="px-6 py-4 text-sm text-green-600">
                        <i class="fas fa-arrow-up"></i> R$ {{ number_format($totalEntradasAnual, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-sm text-red-600">
                        <i class="fas fa-arrow-down"></i> R$ {{ number_format($totalSaidasAnual, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-sm {{ $saldoAnual >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        R$ {{ number_format($saldoAnual, 2, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @php
                            $economiaAnual = $totalEntradasAnual > 0 ? (($saldoAnual / $totalEntradasAnual) * 100) : 0;
                        @endphp
                        <span class="{{ $economiaAnual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($economiaAnual, 1) }}%
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Chart -->
    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Gráfico Anual</h2>
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [
                {
                    label: 'Entradas',
                    data: [
                        @foreach($monthlyData as $data)
                            {{ $data['entradas'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Saídas',
                    data: [
                        @foreach($monthlyData as $data)
                            {{ $data['saidas'] }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Saldo',
                    data: [
                        @foreach($monthlyData as $data)
                            {{ $data['saldo'] }},
                        @endforeach
                    ],
                    type: 'line',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Movimentação Financeira Mensal - {{ $year }}'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    });
</script>
@endsection