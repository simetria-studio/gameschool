<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crachá - {{ $aluno->nome }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --gs-primary: #F2B233; --gs-text: #2C2C2C; }
        body { font-family: system-ui, sans-serif; color: var(--gs-text); padding: 1rem; }
        .cracha { max-width: 320px; margin: 0 auto; border: 2px solid #ddd; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .cracha-header { background: var(--gs-primary); color: #1A1A1A; padding: 0.75rem 1rem; font-weight: bold; font-size: 0.9rem; }
        .cracha-body { padding: 1.25rem; background: #fff; }
        .cracha-foto { width: 80px; height: 80px; border-radius: 50%; background: #eee; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: #999; font-size: 2rem; }
        .cracha-nome { font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; }
        .cracha-info { font-size: 0.9rem; color: #6B6B6B; }
        .cracha-qr { display: inline-block; }
        .cracha-qr-hint { font-size: 0.75rem; color: #999; margin: 0; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print mb-3">
        <a href="{{ route('alunos.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Voltar</a>
        <button type="button" class="btn btn-primary btn-sm ms-2" onclick="window.print();"><i class="bi bi-printer"></i> Imprimir</button>
    </div>
    <div class="cracha">
        <div class="cracha-header">{{ config('app.name') }}</div>
        <div class="cracha-body text-center">
            <div class="cracha-foto"><i class="bi bi-person-fill"></i></div>
            <div class="cracha-nome">{{ $aluno->nome }}</div>
            <div class="cracha-info">{{ $aluno->turma->nome ?? '—' }}</div>
            <div class="cracha-info">{{ $aluno->unidade->titulo ?? '—' }}</div>
            <div class="cracha-info mt-2">Coins: {{ $aluno->coins }} &bull; XP: {{ $aluno->xp }}</div>
            @if($aluno->user)
                <div class="cracha-qr mt-3">
                    <canvas id="qrcode" width="140" height="140"></canvas>
                    <p class="cracha-qr-hint mt-1">Escaneie para acessar o app</p>
                </div>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @if($aluno->user)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script>
        (function() {
            if (window.QRious && document.getElementById('qrcode')) {
                new QRious({
                    element: document.getElementById('qrcode'),
                    value: @json(route('login.qr', $aluno->user->qr_login_token)),
                    size: 140
                });
            }
        })();
    </script>
    @endif
</body>
</html>
