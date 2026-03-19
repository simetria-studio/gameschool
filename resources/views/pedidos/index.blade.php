@extends('layouts.app')

@section('title', 'Pedidos')
@section('breadcrumb', 'PEDIDOS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">PEDIDOS</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalPedido">
            <i class="bi bi-plus-lg me-1"></i> Adicionar
        </button>
    </div>

    <div class="gs-card p-0 overflow-hidden">
        <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Exibir</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('pedidos.index') }}?per_page='+this.value">
                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('pedidos.index') }}" method="get" class="d-flex gap-2">
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                    <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar registros" value="{{ $search ?? '' }}" style="min-width: 200px;">
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Buscar</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table gs-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 ps-4">Aluno <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Produto <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Qnt atual <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Coins <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Status <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3">Data/horário <i class="bi bi-arrow-up-short text-secondary"></i></th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td class="ps-4">{{ $pedido->aluno ?? '-' }}</td>
                            <td>{{ $pedido->produto ?? '-' }}</td>
                            <td>{{ $pedido->qnt_atual ?? '-' }}</td>
                            <td>{{ $pedido->coins ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $pedido->status ?? '-' }}</span></td>
                            <td>{{ $pedido->data_horario ?? '-' }}</td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary">Editar</button>
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

        @if($total > 0)
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $pedidos->firstItem() }} a {{ $pedidos->lastItem() }} de {{ $total }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">Anterior</a></li>
                    <li class="page-item"><a class="page-link" href="#">Próximo</a></li>
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

{{-- Modal criação de pedido (somente front-end, sem backend ainda) --}}
<div class="modal fade" id="modalPedido" tabindex="-1" aria-labelledby="modalPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalPedidoLabel" style="color: var(--gs-text);">Adicionar pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Aluno</label>
                        <select class="form-select">
                            <option value="">Selecione o aluno...</option>
                            {{-- futuramente popular com alunos cadastrados --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Produto</label>
                        <select class="form-select">
                            <option value="">Selecione o produto...</option>
                            {{-- futuramente popular com itens da loja --}}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Qnt atual</label>
                        <input type="number" class="form-control" min="0" value="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                        <input type="number" class="form-control" min="0" value="0">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Status</label>
                        <select class="form-select">
                            <option value="pendente">Pendente</option>
                            <option value="aprovado">Aprovado</option>
                            <option value="recusado">Recusado</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-gs-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
