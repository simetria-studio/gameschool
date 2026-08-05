<?php

/**
 * Re-normaliza peças raster (rosto/base/etc.) que não estão no canvas 512×820.
 *
 * Uso: php scripts/renormalize-avatar-pecas.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AvatarPeca;
use App\Support\AvatarImagemStorage;
use App\Support\AvatarLayerNormalizer;

$slots = array_merge(['base'], AvatarLayerNormalizer::HEAD_SLOTS);
$pecas = AvatarPeca::query()
    ->whereIn('slot', $slots)
    ->where('tipo_asset', 'png')
    ->orderBy('slot')
    ->orderBy('id')
    ->get();

echo "Peças candidatas: {$pecas->count()}\n";

foreach ($pecas as $peca) {
    $full = AvatarLayerNormalizer::publicPathFromUrl((string) $peca->asset_url);
    if (! $full || ! is_file($full)) {
        echo "SKIP #{$peca->id} {$peca->titulo} — arquivo ausente\n";
        continue;
    }

    // SVGs starter não precisam
    if (str_ends_with(strtolower($full), '.svg')) {
        echo "SKIP #{$peca->id} {$peca->titulo} — SVG\n";
        continue;
    }

    $info = @getimagesize($full);
    $w = $info[0] ?? 0;
    $h = $info[1] ?? 0;
    $jaOk = $w === AvatarLayerNormalizer::CANVAS_W && $h === AvatarLayerNormalizer::CANVAS_H
        && str_contains((string) $peca->asset_url, '/normalized/');

    if ($jaOk) {
        echo "OK   #{$peca->id} {$peca->titulo} — já {$w}x{$h} normalizado\n";
        continue;
    }

    echo "FIX  #{$peca->id} {$peca->titulo} ({$peca->slot}) {$w}x{$h} → 512x820 ... ";

    try {
        $paths = AvatarLayerNormalizer::normalizeUploadedFile(
            $full,
            $peca->slot,
            $peca->genero ?: 'unissex',
            \Illuminate\Support\Str::slug($peca->titulo . '-' . $peca->slot) ?: ('peca-' . $peca->id),
        );

        $oldAsset = $peca->asset_url;
        $oldThumb = $peca->thumbnail_url;

        $peca->asset_url = $paths['asset'];
        $peca->thumbnail_url = $paths['thumb'];
        $peca->save();

        // Apaga raw antigo se não for starter/normalized compartilhado
        if ($oldAsset && $oldAsset !== $paths['asset'] && ! str_contains($oldAsset, '/starter/')) {
            // Mantém o arquivo bruto original (útil para reprocessar); só limpa thumb solta
        }
        if ($oldThumb && $oldThumb !== $oldAsset && $oldThumb !== $paths['thumb'] && ! str_contains((string) $oldThumb, '/starter/')) {
            AvatarImagemStorage::delete($oldThumb);
        }

        echo "done → {$paths['asset']}\n";
    } catch (Throwable $e) {
        echo 'ERRO: ' . $e->getMessage() . "\n";
    }
}

echo "Concluído.\n";
