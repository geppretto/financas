<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReceitaController extends Controller
{
    public function create() {
    return view('receitas.create');
}

public function store(Request $request) {
    $request->validate([
        'descricao' => 'required|string|max:255',
        'valor' => 'required|numeric',
        'data' => 'required|date',
    ]);

    Receita::create($request->all());

    return redirect('/resumo')->with('success', 'Receita cadastrada com sucesso!');
}
}
