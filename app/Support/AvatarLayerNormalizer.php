<?php

namespace App\Support;

use App\Models\AvatarPeca;
use RuntimeException;

/**
 * Normaliza peças raster para o canvas compartilhado 512×820.
 * Clientes só empilham com object-fit/contain — o alinhamento precisa estar no PNG.
 */
class AvatarLayerNormalizer
{
    public const CANVAS_W = 512;

    public const CANVAS_H = 820;

    public const THUMB_SIZE = 256;

    /** Guia do briefing quando não há base detectável. */
    private const FALLBACK_HEAD = [
        'x' => 148,
        'y' => 48,
        'w' => 216,
        'h' => 236,
        'cx' => 256.0,
    ];

    /** Slots alinhados à cabeça da base. */
    public const HEAD_SLOTS = [
        'rosto',
        'cabelo',
        'acessorio_cabeca',
        'acessorio_rosto',
    ];

    public static function supportsExtension(string $ext): bool
    {
        return in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'webp'], true);
    }

    /**
     * Normaliza um arquivo de imagem para 512×820 e grava PNG.
     *
     * @return array{asset: string, thumb: string} caminhos relativos (/imgs/avatar/...)
     */
    public static function normalizeUploadedFile(
        string $sourcePath,
        string $slot,
        string $genero = 'unissex',
        ?string $nomeBase = null,
    ): array {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('Arquivo de avatar não encontrado para normalizar.');
        }

        $destinoDir = self::ensureWritableDir(public_path('imgs/avatar/normalized'));

        $slug = $nomeBase ?: ('peca-' . $slot);
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($slug)) ?: 'peca';
        $slug = trim($slug, '-');
        $stamp = time();

        $assetName = $slug . '-' . $stamp . '.png';
        $thumbName = $slug . '-' . $stamp . '-thumb.png';
        $assetFull = $destinoDir . DIRECTORY_SEPARATOR . $assetName;
        $thumbFull = $destinoDir . DIRECTORY_SEPARATOR . $thumbName;

        $canvas = self::buildCanvas($sourcePath, $slot, $genero);
        self::savePng($canvas, $assetFull);

        $thumb = self::makeThumb($canvas);
        self::savePng($thumb, $thumbFull);

        imagedestroy($canvas);
        imagedestroy($thumb);

        return [
            'asset' => '/imgs/avatar/normalized/' . $assetName,
            'thumb' => '/imgs/avatar/normalized/' . $thumbName,
        ];
    }

    /**
     * Garante pasta existente e gravável pelo PHP (www-data / nginx).
     */
    public static function ensureWritableDir(string $dir): string
    {
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
                throw new RuntimeException(
                    'Não foi possível criar a pasta de avatar: '.$dir.
                    '. No servidor, rode: sudo mkdir -p '.$dir.
                    ' && sudo chown -R www-data:www-data '.dirname($dir).
                    ' && sudo chmod -R ug+rwX '.dirname($dir)
                );
            }
        }

        if (! is_writable($dir)) {
            throw new RuntimeException(
                'Sem permissão para gravar em '.$dir.
                '. No servidor, rode: sudo chown -R www-data:www-data '.dirname($dir).
                ' && sudo chmod -R ug+rwX '.dirname($dir)
            );
        }

        return $dir;
    }

    /** @return resource|\GdImage */
    public static function buildCanvas(string $sourcePath, string $slot, string $genero = 'unissex')
    {
        if ($slot === 'base') {
            $placed = self::fitOnCanvas(self::loadImage($sourcePath), self::CANVAS_W, self::CANVAS_H, 0.03);

            return $placed['canvas'];
        }

        if (in_array($slot, self::HEAD_SLOTS, true)) {
            $head = self::resolveHeadBox($genero);

            return self::placeOnHead($sourcePath, $head, $slot);
        }

        return self::fitCentered(self::loadImage($sourcePath), self::CANVAS_W, self::CANVAS_H, 0.04)['canvas'];
    }

    /**
     * @return array{x:int,y:int,w:int,h:int,cx:float}
     */
    public static function resolveHeadBox(string $genero): array
    {
        $basePath = self::resolveBaseImagePath($genero);
        if (! $basePath) {
            return self::FALLBACK_HEAD;
        }

        $base = self::loadImage($basePath);
        // Base já normalizada: bounds do conteúdo
        $bounds = self::opaqueBounds($base);
        if (! $bounds) {
            imagedestroy($base);

            return self::FALLBACK_HEAD;
        }

        $placed = [
            'dx' => $bounds['minX'],
            'dy' => $bounds['minY'],
            'nw' => $bounds['maxX'] - $bounds['minX'] + 1,
            'nh' => $bounds['maxY'] - $bounds['minY'] + 1,
        ];
        $head = self::detectHeadBox($base, $placed);
        imagedestroy($base);

        return $head;
    }

    public static function resolveBaseImagePath(string $genero): ?string
    {
        $prefer = match ($genero) {
            'feminino' => ['feminino', 'unissex', 'masculino'],
            'masculino' => ['masculino', 'unissex', 'feminino'],
            default => ['unissex', 'masculino', 'feminino'],
        };

        foreach ($prefer as $g) {
            $peca = AvatarPeca::query()
                ->where('slot', 'base')
                ->where('status', 'ativo')
                ->where('genero', $g)
                ->where('tipo_asset', 'png')
                ->orderByDesc('is_starter')
                ->orderByDesc('id')
                ->first();

            if ($peca?->asset_url) {
                $full = self::publicPathFromUrl($peca->asset_url);
                if ($full && is_file($full)) {
                    return $full;
                }
            }
        }

        $fallback = public_path('imgs/avatar/normalized/base-masculino-v2.png');

        return is_file($fallback) ? $fallback : null;
    }

    public static function publicPathFromUrl(string $url): ?string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $path = parse_url($url, PHP_URL_PATH);
            if (! is_string($path) || $path === '') {
                return null;
            }
            $url = $path;
        }

        $rel = ltrim($url, '/');
        if (str_starts_with($rel, 'imgs/avatar/')) {
            return public_path($rel);
        }

        return null;
    }

    /** @return resource|\GdImage */
    private static function placeOnHead(string $faceSrc, array $head, string $slot)
    {
        $face = self::cropOpaque(self::removeBlackBg(self::loadImage($faceSrc)));
        $cw = imagesx($face);
        $ch = imagesy($face);

        // Rosto cobre a cabeça careca; cabelo/acessórios um pouco maiores.
        $scaleFactorW = match ($slot) {
            'cabelo', 'acessorio_cabeca' => 1.55,
            'acessorio_rosto' => 1.15,
            default => 1.22,
        };
        $scaleFactorH = match ($slot) {
            'cabelo', 'acessorio_cabeca' => 1.65,
            'acessorio_rosto' => 1.10,
            default => 1.28,
        };

        $targetW = (int) round($head['w'] * $scaleFactorW);
        $targetH = (int) round($head['h'] * $scaleFactorH);
        $scale = min($targetW / max(1, $cw), $targetH / max(1, $ch));
        $nw = max(1, (int) round($cw * $scale));
        $nh = max(1, (int) round($ch * $scale));

        $dx = (int) round($head['cx'] - $nw / 2);
        $dy = match ($slot) {
            'cabelo', 'acessorio_cabeca' => (int) round($head['y'] - $nh * 0.28),
            'acessorio_rosto' => (int) round($head['y'] + $head['h'] * 0.18 - $nh / 2),
            default => (int) round($head['y'] - $nh * 0.06),
        };

        $canvas = self::blankCanvas(self::CANVAS_W, self::CANVAS_H);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $face, $dx, $dy, 0, 0, $nw, $nh, $cw, $ch);
        imagealphablending($canvas, false);
        imagedestroy($face);

        return $canvas;
    }

    /**
     * @param  resource|\GdImage  $src
     * @return array{canvas: resource|\GdImage, dx: int, dy: int, nw: int, nh: int}
     */
    private static function fitOnCanvas($src, int $canvasW, int $canvasH, float $margin = 0.04): array
    {
        $src = self::removeBlackBg($src);
        $cropped = self::cropOpaque($src);
        imagedestroy($src);

        $cw = imagesx($cropped);
        $ch = imagesy($cropped);
        $maxW = (int) ($canvasW * (1 - 2 * $margin));
        $maxH = (int) ($canvasH * (1 - 2 * $margin));
        $scale = min($maxW / max(1, $cw), $maxH / max(1, $ch));
        $nw = max(1, (int) round($cw * $scale));
        $nh = max(1, (int) round($ch * $scale));
        $dx = (int) round(($canvasW - $nw) / 2);
        $dy = (int) round($canvasH - $nh - $canvasH * 0.035);

        $canvas = self::blankCanvas($canvasW, $canvasH);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $cropped, $dx, $dy, 0, 0, $nw, $nh, $cw, $ch);
        imagealphablending($canvas, false);
        imagedestroy($cropped);

        return compact('canvas', 'dx', 'dy', 'nw', 'nh');
    }

    /**
     * @param  resource|\GdImage  $src
     * @return array{canvas: resource|\GdImage, dx: int, dy: int, nw: int, nh: int}
     */
    private static function fitCentered($src, int $canvasW, int $canvasH, float $margin = 0.04): array
    {
        $src = self::removeBlackBg($src);
        $cropped = self::cropOpaque($src);
        imagedestroy($src);

        $cw = imagesx($cropped);
        $ch = imagesy($cropped);
        $maxW = (int) ($canvasW * (1 - 2 * $margin));
        $maxH = (int) ($canvasH * (1 - 2 * $margin));
        $scale = min($maxW / max(1, $cw), $maxH / max(1, $ch));
        $nw = max(1, (int) round($cw * $scale));
        $nh = max(1, (int) round($ch * $scale));
        $dx = (int) round(($canvasW - $nw) / 2);
        $dy = (int) round(($canvasH - $nh) / 2);

        $canvas = self::blankCanvas($canvasW, $canvasH);
        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $cropped, $dx, $dy, 0, 0, $nw, $nh, $cw, $ch);
        imagealphablending($canvas, false);
        imagedestroy($cropped);

        return compact('canvas', 'dx', 'dy', 'nw', 'nh');
    }

    /**
     * @param  resource|\GdImage  $baseCanvas
     * @param  array{dx:int,dy:int,nw:int,nh:int}  $placed
     * @return array{x:int,y:int,w:int,h:int,cx:float}
     */
    private static function detectHeadBox($baseCanvas, array $placed): array
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

    /** @param  resource|\GdImage  $srcCanvas
     *  @return resource|\GdImage */
    public static function makeThumb($srcCanvas, int $size = self::THUMB_SIZE)
    {
        $cropped = self::cropOpaque($srcCanvas);
        $cw = imagesx($cropped);
        $ch = imagesy($cropped);
        $pad = (int) round(max($cw, $ch) * 0.08);
        $side = max($cw, $ch) + 2 * $pad;
        $square = self::blankCanvas($side, $side);
        imagealphablending($square, true);
        imagecopy($square, $cropped, (int) (($side - $cw) / 2), (int) (($side - $ch) / 2), 0, 0, $cw, $ch);
        imagealphablending($square, false);
        imagedestroy($cropped);

        $thumb = self::blankCanvas($size, $size);
        imagealphablending($thumb, true);
        imagecopyresampled($thumb, $square, 0, 0, 0, 0, $size, $size, $side, $side);
        imagealphablending($thumb, false);
        imagedestroy($square);

        return $thumb;
    }

    /** @return resource|\GdImage */
    private static function loadImage(string $path)
    {
        $info = @getimagesize($path);
        $type = $info[2] ?? null;

        $img = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => imagecreatefrompng($path),
        };

        if (! $img) {
            throw new RuntimeException('Falha ao abrir imagem de avatar: ' . basename($path));
        }

        imagealphablending($img, false);
        imagesavealpha($img, true);

        return $img;
    }

    /** @param  resource|\GdImage  $src
     *  @return resource|\GdImage */
    private static function removeBlackBg($src, int $threshold = 32)
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

    /** @param  resource|\GdImage  $img */
    private static function opaqueBounds($img): ?array
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

    /** @param  resource|\GdImage  $img
     *  @return resource|\GdImage */
    private static function cropOpaque($img)
    {
        $b = self::opaqueBounds($img);
        if (! $b) {
            $out = self::blankCanvas(1, 1);

            return $out;
        }
        $cw = $b['maxX'] - $b['minX'] + 1;
        $ch = $b['maxY'] - $b['minY'] + 1;
        $out = imagecreatetruecolor($cw, $ch);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagecopy($out, $img, 0, 0, $b['minX'], $b['minY'], $cw, $ch);

        return $out;
    }

    /** @return resource|\GdImage */
    private static function blankCanvas(int $w, int $h)
    {
        $c = imagecreatetruecolor($w, $h);
        imagealphablending($c, false);
        imagesavealpha($c, true);
        imagefill($c, 0, 0, imagecolorallocatealpha($c, 0, 0, 0, 127));

        return $c;
    }

    /** @param  resource|\GdImage  $img */
    private static function savePng($img, string $path): void
    {
        $ok = @imagepng($img, $path, 6);
        if (! $ok || ! is_file($path)) {
            throw new RuntimeException(
                'Falha ao gravar PNG em '.$path.
                '. Verifique permissões: sudo chown -R www-data:www-data '.dirname($path).
                ' && sudo chmod -R ug+rwX '.dirname($path)
            );
        }
        @chmod($path, 0664);
    }
}
