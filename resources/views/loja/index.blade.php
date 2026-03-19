@extends('layouts.app')

@section('title', 'Loja')
@section('breadcrumb', 'LOJA')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">LOJA</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalLoja" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('loja.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('loja.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3 ps-4">Unidade</th>
                        <th class="py-3">Título do produto</th>
                        <th class="py-3">Quantidade</th>
                        <th class="py-3">Coins</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itens as $item)
                        <tr>
                            <td class="ps-4">{{ $item->unidade->titulo ?? '—' }}</td>
                            <td>{{ $item->titulo }}</td>
                            <td>{{ $item->quantidade }}</td>
                            <td>{{ $item->coins }}</td>
                            <td>{{ $item->status === 'ativo' ? 'Ativo' : 'Inativo' }}</td>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalLoja" data-action="edit" data-item="{{ json_encode($item) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('loja.destroy', $item) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este item?');">
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

        @if($itens->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $itens->firstItem() }} a {{ $itens->lastItem() }} de {{ $itens->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $itens->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $itens->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($itens->getUrlRange(1, $itens->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $itens->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$itens->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $itens->nextPageUrl() }}">Próximo</a>
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
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalLoja')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalLoja" tabindex="-1" aria-labelledby="modalLojaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalLojaLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formLoja" method="post" action="{{ route('loja.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="loja_unidade_id" class="form-label fw-semibold" style="color: var(--gs-text);">Unidade</label>
                        <select class="form-select @error('unidade_id') is-invalid @enderror" id="loja_unidade_id" name="unidade_id" required>
                            <option value="">Selecione...</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ old('unidade_id', '') == $u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                        @error('unidade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="loja_titulo" class="form-label fw-semibold" style="color: var(--gs-text);">Título do produto</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="loja_titulo" name="titulo" value="{{ old('titulo') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="loja_quantidade" class="form-label fw-semibold" style="color: var(--gs-text);">Quantidade</label>
                        <input type="number" min="0" class="form-control @error('quantidade') is-invalid @enderror" id="loja_quantidade" name="quantidade" value="{{ old('quantidade', 0) }}" required>
                        @error('quantidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="loja_coins" class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                        <input type="number" min="0" class="form-control @error('coins') is-invalid @enderror" id="loja_coins" name="coins" value="{{ old('coins', 0) }}" required>
                        @error('coins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="loja_status" class="form-label fw-semibold" style="color: var(--gs-text);">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="loja_status" name="status" required>
                            <option value="ativo" {{ old('status', 'ativo') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                            <option value="inativo" {{ old('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
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
document.getElementById('modalLoja').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    const form = document.getElementById('formLoja');
    const title = this.querySelector('#modalLojaLabel');
    const submitBtn = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (action === 'edit' && button.getAttribute('data-item')) {
        const item = JSON.parse(button.getAttribute('data-item'));
        title.textContent = 'Editar';
        submitBtn.textContent = 'Salvar';
        form.action = '{{ url("loja") }}/' + item.id;
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

        document.getElementById('loja_unidade_id').value = item.unidade_id || (item.unidade && item.unidade.id) || '';
        document.getElementById('loja_titulo').value = item.titulo || '';
        document.getElementById('loja_quantidade').value = item.quantidade ?? 0;
        document.getElementById('loja_coins').value = item.coins ?? 0;
        document.getElementById('loja_status').value = item.status || 'ativo';
    } else {
        title.textContent = 'Adicionar';
        submitBtn.textContent = 'Adicionar';
        form.action = '{{ route("loja.store") }}';
        document.getElementById('loja_unidade_id').value = '{{ old("unidade_id", $unidades->first()?->id ?? "") }}';
        document.getElementById('loja_titulo').value = '';
        document.getElementById('loja_quantidade').value = '0';
        document.getElementById('loja_coins').value = '0';
        document.getElementById('loja_status').value = 'ativo';
    }
});
</script>
@endpush
