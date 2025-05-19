@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow p-4" style="min-width: 320px; max-width: 450px;">
        <h3 class="mb-4 text-center text-primary">Recuperar Senha</h3>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">E-mail cadastrado</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-warning">Enviar link de recuperação</button>
            </div>

            <div class="mt-3 text-center">
                <a href="{{ route('login') }}">Voltar para login</a>
            </div>
        </form>
    </div>
</div>
@endsection
