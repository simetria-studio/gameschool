<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crachá - {{ $aluno->nome }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('alunos.partials.cracha-styles')
</head>
<body class="cracha-print-root">
    <div class="no-print" style="padding: 1rem;">
        <a href="{{ route('alunos.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Voltar</a>
        <button type="button" class="btn btn-dark btn-sm ms-2" onclick="window.print();">Imprimir</button>
    </div>
    <div class="cracha-print-single">
        @include('alunos.partials.cracha-card', ['aluno' => $aluno, 'canvasId' => 'qrcode-single', 'qrSize' => 220])
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script>
        (function () {
            if (!window.QRious) return;
            document.querySelectorAll('canvas[data-cracha-qr]').forEach(function (canvas) {
                new QRious({
                    element: canvas,
                    value: canvas.getAttribute('data-cracha-qr'),
                    size: parseInt(canvas.getAttribute('width'), 10) || 180,
                    background: '#ffffff',
                    foreground: '#000000',
                });
            });
        })();
    </script>
</body>
</html>
