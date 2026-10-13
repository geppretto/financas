<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\FinancasController;
use App\Http\Controllers\ReceitaController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route('resumo');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/resumo', [FinancasController::class, 'resumo'])->name('resumo');
    // outras rotas protegidas
    Route::get('/receitas/create', [ReceitaController::class, 'create'])->name('receitas.create');
    Route::post('/receitas', [ReceitaController::class, 'store'])->name('receitas.store');
    Route::patch('/receitas/{id}/pagar', [ReceitaController::class, 'marcarComoPago'])->name('receitas.pagar');
    Route::get('/receitas/{id}/edit', [ReceitaController::class, 'edit'])->name('receitas.edit');
    Route::put('/receitas/{id}', [ReceitaController::class, 'update'])->name('receitas.update');
    Route::delete('/receitas/{id}', [ReceitaController::class, 'destroy'])->name('receitas.destroy');

    // Despesas
    Route::get('/despesas/create', [DespesaController::class, 'create'])->name('despesas.create');
    Route::post('/despesas', [DespesaController::class, 'store'])->name('despesas.store');
    Route::patch('/despesas/{id}/pagar', [DespesaController::class, 'marcarComoPago'])->name('despesas.pagar');
    Route::get('/despesas/edit/{id}', [DespesaController::class, 'edit'])->name('despesas.edit');
    Route::put('/despesas/{id}', [DespesaController::class, 'update'])->name('despesas.update');
    Route::delete('/despesas/{id}', [DespesaController::class, 'destroy'])->name('despesas.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
