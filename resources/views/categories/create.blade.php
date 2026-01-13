@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <h3 class="mb-0">Nova Categoria</h3>
            <div class="text-end"><a href="{{ route('resumo', ['mes' => $mes, 'ano' => $ano]) }}" class="btn btn-secondary">Volta</a></div>
        </div>
        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <button type="submit" class="btn btn-danger mt-3">Salvar Categoria</button>
                <a href="{{ route('resumo') }}" class="btn btn-secondary mt-3">Cancelar</a>
            </form>
        </div>
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
        opcoesParcelas.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            parcelasDiferentesCampos.style.display = 'none';
            parcelasCamposContainer.innerHTML = '';
        }
    });

    parcelasIguais.addEventListener('change', function () {
        if (this.value === '0') {
            gerarCamposParcelas();
        } else {
            parcelasDiferentesCampos.style.display = 'none';
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
        if (!isNaN(quantidade) && quantidade > 1) {
            for (let i = 0; i < quantidade; i++) {
                const div = document.createElement('div');
                div.classList.add('mb-3', 'border', 'p-3', 'rounded', 'bg-light');
                div.innerHTML = `
                    <label class="form-label">Parcela ${i + 1} - Valor (R$)</label>
                    <input type="number" step="0.01" name="valor_parcela[]" class="form-control mb-2" required>

                    <label class="form-label">Parcela ${i + 1} - Data</label>
                    <input type="date" name="data_parcela[]" class="form-control" required>
                `;
                parcelasCamposContainer.appendChild(div);
            }
            parcelasDiferentesCampos.style.display = 'block';
        } else {
            parcelasDiferentesCampos.style.display = 'none';
        }
    }
});
</script>
@endsection
