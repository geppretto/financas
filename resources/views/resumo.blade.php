<a href="{{ route('receitas.create') }}">Nova Receita</a> |
<a href="{{ route('despesas.create') }}">Nova Despesa</a>
<form method="GET" action="{{ route('resumo') }}" class="mb-4">
    <label for="mes">Mês:</label>
    <select name="mes" id="mes">
        @for ($i = 1; $i <= 12; $i++)
            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" {{ $mes == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
            </option>
        @endfor
    </select>

    <label for="ano">Ano:</label>
    <select name="ano" id="ano">
        @for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++)
            <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
    </select>

    <button type="submit">Filtrar</button>
</form>
<h1>Resumo Financeiro do Mês</h1>

<p><strong>Total Recebido:</strong> R$ {{ number_format($salarioAll, 2, ',', '.') }}</p>
<p><strong>Total Gasto:</strong> R$ {{ number_format($despesasAll, 2, ',', '.') }}</p>
<p><strong>Saldo:</strong> R$ {{ number_format($saldo, 2, ',', '.') }}</p>

<h2>Receitas</h2>
<ul>

@foreach($receitas as $receita)
    <li>{{ $receita->data }} - {{ $receita->descricao }}: R$ {{ number_format($receita->valor, 2, ',', '.') }}</li>
@endforeach

    <li>{{ $salario->descricao }}: R$ {{ number_format($salario->valor, 2, ',', '.') }}</li>
</ul>

<h2>Despesas</h2>
<ul>
@foreach($despesas as $despesa)
    <li>{{ $despesa->data }} - {{ $despesa->descricao }}: R$ {{ number_format($despesa->valor, 2, ',', '.') }}</li>
@endforeach
    <li>{{ $despesasSempre->descricao }}: R$ {{ number_format($despesasSempre->valor, 2, ',', '.') }}</li>
</ul>
