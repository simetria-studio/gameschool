<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ZipArchive;

class AvatarImagemStorage
{
    private const PREFIX = '/imgs/avatar/';

    public const TAMANHO_MAXIMO_KB = 20480;

    public static function tamanhoMaximoRotulo(): string
    {
        return '20 MB';
    }

    public static function uploadAsset(UploadedFile $file, ?string $caminhoAntigo = null): string
    {
        self::delete($caminhoAntigo);

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $nome = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $nome = ($nome !== '' ? $nome : 'asset') . '-' . time() . '.' . $ext;

        $destinoDir = public_path('imgs/avatar');
        if (! is_dir($destinoDir)) {
            mkdir($destinoDir, 0755, true);
        }

        // ZIP Spine: extrai para subpasta e retorna path do .json/.skel se existir
        if (in_array($ext, ['zip'], true)) {
            return self::extrairZipSpine($file, $nome);
        }

        $file->move($destinoDir, $nome);

        return self::PREFIX . $nome;
    }

    public static function uploadThumbnail(UploadedFile $file, ?string $caminhoAntigo = null): string
    {
        self::delete($caminhoAntigo);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'], true)) {
            $ext = 'png';
        }

        $nome = 'thumb-' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $nome = ($nome !== 'thumb-' ? $nome : 'thumb-peca') . '-' . time() . '.' . $ext;

        $destinoDir = public_path('imgs/avatar');
        if (! is_dir($destinoDir)) {
            mkdir($destinoDir, 0755, true);
        }

        $file->move($destinoDir, $nome);

        return self::PREFIX . $nome;
    }

    public static function delete(?string $caminho): void
    {
        if (! $caminho || str_starts_with($caminho, 'http://') || str_starts_with($caminho, 'https://')) {
            return;
        }

        // Não apagar seeds estáticos em /imgs/avatar/starter/
        if (str_contains($caminho, '/imgs/avatar/starter/')) {
            return;
        }

        $full = public_path(ltrim($caminho, '/'));
        if (is_file($full)) {
            @unlink($full);
        }

        // Se for pasta Spine (termina sem extensão de imagem), tentar limpar dir
        if (is_dir($full)) {
            self::removerDiretorio($full);
        }
    }

    private static function extrairZipSpine(UploadedFile $file, string $nomeZip): string
    {
        $pastaNome = pathinfo($nomeZip, PATHINFO_FILENAME);
        $destinoDir = public_path('imgs/avatar/' . $pastaNome);
        if (! is_dir($destinoDir)) {
            mkdir($destinoDir, 0755, true);
        }

        $tmp = $file->getRealPath();
        $zip = new ZipArchive;
        if ($zip->open($tmp) !== true) {
            throw new \RuntimeException('Não foi possível abrir o ZIP do asset Spine.');
        }
        $zip->extractTo($destinoDir);
        $zip->close();

        return self::PREFIX . $pastaNome . '/';
    }

    private static function removerDiretorio(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $itens = scandir($dir) ?: [];
        foreach ($itens as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::removerDiretorio($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
