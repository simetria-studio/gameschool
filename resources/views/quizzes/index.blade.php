@extends('layouts.app')

@section('title', 'Quizzes')
@section('breadcrumb', 'QUIZZES')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">QUIZZES</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalQuiz" data-action="add">
            <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="gs-card p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Exibir</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('quizzes.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('quizzes.index') }}" method="get" class="d-flex gap-2">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar registros" value="{{ $search }}" style="min-width: 200px;">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Buscar</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table gs-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 ps-4">Título</th>
                        <th class="py-3">Unidade</th>
                        <th class="py-3">Turmas</th>
                        <th class="py-3">Perguntas</th>
                        <th class="py-3">Nota mín.</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quizzes as $quiz)
                        <tr>
                            <td class="ps-4">{{ $quiz->titulo }}</td>
                            <td>{{ $quiz->unidade->titulo ?? '—' }}</td>
                            <td>{{ $quiz->turmas->pluck('nome')->filter()->join(', ') ?: '—' }}</td>
                            <td>{{ $quiz->perguntas_count }}</td>
                            <td>{{ $quiz->nota_minima }}%</td>
                            <td>{{ $quiz->status === 'ativa' ? 'Ativa' : 'Inativa' }}</td>
                            <td class="pe-4 text-end">
                                @php
                                    $quizEdit = [
                                        'id' => $quiz->id,
                                        'titulo' => $quiz->titulo,
                                        'unidade_id' => $quiz->unidade_id,
                                        'descricao' => $quiz->descricao,
                                        'xp' => $quiz->xp,
                                        'coins' => $quiz->coins,
                                        'nota_minima' => $quiz->nota_minima,
                                        'tentativas_max' => $quiz->tentativas_max,
                                        'status' => $quiz->status,
                                        'data_encerramento' => $quiz->data_encerramento ? $quiz->data_encerramento->format('Y-m-d') : null,
                                        'turma_ids' => $quiz->turmas->pluck('id')->values()->all(),
                                    ];
                                @endphp
                                <a href="{{ route('quizzes.perguntas', $quiz) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-question-circle me-1"></i> Perguntas
                                </a>
                                <a href="{{ route('quizzes.tentativas', $quiz) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-clipboard-check me-1"></i> Tentativas ({{ $quiz->tentativas_count }})
                                </a>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalQuiz" data-action="edit" data-quiz="{{ json_encode($quizEdit) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('quizzes.destroy', $quiz) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este quiz?');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                                    <input type="hidden" name="search" value="{{ $search }}">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash me-1"></i> Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 gs-text-secondary">
                                Nenhum registro encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($quizzes->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $quizzes->firstItem() }} a {{ $quizzes->lastItem() }} de {{ $quizzes->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $quizzes->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $quizzes->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($quizzes->getUrlRange(1, $quizzes->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $quizzes->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$quizzes->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $quizzes->nextPageUrl() }}">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

@if($errors->any())
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalQuiz')).show(); });</script>
@endpush
@endif

<div class="modal fade" id="modalQuiz" tabindex="-1" aria-labelledby="modalQuizLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalQuizLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formQuiz" method="post" action="{{ route('quizzes.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="quiz_titulo" class="form-label fw-semibold">Título</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="quiz_titulo" name="titulo" value="{{ old('titulo') }}" required>
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quiz_unidade_id" class="form-label fw-semibold">Unidade</label>
                            @if(!($canManageAllUnits ?? false))
                                <input type="hidden" name="unidade_id" id="quiz_unidade_id_hidden" value="{{ old('unidade_id', $unidades->first()?->id) }}">
                                <select class="form-select @error('unidade_id') is-invalid @enderror" id="quiz_unidade_id" disabled>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}" {{ old('unidade_id', $unidades->first()?->id) == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select @error('unidade_id') is-invalid @enderror" id="quiz_unidade_id" name="unidade_id" required>
                                    <option value="">Selecione...</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}" {{ old('unidade_id') == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('unidade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quiz_turma_ids" class="form-label fw-semibold">Turmas</label>
                            <select class="form-select @error('turma_ids') is-invalid @enderror" id="quiz_turma_ids" name="turma_ids[]" multiple size="6"></select>
                            @error('turma_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="quiz_descricao" class="form-label fw-semibold">Descrição</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="quiz_descricao" name="descricao" rows="2">{{ old('descricao') }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="quiz_xp" class="form-label fw-semibold">XP</label>
                            <input type="number" class="form-control @error('xp') is-invalid @enderror" id="quiz_xp" name="xp" value="{{ old('xp', 0) }}" min="0" required>
                            @error('xp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="quiz_coins" class="form-label fw-semibold">Coins</label>
                            <input type="number" class="form-control @error('coins') is-invalid @enderror" id="quiz_coins" name="coins" value="{{ old('coins', 0) }}" min="0" required>
                            @error('coins')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="quiz_nota_minima" class="form-label fw-semibold">Nota mínima (%)</label>
                            <input type="number" class="form-control @error('nota_minima') is-invalid @enderror" id="quiz_nota_minima" name="nota_minima" value="{{ old('nota_minima', 70) }}" min="0" max="100" required>
                            @error('nota_minima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="quiz_tentativas_max" class="form-label fw-semibold">Tentativas</label>
                            <input type="number" class="form-control @error('tentativas_max') is-invalid @enderror" id="quiz_tentativas_max" name="tentativas_max" value="{{ old('tentativas_max', 1) }}" min="0" max="100" required>
                            <small class="text-muted">0 = ilimitadas</small>
                            @error('tentativas_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quiz_data_encerramento" class="form-label fw-semibold">Data de encerramento <span class="text-muted">(opcional)</span></label>
                            <input type="date" class="form-control @error('data_encerramento') is-invalid @enderror" id="quiz_data_encerramento" name="data_encerramento" value="{{ old('data_encerramento') }}">
                            @error('data_encerramento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="quiz_status" class="form-label fw-semibold">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="quiz_status" name="status" required>
                                <option value="ativa" {{ old('status', 'ativa') === 'ativa' ? 'selected' : '' }}>Ativa</option>
                                <option value="inativa" {{ old('status') === 'inativa' ? 'selected' : '' }}>Inativa</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gs-primary btn-lg w-100">Adicionar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const turmasPorUnidade = @json($turmasPorUnidadeJson ?? []);

    function getUnidadeId() {
        const hidden = document.getElementById('quiz_unidade_id_hidden');
        if (hidden && hidden.value) return String(hidden.value);
        const sel = document.getElementById('quiz_unidade_id');
        return sel ? String(sel.value || '') : '';
    }

    function fillTurmasSelect(selectedIds) {
        const select = document.getElementById('quiz_turma_ids');
        if (!select) return;
        select.innerHTML = '';
        const list = turmasPorUnidade[getUnidadeId()] || [];
        list.forEach(function (t) {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.nome;
            if (selectedIds && selectedIds.map(String).includes(String(t.id))) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    const unidadeSel = document.getElementById('quiz_unidade_id');
    if (unidadeSel) {
        unidadeSel.addEventListener('change', function () { fillTurmasSelect([]); });
    }

    document.getElementById('modalQuiz').addEventListener('show.bs.modal', function (e) {
        const button = e.relatedTarget;
        const action = button.getAttribute('data-action');
        const form = document.getElementById('formQuiz');
        const title = this.querySelector('#modalQuizLabel');
        const submitBtn = form.querySelector('button[type="submit"]');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();

        if (action === 'edit' && button.getAttribute('data-quiz')) {
            const quiz = JSON.parse(button.getAttribute('data-quiz'));
            title.textContent = 'Editar';
            submitBtn.textContent = 'Salvar';
            form.action = '{{ url("quizzes") }}/' + quiz.id;
            form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

            document.getElementById('quiz_titulo').value = quiz.titulo || '';
            document.getElementById('quiz_unidade_id').value = quiz.unidade_id || '';
            if (document.getElementById('quiz_unidade_id_hidden')) {
                document.getElementById('quiz_unidade_id_hidden').value = quiz.unidade_id || '';
            }
            fillTurmasSelect(quiz.turma_ids || []);
            document.getElementById('quiz_descricao').value = quiz.descricao || '';
            document.getElementById('quiz_xp').value = quiz.xp ?? 0;
            document.getElementById('quiz_coins').value = quiz.coins ?? 0;
            document.getElementById('quiz_nota_minima').value = quiz.nota_minima ?? 70;
            document.getElementById('quiz_tentativas_max').value = quiz.tentativas_max ?? 1;
            document.getElementById('quiz_status').value = quiz.status || 'ativa';
            document.getElementById('quiz_data_encerramento').value = quiz.data_encerramento || '';
        } else {
            title.textContent = 'Adicionar';
            submitBtn.textContent = 'Adicionar';
            form.action = '{{ route("quizzes.store") }}';
            document.getElementById('quiz_titulo').value = '';
            const def = '{{ old("unidade_id", $unidades->first()?->id ?? "") }}';
            if (document.getElementById('quiz_unidade_id_hidden')) {
                document.getElementById('quiz_unidade_id_hidden').value = def;
            }
            if (document.getElementById('quiz_unidade_id')) {
                document.getElementById('quiz_unidade_id').value = def;
            }
            fillTurmasSelect(@json(old('turma_ids', [])));
            document.getElementById('quiz_descricao').value = '';
            document.getElementById('quiz_xp').value = '0';
            document.getElementById('quiz_coins').value = '0';
            document.getElementById('quiz_nota_minima').value = '70';
            document.getElementById('quiz_tentativas_max').value = '1';
            document.getElementById('quiz_status').value = 'ativa';
            document.getElementById('quiz_data_encerramento').value = '';
        }
    });
})();
</script>
@endpush
