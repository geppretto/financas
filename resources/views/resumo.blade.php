<a href="{{ route('receitas.create') }}">Nova Receita</a> |
<a href="{{ route('despesas.create') }}">Nova Despesa</a>

<h1>Resumo Financeiro do Mês</h1>

<p><strong>Total Recebido:</strong> R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
<p><strong>Total Gasto:</strong> R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
<p><strong>Saldo:</strong> R$ {{ number_format($saldo, 2, ',', '.') }}</p>

<h2>Receitas</h2>
<ul>
@foreach($receitas as $r)
    <li>{{ $r->data }} - {{ $r->descricao }}: R$ {{ number_format($r->valor, 2, ',', '.') }}</li>
@endforeach
</ul>

<h2>Despesas</h2>
<ul>
@foreach($despesas as $d)
    <li>{{ $d->data }} - {{ $d->descricao }}: R$ {{ number_format($d->valor, 2, ',', '.') }}</li>
@endforeach
</ul>
