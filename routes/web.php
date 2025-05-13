<?php

use App\Http\Controllers\FinancasController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [FinancasController::class, 'resumo']);

Route::get('/receitas/create', [ReceitaController::class, 'create'])->name('receitas.create');
Route::post('/receitas', [ReceitaController::class, 'store'])->name('receitas.store');

// Despesas
Route::get('/despesas/create', [DespesaController::class, 'create'])->name('despesas.create');
Route::post('/despesas', [DespesaController::class, 'store'])->name('despesas.store');
