@extends('layouts.app')

@section('title', 'Atitudes')
@section('breadcrumb', 'ATITUDES')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">ATITUDES</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalAtitude" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('atitudes.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('atitudes.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3">Descrição</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3">Tipo</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($atitudes as $atitude)
                        <tr>
                            <td class="ps-4">{{ $atitude->titulo }}</td>
                            <td>{{ Str::limit($atitude->descricao, 50) ?? '—' }}</td>
                            <td>{{ $atitude->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($atitude->tipo === 'positiva')
                                    <span class="text-success"><i class="bi bi-hand-thumbs-up-fill"></i> Positiva</span>
                                @else
                                    <span class="text-danger"><i class="bi bi-hand-thumbs-down-fill"></i> Negativa</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('atitudes.recompensar', $atitude) }}" class="btn btn-sm btn-success">
                                    <i class="bi bi-gift me-1"></i> Recompensar
                                </a>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAtitude" data-action="edit" data-atitude="{{ json_encode($atitude) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('atitudes.destroy', $atitude) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta atitude?');">
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

        @if($atitudes->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $atitudes->firstItem() }} a {{ $atitudes->lastItem() }} de {{ $atitudes->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $atitudes->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $atitudes->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($atitudes->getUrlRange(1, $atitudes->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $atitudes->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$atitudes->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $atitudes->nextPageUrl() }}">Próximo</a>
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
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalAtitude')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalAtitude" tabindex="-1" aria-labelledby="modalAtitudeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalAtitudeLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formAtitude" method="post" action="{{ route('atitudes.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="atitude_titulo" class="form-label fw-semibold" style="color: var(--gs-text);">Título</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="atitude_titulo" name="titulo" value="{{ old('titulo') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="atitude_descricao" class="form-label fw-semibold" style="color: var(--gs-text);">Descrição</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="atitude_descricao" name="descricao" rows="2">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="atitude_tipo" class="form-label fw-semibold" style="color: var(--gs-text);">Tipo</label>
                        <select class="form-select @error('tipo') is-invalid @enderror" id="atitude_tipo" name="tipo" required>
                            <option value="positiva" {{ old('tipo', 'positiva') === 'positiva' ? 'selected' : '' }}>Positiva</option>
                            <option value="negativa" {{ old('tipo') === 'negativa' ? 'selected' : '' }}>Negativa</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="atitude_coins" class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                        <input type="number" class="form-control @error('coins') is-invalid @enderror" id="atitude_coins" name="coins" value="{{ old('coins', 0) }}" required>
                        @error('coins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="atitude_xp" class="form-label fw-semibold" style="color: var(--gs-text);">Xp</label>
                        <input type="number" class="form-control @error('xp') is-invalid @enderror" id="atitude_xp" name="xp" value="{{ old('xp', 0) }}" required>
                        @error('xp')
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
document.getElementById('modalAtitude').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    const form = document.getElementById('formAtitude');
    const title = this.querySelector('#modalAtitudeLabel');
    const submitBtn = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (action === 'edit' && button.getAttribute('data-atitude')) {
        const atitude = JSON.parse(button.getAttribute('data-atitude'));
        title.textContent = 'Editar';
        submitBtn.textContent = 'Salvar';
        form.action = '{{ url("atitudes") }}/' + atitude.id;
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

        document.getElementById('atitude_titulo').value = atitude.titulo || '';
        document.getElementById('atitude_descricao').value = atitude.descricao || '';
        document.getElementById('atitude_tipo').value = atitude.tipo || 'positiva';
        document.getElementById('atitude_coins').value = atitude.coins ?? 0;
        document.getElementById('atitude_xp').value = atitude.xp ?? 0;
    } else {
        title.textContent = 'Adicionar';
        submitBtn.textContent = 'Adicionar';
        form.action = '{{ route("atitudes.store") }}';
        document.getElementById('atitude_titulo').value = '';
        document.getElementById('atitude_descricao').value = '';
        document.getElementById('atitude_tipo').value = 'positiva';
        document.getElementById('atitude_coins').value = '0';
        document.getElementById('atitude_xp').value = '0';
    }
});
</script>
@endpush
