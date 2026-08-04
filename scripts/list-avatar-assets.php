<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$files = [
    __DIR__ . '/../public/imgs/avatar/chatgpt-image-23-de-jul-de-2026-22-35-50-removebg-preview-1784857028.png',
    __DIR__ . '/../public/imgs/avatar/chatgpt-image-23-de-jul-de-2026-15-34-52-removebg-preview-1784831743.png',
];

foreach ($files as $f) {
    $i = @getimagesize($f);
    echo basename($f) . ' => ' . ($i ? "{$i[0]}x{$i[1]}" : 'missing') . PHP_EOL;
}

echo "--- pieces ---" . PHP_EOL;
foreach (App\Models\AvatarPeca::orderBy('id')->get() as $p) {
    echo "{$p->id} | {$p->slot} | {$p->titulo} | {$p->asset_url}" . PHP_EOL;
}
