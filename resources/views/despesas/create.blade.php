<h1>Nova Despesa</h1>
<form action="{{ route('despesas.store') }}" method="POST">
    @csrf

    <label>Descrição:</label><br>
    <input type="text" name="descricao"><br>

    <label>Valor:</label><br>
    <input type="number" step="0.01" name="valor"><br>

    <label>Data:</label><br>
    <input type="date" name="data"><br>

    <!-- Checkbox: Tem parcelas -->
    <label>
        <input type="checkbox" id="tem_parcelas" name="tem_parcelas">
        Tem parcelas?
    </label><br>

    <!-- Campo: Quantidade de parcelas -->
    <div id="parcelas_opcoes" style="display: none; margin-top: 10px;">
        <label>Quantidade de parcelas:</label><br>
        <input type="number" id="quantidade_parcelas" name="quantidade_parcelas" min="2"><br>

        <!-- Escolha se os valores são iguais -->
        <label>Parcelas com o mesmo valor?</label><br>
        <select id="parcelas_iguais" name="parcelas_iguais">
            <option value="">Selecione</option>
            <option value="1">Sim</option>
            <option value="0">Não</option>
        </select>
    </div>

    <!-- Campos para valores e datas diferentes -->
    <div id="parcelas_diferentes_campos" style="display: none; margin-top: 10px;">
        <h4>Informe o valor e a data de cada parcela:</h4>
        <div id="parcelas_campos"></div>
    </div>

    <br>
    <button type="submit">Salvar</button>
</form>

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
                div.innerHTML = `
                    <label>Parcela ${i + 1} - Valor:</label><br>
                    <input type="number" step="0.01" name="valor_parcela[]" required><br>
                    <label>Parcela ${i + 1} - Data:</label><br>
                    <input type="date" name="data_parcela[]" required><br><br>
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
