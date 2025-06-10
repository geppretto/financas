@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="text-primary">Resumo Financeiro {{strtoupper($user->name)}}</h1>
        <div>
            <a href="{{ route('receitas.create') }}" class="btn btn-success me-2">+ Nova Receita</a>
            <a href="{{ route('despesas.create') }}" class="btn btn-danger">+ Nova Despesa</a>
        </div>
    </div>

    <form method="GET" action="{{ route('resumo') }}" class="row g-3 align-items-end mb-4">
        <div class="col-auto">
            <label for="mes" class="form-label">Mês</label>
            <select name="mes" id="mes" class="form-select">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $mes == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <label for="ano" class="form-label">Ano</label>
            <select name="ano" id="ano" class="form-select">
                @for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
                    <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <div class="row mb-5">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Total Recebido</div>
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center">
            <span class="valor-mascarado d-none">R$ {{ number_format($salarioAll, 2, ',', '.') }}</span>
            <span class="valor-visivel">••••••</span>
            <button type="button" class="btn btn-sm btn-light ms-2 toggle-valor" title="Mostrar/Ocultar">
                👁️
            </button>
        </h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">Total Gasto</div>
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center">
            <span class="valor-mascarado d-none">R$ {{ number_format($despesasAll, 2, ',', '.') }}</span>
            <span class="valor-visivel">••••••</span>
            <button type="button" class="btn btn-sm btn-light ms-2 toggle-valor" title="Mostrar/Ocultar">
                👁️
            </button>
        </h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Saldo</div>
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center">
            <span class="valor-mascarado d-none">R$ {{ number_format($saldo, 2, ',', '.') }}</span>
            <span class="valor-visivel">••••••</span>
            <button type="button" class="btn btn-sm btn-light ms-2 toggle-valor" title="Mostrar/Ocultar">
                👁️
            </button>
        </h5>
                </div>
            </div>
        </div>
    </div>
    <h2>Receitas</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receitas as $receita)
                @php
    $pago = $receita->data
        ? ($receitasComData[$receita->id] ?? false)
        : ($pagamentosReceitas[$receita->id] ?? false);
@endphp
                <tr class="linha-pago {{ $pago ? 'table-success' : 'table-danger' }}">
                    <td>{{ $receita->data ?? '-'}}</td>
                    <td>{{ $receita->descricao ?? '-'}}</td>
                    <td>R$ {{ number_format($receita->valor, 2, ',', '.') ?? '-'}}</td>
                    <td>
                        <input 
                            type="checkbox" 
                            class="form-check-input marcar-pago" 
                            data-id="{{ $receita->id }}" 
                            data-type="receitas"
                            {{ $pago ? 'checked' : '' }}>
                    </td>
                </tr>
            @endforeach
           <tr class="linha-pago {{ $salarioPago ? 'table-success' : 'table-danger' }}">
                <td>Mensal</td>
                <td>{{ $salario?->descricao ?? '-' }}</td>
                <td>R$ {{ number_format($salario->valor, 2, ',', '.') ?? '-'}}</td>
                <td>
                    <input 
                        type="checkbox" 
                        class="form-check-input marcar-pago" 
                        data-id="{{ $salario->id }}" 
                        data-type="receitas"
                        {{ $salarioPago ? 'checked' : '' }}>
                </td>
            </tr>
        </tbody>
    </table>
    <h2>Despesas</h2>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach($despesas as $despesa)
            @php
    $pago = $despesa->data
        ? ($despesasComData[$despesa->id] ?? false)
        : ($pagamentosdespesas[$despesa->id] ?? false);
@endphp
<tr class="linha-pago {{ $despesa->pago ? 'table-success' : 'table-danger' }}">
    <td>{{ $despesa->data ?? 'Mensal' }}</td>
    <td>{{ $despesa->descricao ?? '-'}}</td>
    <td>R$ {{ number_format($despesa->valor, 2, ',', '.') ?? '-'}}</td>
    <td>
        <input 
            type="checkbox" 
            class="form-check-input marcar-pago" 
            data-id="{{ $despesa->id }}" 
            data-type="despesas"
            {{ $despesa->pago ? 'checked' : '' }}>
    </td>
</tr>
@endforeach
            <tr class="linha-pago {{ $despesaSemprePago ? 'table-success' : 'table-danger' }}">
    <td>Mensal</td>
    <td>{{ $despesasSempre->descricao ?? '-'}}</td>
    <td>R$ {{ number_format($despesasSempre->valor, 2, ',', '.') ?? '-'}}</td>
    <td>
        <input 
            type="checkbox" 
            class="form-check-input marcar-pago" 
            data-id="{{ $despesasSempre->id }}" 
            data-type="despesas"
            {{ $despesaSemprePago ? 'checked' : '' }}>
    </td>
</tr>
        </tbody>
    </table>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {
    // CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!csrfToken) {
        console.error('CSRF token não encontrado!');
        return;
    }

    // Checkboxes: marcar como pago
    document.querySelectorAll('.marcar-pago').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const pago = this.checked;
            const mes = document.getElementById('mes')?.value;
            const ano = document.getElementById('ano')?.value;
            const periodo = `${ano}-${mes}`;

            if (!id || !type) return;

            const linha = this.closest('tr');

            fetch(`/${type}/${id}/pagar`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ pago, mes: periodo })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`${type} ${id} atualizado com sucesso`);
                    linha.classList.remove('table-success', 'table-danger');
                    linha.classList.add(pago ? 'table-success' : 'table-danger');
                } else {
                    alert(`Erro ao atualizar ${type}`);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
            });
        });
    });

    // Botão "olhinho": mostrar/ocultar valores
    document.querySelectorAll('.toggle-valor').forEach(botao => {
        botao.addEventListener('click', function () {
            const container = this.closest('.card-title') ?? this.closest('td');
            const visivel = container.querySelector('.valor-visivel');
            const mascarado = container.querySelector('.valor-mascarado');

            visivel.classList.toggle('d-none');
            mascarado.classList.toggle('d-none');
        });
    });
});
</script>
