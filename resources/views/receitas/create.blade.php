@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Nova receita</h1>
        <p class="text-muted mb-0">Cadastre uma entrada pontual ou deixe a data vazia para uma receita mensal.</p>
    </div>
    <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0">Dados da receita</h2>
    </div>
    <div class="p-4">
        <form action="{{ route('receitas.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao') }}" required>
            </div>

            <div class="col-md-6">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0.01" value="{{ old('valor') }}" required>
            </div>

            <div class="col-md-6">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="{{ old('data') }}">
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button type="submit" class="btn btn-success">Salvar receita</button>
                <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
