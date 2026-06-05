<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class RoletaImagemStorage
{
    private const PREFIX = '/imgs/roleta/';

    public static function upload(UploadedFile $file, ?string $caminhoAntigo = null): string
    {
        self::delete($caminhoAntigo);

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
}
