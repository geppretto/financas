@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Editar receita</h1>
        <p class="text-muted mb-0">Atualize os dados da entrada selecionada.</p>
    </div>
    <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0">Dados da receita</h2>
    </div>
    <div class="p-4">
        <form action="{{ route('receitas.update', $receita->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao', $receita->descricao) }}" required>
            </div>

            <div class="col-md-6">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="number" class="form-control" id="valor" name="valor" step="0.01" min="0.01" value="{{ old('valor', $receita->valor) }}" required>
            </div>

            <div class="col-md-6">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="{{ old('data', optional($receita->data)->format('Y-m-d')) }}">
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button type="submit" class="btn btn-success">Salvar alterações</button>
                <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
