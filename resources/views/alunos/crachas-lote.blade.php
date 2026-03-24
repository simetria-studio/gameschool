@extends('layouts.app')

@section('title', 'Crachás em lote')
@section('breadcrumb', 'ALUNOS - CRACHÁS EM LOTE')

@section('content')
<div class="cracha-print-root">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">CRACHÁS EM LOTE</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('alunos.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
            @if($alunos->count())
                <button type="button" class="btn btn-gs-primary btn-sm" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Imprimir (8 por página)
                </button>
            @endif
        </div>
    </div>

    <div class="gs-card p-3 mb-4 no-print">
        <form method="get" action="{{ route('alunos.crachas.lote') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="f_unidade_id">Unidade</label>
                <select name="unidade_id" id="f_unidade_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ (string)$selectedUnidade === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->titulo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="f_turma_id">Turma</label>
                <select name="turma_id" id="f_turma_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($turmas as $t)
                        <option value="{{ $t->id }}" {{ (string)$selectedTurma === (string)$t->id ? 'selected' : '' }}>
                            {{ $t->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-gs-primary mt-3 mt-md-0 w-100">
                    Filtrar alunos
                </button>
            </div>
        </form>
    </div>

    @if(!$alunos->count())
        <div class="gs-card p-4 text-center no-print">
            <p class="gs-text-secondary mb-0">Selecione uma unidade e/ou turma para listar os alunos e imprimir os crachás.</p>
        </div>
    @else
        <p class="text-muted small no-print mb-3">Pré-visualização: cada bloco abaixo = 1 página na impressão com até 8 crachás (4 colunas × 2 linhas).</p>

        @foreach($alunos->chunk(8) as $pagina)
            <section class="cracha-print-page" aria-label="Página de crachás">
                @foreach($pagina as $aluno)
                    @include('alunos.partials.cracha-card', [
                        'aluno' => $aluno,
                        'canvasId' => 'qrcode-' . $aluno->id,
                        'qrSize' => 240,
                    ])
                @endforeach
            </section>
        @endforeach
    @endif
</div>
@endsection

@push('styles')
    @include('alunos.partials.cracha-styles')
    <style>
        @media screen {
            .cracha-print-page {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                grid-auto-rows: auto;
                gap: 1rem;
                padding: 1rem;
                margin-bottom: 2rem;
                background: #ececec;
                border-radius: 10px;
                border: 2px dashed #bbb;
                justify-items: center;
            }
            .cracha-print-page .badge-card {
                width: 100%;
                max-width: 148px;
                aspect-ratio: 54 / 86;
                margin: 0;
                min-height: 0;
            }
            .cracha-print-page .badge-card__canvas {
                width: 86px !important;
                height: 86px !important;
            }
            .cracha-print-page .badge-card__yellow {
                padding: 8px 8px 6px;
            }
        }
    </style>
@endpush

@push('scripts')
@if($alunos->count())
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script>
        (function () {
            if (!window.QRious) return;
            document.querySelectorAll('canvas[data-cracha-qr]').forEach(function (canvas) {
                new QRious({
                    element: canvas,
                    value: canvas.getAttribute('data-cracha-qr'),
                    size: parseInt(canvas.getAttribute('width'), 10) || 180,
                    background: '#ffffff',
                    foreground: '#000000',
                });
            });
        })();
    </script>
@endif
@endpush
