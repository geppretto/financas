@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Nova categoria</h1>
        <p class="text-muted mb-0">Organize suas despesas por grupos fáceis de comparar.</p>
    </div>
    <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0">Dados da categoria</h2>
    </div>
    <div class="p-4">
        <form action="{{ route('categories.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Salvar categoria</button>
                <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
