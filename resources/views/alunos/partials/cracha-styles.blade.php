<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,700;1,400&family=Orbitron:wght@600;700&display=swap" rel="stylesheet">
<style>
    /* ========== Pré-visualização (tela) ========== */
    .badge-card {
        --badge-yellow: #e8c82a;
        --badge-yellow-deep: #d4b420;
        --badge-border: #1a1a1a;
        font-family: "DM Sans", system-ui, sans-serif;
        border: 2px solid var(--badge-border);
        border-radius: 2px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #fff;
        width: 100%;
        max-width: 220px;
        margin: 0 auto;
        box-sizing: border-box;
        min-height: 320px;
    }

    .badge-card__yellow {
        flex: 1;
        position: relative;
        background: linear-gradient(165deg, var(--badge-yellow) 0%, var(--badge-yellow-deep) 100%);
        padding: 10px 10px 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 0;
    }

    .badge-card__pattern {
        position: absolute;
        inset: 0;
        opacity: 0.12;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Ctext x='10' y='28' font-size='20'%3E🎮%3C/text%3E%3Ctext x='44' y='42' font-size='16'%3E⚔%3C/text%3E%3Ctext x='12' y='68' font-size='18'%3E🎧%3C/text%3E%3Ctext x='46' y='72' font-size='14'%3E⭐%3C/text%3E%3C/svg%3E");
        background-size: 72px 72px;
        pointer-events: none;
    }

    .badge-card__top {
        position: relative;
        z-index: 1;
        text-align: center;
        width: 100%;
    }

    .badge-card__logo-ring {
        width: 72px;
        height: 72px;
        margin: 0 auto 6px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid var(--badge-border);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .badge-card__logo {
        width: 56px;
        height: 56px;
        object-fit: contain;
        border-radius: 50%;
    }

    .badge-card__brand {
        font-family: "Orbitron", sans-serif;
        font-weight: 700;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        color: #1a1a1a;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .badge-card__qr-box {
        position: relative;
        z-index: 1;
        margin: 10px auto 8px;
        padding: 8px;
        background: #fff;
        border: 2px solid var(--badge-border);
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }

    .badge-card__canvas {
        display: block;
        width: 120px !important;
        height: 120px !important;
    }

    .badge-card__qr-placeholder {
        width: 120px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        color: #666;
        text-align: center;
        padding: 6px;
    }

    .badge-card__student {
        position: relative;
        z-index: 1;
        text-align: center;
        width: 100%;
        margin-top: auto;
        padding-top: 4px;
    }

    .badge-card__name {
        font-weight: 700;
        font-size: 0.78rem;
        color: #1a1a1a;
        line-height: 1.25;
        word-break: break-word;
    }

    .badge-card__unit {
        font-size: 0.65rem;
        color: #2c2c2c;
        margin-top: 2px;
        opacity: 0.9;
        line-height: 1.2;
    }

    .badge-card__footer {
        flex-shrink: 0;
        background: #fff;
        border-top: 2px solid var(--badge-border);
        padding: 8px 6px;
        text-align: center;
        font-family: "DM Sans", sans-serif;
        font-weight: 700;
        font-size: 0.55rem;
        letter-spacing: 0.04em;
        color: #1a1a1a;
        text-transform: uppercase;
        line-height: 1.2;
    }

    /* ========== Folha de impressão: 8 por página (4 col × 2 linhas) ========== */
    .cracha-print-page {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: auto;
        gap: 3mm;
        align-content: start;
        justify-items: center;
        box-sizing: border-box;
    }

    /* Retrato estilo crachá (54×86 mm): estreito no meio da coluna — evita “gordo” */
    .cracha-print-page .badge-card {
        width: 40mm;
        max-width: 40mm;
        margin: 0;
        box-sizing: border-box;
        aspect-ratio: 54 / 86;
        height: auto;
        min-height: 0;
    }

    .cracha-print-page .badge-card__yellow {
        padding: 2mm 2.5mm 1.5mm;
    }

    .cracha-print-page .badge-card__canvas {
        width: 19mm !important;
        height: 19mm !important;
    }

    .cracha-print-page .badge-card__qr-box {
        margin: 1.5mm auto;
        padding: 1mm;
    }

    .cracha-print-page .badge-card__logo-ring {
        width: 11mm;
        height: 11mm;
        margin-bottom: 1mm;
    }

    .cracha-print-page .badge-card__logo {
        width: 8mm;
        height: 8mm;
    }

    .cracha-print-page .badge-card__brand {
        font-size: 4.8pt;
        letter-spacing: 0.04em;
    }

    .cracha-print-page .badge-card__name {
        font-size: 6pt;
    }

    .cracha-print-page .badge-card__unit {
        font-size: 5pt;
    }

    .cracha-print-page .badge-card__footer {
        font-size: 4.5pt;
        padding: 1.5mm 1mm;
    }

    .cracha-print-page .badge-card__student {
        padding-top: 1mm;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 7mm;
        }

        .cracha-print-root,
        .cracha-print-root * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print {
            display: none !important;
        }

        /* Só na impressão de telas que têm .cracha-print-root (lote / painel) */
        body:has(.cracha-print-root) .gs-sidebar,
        body:has(.cracha-print-root) .gs-topbar {
            display: none !important;
        }

        body:has(.cracha-print-root) .d-flex.min-vh-100 {
            display: block !important;
        }

        body:has(.cracha-print-root) main.gs-main-inner {
            padding: 0 !important;
        }

        .cracha-print-root {
            background: #fff !important;
        }

        /* Uma página = até 8 crachás */
        .cracha-print-page {
            page-break-after: always;
        }

        .cracha-print-page:last-of-type {
            page-break-after: auto;
        }

        /* Página única (crachá individual): centralizar */
        .cracha-print-single {
            min-height: calc(297mm - 14mm);
            display: flex;
            align-items: center;
            justify-content: center;
            page-break-after: auto;
        }

        .cracha-print-single .badge-card {
            width: 52mm;
            max-width: 52mm;
            min-height: 95mm;
        }

        .cracha-print-single .badge-card__canvas {
            width: 28mm !important;
            height: 28mm !important;
        }
    }
</style>
