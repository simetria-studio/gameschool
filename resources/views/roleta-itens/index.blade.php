@extends('layouts.app')

@section('title', 'Emotes')
@section('breadcrumb', 'ROLETAS / EMOTES')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold">EMOTES</h1>
            <p class="small gs-text-secondary mb-0">Reações emoji que os alunos podem trocar como presente</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roleta-colecionaveis.index') }}" class="btn btn-outline-secondary btn-sm">Personagens e figurinhas</a>
            <a href="{{ route('roletas.index') }}" class="btn btn-outline-secondary btn-sm">Roletas</a>
            <button type="button" class="btn btn-gs-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalItem" data-action="add">
                <i class="bi bi-plus-lg me-1"></i> Adicionar emote
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="gs-card p-0 overflow-hidden">
        <div class="p-3 border-bottom">
            <form class="d-flex gap-2" method="get">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="search" name="search" class="form-control form-control-sm" placeholder="Buscar emote" value="{{ $search }}" style="max-width:240px;">
                <button class="btn btn-sm btn-outline-secondary">Buscar</button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table gs-table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Emote</th>
                        <th>Título</th>
                        <th>Raridade</th>
                        <th>Unidade</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itens as $item)
                        <tr>
                            <td class="ps-4 fs-3">{{ $item->emoji }}</td>
                            <td>{{ $item->titulo }}</td>
                            <td>{{ ucfirst($item->raridade) }}</td>
                            <td>{{ $item->unidade->titulo ?? '—' }}</td>
                            <td>{{ $item->status === 'ativo' ? 'Ativo' : 'Inativo' }}</td>
                            <td class="text-end pe-4">
                                @php $edit = $item->only(['id','titulo','unidade_id','emoji','raridade','status']); @endphp
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalItem" data-action="edit" data-item="{{ json_encode($edit) }}">Editar</button>
                                <form action="{{ route('roleta-itens.destroy', $item) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir?');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Excluir</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 gs-text-secondary">Nenhum emote cadastrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($itens->hasPages())<div class="p-3 border-top">{{ $itens->links() }}</div>@endif
    </div>
</div>

<div class="modal fade" id="modalItem" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalItemLabel">Adicionar emote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formItem" method="post" action="{{ route('roleta-itens.store') }}">@csrf
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Título</label>
                            <input type="text" name="titulo" id="item_titulo" class="form-control @error('titulo') is-invalid @enderror" required placeholder="Ex: Foguete, Palmas">
                            @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Emoji <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text fs-4" id="emoji_preview">😀</span>
                                <input type="text" name="emoji" id="item_emoji" class="form-control @error('emoji') is-invalid @enderror" required>
                            </div>
                            @error('emoji')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <p class="small gs-text-secondary mb-2">Clique para selecionar:</p>
                    <div class="d-flex flex-wrap gap-1 p-2 border rounded bg-white mb-3" style="max-height:140px;overflow-y:auto;">
                        @foreach($emojisSugeridos as $e)
                            <button type="button" class="btn btn-sm btn-outline-light border emoji-btn" data-emoji="{{ $e['emoji'] }}" title="{{ $e['nome'] }}">{{ $e['emoji'] }}</button>
                        @endforeach
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Raridade</label>
                            <select name="raridade" id="item_raridade" class="form-select">
                                <option value="comum">Comum</option>
                                <option value="raro">Raro</option>
                                <option value="epico">Épico</option>
                                <option value="lendario">Lendário</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" id="item_status" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    @if($canManageAllUnits)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Unidade</label>
                        <select name="unidade_id" id="item_unidade_id" class="form-select" required>
                            @foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->titulo }}</option>@endforeach
                        </select>
                    </div>
                    @else
                        <input type="hidden" name="unidade_id" id="item_unidade_id" value="{{ $unidades->first()?->id }}">
                    @endif
                    <button type="submit" class="btn btn-gs-primary w-100">Salvar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>.emoji-btn{font-size:1.35rem;padding:.25rem .45rem;min-width:2.5rem}.emoji-btn.active{background:var(--gs-primary);border-color:var(--gs-primary)}</style>
@endpush

@push('scripts')
<script>
(function(){
    const emojiInput=document.getElementById('item_emoji'), emojiPreview=document.getElementById('emoji_preview');
    function upd(){ if(emojiPreview) emojiPreview.textContent=emojiInput.value||'😀'; }
    emojiInput?.addEventListener('input',upd);
    document.querySelectorAll('.emoji-btn').forEach(function(btn){
        btn.addEventListener('click',function(){
            document.querySelectorAll('.emoji-btn').forEach(b=>b.classList.remove('active'));
            btn.classList.add('active');
            emojiInput.value=btn.getAttribute('data-emoji');
            upd();
        });
    });
    document.getElementById('modalItem').addEventListener('show.bs.modal',function(e){
        const btn=e.relatedTarget, form=document.getElementById('formItem');
        form.querySelector('input[name="_method"]')?.remove();
        if(btn.getAttribute('data-action')==='edit'&&btn.getAttribute('data-item')){
            const item=JSON.parse(btn.getAttribute('data-item'));
            document.getElementById('modalItemLabel').textContent='Editar emote';
            form.action='{{ url('roleta-itens') }}/'+item.id;
            form.insertAdjacentHTML('afterbegin','<input type="hidden" name="_method" value="PUT">');
            document.getElementById('item_titulo').value=item.titulo||'';
            document.getElementById('item_emoji').value=item.emoji||'';
            document.getElementById('item_raridade').value=item.raridade||'comum';
            document.getElementById('item_status').value=item.status||'ativo';
            if(document.getElementById('item_unidade_id'))document.getElementById('item_unidade_id').value=item.unidade_id||'';
            upd();
        } else {
            document.getElementById('modalItemLabel').textContent='Adicionar emote';
            form.action='{{ route('roleta-itens.store') }}';
            form.reset();
        }
    });
    @if($errors->any()) document.addEventListener('DOMContentLoaded',function(){ new bootstrap.Modal(document.getElementById('modalItem')).show(); }); @endif
})();
</script>
@endpush
