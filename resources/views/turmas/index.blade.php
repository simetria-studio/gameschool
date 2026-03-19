@extends('layouts.app')

@section('title', 'Turmas')
@section('breadcrumb', 'TURMAS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">TURMAS</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalTurma" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('turmas.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('turmas.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3 ps-4">Nome da turma</th>
                        <th class="py-3">Ativo</th>
                        <th class="py-3">Período</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($turmas as $turma)
                        <tr>
                            <td class="ps-4">{{ $turma->nome }}</td>
                            <td>{{ $turma->ativo ? 'Sim' : 'Não' }}</td>
                            <td>@switch($turma->periodo)
                                @case('manha') Manhã @break
                                @case('tarde') Tarde @break
                                @case('noite') Noite @break
                                @default — @endswitch</td>
                            <td>{{ $turma->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalTurma" data-action="edit" data-turma="{{ json_encode($turma) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('turmas.destroy', $turma) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta turma?');">
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
                            <td colspan="5" class="text-center py-5 gs-text-secondary">
                                Nenhum registro encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($turmas->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $turmas->firstItem() }} a {{ $turmas->lastItem() }} de {{ $turmas->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $turmas->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $turmas->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($turmas->getUrlRange(1, $turmas->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $turmas->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$turmas->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $turmas->nextPageUrl() }}">Próximo</a>
                    </li>
                </ul>
            </nav>
        </div>
        @else
        <div class="d-flex justify-content-end p-3 border-top">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">Próximo</a></li>
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

@if($errors->any())
@push('scripts')
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalTurma')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalTurma" tabindex="-1" aria-labelledby="modalTurmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTurmaLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formTurma" method="post" action="{{ route('turmas.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="turma_nome" class="form-label fw-semibold" style="color: var(--gs-text);">Nome da turma</label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="turma_nome" name="nome" value="{{ old('nome') }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="turma_ativo" class="form-label fw-semibold" style="color: var(--gs-text);">Ativo</label>
                        <select class="form-select @error('ativo') is-invalid @enderror" id="turma_ativo" name="ativo" required>
                            <option value="1" {{ old('ativo', '1') === '1' || old('ativo') === 1 ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ old('ativo') === '0' || old('ativo') === 0 ? 'selected' : '' }}>Não</option>
                        </select>
                        @error('ativo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="turma_periodo" class="form-label fw-semibold" style="color: var(--gs-text);">Período</label>
                        <select class="form-select @error('periodo') is-invalid @enderror" id="turma_periodo" name="periodo" required>
                            <option value="">Selecione...</option>
                            <option value="manha" {{ old('periodo', 'manha') === 'manha' ? 'selected' : '' }}>Manhã</option>
                            <option value="tarde" {{ old('periodo') === 'tarde' ? 'selected' : '' }}>Tarde</option>
                            <option value="noite" {{ old('periodo') === 'noite' ? 'selected' : '' }}>Noite</option>
                        </select>
                        @error('periodo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
document.getElementById('modalTurma').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    const form = document.getElementById('formTurma');
    const title = this.querySelector('#modalTurmaLabel');
    const submitBtn = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (action === 'edit' && button.getAttribute('data-turma')) {
        const turma = JSON.parse(button.getAttribute('data-turma'));
        title.textContent = 'Editar';
        submitBtn.textContent = 'Salvar';
        form.action = '{{ url("turmas") }}/' + turma.id;
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

        document.getElementById('turma_nome').value = turma.nome || '';
        document.getElementById('turma_ativo').value = turma.ativo ? '1' : '0';
        document.getElementById('turma_periodo').value = turma.periodo || 'manha';
    } else {
        title.textContent = 'Adicionar';
        submitBtn.textContent = 'Adicionar';
        form.action = '{{ route("turmas.store") }}';
        document.getElementById('turma_nome').value = '';
        document.getElementById('turma_ativo').value = '1';
        document.getElementById('turma_periodo').value = 'manha';
    }
});
</script>
@endpush
