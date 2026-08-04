@extends('layouts.app')

@section('title', 'Peças de Avatar')
@section('breadcrumb', 'AVATAR / PEÇAS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold">PEÇAS DE AVATAR</h1>
            <p class="small gs-text-secondary mb-0">
                Peças por slot (base, sombra, calçado, roupa inf/sup, rosto, cabelo, acessórios) · PNG/SVG ou ZIP Spine (máx. {{ $tamanhoMaxUpload }})
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="gs-card p-4">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-person-bounding-box me-1"></i> Nova peça</h2>
                <form method="post" action="{{ route('avatar-pecas.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" required placeholder="Ex: Cabelo punk">
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Slot</label>
                            <select name="slot" class="form-select" required>
                                @foreach($slots as $s)
                                    <option value="{{ $s }}" @selected(old('slot') === $s)>{{ $slotLabels[$s] ?? ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Gênero</label>
                            <select name="genero" class="form-select" required>
                                @foreach($generos as $g)
                                    <option value="{{ $g }}" @selected(old('genero', 'unissex') === $g)>{{ ucfirst($g) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Asset (PNG/SVG/ZIP Spine)</label>
                        <input type="file" name="arquivo" class="form-control @error('arquivo') is-invalid @enderror" accept=".png,.jpg,.jpeg,.webp,.gif,.svg,.zip" required>
                        @error('arquivo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thumbnail <span class="text-muted">(opcional)</span></label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*,.svg">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Tipo asset</label>
                            <select name="tipo_asset" class="form-select">
                                @foreach($tiposAsset as $t)
                                    <option value="{{ $t }}" @selected(old('tipo_asset', 'png') === $t)>{{ strtoupper($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Raridade</label>
                            <select name="raridade" class="form-select">
                                @foreach($raridades as $r)
                                    <option value="{{ $r }}" @selected(old('raridade', 'comum') === $r)>{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="ativo" @selected(old('status', 'ativo') === 'ativo')>Ativo</option>
                                <option value="inativo" @selected(old('status') === 'inativo')>Inativo</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_starter" value="1" id="is_starter" @checked(old('is_starter'))>
                                <label class="form-check-label" for="is_starter">Starter (desbloqueia auto)</label>
                            </div>
                        </div>
                    </div>

                    @if($canManageAllUnits)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unidade <span class="text-muted">(vazio = global)</span></label>
                        <select name="unidade_id" class="form-select">
                            <option value="">Global</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}" @selected(old('unidade_id') == $u->id)>{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-gs-primary w-100">Salvar peça</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="gs-card p-0 overflow-hidden">
                <div class="p-3 border-bottom d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <span class="fw-semibold small">Cadastradas ({{ $itens->total() }})</span>
                    <form class="d-flex gap-2 flex-wrap" method="get">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <select name="slot" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">Slot</option>
                            @foreach($slots as $s)
                                <option value="{{ $s }}" @selected($slot === $s)>{{ $slotLabels[$s] ?? ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <select name="genero" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">Gênero</option>
                            @foreach($generos as $g)
                                <option value="{{ $g }}" @selected($genero === $g)>{{ ucfirst($g) }}</option>
                            @endforeach
                        </select>
                        <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">Status</option>
                            <option value="ativo" @selected($status === 'ativo')>Ativo</option>
                            <option value="inativo" @selected($status === 'inativo')>Inativo</option>
                        </select>
                        <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar" value="{{ $search }}">
                        <button class="btn btn-sm btn-outline-secondary">Buscar</button>
                    </form>
                </div>

                @if($itens->isEmpty())
                    <div class="p-5 text-center gs-text-secondary">Nenhuma peça cadastrada.</div>
                @else
                    <div class="row g-0">
                        @foreach($itens as $item)
                            <div class="col-md-6 col-xl-4 border-bottom border-end p-3">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        @if($item->thumbnail_url || $item->asset_url)
                                            <img src="{{ $item->thumbnailPublicUrl() }}" alt="{{ $item->titulo }}" class="rounded border bg-light" style="width:72px;height:72px;object-fit:contain;">
                                        @else
                                            <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:72px;height:72px;">—</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $item->titulo }}</div>
                                        <div class="small gs-text-secondary">{{ $slotLabels[$item->slot] ?? ucfirst($item->slot) }} · {{ ucfirst($item->genero) }}</div>
                                        <div class="small">
                                            {{ ucfirst($item->raridade) }}
                                            ·
                                            {{ $item->status === 'ativo' ? 'Ativo' : 'Inativo' }}
                                            @if ($item->is_starter)
                                                · Starter
                                            @endif
                                        </div>
                                        <div class="mt-2 d-flex gap-1">
                                            @php
                                                $edit = [
                                                    'id' => $item->id,
                                                    'titulo' => $item->titulo,
                                                    'unidade_id' => $item->unidade_id,
                                                    'slot' => $item->slot,
                                                    'genero' => $item->genero,
                                                    'tipo_asset' => $item->tipo_asset,
                                                    'raridade' => $item->raridade,
                                                    'status' => $item->status,
                                                    'is_starter' => (bool) $item->is_starter,
                                                    'thumb' => $item->thumbnailPublicUrl(),
                                                ];
                                            @endphp
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditar" data-item='@json($edit)'>Editar</button>
                                            <form action="{{ route('avatar-pecas.destroy', $item) }}" method="post" onsubmit="return confirm('Excluir esta peça?');">
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
                <h5 class="modal-title">Editar peça</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="text-center mb-3">
                        <img id="edit_preview_atual" src="" alt="" class="rounded border bg-light" style="max-height:120px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Título</label>
                        <input type="text" name="titulo" id="edit_titulo" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Slot</label>
                            <select name="slot" id="edit_slot" class="form-select" required>
                                @foreach($slots as $s)
                                    <option value="{{ $s }}">{{ $slotLabels[$s] ?? ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Gênero</label>
                            <select name="genero" id="edit_genero" class="form-select" required>
                                @foreach($generos as $g)
                                    <option value="{{ $g }}">{{ ucfirst($g) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Novo asset <span class="text-muted">(opcional)</span></label>
                        <input type="file" name="arquivo" class="form-control" accept=".png,.jpg,.jpeg,.webp,.gif,.svg,.zip">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nova thumbnail <span class="text-muted">(opcional)</span></label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*,.svg">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Tipo asset</label>
                            <select name="tipo_asset" id="edit_tipo_asset" class="form-select">
                                @foreach($tiposAsset as $t)
                                    <option value="{{ $t }}">{{ strtoupper($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Raridade</label>
                            <select name="raridade" id="edit_raridade" class="form-select">
                                @foreach($raridades as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_starter" value="1" id="edit_is_starter">
                                <label class="form-check-label" for="edit_is_starter">Starter</label>
                            </div>
                        </div>
                    </div>
                    @if($canManageAllUnits)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unidade</label>
                        <select name="unidade_id" id="edit_unidade_id" class="form-select">
                            <option value="">Global</option>
                            @foreach($unidades as $u)
                                <option value="{{ $u->id }}">{{ $u->titulo }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <button type="submit" class="btn btn-gs-primary w-100">Atualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('modalEditar')?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const item = JSON.parse(btn.getAttribute('data-item'));
    document.getElementById('formEditar').action = @json(url('/avatar-pecas')) + '/' + item.id;
    document.getElementById('edit_titulo').value = item.titulo;
    document.getElementById('edit_slot').value = item.slot;
    document.getElementById('edit_genero').value = item.genero;
    document.getElementById('edit_tipo_asset').value = item.tipo_asset;
    document.getElementById('edit_raridade').value = item.raridade;
    document.getElementById('edit_status').value = item.status;
    document.getElementById('edit_is_starter').checked = !!item.is_starter;
    const uni = document.getElementById('edit_unidade_id');
    if (uni) uni.value = item.unidade_id || '';
    document.getElementById('edit_preview_atual').src = item.thumb || '';
});
</script>
@endpush
