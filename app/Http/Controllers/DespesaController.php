<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DespesaController extends Controller
{
    public function create() {
    return view('despesas.create');
}

public function store(Request $request) {
    $request->validate([
        'descricao' => 'required|string|max:255',
        'valor' => 'required|numeric',
        'data' => 'required|date',
    ]);

    Despesa::create($request->all());

    return redirect('/resumo')->with('success', 'Despesa cadastrada com sucesso!');
}
}
