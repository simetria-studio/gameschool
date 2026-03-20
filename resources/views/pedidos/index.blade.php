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
                            <td class="ps-4">{{ $pedido->aluno->nome ?? '-' }}</td>
                            <td>{{ $pedido->produto->titulo ?? '-' }}</td>
                            <td>{{ $pedido->qnt_atual }}</td>
                            <td>{{ $pedido->coins }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($pedido->status) }}</span></td>
                            <td>{{ $pedido->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-1">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary js-editar-pedido"
                                        data-id="{{ $pedido->id }}"
                                        data-aluno-id="{{ $pedido->aluno_id }}"
                                        data-loja-item-id="{{ $pedido->loja_item_id }}"
                                        data-qnt-atual="{{ $pedido->qnt_atual }}"
                                        data-coins="{{ $pedido->coins }}"
                                        data-status="{{ $pedido->status }}"
                                        data-update-url="{{ route('pedidos.update', $pedido) }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalPedido"
                                    >
                                        Editar
                                    </button>
                                    <form action="{{ route('pedidos.destroy', $pedido) }}" method="post" onsubmit="return confirm('Deseja apagar este pedido?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                                        <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Apagar</button>
                                    </form>
                                </div>
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

{{-- Modal criação/edição de pedido --}}
<div class="modal fade" id="modalPedido" tabindex="-1" aria-labelledby="modalPedidoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalPedidoLabel" style="color: var(--gs-text);">Adicionar pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form method="post" action="{{ route('pedidos.store') }}" id="formPedido">
                    @csrf
                    <input type="hidden" name="_method" id="pedido_method" value="POST">
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                    <input type="hidden" name="search" value="{{ $search ?? '' }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Aluno</label>
                        <select class="form-select @error('aluno_id') is-invalid @enderror" id="pedido_aluno_id" name="aluno_id" required>
                            <option value="">Selecione o aluno...</option>
                            @foreach(($alunos ?? collect()) as $aluno)
                                <option value="{{ $aluno->id }}" {{ old('aluno_id') == $aluno->id ? 'selected' : '' }}>{{ $aluno->nome }}</option>
                            @endforeach
                        </select>
                        @error('aluno_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Produto</label>
                        <select class="form-select @error('loja_item_id') is-invalid @enderror" id="pedido_produto_id" name="loja_item_id" required>
                            <option value="">Selecione o produto...</option>
                            @foreach(($produtos ?? collect()) as $produto)
                                <option value="{{ $produto->id }}" data-coins="{{ $produto->coins }}" {{ old('loja_item_id') == $produto->id ? 'selected' : '' }}>
                                    {{ $produto->titulo }}
                                </option>
                            @endforeach
                        </select>
                        @error('loja_item_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Qnt atual</label>
                        <input type="number" class="form-control @error('qnt_atual') is-invalid @enderror" min="1" value="{{ old('qnt_atual', 1) }}" id="pedido_qnt_atual" name="qnt_atual" required>
                        @error('qnt_atual')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Coins</label>
                        <input type="number" class="form-control @error('coins') is-invalid @enderror" min="0" value="{{ old('coins', 0) }}" id="pedido_coins" name="coins" required>
                        @error('coins')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color: var(--gs-text);">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                            <option value="pendente" {{ old('status', 'pendente') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="aprovado" {{ old('status') === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                            <option value="recusado" {{ old('status') === 'recusado' ? 'selected' : '' }}>Recusado</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-gs-primary" id="pedido_submit_btn">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('modalPedido');
    const form = document.getElementById('formPedido');
    const methodInput = document.getElementById('pedido_method');
    const title = document.getElementById('modalPedidoLabel');
    const submitBtn = document.getElementById('pedido_submit_btn');
    const defaultAction = '{{ route('pedidos.store') }}';
    const alunoInput = document.getElementById('pedido_aluno_id');
    const produtoSelect = document.getElementById('pedido_produto_id');
    const qntInput = document.getElementById('pedido_qnt_atual');
    const coinsInput = document.getElementById('pedido_coins');
    const statusInput = document.querySelector('select[name="status"]');
    const editarButtons = document.querySelectorAll('.js-editar-pedido');
    const addButton = document.querySelector('[data-bs-target="#modalPedido"]:not(.js-editar-pedido)');

    function resetParaCriacao() {
        if (!form) return;
        form.setAttribute('action', defaultAction);
        if (methodInput) methodInput.value = 'POST';
        if (title) title.textContent = 'Adicionar pedido';
        if (submitBtn) submitBtn.textContent = 'Adicionar';
        if (alunoInput) alunoInput.value = '';
        if (produtoSelect) produtoSelect.value = '';
        if (qntInput) qntInput.value = 1;
        if (coinsInput) coinsInput.value = 0;
        if (statusInput) statusInput.value = 'pendente';
    }

    if (addButton) {
        addButton.addEventListener('click', resetParaCriacao);
    }

    editarButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!form) return;
            form.setAttribute('action', this.dataset.updateUrl || defaultAction);
            if (methodInput) methodInput.value = 'PUT';
            if (title) title.textContent = 'Editar pedido';
            if (submitBtn) submitBtn.textContent = 'Salvar';
            if (alunoInput) alunoInput.value = this.dataset.alunoId || '';
            if (produtoSelect) produtoSelect.value = this.dataset.lojaItemId || '';
            if (qntInput) qntInput.value = this.dataset.qntAtual || 1;
            if (coinsInput) coinsInput.value = this.dataset.coins || 0;
            if (statusInput) statusInput.value = this.dataset.status || 'pendente';
        });
    });

    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', function () {
            resetParaCriacao();
        });
    }

    if (!produtoSelect || !coinsInput) return;

    produtoSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        const coins = option ? option.getAttribute('data-coins') : null;
        coinsInput.value = coins ?? 0;
    });

    @if($errors->any())
        new bootstrap.Modal(modalElement).show();
    @endif
});
</script>
@endpush
