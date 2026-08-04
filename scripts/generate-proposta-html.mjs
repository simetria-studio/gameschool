import { readFileSync, writeFileSync } from 'fs';
import { marked } from 'marked';

const input = 'docs/PROPOSTA-SISTEMA-AVATAR-PERSONAGEM.md';
const output = 'docs/proposta-avatar.html';

const md = readFileSync(input, 'utf8');
const body = marked.parse(md);

const html = `<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Proposta — Sistema de Avatar | Game School</title>
  <style>
    @page { margin: 18mm 15mm; size: A4; }
    * { box-sizing: border-box; }
    body {
      font-family: "Segoe UI", system-ui, sans-serif;
      font-size: 11pt;
      line-height: 1.5;
      color: #1a1a1a;
      max-width: 210mm;
      margin: 0 auto;
      padding: 12mm 10mm;
    }
    h1 { font-size: 22pt; border-bottom: 2px solid #2563eb; padding-bottom: 8px; page-break-after: avoid; }
    h2 { font-size: 15pt; color: #1e40af; margin-top: 1.4em; page-break-after: avoid; }
    h3 { font-size: 12pt; page-break-after: avoid; }
    table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 10pt; page-break-inside: avoid; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
    th { background: #f1f5f9; font-weight: 600; }
    tr:nth-child(even) td { background: #fafafa; }
    code, pre { font-family: Consolas, monospace; font-size: 9pt; }
    pre {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      padding: 10px;
      overflow-x: auto;
      page-break-inside: avoid;
    }
    blockquote { border-left: 4px solid #2563eb; margin: 12px 0; padding: 8px 16px; background: #eff6ff; }
    hr { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
    .print-hint {
      background: #fef3c7;
      border: 1px solid #f59e0b;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 24px;
      font-size: 10pt;
    }
    @media print {
      .print-hint { display: none; }
      body { padding: 0; }
      a { color: inherit; text-decoration: none; }
    }
  </style>
</head>
<body>
  <div class="print-hint">
    <strong>Exportar PDF:</strong> pressione <kbd>Ctrl+P</kbd> → destino <strong>Salvar como PDF</strong> → Salvar.
    Este aviso não aparece no PDF impresso.
  </div>
  ${body}
</body>
</html>`;

writeFileSync(output, html, 'utf8');
console.log('Gerado:', output);
console.log('Abra no navegador e use Ctrl+P → Salvar como PDF');
