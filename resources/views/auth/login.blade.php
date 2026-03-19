@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
<div class="container py-2" style="max-width: 420px;">
    <div class="gs-login-card p-4 p-md-5">
        {{-- Logo e título --}}
        <div class="text-center mb-5 overflow-hidden">
            <img src="{{ asset('imgs/logo.png') }}" alt="Game School" class="d-block mx-auto mb-3" style="max-height: 64px; max-width: 100%; width: auto; height: auto; object-fit: contain;">
            <p class="mb-0 small" style="color: var(--gs-text-secondary);">Entre com seu usuário e senha</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3 small mb-4 rounded-2" role="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold" style="color: var(--gs-text); font-size: 0.9rem;">Usuário</label>
                <input type="text"
                       class="form-control form-control-lg @error('username') is-invalid @enderror"
                       id="username"
                       name="username"
                       value="{{ old('username') }}"
                       placeholder="Digite seu usuário"
                       autocomplete="username"
                       autofocus
                       required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold" style="color: var(--gs-text); font-size: 0.9rem;">Senha</label>
                <input type="password"
                       class="form-control form-control-lg @error('password') is-invalid @enderror"
                       id="password"
                       name="password"
                       placeholder="Digite sua senha"
                       autocomplete="current-password"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="color: var(--gs-text-secondary); font-size: 0.9rem;">
                        Lembrar de mim
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-gs-primary btn-lg w-100">
                Entrar
            </button>
        </form>
    </div>
</div>
@endsection
