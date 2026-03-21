@extends('layouts.app')

@section('title', 'Missões')
@section('breadcrumb', 'MISSÕES')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">MISSÕES</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalMissao" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('missoes.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('missoes.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3">Turma</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3">Status</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missoes as $missao)
                        <tr>
                            <td class="ps-4">{{ $missao->titulo }}</td>
                            <td>{{ $missao->unidade->titulo ?? '—' }}</td>
                            <td>{{ $missao->turmas->pluck('nome')->filter()->join(', ') ?: '—' }}</td>
                            <td>{{ $missao->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $missao->status === 'ativa' ? 'Ativa' : 'Inativa' }}</td>
                            <td class="pe-4 text-end">
                                @php
                                    $missaoEdit = [
                                        'id' => $missao->id,
                                        'titulo' => $missao->titulo,
                                        'unidade_id' => $missao->unidade_id,
                                        'descricao' => $missao->descricao,
                                        'xp' => $missao->xp,
                                        'coins' => $missao->coins,
                                        'status' => $missao->status,
                                        'data_encerramento' => $missao->data_encerramento ? $missao->data_encerramento->format('Y-m-d') : null,
                                        'turma_ids' => $missao->turmas->pluck('id')->values()->all(),
                                    ];
                                @endphp
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalMissao" data-action="edit" data-missao="{{ json_encode($missaoEdit) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('missoes.destroy', $missao) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta missão?');">
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

        @if($missoes->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $missoes->firstItem() }} a {{ $missoes->lastItem() }} de {{ $missoes->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $missoes->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $missoes->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($missoes->getUrlRange(1, $missoes->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $missoes->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$missoes->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $missoes->nextPageUrl() }}">Próximo</a>
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
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalMissao')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalMissao" tabindex="-1" aria-labelledby="modalMissaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalMissaoLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formMissao" method="post" action="{{ route('missoes.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="missao_titulo" class="form-label fw-semibold" style="color: var(--gs-text);">Título</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="missao_titulo" name="titulo" value="{{ old('titulo') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="missao_unidade_id" class="form-label fw-semibold" style="color: var(--gs-text);">Unidade</label>
                            @if(!($canManageAllUnits ?? false))
                                <input type="hidden" name="unidade_id" id="missao_unidade_id_hidden" value="{{ old('unidade_id', $unidades->first()?->id) }}">
                                <select class="form-select @error('unidade_id') is-invalid @enderror" id="missao_unidade_id" disabled>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}" {{ old('unidade_id', $unidades->first()?->id) == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select @error('unidade_id') is-invalid @enderror" id="missao_unidade_id" name="unidade_id" required>
                                    <option value="">Selecione...</option>
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}" {{ old('unidade_id') == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('unidade_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="missao_turma_ids" class="form-label fw-semibold" style="color: var(--gs-text);">Turmas</label>
                            <select class="form-select @error('turma_ids') is-invalid @enderror" id="missao_turma_ids" name="turma_ids[]" multiple size="6">
                            </select>
                            <small class="text-muted">Segure <kbd>Ctrl</kbd> (Windows) ou <kbd>Cmd</kbd> (Mac) para selecionar várias turmas da escola.</small>
                            @error('turma_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('turma_ids.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="missao_descricao" class="form-label fw-semibold" style="color: var(--gs-text);">Descrição</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="missao_descricao" name="descricao" rows="2">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="missao_xp" class="form-label fw-semibold" style="color: var(--gs-text);">Xp</label>
                            <input type="number" class="form-control @error('xp') is-invalid @enderror" id="missao_xp" name="xp" value="{{ old('xp', 0) }}" required>
                            @error('xp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="missao_coins" class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                            <input type="number" class="form-control @error('coins') is-invalid @enderror" id="missao_coins" name="coins" value="{{ old('coins', 0) }}" required>
                            @error('coins')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="missao_data_encerramento" class="form-label fw-semibold" style="color: var(--gs-text);">Data de encerramento <span class="text-muted">(opcional)</span></label>
                        <input type="date" class="form-control @error('data_encerramento') is-invalid @enderror" id="missao_data_encerramento" name="data_encerramento" value="{{ old('data_encerramento') }}">
                        @error('data_encerramento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="missao_status" class="form-label fw-semibold" style="color: var(--gs-text);">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="missao_status" name="status" required>
                            <option value="ativa" {{ old('status', 'ativa') === 'ativa' ? 'selected' : '' }}>Ativa</option>
                            <option value="inativa" {{ old('status') === 'inativa' ? 'selected' : '' }}>Inativa</option>
                        </select>
                        @error('status')
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
(function () {
    const turmasPorUnidade = @json($turmasPorUnidadeJson ?? []);

    function getUnidadeSelect() {
        return document.getElementById('missao_unidade_id');
    }

    function getUnidadeId() {
        const sel = getUnidadeSelect();
        const hidden = document.getElementById('missao_unidade_id_hidden');
        if (hidden && hidden.value) return String(hidden.value);
        return sel ? String(sel.value || '') : '';
    }

    function fillTurmasSelect(selectedIds) {
        const select = document.getElementById('missao_turma_ids');
        if (!select) return;
        const uid = getUnidadeId();
        select.innerHTML = '';
        const list = turmasPorUnidade[uid] || [];
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

    const unidadeSel = getUnidadeSelect();
    if (unidadeSel) {
        unidadeSel.addEventListener('change', function () {
            fillTurmasSelect([]);
        });
    }

    document.getElementById('modalMissao').addEventListener('show.bs.modal', function (e) {
        const button = e.relatedTarget;
        const action = button.getAttribute('data-action');
        const form = document.getElementById('formMissao');
        const title = this.querySelector('#modalMissaoLabel');
        const submitBtn = form.querySelector('button[type="submit"]');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();

        if (action === 'edit' && button.getAttribute('data-missao')) {
            const missao = JSON.parse(button.getAttribute('data-missao'));
            title.textContent = 'Editar';
            submitBtn.textContent = 'Salvar';
            form.action = '{{ url("missoes") }}/' + missao.id;
            form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

            document.getElementById('missao_titulo').value = missao.titulo || '';
            document.getElementById('missao_unidade_id').value = missao.unidade_id || '';
            if (document.getElementById('missao_unidade_id_hidden')) {
                document.getElementById('missao_unidade_id_hidden').value = missao.unidade_id || '';
            }
            fillTurmasSelect(missao.turma_ids || []);
            document.getElementById('missao_descricao').value = missao.descricao || '';
            document.getElementById('missao_xp').value = missao.xp ?? 0;
            document.getElementById('missao_coins').value = missao.coins ?? 0;
            document.getElementById('missao_status').value = missao.status || 'ativa';
            document.getElementById('missao_data_encerramento').value = missao.data_encerramento ? String(missao.data_encerramento).split('T')[0] : '';
        } else {
            title.textContent = 'Adicionar';
            submitBtn.textContent = 'Adicionar';
            form.action = '{{ route("missoes.store") }}';
            document.getElementById('missao_titulo').value = '';
            document.getElementById('missao_unidade_id').value = '';
            if (document.getElementById('missao_unidade_id_hidden')) {
                const def = '{{ old("unidade_id", $unidades->first()?->id ?? "") }}';
                document.getElementById('missao_unidade_id_hidden').value = def;
                document.getElementById('missao_unidade_id').value = def;
            }
            fillTurmasSelect(@json(old('turma_ids', [])));
            document.getElementById('missao_descricao').value = '';
            document.getElementById('missao_xp').value = '0';
            document.getElementById('missao_coins').value = '0';
            document.getElementById('missao_status').value = 'ativa';
            document.getElementById('missao_data_encerramento').value = '';
        }
    });
})();
</script>
@endpush
