@extends('layouts.app')

@section('title', 'Unidades')
@section('breadcrumb', 'UNIDADES')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">UNIDADES</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalUnidade" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('unidades.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('unidades.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3 ps-4">Título <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Endereço</th>
                        <th class="py-3">Telefone</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unidades as $unidade)
                        <tr>
                            <td class="ps-4">{{ $unidade->titulo }}</td>
                            <td>{{ $unidade->endereco ?? '—' }}</td>
                            <td>{{ $unidade->telefone ?? '—' }}</td>
                            <td>{{ $unidade->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalUnidade" data-action="edit" data-unidade="{{ json_encode($unidade) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('unidades.destroy', $unidade) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir esta unidade?');">
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

        @if($unidades->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $unidades->firstItem() }} a {{ $unidades->lastItem() }} de {{ $unidades->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $unidades->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $unidades->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($unidades->getUrlRange(1, $unidades->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $unidades->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$unidades->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $unidades->nextPageUrl() }}">Próximo</a>
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
<script>document.addEventListener('DOMContentLoaded', function() { new bootstrap.Modal(document.getElementById('modalUnidade')).show(); });</script>
@endpush
@endif

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalUnidade" tabindex="-1" aria-labelledby="modalUnidadeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalUnidadeLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formUnidade" method="post" action="{{ route('unidades.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="unidade_titulo" class="form-label fw-semibold" style="color: var(--gs-text);">Título</label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="unidade_titulo" name="titulo" value="{{ old('titulo') }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="unidade_endereco" class="form-label fw-semibold" style="color: var(--gs-text);">Endereço</label>
                        <input type="text" class="form-control @error('endereco') is-invalid @enderror" id="unidade_endereco" name="endereco" value="{{ old('endereco') }}">
                        @error('endereco')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="unidade_email" class="form-label fw-semibold" style="color: var(--gs-text);">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="unidade_email" name="email" value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="unidade_telefone" class="form-label fw-semibold" style="color: var(--gs-text);">Telefone</label>
                        <input type="text" class="form-control @error('telefone') is-invalid @enderror" id="unidade_telefone" name="telefone" value="{{ old('telefone') }}" placeholder="(00) 00000-0000">
                        @error('telefone')
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
document.getElementById('modalUnidade').addEventListener('show.bs.modal', function (e) {
    const button = e.relatedTarget;
    const action = button.getAttribute('data-action');
    const form = document.getElementById('formUnidade');
    const title = this.querySelector('#modalUnidadeLabel');
    const submitBtn = form.querySelector('button[type="submit"]');
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (action === 'edit' && button.getAttribute('data-unidade')) {
        const unidade = JSON.parse(button.getAttribute('data-unidade'));
        title.textContent = 'Editar';
        submitBtn.textContent = 'Salvar';
        form.action = '{{ url("unidades") }}/' + unidade.id;
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

        document.getElementById('unidade_titulo').value = unidade.titulo;
        document.getElementById('unidade_endereco').value = unidade.endereco || '';
        document.getElementById('unidade_email').value = unidade.email || '';
        document.getElementById('unidade_telefone').value = unidade.telefone || '';
    } else {
        title.textContent = 'Adicionar';
        submitBtn.textContent = 'Adicionar';
        form.action = '{{ route("unidades.store") }}';
        document.getElementById('unidade_titulo').value = '';
        document.getElementById('unidade_endereco').value = '';
        document.getElementById('unidade_email').value = '';
        document.getElementById('unidade_telefone').value = '';
    }
});
</script>
@endpush
