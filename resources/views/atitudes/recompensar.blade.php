@extends('layouts.app')

@section('title', 'Recompensar - ' . $atitude->titulo)
@section('breadcrumb', 'ATITUDES')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('atitudes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
    </div>

    <div class="gs-card" style="max-width: 640px;">
        <h5 class="fw-bold mb-3" style="color: var(--gs-text);">Recompensar: {{ $atitude->titulo }}</h5>
        <p class="gs-text-secondary small mb-4">
            Selecione um ou mais alunos. Serão creditados <strong>{{ $atitude->coins }}</strong> coins e <strong>{{ $atitude->xp }}</strong> XP para cada aluno escolhido.
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

            @php $oldAlunoIds = array_map('strval', (array) old('aluno_ids', [])); @endphp
            <div class="mb-4">
                <label for="aluno_id" class="form-label fw-semibold" style="color: var(--gs-text);">Alunos</label>
                <select class="form-select @error('aluno_ids') is-invalid @enderror @error('aluno_ids.*') is-invalid @enderror"
                        id="aluno_id"
                        name="aluno_ids[]"
                        multiple
                        size="10"
                        required>
                    @foreach($alunos as $aluno)
                        <option value="{{ $aluno->id }}"
                                data-unidade="{{ $aluno->unidade_id }}"
                                data-turma="{{ $aluno->turma_id }}"
                                {{ in_array((string) $aluno->id, $oldAlunoIds, true) ? 'selected' : '' }}>
                            {{ $aluno->nome }} — {{ $aluno->turma->nome ?? '' }} ({{ $aluno->unidade->titulo ?? '' }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Use Ctrl+clique (Windows) ou Cmd+clique (Mac) para selecionar vários. Filtre por unidade e turma acima.</div>
                @error('aluno_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @foreach($errors->get('aluno_ids.*') as $message)
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @endforeach
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

    const selectedBeforeFilter = () => new Set(Array.from(alunoSelect.selectedOptions).map(o => o.value));
    const optionTemplates = Array.from(alunoSelect.options)
        .filter(o => o.value)
        .map(o => o.cloneNode(true));

    function applyFilter() {
        const selected = selectedBeforeFilter();
        const unidadeId = unidadeSelect.value;
        const turmaId = turmaSelect.value;

        alunoSelect.innerHTML = '';

        optionTemplates.forEach(template => {
            const optUnidade = template.getAttribute('data-unidade');
            const optTurma = template.getAttribute('data-turma');
            const matchUnidade = !unidadeId || optUnidade === unidadeId;
            const matchTurma = !turmaId || optTurma === turmaId;

            if (matchUnidade && matchTurma) {
                const opt = template.cloneNode(true);
                opt.selected = selected.has(opt.value);
                alunoSelect.appendChild(opt);
            }
        });
    }

    unidadeSelect.addEventListener('change', applyFilter);
    turmaSelect.addEventListener('change', applyFilter);
});
</script>
@endpush
