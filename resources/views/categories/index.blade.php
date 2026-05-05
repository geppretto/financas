@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Categorias</h1>
        <p class="text-muted mb-0">Crie, edite e exclua os grupos usados nas suas despesas.</p>
    </div>
    <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel">
            <div class="panel-header">
                <h2 class="h5 mb-0">Nova categoria</h2>
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
    </div>

    <div class="col-lg-8">
        <div class="panel overflow-hidden">
            <div class="panel-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Minhas categorias</h2>
                    <p class="text-muted mb-0">Ao excluir, as despesas ficam salvas sem categoria.</p>
                </div>
                <span class="badge-soft">{{ $categories->count() }}</span>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th class="text-center">Despesas</th>
                            <th class="text-end">Total vinculado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td class="text-center">{{ $category->despesas_count }}</td>
                                <td class="text-end">R$ {{ number_format((float) ($category->despesas_sum_valor ?? 0), 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Excluir esta categoria? As despesas vinculadas serão mantidas sem categoria.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Nenhuma categoria cadastrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
