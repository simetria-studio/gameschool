@extends('layouts.app')

@section('title', 'Tentativas do Quiz')
@section('breadcrumb', 'QUIZZES / TENTATIVAS')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Voltar aos quizzes
        </a>
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">Tentativas — {{ $quiz->titulo }}</h1>
        <p class="small gs-text-secondary mb-0 mt-1">
            {{ $quiz->unidade->titulo ?? '—' }} · Nota mínima: {{ $quiz->nota_minima }}%
            · Clique no aluno para ver as tentativas
        </p>
    </div>

    <div class="gs-card p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Exibir</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('quizzes.tentativas', $quiz) }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="gs-text-secondary small">alunos por página</span>
            </div>
            <form action="{{ route('quizzes.tentativas', $quiz) }}" method="get" class="d-flex gap-2">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar por aluno" value="{{ $search }}" style="min-width: 200px;">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Buscar</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table gs-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 ps-4" style="width: 40%;">Aluno</th>
                        <th class="py-3">Turma</th>
                        <th class="py-3">Tentativas</th>
                        <th class="py-3">Melhor nota</th>
                        <th class="py-3">Situação</th>
                        <th class="py-3 pe-4">Última em</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $aluno)
                        @php
                            $tentativasAluno = $aluno->tentativas;
                            $collapseId = 'tentativas-aluno-' . $aluno->id;
                            $melhorNota = $tentativasAluno->max('nota');
                            $aprovou = $tentativasAluno->contains('aprovado', true);
                            $ultima = $tentativasAluno->first()?->completed_at;
                        @endphp
                        <tr class="quiz-aluno-row" role="button" tabindex="0"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $collapseId }}"
                            aria-expanded="false"
                            aria-controls="{{ $collapseId }}">
                            <td class="ps-4">
                                <i class="bi bi-chevron-right quiz-chevron me-2 gs-text-secondary"></i>
                                <span class="fw-semibold">{{ $aluno->nome }}</span>
                            </td>
                            <td>{{ $aluno->turma->nome ?? '—' }}</td>
                            <td>{{ $tentativasAluno->count() }}</td>
                            <td>{{ $melhorNota }}%</td>
                            <td>
                                @if($aprovou)
                                    <span class="badge text-bg-success">Aprovado</span>
                                @else
                                    <span class="badge text-bg-secondary">Sem aprovação</span>
                                @endif
                            </td>
                            <td class="pe-4 small">{{ $ultima?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                        <tr class="collapse bg-light" id="{{ $collapseId }}">
                            <td colspan="6" class="p-0 border-0">
                                <div class="px-4 py-3">
                                    <table class="table table-sm mb-0 bg-white rounded border">
                                        <thead>
                                            <tr class="small gs-text-secondary">
                                                <th class="ps-3">#</th>
                                                <th>Nota</th>
                                                <th>Acertos</th>
                                                <th>Resultado</th>
                                                <th>Recompensa</th>
                                                <th>Data</th>
                                                <th class="text-end pe-3">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tentativasAluno as $tentativa)
                                                <tr>
                                                    <td class="ps-3">
                                                        @if($loop->first)
                                                            <span class="text-muted">Mais recente</span>
                                                        @else
                                                            Tentativa {{ $loop->iteration }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $tentativa->nota }}%</td>
                                                    <td>{{ $tentativa->acertos }}/{{ $tentativa->total_perguntas }}</td>
                                                    <td>
                                                        @if($tentativa->aprovado)
                                                            <span class="badge text-bg-success">Aprovado</span>
                                                        @else
                                                            <span class="badge text-bg-secondary">Reprovado</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        @if($tentativa->xp_ganho || $tentativa->coins_ganho)
                                                            {{ $tentativa->xp_ganho }} XP, {{ $tentativa->coins_ganho }} coins
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="small">{{ $tentativa->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                                    <td class="text-end pe-3">
                                                        @if($tentativa->respostas_count > 0)
                                                            <a href="{{ route('quizzes.tentativas.show', [$quiz, $tentativa]) }}" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-eye me-1"></i> Ver respostas
                                                            </a>
                                                        @else
                                                            <span class="small gs-text-secondary">Sem detalhe</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 gs-text-secondary">
                                Nenhuma tentativa registrada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alunos->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $alunos->firstItem() }} a {{ $alunos->lastItem() }} de {{ $alunos->total() }} alunos</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $alunos->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $alunos->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($alunos->getUrlRange(1, $alunos->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $alunos->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$alunos->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $alunos->nextPageUrl() }}">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .quiz-aluno-row { cursor: pointer; transition: background-color 0.15s ease; }
    .quiz-aluno-row:hover { background-color: rgba(242, 178, 51, 0.08); }
    .quiz-aluno-row[aria-expanded="true"] .quiz-chevron { transform: rotate(90deg); display: inline-block; }
    .quiz-chevron { transition: transform 0.15s ease; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.quiz-aluno-row').forEach(function (row) {
        var targetId = row.getAttribute('data-bs-target');
        if (!targetId) return;
        var el = document.querySelector(targetId);
        if (!el) return;
        el.addEventListener('show.bs.collapse', function () { row.setAttribute('aria-expanded', 'true'); });
        el.addEventListener('hide.bs.collapse', function () { row.setAttribute('aria-expanded', 'false'); });
        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                bootstrap.Collapse.getOrCreateInstance(el).toggle();
            }
        });
    });
})();
</script>
@endpush
