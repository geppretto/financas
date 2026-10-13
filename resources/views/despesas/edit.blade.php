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

                    @if ($despesa->recorrente)
                        <div class="col-md-6">
                            <label for="recorrencia_fim" class="form-label">Encerrar recorrência em (opcional)</label>
                            <input type="date" class="form-control" id="recorrencia_fim" name="recorrencia_fim"
                                   value="{{ old('recorrencia_fim', optional($despesa->recorrencia_fim)->format('Y-m-d')) }}">
                            <div class="form-text">Despesa recorrente: repete todo dia {{ $despesa->data->format('d') }}. Deixe vazio para continuar indefinidamente.</div>
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label for="origem" class="form-label">Origem</label>
                        <input type="text" class="form-control" id="origem" name="origem" maxlength="100" list="origensLista" autocomplete="off"
                               placeholder="Ex: Shopee, Nubank, Jeitto" value="{{ old('origem', $despesa->origem) }}">
                        <datalist id="origensLista">
                            @foreach ($origens as $origemSugestao)
                                <option value="{{ $origemSugestao }}">
                            @endforeach
                        </datalist>
                        <div class="form-text">A categoria diz o tipo (Empréstimo, Crédito); a origem diz qual (Shopee, Nubank...).</div>
                    </div>

                    @if (($serieTotal ?? 0) > 1)
                        <div class="col-12">
                            <div class="form-check p-3 border rounded bg-light ps-5">
                                <input class="form-check-input" type="checkbox" name="aplicar_serie" id="aplicar_serie" value="1" {{ old('aplicar_serie') ? 'checked' : '' }}>
                                <label class="form-check-label" for="aplicar_serie">
                                    <strong>Aplicar a todas as {{ $serieTotal }} parcelas desta compra</strong>
                                    <div class="text-muted small">
                                        Desloca as datas de todas as parcelas pelo mesmo intervalo (ex.: mudar esta de novembro para outubro move todas um mês para trás),
                                        atualiza nome, categoria e valor, e mantém quais parcelas estão pagas.
                                    </div>
                                </label>
                            </div>
                        </div>

 <div class="col-12">
                            <div class="form-check p-3 border rounded bg-light ps-5">
                                <input class="form-check-input" type="checkbox" name="sequencia_mensal" id="sequencia_mensal" value="1" {{ old('sequencia_mensal') ? 'checked' : '' }}>
                                <label class="form-check-label" for="sequencia_mensal">
                                    <strong>Reorganizar as datas em sequência mensal, a partir desta parcela</strong>
                                    <div class="text-muted small">
                                        Use quando houver meses "pulados" entre as parcelas. Esta parcela mantém a data informada acima e as demais
                                        ficam uma por mês: a anterior um mês antes, a seguinte um mês depois, e assim por diante. Já considera "aplicar a todas".
                                    </div>
                                </label>
                            </div>
                        </div>

 <div class="col-md-6">
                            <label for="total_parcelas" class="form-label">Total de parcelas da compra</label>
                            <input type="number" class="form-control" id="total_parcelas" name="total_parcelas"
                                   min="2" max="120" value="{{ old('total_parcelas', $totalDeclarado) }}">
                            <div class="form-text">
                                Errou a quantidade? Aumente (ex.: 6 para 8) e as parcelas que faltam são criadas, pendentes, um mês após a última.
                                Ao diminuir, as excedentes são removidas (só se estiverem pendentes).
                            </div>
                            @error('total_parcelas')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="parcelas_pagas" class="form-label">Parcelas já pagas (de {{ $serieTotal }})</label>
                            <input type="number" class="form-control" id="parcelas_pagas" name="parcelas_pagas"
                                   min="0" max="{{ $serieTotal }}" value="{{ old('parcelas_pagas', $seriePagas) }}">
                            <div class="form-text">As primeiras N parcelas, em ordem, ficam pagas e as demais pendentes. Vale mesmo sem marcar "aplicar a todas".</div>
                        </div>
                    @endif

                    <div class="col-12">
                        <label for="notas" class="form-label">Notas adicionais (opcional)</label>
                        <textarea class="form-control" id="notas" name="notas" rows="3" maxlength="1000">{{ old('notas', $despesa->notas) }}</textarea>
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
                @php $totalLista = $despesas->count(); @endphp
                @forelse ($despesas as $despesaRelacionada)
                    @php $atual = $despesaRelacionada->id === $despesa->id; @endphp
                    <a href="{{ $atual ? '#' : route('despesas.edit', $despesaRelacionada->id) }}"
                       class="list-group-item list-group-item-action {{ $atual ? 'active-parcela' : '' }}"
                       @if($atual) aria-current="true" @endif
                       style="{{ $atual ? 'background:#fff1f2;border-left:4px solid #dc3545;' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-semibold">{{ $despesaRelacionada->descricao }}</div>
                            @if ($atual)
                                <span class="badge text-bg-danger flex-shrink-0">Editando</span>
                            @endif
                        </div>
                        <div class="text-muted small">R$ {{ number_format((float) $despesaRelacionada->valor, 2, ',', '.') }} • {{ optional($despesaRelacionada->data)->format('d/m/Y') }} • {{ $despesaRelacionada->pago ? 'paga' : 'pendente' }}</div>
                    </a>
                @empty
                    <div class="p-4 text-muted">Nenhuma despesa relacionada.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
