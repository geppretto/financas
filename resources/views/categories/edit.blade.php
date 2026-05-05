@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Editar categoria</h1>
        <p class="text-muted mb-0">Atualize o nome usado para agrupar suas despesas.</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0">Dados da categoria</h2>
    </div>
    <div class="p-4">
        <form action="{{ route('categories.update', $category->id) }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-12">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
