<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AvatarPeca;

$base = AvatarPeca::query()->where('slot', 'base')->orderByDesc('id')->first();
$face = AvatarPeca::query()->where('slot', 'rosto')->orderByDesc('id')->first();

if ($base) {
    $base->update([
        'asset_url' => '/imgs/avatar/normalized/base-masculino-v2.png',
        'thumbnail_url' => '/imgs/avatar/normalized/base-masculino-v2-thumb.png',
    ]);
    echo "base #{$base->id}\n";
}

if ($face) {
    $face->update([
        'titulo' => 'Rosto feliz',
        'asset_url' => '/imgs/avatar/normalized/rosto-feliz-v2.png',
        'thumbnail_url' => '/imgs/avatar/normalized/rosto-feliz-v2-thumb.png',
    ]);
    echo "face #{$face->id}\n";
}

echo "OK\n";
