<?php

/**
 * Monta uma prévia empilhando peças já normalizadas, para conferir encaixe.
 *
 * Uso: php scripts/preview-avatar.php 32,25,31
 * Saída: storage/app/avatar-test/preview.png
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AvatarPeca;
use App\Support\AvatarLayerNormalizer;

$ids = array_filter(array_map('trim', explode(',', $argv[1] ?? '')));

if (! $ids) {
    $ids = AvatarPeca::query()
        ->whereIn('slot', ['base', 'rosto', 'cabelo'])
        ->where('status', 'ativo')
        ->orderByRaw("FIELD(slot,'base','rosto','cabelo')")
        ->orderByDesc('id')
        ->get()
        ->unique('slot')
        ->sortBy(fn ($p) => array_search($p->slot, ['base', 'rosto', 'cabelo'], true))
        ->pluck('id')
        ->all();
}

$outDir = __DIR__ . '/../storage/app/avatar-test';
if (! is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$canvas = imagecreatetruecolor(AvatarLayerNormalizer::CANVAS_W, AvatarLayerNormalizer::CANVAS_H);
imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
imagealphablending($canvas, true);

foreach ($ids as $id) {
    $peca = AvatarPeca::find((int) $id);
    if (! $peca) {
        echo "peça #{$id} não encontrada\n";
        continue;
    }

    $path = AvatarLayerNormalizer::publicPathFromUrl((string) $peca->asset_url);
    if (! $path || ! is_file($path)) {
        echo "arquivo ausente para #{$id}\n";
        continue;
    }

    $img = imagecreatefrompng($path);
    imagecopy($canvas, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
    imagedestroy($img);

    echo "+ #{$peca->id} {$peca->slot} {$peca->titulo}\n";
}

imagepng($canvas, $outDir . '/preview.png', 6);
echo "→ storage/app/avatar-test/preview.png\n";
