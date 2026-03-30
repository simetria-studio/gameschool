{{--
  $aluno — modelo Aluno com user, unidade
  $canvasId — id único para o canvas do QR (ex.: qrcode-123)
  $qrSize — tamanho interno do QR em px (padrão 180)
--}}
@php
    $qrSize = (int) ($qrSize ?? 180);
    $loginUrl = ($aluno->user && $aluno->user->qr_login_token)
        ? route('login.qr', $aluno->user->qr_login_token)
        : null;
@endphp
<article class="badge-card">
    <div class="badge-card__yellow">
        <div class="badge-card__pattern" aria-hidden="true"></div>
        <div class="badge-card__top">
            <div class="badge-card__logo-ring">
                <img class="badge-card__logo" src="{{ asset('imgs/icone.png') }}" alt="" width="64" height="64">
            </div>
            <div class="badge-card__brand">GO GAME SCHOOL</div>
        </div>
        <div class="badge-card__qr-box">
            @if($loginUrl)
                <canvas
                    id="{{ $canvasId }}"
                    class="badge-card__canvas"
                    width="{{ $qrSize }}"
                    height="{{ $qrSize }}"
                    data-cracha-qr="{{ $loginUrl }}"
                ></canvas>
            @else
                <div class="badge-card__qr-placeholder">QR indisponível</div>
            @endif
        </div>
        <div class="badge-card__student">
            <div class="badge-card__name">{{ $aluno->nome }}</div>
            <div class="badge-card__unit">{{ $aluno->unidade?->titulo ?? '—' }}</div>
        </div>
    </div>
    <footer class="badge-card__footer">{{ $aluno->unidade?->titulo ?? '—' }}</footer>
</article>
