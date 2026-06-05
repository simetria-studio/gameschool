@extends('layouts.app')

@section('title', 'Personagens e Figurinhas')
@section('breadcrumb', 'ROLETAS / PERSONAGENS E FIGURINHAS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold">PERSONAGENS E FIGURINHAS</h1>
            <p class="small gs-text-secondary mb-0">Upload de imagem · PNG, JPG, WEBP ou GIF (máx. 2 MB)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roletas.index') }}" class="btn btn-outline-secondary btn-sm">Roletas</a>
            <a href="{{ route('roleta-itens.index') }}" class="btn btn-outline-secondary btn-sm">Emotes</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="gs-card p-4">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-cloud-upload me-1"></i> Novo cadastro</h2>
                <form method="post" action="{{ route('roleta-colecionaveis.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="personagem" @selected(old('tipo') === 'personagem')>Personagem</option>
                            <option value="figurinha" @selected(old('tipo') === 'figurinha')>Figurinha</option>
                        </select>
                        @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" required placeholder="Ex: Herói Espacial">
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Imagem</label>
                        <input type="file" name="arquivo" id="arquivo_novo" class="form-control @error('arquivo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/gif" required>
                        @error('arquivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="mt-2 text-center border rounded p-2 bg-light" id="preview_novo_wrap" style="display:none;">
                            <img id="preview_novo" src="" alt="Preview" class="img-fluid rounded" style="max-height:160px;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Raridade</label>
                            <select name="raridade" class="form-select">
                                <option value="comum" @selected(old('raridade', 'comum') === 'comum')>Comum</option>
                                <option value="raro" @selected(old('raridade') === 'raro')>Raro</option>
                                <option value="epico" @selected(old('raridade') === 'epico')>Épico</option>
                                <option value="lendario" @selected(old('raridade') === 'lendario')>Lendário</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="ativo" @selected(old('status', 'ativo') === 'ativo')>Ativo</option>
                                <option value="inativo" @selected(old('status') === 'inativo')>Inativo</option>
                            </select>
                        </div>
                    </div>

                    @if($canManageAllUnits)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unidade</label>
                        <select name="unidade_id" class="form-select" required>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" @selected(old('unidade_id') == $u->id)>{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="unidade_id" value="{{ $unidades->first()?->id }}">
                    @endif

                    <button type="submit" class="btn btn-gs-primary w-100">Salvar com upload</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="gs-card p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <span class="fw-semibold small">Cadastrados ({{ $itens->total() }})</span>
                    <form class="d-flex gap-2 flex-wrap" method="get">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <select name="tipo" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="personagem" @selected($tipo==='personagem')>Personagens</option>
                            <option value="figurinha" @selected($tipo==='figurinha')>Figurinhas</option>
                        </select>
                        <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar" value="{{ $search }}">
                        <button class="btn btn-sm btn-outline-secondary">Buscar</button>
                    </form>
                </div>

                @if($itens->isEmpty())
                    <div class="p-5 text-center gs-text-secondary">Nenhum personagem ou figurinha cadastrado.</div>
                @else
                    <div class="row g-0">
                        @foreach($itens as $item)
                            <div class="col-md-6 col-xl-4 border-bottom border-end p-3">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        @if($item->imagem)
                                            <img src="{{ $item->imagem }}" alt="{{ $item->titulo }}" class="rounded border" style="width:72px;height:72px;object-fit:cover;">
                                        @else
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:72px;height:72px;">—</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $item->titulo }}</div>
                                        <div class="small gs-text-secondary">{{ ucfirst($item->tipo) }} · {{ ucfirst($item->raridade) }}</div>
                                        <div class="small">{{ $item->status === 'ativo' ? 'Ativo' : 'Inativo' }}</div>
                                        <div class="mt-2 d-flex gap-1">
                                            @php $edit = $item->only(['id','titulo','unidade_id','tipo','imagem','raridade','status']); @endphp
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditar" data-item="{{ json_encode($edit) }}">Editar</button>
                                            <form action="{{ route('roleta-colecionaveis.destroy', $item) }}" method="post" onsubmit="return confirm('Excluir este cadastro e a imagem?');">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($itens->hasPages())
                <div class="p-3 border-top">{{ $itens->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar cadastro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" method="post" enctype="multipart/form-data">@csrf
                    <div class="text-center mb-3">
                        <img id="edit_preview_atual" src="" alt="" class="rounded border" style="max-height:120px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select name="tipo" id="edit_tipo" class="form-select" required>
                            <option value="personagem">Personagem</option>
                            <option value="figurinha">Figurinha</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nova imagem <span class="text-muted">(opcional)</span></label>
                        <input type="file" name="arquivo" id="edit_arquivo" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                        <div class="mt-2 text-center" id="edit_preview_novo_wrap" style="display:none;">
                            <img id="edit_preview_novo" src="" alt="" class="rounded border" style="max-height:100px;">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Raridade</label>
                            <select name="raridade" id="edit_raridade" class="form-select">
                                <option value="comum">Comum</option>
                                <option value="raro">Raro</option>
                                <option value="epico">Épico</option>
                                <option value="lendario">Lendário</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    @if($canManageAllUnits)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unidade</label>
                        <select name="unidade_id" id="edit_unidade_id" class="form-select" required>
                            @foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->titulo }}</option>@endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="unidade_id" id="edit_unidade_id" value="{{ $unidades->first()?->id }}">
                    @endif
                    <button type="submit" class="btn btn-gs-primary w-100">Salvar alterações</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function bindPreview(inputId, wrapId, imgId) {
        const input = document.getElementById(inputId);
        const wrap = document.getElementById(wrapId);
        const img = document.getElementById(imgId);
        if (!input || !wrap || !img) return;
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) {
                wrap.style.display = 'none';
                return;
            }
            img.src = URL.createObjectURL(input.files[0]);
            wrap.style.display = 'block';
        });
    }
    bindPreview('arquivo_novo', 'preview_novo_wrap', 'preview_novo');
    bindPreview('edit_arquivo', 'edit_preview_novo_wrap', 'edit_preview_novo');

    document.getElementById('modalEditar').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        if (!btn || !btn.getAttribute('data-item')) return;
        const item = JSON.parse(btn.getAttribute('data-item'));
        const form = document.getElementById('formEditar');
        form.action = '{{ url('roleta-colecionaveis') }}/' + item.id;
        form.querySelector('input[name="_method"]')?.remove();
        form.insertAdjacentHTML('afterbegin', '<input type="hidden" name="_method" value="PUT">');
        document.getElementById('edit_titulo').value = item.titulo || '';
        document.getElementById('edit_tipo').value = item.tipo || 'personagem';
        document.getElementById('edit_raridade').value = item.raridade || 'comum';
        document.getElementById('edit_status').value = item.status || 'ativo';
        const unidade = document.getElementById('edit_unidade_id');
        if (unidade) unidade.value = item.unidade_id || '';
        const atual = document.getElementById('edit_preview_atual');
        if (atual && item.imagem) atual.src = item.imagem;
        document.getElementById('edit_arquivo').value = '';
        document.getElementById('edit_preview_novo_wrap').style.display = 'none';
    });
})();
</script>
@endpush
