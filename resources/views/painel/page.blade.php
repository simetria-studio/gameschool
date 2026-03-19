@extends('layouts.app')

@section('title', $titulo)
@section('breadcrumb', $titulo)

@section('content')
<div class="container-fluid">
    <h1 class="h5 mb-4 fw-bold" style="color: var(--gs-text);">{{ $titulo }}</h1>
    <div class="gs-card p-4">
        <p class="gs-text-secondary mb-0">Conteúdo em breve. Use o menu para navegar.</p>
    </div>
</div>
@endsection
