@extends('layouts.app')

@section('title', 'Transações - Controle Financeiro')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-4">Transações Financeiras</h1>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-500 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Total Entradas</p>
                    <p class="text-2xl font-bold">R$ {{ number_format($summary['total_entradas'], 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-up text-3xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-red-500 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Total Saídas</p>
                    <p class="text-2xl font-bold">R$ {{ number_format($summary['total_saidas'], 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-arrow-down text-3xl text-red-200"></i>
            </div>
        </div>

        <div class="bg-yellow-500 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">Pendentes</p>
                    <p class="text-2xl font-bold">R$ {{ number_format($summary['pendentes'], 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-clock text-3xl text-yellow-200"></i>
            </div>
        </div>

        <div class="bg-blue-500 text-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Saldo</p>
                    <p class="text-2xl font-bold">R$ {{ number_format($summary['saldo'], 2, ',', '.') }}</p>
                </div>
                <i class="fas fa-balance-scale text-3xl text-blue-200"></i>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Filtros</h2>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mês</label>
                <select name="month" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ano</label>
                <select name="year" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ request('year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select name="type" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('type') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ request('type') == 'saida' ? 'selected' : '' }}>Saída</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todos</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter mr-2"></i>Filtrar
                </button>
                <a href="{{ route('transactions.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    <i class="fas fa-redo mr-2"></i>Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagamento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            {{ $transaction->due_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $transaction->description }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($transaction->category)
                                <span class="bg-gray-200 px-2 py-1 rounded text-xs">{{ $transaction->category }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($transaction->type === 'entrada')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">
                                    <i class="fas fa-arrow-up"></i> Entrada
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">
                                    <i class="fas fa-arrow-down"></i> Saída
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($transaction->payment_type === 'parcelado')
                                <span class="text-orange-600">
                                    <i class="fas fa-credit-card"></i> Parcelado
                                    @if($transaction->current_installment > 0)
                                        ({{ $transaction->current_installment }}/{{ $transaction->installments }})
                                    @endif
                                </span>
                            @else
                                <span class="text-blue-600"><i class="fas fa-money-bill"></i> À vista</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form method="POST" action="{{ route('transactions.toggle-status', $transaction) }}" class="inline">
                                @csrf
                                <button type="submit" class="focus:outline-none">
                                    @if($transaction->status === 'pago')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold cursor-pointer hover:bg-green-200">
                                            <i class="fas fa-check-circle"></i> Pago
                                        </span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-semibold cursor-pointer hover:bg-yellow-200">
                                            <i class="fas fa-clock"></i> Pendente
                                        </span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            Nenhuma transação encontrada. <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:underline">Cadastre a primeira</a>!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection