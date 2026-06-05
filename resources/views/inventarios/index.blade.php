@extends('layouts.app')

@section('title', 'Inventário dos Alunos')
@section('breadcrumb', 'ROLETAS / INVENTÁRIO')

@section('content')
@php
    $raridadeClass = [
        'comum' => 'secondary',
        'raro' => 'primary',
        'epico' => 'info',
        'lendario' => 'warning',
    ];
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold">INVENTÁRIO DOS ALUNOS</h1>
            <p class="small gs-text-secondary mb-0">Personagens, figurinhas e emotes ganhos na roleta</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roletas.index') }}" class="btn btn-outline-secondary btn-sm">Roletas</a>
            <a href="{{ route('roleta-colecionaveis.index') }}" class="btn btn-outline-secondary btn-sm">Colecionáveis</a>
        </div>
    </div>

    <div class="gs-card p-4 mb-4">
        <form method="get" action="{{ route('inventarios.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Aluno</label>
                <select name="aluno_id" class="form-select" required>
                    <option value="">Selecione um aluno…</option>
                    @foreach ($alunos as $a)
                        <option value="{{ $a->id }}" @selected($alunoId === $a->id)>{{ $a->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Filtrar por tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos os tipos</option>
                    @foreach ($tipos as $t)
                        <option value="{{ $t }}" @selected($tipo === $t)>{{ \App\Support\InventarioAluno::tipoLabel($t, plural: true) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Ver inventário
                </button>
            </div>
        </form>
    </div>

    @if ($aluno && $resumo)
        <div class="gs-card p-4 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h6 fw-bold mb-1">{{ $aluno->nome }}</h2>
                    <p class="small gs-text-secondary mb-0">
                        {{ $resumo['total_unicos'] }} itens únicos · {{ $resumo['total_quantidade'] }} unidades no total
                        · {{ number_format($aluno->coins, 0, ',', '.') }} coins · {{ number_format($aluno->xp, 0, ',', '.') }} XP
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($resumo['por_tipo'] as $tipoKey => $stats)
                        @if ($stats['unicos'] > 0)
                            <span class="badge text-bg-light border">
                                {{ \App\Support\InventarioAluno::tipoLabel($tipoKey, plural: true) }}:
                                {{ $stats['unicos'] }} (×{{ $stats['quantidade'] }})
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        @if (count($categorias) === 0)
            <div class="gs-card p-5 text-center">
                <i class="bi bi-inbox display-6 gs-text-secondary"></i>
                <p class="mt-3 mb-0 gs-text-secondary">Este aluno ainda não possui itens no inventário.</p>
            </div>
        @else
            @foreach ($categorias as $categoria)
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h6 fw-bold mb-0">{{ $categoria['titulo'] }}</h3>
                        <span class="small gs-text-secondary">{{ $categoria['unicos'] }} únicos · {{ $categoria['total'] }} unidades</span>
                    </div>

                    <div class="row g-3">
                        @foreach ($categoria['itens'] as $entrada)
                            @php
                                $item = $entrada['item'] ?? null;
                                $raridade = $item['raridade'] ?? 'comum';
                                $badgeClass = $raridadeClass[$raridade] ?? 'secondary';
                            @endphp
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                <div class="gs-card h-100 p-2 text-center position-relative">
                                    @if ($entrada['quantidade'] > 1)
                                        <span class="position-absolute top-0 end-0 m-2 badge rounded-pill text-bg-dark">
                                            ×{{ $entrada['quantidade'] }}
                                        </span>
                                    @endif

                                    <div class="ratio ratio-1x1 mb-2 rounded overflow-hidden bg-light d-flex align-items-center justify-content-center">
                                        @if (! empty($item['imagem_url']))
                                            <img
                                                src="{{ $item['imagem_url'] }}"
                                                alt="{{ $item['titulo'] ?? 'Item' }}"
                                                class="img-fluid object-fit-contain w-100 h-100"
                                                loading="lazy"
                                            >
                                        @elseif (! empty($item['emoji']))
                                            <span class="display-4">{{ $item['emoji'] }}</span>
                                        @else
                                            <i class="bi bi-image gs-text-secondary fs-2"></i>
                                        @endif
                                    </div>

                                    <div class="small fw-semibold text-truncate" title="{{ $item['titulo'] ?? '' }}">
                                        {{ $item['titulo'] ?? '—' }}
                                    </div>
                                    <span class="badge text-bg-{{ $badgeClass }} mt-1">
                                        {{ $item['raridade_label'] ?? ucfirst($raridade) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    @elseif ($alunoId > 0)
        <div class="alert alert-warning py-2 small">Aluno não encontrado.</div>
    @else
        <div class="gs-card p-5 text-center">
            <i class="bi bi-backpack display-6 gs-text-secondary"></i>
            <p class="mt-3 mb-0 gs-text-secondary">Selecione um aluno para visualizar o inventário com imagens.</p>
        </div>
    @endif
</div>
@endsection
