@extends('layouts.app')

@section('title', 'Roletas')
@section('breadcrumb', 'ROLETAS')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold">ROLETAS PREMIADAS</h1>
            <p class="small gs-text-secondary mb-0">1 giro grátis por semana · giros extras com coins</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('roleta-colecionaveis.index') }}" class="btn btn-outline-secondary btn-sm">Personagens e figurinhas</a>
            <a href="{{ route('roleta-itens.index') }}" class="btn btn-outline-secondary btn-sm">Emotes</a>
            <button type="button" class="btn btn-gs-primary" data-bs-toggle="modal" data-bs-target="#modalRoleta" data-action="add"><i class="bi bi-plus-lg me-1"></i> Nova roleta</button>
        </div>
    </div>

    @if (session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif

    <div class="gs-card p-0 overflow-hidden">
        <table class="table gs-table table-hover mb-0">
            <thead><tr><th class="ps-4">Título</th><th>Unidade</th><th>Turmas</th><th>Segmentos</th><th>Custo</th><th>Status</th><th class="pe-4 text-end">Ações</th></tr></thead>
            <tbody>
                @forelse($roletas as $roleta)
                    <tr>
                        <td class="ps-4">{{ $roleta->titulo }}</td>
                        <td>{{ $roleta->unidade->titulo ?? '—' }}</td>
                        <td>{{ $roleta->turmas->pluck('nome')->join(', ') ?: '—' }}</td>
                        <td>{{ $roleta->segmentos_count }}</td>
                        <td>{{ $roleta->custo_coins }} coins</td>
                        <td>{{ $roleta->status === 'ativa' ? 'Ativa' : 'Inativa' }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('roletas.segmentos', $roleta) }}" class="btn btn-sm btn-outline-secondary">Prêmios</a>
                            @php $edit = array_merge($roleta->only(['id','titulo','unidade_id','descricao','custo_coins','status']), ['data_encerramento'=>$roleta->data_encerramento?->format('Y-m-d'), 'turma_ids'=>$roleta->turmas->pluck('id')->values()->all()]); @endphp
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalRoleta" data-action="edit" data-roleta="{{ json_encode($edit) }}">Editar</button>
                            <form action="{{ route('roletas.destroy', $roleta) }}" method="post" class="d-inline" onsubmit="return confirm('Excluir roleta?');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Excluir</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-5 gs-text-secondary">Nenhuma roleta cadastrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalRoleta" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Nova roleta</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <form id="formRoleta" method="post" action="{{ route('roletas.store') }}">@csrf
                <div class="mb-3"><label class="form-label fw-semibold">Título</label><input type="text" name="titulo" id="roleta_titulo" class="form-control" required></div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Unidade</label>
                        @if($canManageAllUnits)
                            <select name="unidade_id" id="roleta_unidade_id" class="form-select" required><option value="">Selecione...</option>@foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->titulo }}</option>@endforeach</select>
                        @else
                            <input type="hidden" name="unidade_id" id="roleta_unidade_id_hidden" value="{{ $unidades->first()?->id }}">
                            <select class="form-select" id="roleta_unidade_id" disabled>@foreach($unidades as $u)<option value="{{ $u->id }}">{{ $u->titulo }}</option>@endforeach</select>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Turmas</label><select name="turma_ids[]" id="roleta_turma_ids" class="form-select" multiple size="5"></select></div>
                </div>
                <div class="mb-3"><label class="form-label fw-semibold">Descrição</label><textarea name="descricao" id="roleta_descricao" class="form-control" rows="2"></textarea></div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Custo giro pago (coins)</label><input type="number" name="custo_coins" id="roleta_custo" class="form-control" value="50" min="1" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Status</label><select name="status" id="roleta_status" class="form-select"><option value="ativa">Ativa</option><option value="inativa">Inativa</option></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label fw-semibold">Encerramento</label><input type="date" name="data_encerramento" id="roleta_data" class="form-control"></div>
                </div>
                <button type="submit" class="btn btn-gs-primary w-100">Salvar</button>
            </form>
        </div>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const turmasPorUnidade = @json($turmasPorUnidadeJson ?? []);
    function getUid(){ const h=document.getElementById('roleta_unidade_id_hidden'); if(h&&h.value)return h.value; const s=document.getElementById('roleta_unidade_id'); return s?s.value:''; }
    function fillTurmas(ids){ const sel=document.getElementById('roleta_turma_ids'); sel.innerHTML=''; (turmasPorUnidade[getUid()]||[]).forEach(function(t){ const o=document.createElement('option'); o.value=t.id; o.textContent=t.nome; if(ids&&ids.map(String).includes(String(t.id)))o.selected=true; sel.appendChild(o); }); }
    document.getElementById('roleta_unidade_id')?.addEventListener('change',()=>fillTurmas([]));
    document.getElementById('modalRoleta').addEventListener('show.bs.modal',function(e){
        const btn=e.relatedTarget, form=document.getElementById('formRoleta');
        form.querySelector('input[name="_method"]')?.remove();
        if(btn.getAttribute('data-action')==='edit'&&btn.getAttribute('data-roleta')){
            const r=JSON.parse(btn.getAttribute('data-roleta'));
            form.action='{{ url('roletas') }}/'+r.id;
            form.insertAdjacentHTML('afterbegin','<input type="hidden" name="_method" value="PUT">');
            document.getElementById('roleta_titulo').value=r.titulo||'';
            document.getElementById('roleta_descricao').value=r.descricao||'';
            document.getElementById('roleta_custo').value=r.custo_coins||50;
            document.getElementById('roleta_status').value=r.status||'ativa';
            document.getElementById('roleta_data').value=r.data_encerramento||'';
            if(document.getElementById('roleta_unidade_id_hidden'))document.getElementById('roleta_unidade_id_hidden').value=r.unidade_id;
            if(document.getElementById('roleta_unidade_id'))document.getElementById('roleta_unidade_id').value=r.unidade_id;
            fillTurmas(r.turma_ids||[]);
        } else {
            form.action='{{ route('roletas.store') }}';
            fillTurmas([]);
        }
    });
})();
</script>
@endpush
