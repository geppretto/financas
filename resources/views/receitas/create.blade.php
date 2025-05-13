<h1>Nova Receitas</h1>
<form action="{{ route('receitas.store') }}" method="POST">
    @csrf
    <label>Descrição:</label><br>
    <input type="text" name="descricao"><br>

    <label>Valor:</label><br>
    <input type="number" step="0.01" name="valor"><br>

    <label>Data:</label><br>
    <input type="date" name="data"><br>

    <button type="submit">Salvar</button>
</form>
