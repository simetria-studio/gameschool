<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class RoletaImagemStorage
{
    private const PREFIX = '/imgs/roleta/';

    /** Limite de upload em kilobytes (10 MB). */
    public const TAMANHO_MAXIMO_KB = 10240;

    /** Tons da versão bloqueada (preserva luminosidade da arte original). */
    private const BLOQUEADA_ESCURO = [58, 72, 88];

    private const BLOQUEADA_CLARO = [118, 132, 152];

    public static function tamanhoMaximoRotulo(): string
    {
        return '10 MB';
    }

    /**
     * @return array{imagem: string, imagem_bloqueada: ?string}
     */
    public static function uploadColecionavel(
        UploadedFile $file,
        string $tipo,
        ?string $caminhoAntigo = null,
        ?string $caminhoBloqueadaAntigo = null
    ): array {
        self::delete($caminhoAntigo);
        self::delete($caminhoBloqueadaAntigo);

        $imagem = self::salvarArquivo($file);
        $imagemBloqueada = $tipo === 'figurinha' ? self::gerarSilhueta($imagem) : null;

        return [
            'imagem' => $imagem,
            'imagem_bloqueada' => $imagemBloqueada,
        ];
    }

    public static function upload(UploadedFile $file, ?string $caminhoAntigo = null): string
    {
        self::delete($caminhoAntigo);

        return self::salvarArquivo($file);
    }

    public static function gerarSilhueta(string $caminhoImagem): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $origem = public_path(ltrim($caminhoImagem, '/'));

        if (! is_file($origem)) {
            return null;
        }

        $imagem = self::carregarImagem($origem);

        if (! $imagem) {
            return null;
        }

        $largura = imagesx($imagem);
        $altura = imagesy($imagem);
        $tipo = @exif_imagetype($origem);
        $temAlpha = in_array($tipo, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP], true);

        imagealphablending($imagem, false);
        imagesavealpha($imagem, true);

        $destino = imagecreatetruecolor($largura, $altura);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);

        $transparente = imagecolorallocatealpha($destino, 0, 0, 0, 127);
        imagefill($destino, 0, 0, $transparente);

        [$er, $eg, $eb] = self::BLOQUEADA_ESCURO;
        [$cr, $cg, $cb] = self::BLOQUEADA_CLARO;

        for ($y = 0; $y < $altura; $y++) {
            for ($x = 0; $x < $largura; $x++) {
                $rgba = imagecolorat($imagem, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                if ($temAlpha && $alpha >= 100) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if (! $temAlpha && self::ehFundoClaro($r, $g, $b)) {
                    continue;
                }

                $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
                $luminancia = max(0.0, min(1.0, pow($luminancia, 0.82)));

                $nr = (int) ($er + ($cr - $er) * $luminancia);
                $ng = (int) ($eg + ($cg - $eg) * $luminancia);
                $nb = (int) ($eb + ($cb - $eb) * $luminancia);

                $alphaSaida = $temAlpha
                    ? max(0, min(127, (int) round($alpha * 0.95)))
                    : 0;

                $cor = imagecolorallocatealpha($destino, $nr, $ng, $nb, $alphaSaida);
                imagesetpixel($destino, $x, $y, $cor);
            }
        }

        imagedestroy($imagem);

        $nomeSilhueta = pathinfo($origem, PATHINFO_FILENAME) . '-bloqueada.png';
        $pathCompleto = public_path('imgs/roleta/' . $nomeSilhueta);

        imagepng($destino, $pathCompleto, 9);
        imagedestroy($destino);

        return self::PREFIX . $nomeSilhueta;
    }

    public static function delete(?string $caminho): void
    {
        if (! $caminho || ! str_starts_with($caminho, self::PREFIX)) {
            return;
        }

        $arquivo = public_path(ltrim($caminho, '/'));

        if (is_file($arquivo)) {
            @unlink($arquivo);
        }
    }

    public static function urlPublica(?string $caminho): ?string
    {
        if (! $caminho) {
            return null;
        }

        if (str_starts_with($caminho, 'http://') || str_starts_with($caminho, 'https://')) {
            return $caminho;
        }

        return url($caminho);
    }

    private static function salvarArquivo(UploadedFile $file): string
    {
        $dir = public_path('imgs/roleta');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'item';
        $base = Str::limit($base, 40, '');
        $nome = $base . '-' . time() . '.' . strtolower($file->getClientOriginalExtension());

        $file->move($dir, $nome);

        return self::PREFIX . $nome;
    }

    /**
     * @return \GdImage|resource|false|null
     */
    private static function carregarImagem(string $caminho)
    {
        $tipo = @exif_imagetype($caminho);

        return match ($tipo) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($caminho),
            IMAGETYPE_PNG => @imagecreatefrompng($caminho),
            IMAGETYPE_GIF => @imagecreatefromgif($caminho),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($caminho) : false,
            default => null,
        };
    }

    private static function ehFundoClaro(int $r, int $g, int $b): bool
    {
        return $r >= 248 && $g >= 248 && $b >= 248;
    }
}
