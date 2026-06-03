@extends('layouts.app')

@section('title', 'Detalhe da tentativa')
@section('breadcrumb', 'QUIZZES / TENTATIVAS / DETALHE')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('quizzes.tentativas', $quiz) }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Voltar às tentativas
        </a>
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">{{ $tentativa->aluno->nome ?? 'Aluno' }}</h1>
        <p class="small gs-text-secondary mb-0 mt-1">
            Quiz: {{ $quiz->titulo }} · {{ $tentativa->completed_at?->format('d/m/Y H:i') ?? '—' }}
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="gs-card p-3 text-center">
                <div class="small gs-text-secondary">Nota</div>
                <div class="fs-3 fw-bold">{{ $tentativa->nota }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="gs-card p-3 text-center">
                <div class="small gs-text-secondary">Acertos</div>
                <div class="fs-3 fw-bold">{{ $tentativa->acertos }}/{{ $tentativa->total_perguntas }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="gs-card p-3 text-center">
                <div class="small gs-text-secondary">Resultado</div>
                <div class="fs-5 fw-bold mt-1">
                    @if($tentativa->aprovado)
                        <span class="text-success">Aprovado</span>
                    @else
                        <span class="text-secondary">Reprovado</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="gs-card p-3 text-center">
                <div class="small gs-text-secondary">Recompensa</div>
                <div class="fs-6 fw-semibold mt-1">
                    {{ $tentativa->xp_ganho }} XP · {{ $tentativa->coins_ganho }} coins
                </div>
            </div>
        </div>
    </div>

    <div class="gs-card p-0 overflow-hidden">
        <div class="p-3 border-bottom">
            <h2 class="h6 fw-bold mb-0">Respostas por pergunta</h2>
        </div>

        @forelse($respostas as $index => $resposta)
            <div class="p-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <strong>{{ $index + 1 }}. {{ $resposta->pergunta->enunciado ?? 'Pergunta removida' }}</strong>
                    @if($resposta->correta)
                        <span class="badge text-bg-success">Correta</span>
                    @else
                        <span class="badge text-bg-danger">Incorreta</span>
                    @endif
                </div>
                <p class="mb-0 small">
                    <span class="gs-text-secondary">Resposta do aluno:</span>
                    {{ $resposta->opcao->texto ?? '—' }}
                </p>
            </div>
        @empty
            <div class="p-5 text-center gs-text-secondary">
                Esta tentativa foi registrada antes do armazenamento detalhado de respostas.
            </div>
        @endforelse
    </div>
</div>
@endsection
