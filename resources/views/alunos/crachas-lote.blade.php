@extends('layouts.app')

@section('title', 'Crachás em lote')
@section('breadcrumb', 'ALUNOS - CRACHÁS EM LOTE')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">CRACHÁS EM LOTE</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('alunos.index') }}" class="btn btn-outline-secondary btn-sm">Voltar</a>
            @if($alunos->count())
                <button type="button" class="btn btn-gs-primary btn-sm" onclick="window.print();">
                    <i class="bi bi-printer me-1"></i> Imprimir página
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
        <div class="gs-card p-4 text-center">
            <p class="gs-text-secondary mb-0">Selecione uma unidade e/ou turma para listar os alunos e imprimir os crachás.</p>
        </div>
    @else
        <style>
            @media print {
                .no-print { display: none !important; }
                body { background: #fff !important; }
            }
            .cracha-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 1.5rem;
            }
            .cracha-card {
                max-width: 320px;
                margin: 0 auto;
                border: 2px solid #ddd;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                font-family: system-ui, sans-serif;
            }
            .cracha-header { background: #F2B233; color: #1A1A1A; padding: 0.75rem 1rem; font-weight: bold; font-size: 0.9rem; }
            .cracha-body { padding: 1.25rem; background: #fff; text-align: center; }
            .cracha-foto { width: 80px; height: 80px; border-radius: 50%; background: #eee; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: #999; font-size: 2rem; }
            .cracha-nome { font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; }
            .cracha-info { font-size: 0.9rem; color: #6B6B6B; }
            .cracha-qr { display: inline-block; margin-top: 0.75rem; }
            .cracha-qr-hint { font-size: 0.75rem; color: #999; margin: 0.25rem 0 0; }
        </style>

        <div class="cracha-grid">
            @foreach($alunos as $aluno)
                <div class="cracha-card">
                    <div class="cracha-header">{{ config('app.name') }}</div>
                    <div class="cracha-body">
                        <div class="cracha-foto"><i class="bi bi-person-fill"></i></div>
                        <div class="cracha-nome">{{ $aluno->nome }}</div>
                        <div class="cracha-info">{{ $aluno->turma->nome ?? '—' }}</div>
                        <div class="cracha-info">{{ $aluno->unidade->titulo ?? '—' }}</div>
                        <div class="cracha-info mt-2">Coins: {{ $aluno->coins }} &bull; XP: {{ $aluno->xp }}</div>
                        @if($aluno->user)
                            <div class="cracha-qr">
                                <canvas id="qrcode-{{ $aluno->id }}" width="120" height="120"></canvas>
                                <p class="cracha-qr-hint">Escaneie para acessar o app</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
        <script>
            (function () {
                if (!window.QRious) return;

                var itens = [
                    @foreach($alunos as $aluno)
                        @if($aluno->user && $aluno->user->qr_login_token)
                            { id: {{ $aluno->id }}, url: @json(route('login.qr', $aluno->user->qr_login_token)) },
                        @endif
                    @endforeach
                ];

                itens.forEach(function (item) {
                    var canvas = document.getElementById('qrcode-' + item.id);
                    if (!canvas) return;

                    new QRious({
                        element: canvas,
                        value: item.url,
                        size: 120
                    });
                });
            })();
        </script>
    @endif
</div>
@endsection

