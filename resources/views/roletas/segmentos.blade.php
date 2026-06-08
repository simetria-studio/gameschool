@extends('layouts.app')

@section('title', 'Prêmios da Roleta')
@section('breadcrumb', 'ROLETAS / PRÊMIOS')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 mb-2">
            <a href="{{ route('roletas.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Voltar</a>
            <a href="{{ route('roleta-colecionaveis.index') }}" class="btn btn-sm btn-outline-secondary">Personagens e figurinhas</a>
        </div>
        <h1 class="h5 fw-bold mb-0">{{ $roleta->titulo }}</h1>
        <p class="small gs-text-secondary">
            Configure os segmentos da roleta ·
            @if ($roleta->somente_gratis)
                <strong>totalmente grátis</strong> (giros ilimitados)
            @elseif ((int) $roleta->giros_gratis_por_semana > 0 && (int) $roleta->custo_coins > 0)
                {{ $roleta->giros_gratis_por_semana }} giro(s) grátis/semana · extras: {{ $roleta->custo_coins }} coins
            @elseif ((int) $roleta->giros_gratis_por_semana > 0)
                {{ $roleta->giros_gratis_por_semana }} giro(s) grátis/semana
            @elseif ((int) $roleta->custo_coins > 0)
                somente pago: {{ $roleta->custo_coins }} coins
            @else
                configure giros na edição da roleta
            @endif
        </p>
    </div>

    @if (session('success'))<div class="alert alert-success py-2 small">{{ session('success') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="gs-card p-4">
                <h2 class="h6 fw-bold mb-3">Novo segmento</h2>
                <form method="post" action="{{ route('roletas.segmentos.store', $roleta) }}" id="formSegmento">@csrf
                    <div class="mb-3"><label class="form-label fw-semibold">Título na roleta</label><input type="text" name="titulo" class="form-control" required placeholder="Ex: Baú Misterioso"></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select name="tipo" id="seg_tipo" class="form-select" required>
                                <option value="item">Item fixo (personagem/figurinha/emote)</option>
                                <option value="item_aleatorio">1 item aleatório (qualquer item da escola)</option>
                                <option value="coins">Coins</option>
                                <option value="xp">XP</option>
                                <option value="bau">Baú (vários itens aleatórios)</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-semibold">Peso (chance)</label>
                            <input type="number" name="peso" class="form-control" value="10" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label fw-semibold">Cor (hex)</label><input type="color" name="cor" class="form-control form-control-color w-100" value="#F2B233"></div>

                    <div id="campo_item" class="mb-3">
                        <label class="form-label fw-semibold">Item</label>
                        <select name="roleta_item_id" class="form-select">
                            <option value="">Selecione...</option>
                            @foreach($itens as $item)
                                <option value="{{ $item->id }}">{{ $item->emoji }} {{ $item->titulo }} ({{ $item->tipo }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="campo_item_aleatorio" class="mb-3 d-none">
                        <p class="small text-muted mb-0">
                            <i class="bi bi-shuffle me-1"></i>
                            Ao ganhar, sorteia <strong>1 item</strong> entre todos os personagens, figurinhas e emotes ativos da escola.
                        </p>
                    </div>
                    <div id="campo_coins" class="mb-3 d-none"><label class="form-label fw-semibold">Coins</label><input type="number" name="coins" class="form-control" value="10" min="0"></div>
                    <div id="campo_xp" class="mb-3 d-none"><label class="form-label fw-semibold">XP</label><input type="number" name="xp" class="form-control" value="5" min="0"></div>

                    <div id="campo_bau" class="mb-3 d-none">
                        <label class="form-label fw-semibold">Itens do baú (mín. 2)</label>
                        <p class="small text-muted">Ao ganhar, sorteia 2–4 itens aleatórios deste pool.</p>
                        @foreach($itens as $i => $item)
                            <div class="input-group input-group-sm mb-2">
                                <div class="input-group-text"><input type="checkbox" name="bau_itens[{{ $i }}][ativo]" value="1" class="bau-check" data-index="{{ $i }}"></div>
                                <span class="input-group-text">{{ $item->emoji }} {{ Str::limit($item->titulo, 20) }}</span>
                                <input type="hidden" name="bau_itens[{{ $i }}][roleta_item_id]" value="{{ $item->id }}" disabled class="bau-id" data-index="{{ $i }}">
                                <input type="number" name="bau_itens[{{ $i }}][peso]" value="10" min="1" class="form-control bau-peso" data-index="{{ $i }}" disabled placeholder="Peso">
                            </div>
                        @endforeach
                    </div>

                    <button type="submit" class="btn btn-gs-primary w-100">Adicionar segmento</button>
                </form>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="gs-card p-0 overflow-hidden">
                <div class="p-3 border-bottom"><h2 class="h6 fw-bold mb-0">Segmentos ({{ $roleta->segmentos->count() }})</h2></div>
                @forelse($roleta->segmentos as $seg)
                    <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }} d-flex justify-content-between gap-2">
                        <div>
                            <span class="badge me-2" style="background:{{ $seg->cor ?? '#ccc' }}">&nbsp;</span>
                            <strong>{{ $seg->titulo }}</strong>
                            <span class="small gs-text-secondary ms-2">{{ strtoupper($seg->tipo) }} · peso {{ $seg->peso }}</span>
                            @if($seg->tipo==='item' && $seg->item)<div class="small mt-1">{{ $seg->item->emoji }} {{ $seg->item->titulo }}</div>@endif
                            @if($seg->tipo==='item_aleatorio')<div class="small mt-1">🎲 Qualquer item ativo da escola</div>@endif
                            @if($seg->tipo==='coins')<div class="small mt-1">{{ $seg->coins }} coins</div>@endif
                            @if($seg->tipo==='xp')<div class="small mt-1">{{ $seg->xp }} XP</div>@endif
                            @if($seg->tipo==='bau')
                                <div class="small mt-1">Baú: {{ $seg->bauItens->map(fn($b)=>($b->item->emoji??'').$b->item->titulo)->join(', ') ?: '—' }}</div>
                            @endif
                        </div>
                        <form action="{{ route('roletas.segmentos.destroy', [$roleta, $seg]) }}" method="post" onsubmit="return confirm('Remover?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </div>
                @empty
                    <div class="p-5 text-center gs-text-secondary">Adicione segmentos para ativar a roleta.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const tipo = document.getElementById('seg_tipo');
    const campos = { item: 'campo_item', item_aleatorio: 'campo_item_aleatorio', coins: 'campo_coins', xp: 'campo_xp', bau: 'campo_bau' };
    function toggle(){
        Object.values(campos).forEach(id => document.getElementById(id).classList.add('d-none'));
        document.getElementById(campos[tipo.value]).classList.remove('d-none');
    }
    tipo.addEventListener('change', toggle); toggle();

    document.querySelectorAll('.bau-check').forEach(function(ch){
        ch.addEventListener('change', function(){
            const i = this.dataset.index;
            document.querySelectorAll('[data-index="'+i+'"].bau-id, [data-index="'+i+'"].bau-peso').forEach(function(el){
                el.disabled = !ch.checked;
            });
        });
    });

    document.getElementById('formSegmento').addEventListener('submit', function(){
        document.querySelectorAll('.bau-check:not(:checked)').forEach(function(ch){
            const i = ch.dataset.index;
            document.querySelectorAll('[name^="bau_itens['+i+']"]').forEach(function(el){ el.disabled = true; });
        });
    });
})();
</script>
@endpush
