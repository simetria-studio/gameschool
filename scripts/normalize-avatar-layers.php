<?php

/**
 * Recentra o rosto na cabeça da base + gera thumbs 256x256.
 */

$W = 512;
$H = 820;

$baseSrc = __DIR__ . '/../public/imgs/avatar/chatgpt-image-23-de-jul-de-2026-15-34-52-removebg-preview-1784831743.png';
$faceSrc = __DIR__ . '/../public/imgs/avatar/chatgpt-image-23-de-jul-de-2026-22-35-50-removebg-preview-1784857028.png';
$outDir = __DIR__ . '/../public/imgs/avatar/normalized';

if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

function loadPng(string $path)
{
    $img = imagecreatefrompng($path);
    if (! $img) {
        throw new RuntimeException("Falha ao abrir: $path");
    }
    imagealphablending($img, false);
    imagesavealpha($img, true);

    return $img;
}

function removeBlackBg($src, int $threshold = 32)
{
    $w = imagesx($src);
    $h = imagesy($src);
    $dst = imagecreatetruecolor($w, $h);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $clear = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $clear);

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgba = imagecolorat($src, $x, $y);
            $a = ($rgba & 0x7F000000) >> 24;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;
            if ($a >= 120) {
                continue;
            }
            if ($r <= $threshold && $g <= $threshold && $b <= $threshold) {
                continue;
            }
            imagesetpixel($dst, $x, $y, imagecolorallocatealpha($dst, $r, $g, $b, $a));
        }
    }

    return $dst;
}

function opaqueBounds($img): ?array
{
    $w = imagesx($img);
    $h = imagesy($img);
    $minX = $w;
    $minY = $h;
    $maxX = 0;
    $maxY = 0;
    $found = false;

    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $a = (imagecolorat($img, $x, $y) & 0x7F000000) >> 24;
            if ($a < 110) {
                $found = true;
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }
    }

    return $found ? compact('minX', 'minY', 'maxX', 'maxY') : null;
}

function cropOpaque($img)
{
    $b = opaqueBounds($img);
    $cw = $b['maxX'] - $b['minX'] + 1;
    $ch = $b['maxY'] - $b['minY'] + 1;
    $out = imagecreatetruecolor($cw, $ch);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopy($out, $img, 0, 0, $b['minX'], $b['minY'], $cw, $ch);

    return $out;
}

function blankCanvas(int $w, int $h)
{
    $c = imagecreatetruecolor($w, $h);
    imagealphablending($c, false);
    imagesavealpha($c, true);
    imagefill($c, 0, 0, imagecolorallocatealpha($c, 0, 0, 0, 127));

    return $c;
}

function fitOnCanvas($src, int $canvasW, int $canvasH, float $margin = 0.04): array
{
    $src = removeBlackBg($src);
    $cropped = cropOpaque($src);
    imagedestroy($src);

    $cw = imagesx($cropped);
    $ch = imagesy($cropped);
    $maxW = (int) ($canvasW * (1 - 2 * $margin));
    $maxH = (int) ($canvasH * (1 - 2 * $margin));
    $scale = min($maxW / $cw, $maxH / $ch);
    $nw = (int) round($cw * $scale);
    $nh = (int) round($ch * $scale);
    // âncora no fundo (pés) com folga pequena — evita “cortar” sombra
    $dx = (int) round(($canvasW - $nw) / 2);
    $dy = (int) round($canvasH - $nh - $canvasH * 0.035);

    $canvas = blankCanvas($canvasW, $canvasH);
    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $cropped, $dx, $dy, 0, 0, $nw, $nh, $cw, $ch);
    imagealphablending($canvas, false);
    imagedestroy($cropped);

    return compact('canvas', 'dx', 'dy', 'nw', 'nh');
}

/**
 * Cabeça = faixa superior do personagem (do topo até o pescoço).
 * Não usa a maior largura do tronco (ombros confundiam o algoritmo).
 */
function detectHeadBox($baseCanvas, array $placed): array
{
    $dx = $placed['dx'];
    $dy = $placed['dy'];
    $nw = $placed['nw'];
    $nh = $placed['nh'];

    $limitY = $dy + (int) round($nh * 0.34);
    $rows = [];

    for ($y = $dy; $y < $limitY; $y++) {
        $minX = null;
        $maxX = null;
        for ($x = $dx; $x < $dx + $nw; $x++) {
            $a = (imagecolorat($baseCanvas, $x, $y) & 0x7F000000) >> 24;
            if ($a < 110) {
                $minX = $minX === null ? $x : min($minX, $x);
                $maxX = $maxX === null ? $x : max($maxX, $x);
            }
        }
        if ($minX !== null) {
            $rows[$y] = [
                'w' => $maxX - $minX + 1,
                'minX' => $minX,
                'maxX' => $maxX,
                'cx' => ($minX + $maxX) / 2,
            ];
        }
    }

    if (! $rows) {
        return [
            'x' => (int) round($dx + $nw * 0.26),
            'y' => $dy,
            'w' => (int) round($nw * 0.48),
            'h' => (int) round($nh * 0.28),
            'cx' => $dx + $nw / 2,
        ];
    }

    // Do topo: cresce, depois estreita no pescoço
    $peakW = 0;
    $peakY = array_key_first($rows);
    $neckY = null;
    $started = false;

    foreach ($rows as $y => $info) {
        if ($info['w'] > $peakW) {
            $peakW = $info['w'];
            $peakY = $y;
            $started = true;
        } elseif ($started && $info['w'] < $peakW * 0.62) {
            $neckY = $y;
            break;
        }
    }

    $ys = array_keys($rows);
    $topY = $ys[0];
    // se não achou pescoço, corta em ~28% da altura do corpo
    $bottomY = $neckY ?? ($dy + (int) round($nh * 0.28));
    $bottomY = min($bottomY, $limitY - 1);

    $minX = PHP_INT_MAX;
    $maxX = 0;
    $cxSum = 0;
    $n = 0;
    foreach ($rows as $y => $info) {
        if ($y < $topY || $y > $bottomY) {
            continue;
        }
        // só linhas “de cabeça” (>= 70% do pico) — ignora ponta do topo estreita demais e ombros
        if ($info['w'] < $peakW * 0.70 && $y > $peakY) {
            continue;
        }
        $minX = min($minX, $info['minX']);
        $maxX = max($maxX, $info['maxX']);
        $cxSum += $info['cx'];
        $n++;
    }

    if ($n === 0 || $minX === PHP_INT_MAX) {
        $minX = (int) round($dx + $nw * 0.26);
        $maxX = (int) round($dx + $nw * 0.74);
        $cx = $dx + $nw / 2;
    } else {
        $cx = $cxSum / $n;
    }

    $padX = (int) round(($maxX - $minX) * 0.04);
    $h = max(40, $bottomY - $topY + 1);

    return [
        'x' => max(0, $minX - $padX),
        'y' => max(0, $topY - 2),
        'w' => ($maxX - $minX + 1) + 2 * $padX,
        'h' => (int) round($h * 1.05),
        'cx' => $cx,
    ];
}

function placeFace($faceSrc, array $head, int $canvasW, int $canvasH)
{
    $face = cropOpaque(removeBlackBg(loadPng($faceSrc)));
    $cw = imagesx($face);
    $ch = imagesy($face);

    // cobrir a silhueta careca por completo (orelhas da base)
    $targetW = (int) round($head['w'] * 1.22);
    $targetH = (int) round($head['h'] * 1.28);
    $scale = min($targetW / $cw, $targetH / $ch);
    $nw = (int) round($cw * $scale);
    $nh = (int) round($ch * $scale);

    $dx = (int) round($head['cx'] - $nw / 2);
    // alinha topo do rosto um pouco acima do topo da cabeça careca
    $dy = (int) round($head['y'] - $nh * 0.06);

    $canvas = blankCanvas($canvasW, $canvasH);
    imagealphablending($canvas, true);
    imagecopyresampled($canvas, $face, $dx, $dy, 0, 0, $nw, $nh, $cw, $ch);
    imagealphablending($canvas, false);
    imagedestroy($face);

    return $canvas;
}

/** Thumb 256x256: crop do conteúdo opaco centrado */
function makeThumb($srcCanvas, int $size = 256)
{
    $cropped = cropOpaque($srcCanvas);
    $cw = imagesx($cropped);
    $ch = imagesy($cropped);
    $pad = (int) round(max($cw, $ch) * 0.08);
    $side = max($cw, $ch) + 2 * $pad;
    $square = blankCanvas($side, $side);
    imagealphablending($square, true);
    imagecopy($square, $cropped, (int) (($side - $cw) / 2), (int) (($side - $ch) / 2), 0, 0, $cw, $ch);
    imagealphablending($square, false);
    imagedestroy($cropped);

    $thumb = blankCanvas($size, $size);
    imagealphablending($thumb, true);
    imagecopyresampled($thumb, $square, 0, 0, 0, 0, $size, $size, $side, $side);
    imagealphablending($thumb, false);
    imagedestroy($square);

    return $thumb;
}

function savePng($img, string $path): void
{
    imagepng($img, $path, 6);
    echo 'OK ' . basename($path) . ' ' . imagesx($img) . 'x' . imagesy($img) . PHP_EOL;
}

$basePlaced = fitOnCanvas(loadPng($baseSrc), $W, $H, 0.03);
$basePath = $outDir . '/base-masculino-v2.png';
savePng($basePlaced['canvas'], $basePath);

$head = detectHeadBox($basePlaced['canvas'], $basePlaced);
echo "Head: x={$head['x']} y={$head['y']} w={$head['w']} h={$head['h']} cx=" . round($head['cx']) . PHP_EOL;

$faceCanvas = placeFace($faceSrc, $head, $W, $H);
$facePath = $outDir . '/rosto-feliz-v2.png';
savePng($faceCanvas, $facePath);

$baseThumb = makeThumb($basePlaced['canvas']);
$faceThumb = makeThumb($faceCanvas);
savePng($baseThumb, $outDir . '/base-masculino-v2-thumb.png');
savePng($faceThumb, $outDir . '/rosto-feliz-v2-thumb.png');

$preview = blankCanvas($W, $H);
// fundo suave para preview
imagealphablending($preview, true);
$bg = imagecreatetruecolor($W, $H);
imagefill($bg, 0, 0, imagecolorallocate($bg, 20, 50, 80));
imagecopy($preview, $bg, 0, 0, 0, 0, $W, $H);
imagedestroy($bg);
imagecopy($preview, $basePlaced['canvas'], 0, 0, 0, 0, $W, $H);
imagecopy($preview, $faceCanvas, 0, 0, 0, 0, $W, $H);
imagealphablending($preview, false);
savePng($preview, $outDir . '/preview-base-rosto.png');

imagedestroy($basePlaced['canvas']);
imagedestroy($faceCanvas);
imagedestroy($baseThumb);
imagedestroy($faceThumb);
imagedestroy($preview);

echo "Done\n";
