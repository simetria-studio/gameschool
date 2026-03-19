@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Início')

@section('content')
<div class="container-fluid">
    <h1 class="h4 mb-4" style="color: var(--gs-text);">Bem-vindo, {{ auth()->user()->name ?? auth()->user()->username }}!</h1>
    <p class="gs-text-secondary">Você está logado no Game School. Use o menu lateral para navegar.</p>
</div>
@endsection
