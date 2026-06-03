@extends('layouts.app')

@section('title', 'Perguntas do Quiz')
@section('breadcrumb', 'QUIZZES / PERGUNTAS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
        <div class="d-flex flex-wrap gap-2 mb-2">
            <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <a href="{{ route('quizzes.tentativas', $quiz) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-clipboard-check me-1"></i> Tentativas
            </a>
        </div>
            <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">{{ $quiz->titulo }}</h1>
            <p class="small gs-text-secondary mb-0 mt-1">
                {{ $quiz->unidade->titulo ?? '—' }} · {{ $quiz->turmas->pluck('nome')->join(', ') ?: '—' }}
                · Nota mínima: {{ $quiz->nota_minima }}% · Recompensa: {{ $quiz->xp }} XP, {{ $quiz->coins }} coins
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="gs-card p-4">
                <h2 class="h6 fw-bold mb-3">Nova pergunta</h2>
                <form method="post" action="{{ route('quizzes.perguntas.store', $quiz) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="enunciado" class="form-label fw-semibold">Enunciado</label>
                        <textarea class="form-control @error('enunciado') is-invalid @enderror" id="enunciado" name="enunciado" rows="3" required>{{ old('enunciado') }}</textarea>
                        @error('enunciado')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Opções</label>
                        @error('opcoes')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        @for ($i = 0; $i < 4; $i++)
                            <div class="input-group mb-2">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="correta_index" value="{{ $i }}" {{ old('correta_index', '0') == (string) $i ? 'checked' : '' }} required>
                                </div>
                                <input type="text" class="form-control @error('opcoes.'.$i.'.texto') is-invalid @enderror" name="opcoes[{{ $i }}][texto]" placeholder="Opção {{ $i + 1 }}" value="{{ old('opcoes.'.$i.'.texto') }}">
                            </div>
                            @error('opcoes.'.$i.'.texto')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                        @endfor
                        <small class="text-muted">Marque o botão ao lado da opção correta.</small>
                    </div>

                    <button type="submit" class="btn btn-gs-primary w-100">Adicionar pergunta</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="gs-card p-0 overflow-hidden">
                <div class="p-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0">Perguntas cadastradas ({{ $quiz->perguntas->count() }})</h2>
                </div>

                @forelse($quiz->perguntas as $index => $pergunta)
                    <div class="p-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <strong>{{ $index + 1 }}. {{ $pergunta->enunciado }}</strong>
                            <form action="{{ route('quizzes.perguntas.destroy', [$quiz, $pergunta]) }}" method="post" onsubmit="return confirm('Excluir esta pergunta?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        <ul class="list-unstyled mb-0 small">
                            @foreach($pergunta->opcoes as $opcao)
                                <li class="mb-1">
                                    @if($opcao->correta)
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                    @else
                                        <i class="bi bi-circle me-1 gs-text-secondary"></i>
                                    @endif
                                    {{ $opcao->texto }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="p-5 text-center gs-text-secondary">
                        Nenhuma pergunta cadastrada. Adicione a primeira ao lado.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('form[action="{{ route('quizzes.perguntas.store', $quiz) }}"]').addEventListener('submit', function (e) {
    const corretaIndex = this.querySelector('input[name="correta_index"]:checked');
    if (!corretaIndex) return;

    const idx = corretaIndex.value;
    for (let i = 0; i < 4; i++) {
        let hidden = this.querySelector('input[name="opcoes[' + i + '][correta]"]');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'opcoes[' + i + '][correta]';
            this.appendChild(hidden);
        }
        hidden.value = i == idx ? '1' : '0';
    }
});
</script>
@endpush
