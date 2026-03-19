@extends('layouts.app')

@section('title', 'Alunos')
@section('breadcrumb', 'ALUNOS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">ALUNOS</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('alunos.crachas.lote') }}" class="btn btn-outline-secondary">
                <i class="bi bi-person-badge me-1"></i> Crachás em lote
            </a>
            <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalAluno" data-action="add">
                <i class="bi bi-plus-lg me-1"></i> Adicionar
            </button>
        </div>
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('alunos.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('alunos.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3 ps-4">Gênero</th>
                        <th class="py-3">Nome</th>
                        <th class="py-3">Coins</th>
                        <th class="py-3">Xp</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alunos as $aluno)
                        <tr>
                            <td class="ps-4">{{ $aluno->genero === 'masculino' ? 'Masculino' : ($aluno->genero === 'feminino' ? 'Feminino' : 'Outro') }}</td>
                            <td>{{ $aluno->nome }}</td>
                            <td>{{ $aluno->coins }}</td>
                            <td>{{ $aluno->xp }}</td>
                            <td>{{ $aluno->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAluno" data-action="edit" data-aluno="{{ json_encode($aluno) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <a href="{{ route('alunos.cracha', $aluno) }}" target="_blank" class="btn btn-sm btn-success">
                                    <i class="bi bi-person-badge me-1"></i> Ver crachá
                                </a>
                                <form action="{{ route('alunos.destroy', $aluno) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este aluno?');">
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
                            <td colspan="6" class="text-center py-5 gs-text-secondary">
                                Nenhum registro encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alunos->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $alunos->firstItem() }} a {{ $alunos->lastItem() }} de {{ $alunos->total() }}</span>
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
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalAluno')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalAluno" tabindex="-1" aria-labelledby="modalAlunoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalAlunoLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formAluno" method="post" action="{{ route('alunos.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="aluno_genero" class="form-label fw-semibold" style="color: var(--gs-text);">Gênero</label>
                        <select class="form-select @error('genero') is-invalid @enderror" id="aluno_genero" name="genero" required>
                            <option value="masculino" {{ old('genero', 'masculino') === 'masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="feminino" {{ old('genero') === 'feminino' ? 'selected' : '' }}>Feminino</option>
                            <option value="outro" {{ old('genero') === 'outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('genero')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="aluno_nome" class="form-label fw-semibold" style="color: var(--gs-text);">Nome</label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" id="aluno_nome" name="nome" value="{{ old('nome') }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="aluno_data_nascimento" class="form-label fw-semibold" style="color: var(--gs-text);">Data de Nascimento</label>
                        <input type="date" class="form-control @error('data_nascimento') is-invalid @enderror" id="aluno_data_nascimento" name="data_nascimento" value="{{ old('data_nascimento') }}" placeholder="dd/mm/aaaa">
                        @error('data_nascimento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="aluno_coins" class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                        <input type="number" min="0" class="form-control @error('coins') is-invalid @enderror" id="aluno_coins" name="coins" value="{{ old('coins', 0) }}" required>
                        @error('coins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="aluno_xp" class="form-label fw-semibold" style="color: var(--gs-text);">Xp</label>
                        <input type="number" min="0" class="form-control @error('xp') is-invalid @enderror" id="aluno_xp" name="xp" value="{{ old('xp', 0) }}" required>
                        @error('xp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="aluno_unidade_id" class="form-label fw-semibold" style="color: var(--gs-text);">Unidade</label>
                        <select class="form-select @error('unidade_id') is-invalid @enderror" id="aluno_unidade_id" name="unidade_id" required>
                            <option value="">Selecione...</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('unidade_id', $unidades->first()?->id) == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                        @error('unidade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="aluno_turma_id" class="form-label fw-semibold" style="color: var(--gs-text);">Turma</label>
                        <select class="form-select @error('turma_id') is-invalid @enderror" id="aluno_turma_id" name="turma_id" required>
                            <option value="">Selecione...</option>
                            @foreach($turmas as $t)
                                <option value="{{ $t->id }}" {{ old('turma_id', $turmas->first()?->id) == $t->id ? 'selected' : '' }}>{{ $t->nome }}</option>
                            @endforeach
                        </select>
                        @error('turma_id')
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
document.getElementById('modalAluno').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    const form = document.getElementById('formAluno');
    const title = this.querySelector('#modalAlunoLabel');
    const submitBtn = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (action === 'edit' && button.getAttribute('data-aluno')) {
        const aluno = JSON.parse(button.getAttribute('data-aluno'));
        title.textContent = 'Editar';
        submitBtn.textContent = 'Salvar';
        form.action = '{{ url("alunos") }}/' + aluno.id;
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

        document.getElementById('aluno_genero').value = aluno.genero || 'masculino';
        document.getElementById('aluno_nome').value = aluno.nome || '';
        document.getElementById('aluno_data_nascimento').value = aluno.data_nascimento ? aluno.data_nascimento.split('T')[0] : '';
        document.getElementById('aluno_coins').value = aluno.coins ?? 0;
        document.getElementById('aluno_xp').value = aluno.xp ?? 0;
        document.getElementById('aluno_unidade_id').value = aluno.unidade_id || (aluno.unidade && aluno.unidade.id) || '';
        document.getElementById('aluno_turma_id').value = aluno.turma_id || (aluno.turma && aluno.turma.id) || '';
    } else {
        title.textContent = 'Adicionar';
        submitBtn.textContent = 'Adicionar';
        form.action = '{{ route("alunos.store") }}';
        document.getElementById('aluno_genero').value = 'masculino';
        document.getElementById('aluno_nome').value = '';
        document.getElementById('aluno_data_nascimento').value = '';
        document.getElementById('aluno_coins').value = '0';
        document.getElementById('aluno_xp').value = '0';
        document.getElementById('aluno_unidade_id').value = '{{ old("unidade_id", $unidades->first()?->id ?? "") }}';
        document.getElementById('aluno_turma_id').value = '{{ old("turma_id", $turmas->first()?->id ?? "") }}';
    }
});
</script>
@endpush
