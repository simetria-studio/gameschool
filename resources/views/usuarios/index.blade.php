@extends('layouts.app')

@section('title', 'Usuários')
@section('breadcrumb', 'USUÁRIOS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h1 class="h5 mb-0 fw-bold" style="color: var(--gs-text);">USUÁRIOS</h1>
        <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-action="add">
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
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('usuarios.index') }}?per_page='+this.value+'&search={{ urlencode($search) }}'">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="gs-text-secondary small">resultados por página</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="gs-text-secondary small mb-0">Pesquisar</label>
                <form action="{{ route('usuarios.index') }}" method="get" class="d-flex gap-2">
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
                        <th class="py-3 ps-4">Nome</th>
                        <th class="py-3">Usuário</th>
                        <th class="py-3">E-mail</th>
                        <th class="py-3">Acesso</th>
                        <th class="py-3">Unidade</th>
                        <th class="py-3">Turmas</th>
                        <th class="py-3">Criado em</th>
                        <th class="py-3 pe-4 text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                        <tr>
                            <td class="ps-4">{{ $usuario->name }}</td>
                            <td>{{ $usuario->username }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ ucfirst($usuario->access_role ?? 'professor') }}</td>
                            <td>{{ $usuario->unidade?->titulo ?? '—' }}</td>
                            <td class="small">{{ $usuario->turmas->pluck('nome')->join(', ') ?: '—' }}</td>
                            <td>{{ $usuario->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="pe-4 text-end">
                                @php
                                    $usuarioEdit = [
                                        'id' => $usuario->id,
                                        'name' => $usuario->name,
                                        'username' => $usuario->username,
                                        'email' => $usuario->email,
                                        'access_role' => $usuario->access_role,
                                        'unidade_id' => $usuario->unidade_id,
                                        'turma_ids' => $usuario->turmas->pluck('id')->values()->all(),
                                    ];
                                @endphp
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" data-action="edit" data-usuario="{{ json_encode($usuarioEdit) }}">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                <form action="{{ route('usuarios.destroy', $usuario) }}" method="post" class="d-inline" onsubmit="return confirm('Deseja realmente excluir este usuário?');">
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
                            <td colspan="8" class="text-center py-5 gs-text-secondary">
                                Nenhum registro encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <span class="gs-text-secondary small">Mostrando {{ $usuarios->firstItem() }} a {{ $usuarios->lastItem() }} de {{ $usuarios->total() }}</span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item {{ $usuarios->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $usuarios->previousPageUrl() }}">Anterior</a>
                    </li>
                    @foreach ($usuarios->getUrlRange(1, $usuarios->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $usuarios->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach
                    <li class="page-item {{ !$usuarios->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $usuarios->nextPageUrl() }}">Próximo</a>
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

{{-- Modal Adicionar / Editar --}}
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalUsuarioLabel" style="color: var(--gs-text);">Adicionar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <form id="formUsuario" method="post" action="{{ route('usuarios.store') }}">
                    @csrf
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <input type="hidden" name="search" value="{{ $search }}">

                    <div class="mb-3">
                        <label for="usuario_name" class="form-label fw-semibold" style="color: var(--gs-text);">Nome</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="usuario_name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="usuario_username" class="form-label fw-semibold" style="color: var(--gs-text);">Usuário</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="usuario_username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="usuario_email" class="form-label fw-semibold" style="color: var(--gs-text);">E-mail</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="usuario_email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="usuario_access_role" class="form-label fw-semibold" style="color: var(--gs-text);">Tipo de acesso</label>
                        <select class="form-select @error('access_role') is-invalid @enderror" id="usuario_access_role" name="access_role" required>
                            <option value="master" {{ old('access_role', 'professor') === 'master' ? 'selected' : '' }}>Acesso master</option>
                            <option value="direcao" {{ old('access_role') === 'direcao' ? 'selected' : '' }}>Acesso Direção</option>
                            <option value="professor" {{ old('access_role') === 'professor' ? 'selected' : '' }}>Acesso professor</option>
                        </select>
                        @error('access_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="usuario_unidade_id" class="form-label fw-semibold" style="color: var(--gs-text);">Unidade (direção/professor)</label>
                        <select class="form-select @error('unidade_id') is-invalid @enderror" id="usuario_unidade_id" name="unidade_id" {{ old('access_role') === 'master' ? '' : '' }}>
                            <option value="">Selecione...</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" {{ (string)old('unidade_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                        @error('unidade_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4" id="wrap_usuario_turmas" style="display: none;">
                        <label for="usuario_turma_ids" class="form-label fw-semibold" style="color: var(--gs-text);">Turmas do professor</label>
                        <select class="form-select @error('turma_ids') is-invalid @enderror" id="usuario_turma_ids" name="turma_ids[]" multiple size="6"></select>
                        <small class="text-muted d-block mt-1">Segure <kbd>Ctrl</kbd> / <kbd>Cmd</kbd> para selecionar várias turmas da mesma escola.</small>
                        @error('turma_ids')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="usuario_password" class="form-label fw-semibold" style="color: var(--gs-text);">Senha</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="usuario_password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted" id="usuario_password_help">No modo edição, deixe em branco para manter a senha atual.</small>
                    </div>
                    <div class="mb-4">
                        <label for="usuario_password_confirmation" class="form-label fw-semibold" style="color: var(--gs-text);">Confirmar senha</label>
                        <input type="password" class="form-control" id="usuario_password_confirmation" name="password_confirmation">
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

    function toggleTurmasWrap() {
        const role = document.getElementById('usuario_access_role').value;
        const wrap = document.getElementById('wrap_usuario_turmas');
        if (!wrap) return;
        wrap.style.display = role === 'professor' ? 'block' : 'none';
    }

    function fillUsuarioTurmas(selectedIds) {
        const select = document.getElementById('usuario_turma_ids');
        if (!select) return;
        const uid = String(document.getElementById('usuario_unidade_id').value || '');
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

    const unidadeSel = document.getElementById('usuario_unidade_id');
    if (unidadeSel) {
        unidadeSel.addEventListener('change', function () {
            if (document.getElementById('usuario_access_role').value === 'professor') {
                fillUsuarioTurmas([]);
            }
        });
    }

    const roleSel = document.getElementById('usuario_access_role');
    if (roleSel) {
        roleSel.addEventListener('change', function () {
            toggleTurmasWrap();
            if (this.value === 'professor') {
                fillUsuarioTurmas([]);
            }
        });
    }

    document.getElementById('modalUsuario').addEventListener('show.bs.modal', function (e) {
        const button = e.relatedTarget;
        const action = button ? button.getAttribute('data-action') : 'add';
        const form = document.getElementById('formUsuario');
        const title = this.querySelector('#modalUsuarioLabel');
        const submitBtn = form.querySelector('button[type="submit"]');
        const methodInput = form.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();

        document.getElementById('usuario_password').value = '';
        document.getElementById('usuario_password_confirmation').value = '';

        if (action === 'edit' && button.getAttribute('data-usuario')) {
            const usuario = JSON.parse(button.getAttribute('data-usuario'));
            title.textContent = 'Editar';
            submitBtn.textContent = 'Salvar';
            form.action = '{{ url('usuarios') }}/' + usuario.id;
            form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');

            document.getElementById('usuario_name').value = usuario.name || '';
            document.getElementById('usuario_username').value = usuario.username || '';
            document.getElementById('usuario_email').value = usuario.email || '';
            document.getElementById('usuario_access_role').value = usuario.access_role || 'professor';
            document.getElementById('usuario_unidade_id').value = usuario.unidade_id ?? '';
            toggleTurmasWrap();
            fillUsuarioTurmas(usuario.turma_ids || []);
        } else {
            title.textContent = 'Adicionar';
            submitBtn.textContent = 'Adicionar';
            form.action = '{{ route('usuarios.store') }}';
            document.getElementById('usuario_name').value = '';
            document.getElementById('usuario_username').value = '';
            document.getElementById('usuario_email').value = '';
            document.getElementById('usuario_access_role').value = 'professor';
            document.getElementById('usuario_unidade_id').value = '{{ $unidades->first()?->id ?? '' }}';
            toggleTurmasWrap();
            fillUsuarioTurmas(@json(old('turma_ids', [])));
        }
    });
})();
</script>
@endpush

