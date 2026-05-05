@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Editar despesa</h1>
        <p class="text-muted mb-0">Atualize os dados do lançamento selecionado.</p>
    </div>
    <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="panel">
            <div class="panel-header">
                <h2 class="h5 mb-0">Dados da despesa</h2>
            </div>
            <div class="p-4">
                <form action="{{ route('despesas.update', $despesa->id) }}" method="POST" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-12">
                        <label for="descricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" value="{{ old('descricao', $despesa->descricao) }}" id="descricao" name="descricao" required>
                    </div>

                    <div class="col-md-4">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Sem categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $despesa->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="valor" class="form-label">Valor (R$)</label>
                        <input type="number" step="0.01" min="0.01" value="{{ old('valor', $despesa->valor) }}" class="form-control" id="valor" name="valor" required>
                    </div>

                    <div class="col-md-4">
                        <label for="data" class="form-label">Data</label>
                        <input type="date" class="form-control" value="{{ old('data', optional($despesa->data)->format('Y-m-d')) }}" id="data" name="data" required>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                        <button type="submit" class="btn btn-danger">Salvar despesa</button>
                        <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel overflow-hidden">
            <div class="panel-header">
                <h2 class="h5 mb-0">Despesas relacionadas</h2>
            </div>
            <div class="list-group list-group-flush">
                @forelse ($despesas->where('id', '!=', $despesa->id) as $despesaRelacionada)
                    <a href="{{ route('despesas.edit', $despesaRelacionada->id) }}" class="list-group-item list-group-item-action">
                        <div class="fw-semibold">{{ $despesaRelacionada->descricao }}</div>
                        <div class="text-muted small">R$ {{ number_format((float) $despesaRelacionada->valor, 2, ',', '.') }}</div>
                    </a>
                @empty
                    <div class="p-4 text-muted">Nenhuma despesa relacionada.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
