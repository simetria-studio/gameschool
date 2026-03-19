@extends('layouts.app')

@section('title', 'Recompensar - ' . $atitude->titulo)
@section('breadcrumb', 'ATITUDES')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('atitudes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
    </div>

    <div class="gs-card" style="max-width: 520px;">
        <h5 class="fw-bold mb-3" style="color: var(--gs-text);">Recompensar: {{ $atitude->titulo }}</h5>
        <p class="gs-text-secondary small mb-4">
            Selecione o aluno para aplicar esta atitude. Serão creditados <strong>{{ $atitude->coins }}</strong> coins e <strong>{{ $atitude->xp }}</strong> XP ao aluno.
        </p>

        @if (session('error'))
            <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif

        <form action="{{ route('atitudes.recompensar.store', $atitude) }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="unidade_id" class="form-label fw-semibold" style="color: var(--gs-text);">Unidade</label>
                <select class="form-select" id="unidade_id" name="unidade_id">
                    <option value="">Todas as unidades</option>
                    @foreach($unidades as $unidade)
                        <option value="{{ $unidade->id }}">{{ $unidade->titulo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="turma_id" class="form-label fw-semibold" style="color: var(--gs-text);">Turma</label>
                <select class="form-select" id="turma_id" name="turma_id">
                    <option value="">Todas as turmas</option>
                    @foreach($turmas as $turma)
                        <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="aluno_id" class="form-label fw-semibold" style="color: var(--gs-text);">Aluno</label>
                <select class="form-select @error('aluno_id') is-invalid @enderror" id="aluno_id" name="aluno_id" required>
                    <option value="">Selecione o aluno...</option>
                    @foreach($alunos as $aluno)
                        <option value="{{ $aluno->id }}"
                                data-unidade="{{ $aluno->unidade_id }}"
                                data-turma="{{ $aluno->turma_id }}"
                                {{ old('aluno_id') == $aluno->id ? 'selected' : '' }}>
                            {{ $aluno->nome }} — {{ $aluno->turma->nome ?? '' }} ({{ $aluno->unidade->titulo ?? '' }})
                        </option>
                    @endforeach
                </select>
                @error('aluno_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-gs-primary">Aplicar atitude</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const unidadeSelect = document.getElementById('unidade_id');
    const turmaSelect = document.getElementById('turma_id');
    const alunoSelect = document.getElementById('aluno_id');

    if (!unidadeSelect || !turmaSelect || !alunoSelect) return;

    const allAlunoOptions = Array.from(alunoSelect.options);

    function applyFilter() {
        const unidadeId = unidadeSelect.value;
        const turmaId = turmaSelect.value;

        alunoSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione o aluno...';
        alunoSelect.appendChild(placeholder);

        allAlunoOptions.forEach(opt => {
            if (!opt.value) return;
            const optUnidade = opt.getAttribute('data-unidade');
            const optTurma = opt.getAttribute('data-turma');

            const matchUnidade = !unidadeId || optUnidade === unidadeId;
            const matchTurma = !turmaId || optTurma === turmaId;

            if (matchUnidade && matchTurma) {
                alunoSelect.appendChild(opt);
            }
        });

        alunoSelect.value = '';
    }

    unidadeSelect.addEventListener('change', applyFilter);
    turmaSelect.addEventListener('change', applyFilter);
});
</script>
@endpush
