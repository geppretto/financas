<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\FinancasController;
use App\Http\Controllers\ReceitaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return view('welcome');
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

    // Despesas
    Route::get('/despesas/create', [DespesaController::class, 'create'])->name('despesas.create');
    Route::post('/despesas', [DespesaController::class, 'store'])->name('despesas.store');
    Route::patch('/despesas/{id}/pagar', [DespesaController::class, 'marcarComoPago'])->name('despesas.pagar');
});


