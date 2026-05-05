@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">Nova despesa</h1>
        <p class="text-muted mb-0">Lance uma saída simples ou divida em parcelas.</p>
    </div>
    <a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="h5 mb-0">Dados da despesa</h2>
    </div>
    <div class="p-4">
        <form action="{{ route('despesas.store') }}" method="POST" class="row g-3">
            @csrf

            <div class="col-12">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="{{ old('descricao') }}" required>
            </div>

            <div class="col-md-4">
                <label for="category_id" class="form-label">Categoria</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Sem categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="number" step="0.01" min="0.01" class="form-control" id="valor" name="valor" value="{{ old('valor') }}" required>
            </div>

            <div class="col-md-4">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="{{ old('data') }}" required>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="tem_parcelas" name="tem_parcelas" {{ old('tem_parcelas') ? 'checked' : '' }}>
                    <label class="form-check-label" for="tem_parcelas">Dividir em parcelas</label>
                </div>
            </div>

            <div id="parcelas_opcoes" class="col-12 {{ old('tem_parcelas') ? '' : 'd-none' }}">
                <div class="row g-3 p-3 border rounded bg-light">
                    <div class="col-md-6">
                        <label for="quantidade_parcelas" class="form-label">Quantidade de parcelas</label>
                        <input type="number" class="form-control" id="quantidade_parcelas" name="quantidade_parcelas" min="2" max="120" value="{{ old('quantidade_parcelas') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="parcelas_iguais" class="form-label">Parcelas com o mesmo valor?</label>
                        <select class="form-select" id="parcelas_iguais" name="parcelas_iguais">
                            <option value="">Selecione</option>
                            <option value="1" {{ old('parcelas_iguais') === '1' ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ old('parcelas_iguais') === '0' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="parcelas_diferentes_campos" class="col-12 d-none">
                <div class="alert alert-info mb-3">Informe o valor e a data de cada parcela.</div>
                <div id="parcelas_campos" class="row g-3"></div>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button type="submit" class="btn btn-danger">Salvar despesa</button>
                <a href="{{ route('resumo') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const temParcelas = document.getElementById('tem_parcelas');
    const opcoesParcelas = document.getElementById('parcelas_opcoes');
    const parcelasIguais = document.getElementById('parcelas_iguais');
    const parcelasDiferentesCampos = document.getElementById('parcelas_diferentes_campos');
    const parcelasCamposContainer = document.getElementById('parcelas_campos');
    const quantidadeParcelasInput = document.getElementById('quantidade_parcelas');

    temParcelas.addEventListener('change', function () {
        opcoesParcelas.classList.toggle('d-none', !this.checked);
        if (!this.checked) {
            parcelasDiferentesCampos.classList.add('d-none');
            parcelasCamposContainer.innerHTML = '';
        }
    });

    parcelasIguais.addEventListener('change', function () {
        if (this.value === '0') {
            gerarCamposParcelas();
        } else {
            parcelasDiferentesCampos.classList.add('d-none');
            parcelasCamposContainer.innerHTML = '';
        }
    });

    quantidadeParcelasInput.addEventListener('input', function () {
        if (parcelasIguais.value === '0') {
            gerarCamposParcelas();
        }
    });

    function gerarCamposParcelas() {
        const quantidade = parseInt(quantidadeParcelasInput.value);
        parcelasCamposContainer.innerHTML = '';

        if (!Number.isInteger(quantidade) || quantidade < 2) {
            parcelasDiferentesCampos.classList.add('d-none');
            return;
        }

        for (let i = 0; i < quantidade; i++) {
            const div = document.createElement('div');
            div.classList.add('col-md-6');
            div.innerHTML = `
                <div class="border rounded bg-light p-3 h-100">
                    <h3 class="h6 mb-3">Parcela ${i + 1}</h3>
                    <label class="form-label">Valor (R$)</label>
                    <input type="number" step="0.01" min="0.01" name="valor_parcela[]" class="form-control mb-3" required>

                    <label class="form-label">Data</label>
                    <input type="date" name="data_parcela[]" class="form-control" required>
                </div>
            `;
            parcelasCamposContainer.appendChild(div);
        }

        parcelasDiferentesCampos.classList.remove('d-none');
    }
});
</script>
@endsection
