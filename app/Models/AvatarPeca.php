<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AvatarPeca extends Model
{
    protected $table = 'avatar_pecas';

    /**
     * Ordem de empilhamento (atrás → frente).
     * Roupa superior/inferior permanecem só por compatibilidade com dados antigos —
     * o fluxo atual usa corpo já vestido no slot `base`.
     */
    public const SLOTS = [
        'base',
        'sombra',
        'calcado',
        'roupa_inferior',
        'roupa_superior',
        'rosto',
        'cabelo',
        'acessorio_cabeca',
        'acessorio_rosto',
        'acessorio_outro',
    ];

    /** Slots usados no cadastro e no editor (sem roupa em camadas). */
    public const SLOTS_ATIVOS = [
        'base',
        'sombra',
        'calcado',
        'rosto',
        'cabelo',
        'acessorio_cabeca',
        'acessorio_rosto',
        'acessorio_outro',
    ];

    public const SLOT_LABELS = [
        'base' => 'Corpo / personagem',
        'sombra' => 'Sombra / efeitos',
        'calcado' => 'Calçados',
        'roupa_inferior' => 'Roupa (inferior)',
        'roupa_superior' => 'Roupa (superior)',
        'rosto' => 'Rosto / expressão',
        'cabelo' => 'Cabelos',
        'acessorio_cabeca' => 'Acessório (cabeça)',
        'acessorio_rosto' => 'Acessório (rosto)',
        'acessorio_outro' => 'Acessório (outros)',
    ];

    public const GENEROS = ['masculino', 'feminino', 'unissex'];

    public const RARIDADES = ['comum', 'raro', 'epico', 'lendario'];

    public const TIPOS_ASSET = ['png', 'spine'];

    protected $fillable = [
        'unidade_id',
        'titulo',
        'slot',
        'genero',
        'asset_url',
        'thumbnail_url',
        'tipo_asset',
        'raridade',
        'status',
        'is_starter',
        'meta_json',
    ];

    protected $casts = [
        'is_starter' => 'boolean',
        'meta_json' => 'array',
    ];

    public static function zIndexForSlot(string $slot): int
    {
        $index = array_search($slot, self::SLOTS, true);

        return $index === false ? 1 : $index + 1;
    }

    public static function labelForSlot(string $slot): string
    {
        return self::SLOT_LABELS[$slot] ?? ucfirst(str_replace('_', ' ', $slot));
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_avatar_pecas')
            ->withPivot('desbloqueado_em')
            ->withTimestamps();
    }

    public function compativelComGenero(string $genero): bool
    {
        if ($this->genero === 'unissex') {
            return true;
        }

        return $this->genero === $genero;
    }

    public function assetPublicUrl(): ?string
    {
        return $this->absoluteUrl($this->asset_url);
    }

    public function thumbnailPublicUrl(): ?string
    {
        return $this->absoluteUrl($this->thumbnail_url ?: $this->asset_url);
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
