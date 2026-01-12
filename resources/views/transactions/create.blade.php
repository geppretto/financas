{{-- resources/views/transactions/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Nova Transação - Controle Financeiro')

@section('content')
<div class="min-h-[calc(100vh-6rem)] bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-blue-950/30 dark:to-indigo-950/20 flex items-start justify-center px-4 py-10 relative overflow-hidden">
    {{-- Elementos decorativos de fundo --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-200/30 to-indigo-200/30 dark:from-blue-500/10 dark:to-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-purple-200/20 to-pink-200/20 dark:from-purple-500/5 dark:to-pink-500/5 rounded-full blur-3xl animate-pulse animation-delay-2000"></div>
    </div>

    <div class="w-full max-w-3xl relative z-10">
        {{-- Header --}}
        <div class="mb-8 animate-fade-in">
            <div class="inline-flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 font-medium mb-4 backdrop-blur-sm bg-white/50 dark:bg-slate-900/50 px-3 py-1 rounded-full border border-blue-200/50 dark:border-blue-700/50">
                <i class="fas fa-chart-line text-xs"></i>
                <span>Finanças</span>
                <span class="opacity-50">/</span>
                <span class="text-blue-700 dark:text-blue-300">Nova Transação</span>
            </div>

            <div class="mt-4 flex items-end justify-between gap-4">
                <div>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-600 dark:from-blue-400 dark:via-blue-300 dark:to-indigo-400 bg-clip-text text-transparent pb-2">
                        Nova Transação
                    </h1>
                    <p class="mt-3 text-base text-gray-600 dark:text-gray-300">
                        <i class="fas fa-sparkles text-yellow-500 mr-2"></i>
                        Registre uma entrada ou saída com categoria e observações
                    </p>
                </div>

                <a href="{{ route('transactions.index') }}"
                   class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-blue-200 dark:border-blue-700 bg-white/80 dark:bg-slate-900/60 backdrop-blur-sm px-4 py-2 text-sm font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-slate-800 hover:border-blue-300 dark:hover:border-blue-600 transition duration-300 shadow-sm hover:shadow-md">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Voltar
                </a>
            </div>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-gray-200/70 dark:border-gray-700/70 bg-white/95 dark:bg-slate-900/60 shadow-2xl shadow-blue-500/10 dark:shadow-blue-900/20 backdrop-blur-xl animate-fade-in-up">
            <div class="p-6 sm:p-8">
                {{-- (Opcional) erros --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 dark:border-red-900/30 bg-red-50 dark:bg-red-950/20 px-4 py-4 text-sm text-red-700 dark:text-red-300 animate-shake backdrop-blur">
                        <div class="flex items-center gap-2 font-semibold mb-2">
                            <i class="fas fa-exclamation-circle"></i>
                            Verifique os campos:
                        </div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('transactions.store') }}" class="space-y-6">
                    @csrf

                    {{-- Tipo + Valor --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-exchange-alt text-blue-500"></i>
                                Tipo <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select name="type" required
                                        class="w-full appearance-none rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-4 py-3 pr-10 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition group-hover:border-blue-300 dark:group-hover:border-blue-600">
                                    <option value="">Selecione...</option>
                                    <option value="entrada" {{ old('type') == 'entrada' ? 'selected' : '' }}>✅ Entrada</option>
                                    <option value="saida" {{ old('type') == 'saida' ? 'selected' : '' }}>📤 Saída</option>
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-blue-500 transition">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-green-500"></i>
                                Valor <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm font-semibold group-hover:text-green-500 transition">
                                    R$
                                </span>

                                <input type="number" name="amount" step="0.01" required value="{{ old('amount') }}"
                                       inputmode="decimal"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 pl-10 pr-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition group-hover:border-green-300 dark:group-hover:border-green-600"
                                       placeholder="0,00">
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Use ponto para centavos (ex.: 10.50).</p>
                        </div>
                    </div>

                    {{-- Descrição --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            Descrição <span class="text-red-500">*</span>
                        </label>

                        <div class="relative group">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-purple-500 transition">
                                <i class="fas fa-pen text-sm"></i>
                            </span>

                            <input type="text" name="description" required value="{{ old('description') }}"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 pl-10 pr-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-purple-500/20 focus:border-purple-500 transition group-hover:border-purple-300 dark:group-hover:border-purple-600"
                                   placeholder="Ex: Salário, Conta de luz, Compra no mercado...">
                        </div>
                    </div>

                    {{-- Pagamento + Parcelas --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-credit-card text-orange-500"></i>
                                Tipo de Pagamento <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select name="payment_type" id="payment_type" required
                                        class="w-full appearance-none rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-4 py-3 pr-10 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-orange-500/20 focus:border-orange-500 transition group-hover:border-orange-300 dark:group-hover:border-orange-600"
                                        onchange="toggleInstallments()">
                                    <option value="">Selecione...</option>
                                    <option value="avista" {{ old('payment_type') == 'avista' ? 'selected' : '' }}>À Vista</option>
                                    <option value="parcelado" {{ old('payment_type') == 'parcelado' ? 'selected' : '' }}>Parcelado</option>
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-orange-500 transition">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>

                        <div id="installments_div" class="hidden opacity-0 transition-all duration-300">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-layer-group text-indigo-500"></i>
                                Número de Parcelas <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </span>

                                <input type="number" name="installments" id="installments" min="1" max="60"
                                       value="{{ old('installments', 1) }}"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 pl-10 pr-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition hover:border-indigo-300 dark:hover:border-indigo-600"
                                       placeholder="Ex: 12">
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Máx. 60 parcelas.</p>
                        </div>
                    </div>

                    {{-- Data + Categoria --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-cyan-500"></i>
                                Data da Transação <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 group-hover:text-cyan-500 transition">
                                    <i class="fas fa-calendar-alt text-sm"></i>
                                </span>

                                <input type="date" name="transaction_date" required value="{{ old('transaction_date', date('Y-m-d')) }}"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 pl-10 pr-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 transition group-hover:border-cyan-300 dark:group-hover:border-cyan-600">
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-tag text-pink-500"></i>
                                Categoria
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <i class="fas fa-tag text-sm"></i>
                                </span>

                                <input type="text" name="category" value="{{ old('category') }}"
                                       list="categories"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 pl-10 pr-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition"
                                       placeholder="Ex: Alimentação, Transporte...">

                                <datalist id="categories">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>

                    {{-- Observações --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-amber-500"></i>
                            Observações
                        </label>

                        <textarea name="notes" rows="4"
                                  class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-4 py-3 text-gray-900 dark:text-gray-100 shadow-sm outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition hover:border-amber-300 dark:hover:border-amber-600"
                                  placeholder="Informações adicionais...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex flex-col sm:flex-row gap-3">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-3 font-bold text-white shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-500/30 transition duration-300 transform">
                            <i class="fas fa-save text-sm"></i>
                            Salvar Transação
                        </button>

                        <a href="{{ route('transactions.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-6 py-3 font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-900 hover:scale-105 transition duration-300 transform">
                            <i class="fas fa-times text-sm"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Footer hint --}}
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
            Campos com <span class="text-red-500 font-semibold">*</span> são obrigatórios.
        </p>
    </div>
</div>

<script>
    function toggleInstallments() {
        const paymentType = document.getElementById('payment_type').value;
        const installmentsDiv = document.getElementById('installments_div');
        const installmentsInput = document.getElementById('installments');

        if (paymentType === 'parcelado') {
            installmentsDiv.classList.remove('hidden');
            installmentsDiv.classList.add('opacity-100');
            installmentsInput.required = true;
        } else {
            installmentsDiv.classList.add('hidden');
            installmentsDiv.classList.remove('opacity-100');
            installmentsInput.required = false;
            installmentsInput.value = 1;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        toggleInstallments();
        
        // Add fade-in effect to all inputs on focus
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.style.transform = 'scale(1.01)';
            });
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.style.transform = 'scale(1)';
            });
        });
    });
</script>
@endsection
