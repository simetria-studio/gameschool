@extends('layouts.app')

@section('title', 'Minha conta')
@section('breadcrumb', 'CONTA')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">MINHA CONTA</h1>
    </div>

    <div class="gs-card p-4" style="max-width: 640px;">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        @endif

        <form action="{{ route('conta.update') }}" method="post">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="conta_name" class="form-label fw-semibold" style="color: var(--gs-text);">Nome</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="conta_name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="conta_username" class="form-label fw-semibold" style="color: var(--gs-text);">Usuário</label>
                <input type="text" class="form-control @error('username') is-invalid @enderror" id="conta_username" name="username" value="{{ old('username', $user->username) }}" required>
                @error('username')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="conta_email" class="form-label fw-semibold" style="color: var(--gs-text);">E-mail</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="conta_email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="conta_password" class="form-label fw-semibold" style="color: var(--gs-text);">Nova senha</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="conta_password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Deixe em branco para manter a senha atual.</small>
                </div>
                <div class="col-md-6 mb-4">
                    <label for="conta_password_confirmation" class="form-label fw-semibold" style="color: var(--gs-text);">Confirmar senha</label>
                    <input type="password" class="form-control" id="conta_password_confirmation" name="password_confirmation">
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-gs-primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>
@endsection

