<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\AlunoAvatar;
use App\Models\AlunoAvatarPeca;
use App\Models\AvatarPeca;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AvatarAluno
{
    /** Slots que o aluno pode trocar no editor (corpo já vem vestido na base). */
    public const SLOTS_EQUIPAVEIS = [
        'base',
        'sombra',
        'calcado',
        'rosto',
        'cabelo',
        'acessorio_cabeca',
        'acessorio_rosto',
        'acessorio_outro',
    ];

    /** Ordem de render (atrás → frente), inclui slots legados vazios. */
    public static function slotsRender(): array
    {
        return AvatarPeca::SLOTS;
    }

    /**
     * Concede peças starter compatíveis com o gênero (e unissex).
     */
    public static function concederStarters(Aluno $aluno, string $genero): void
    {
        $pecas = AvatarPeca::query()
            ->where('status', 'ativo')
            ->where('is_starter', true)
            ->where(function ($q) use ($genero) {
                $q->where('genero', $genero)->orWhere('genero', 'unissex');
            })
            ->when($aluno->unidade_id, function ($q) use ($aluno) {
                $q->where(function ($inner) use ($aluno) {
                    $inner->whereNull('unidade_id')
                        ->orWhere('unidade_id', $aluno->unidade_id);
                });
            }, fn ($q) => $q->whereNull('unidade_id'))
            ->get();

        $now = now();
        foreach ($pecas as $peca) {
            AlunoAvatarPeca::query()->firstOrCreate(
                [
                    'aluno_id' => $aluno->id,
                    'avatar_peca_id' => $peca->id,
                ],
                ['desbloqueado_em' => $now]
            );
        }
    }

    /**
     * Garante registro de avatar + starters. Se ainda não escolheu gênero, retorna null.
     */
    public static function obterOuCriar(Aluno $aluno, ?string $genero = null): ?AlunoAvatar
    {
        $avatar = $aluno->avatar;

        if ($avatar) {
            return $avatar;
        }

        if (! $genero || ! in_array($genero, ['masculino', 'feminino'], true)) {
            return null;
        }

        return DB::transaction(function () use ($aluno, $genero) {
            self::concederStarters($aluno, $genero);

            $config = self::montarConfigPadrao($aluno, $genero);

            return AlunoAvatar::query()->create([
                'aluno_id' => $aluno->id,
                'genero' => $genero,
                'configuracao_json' => $config,
                'thumbnail_url' => self::thumbnailDaConfig($config),
            ]);
        });
    }

    /**
     * @return array<string, int|null>
     */
    public static function montarConfigPadrao(Aluno $aluno, string $genero): array
    {
        $possuidas = AvatarPeca::query()
            ->where('status', 'ativo')
            ->where(function ($q) use ($genero) {
                $q->where('genero', $genero)->orWhere('genero', 'unissex');
            })
            ->where(function ($q) use ($aluno) {
                $q->where('is_starter', true)
                    ->orWhereIn('id', function ($sub) use ($aluno) {
                        $sub->select('avatar_peca_id')
                            ->from('aluno_avatar_pecas')
                            ->where('aluno_id', $aluno->id);
                    });
            })
            ->orderBy('id')
            ->get()
            ->groupBy('slot');

        $config = [];
        foreach (AvatarPeca::SLOTS_ATIVOS as $slot) {
            $peca = ($possuidas[$slot] ?? collect())->first();
            $config[$slot] = $peca?->id;
        }
        $config['roupa_superior'] = null;
        $config['roupa_inferior'] = null;

        return $config;
    }

    /**
     * @param  array{genero?: string, slots?: array<string, int|null>}  $payload
     */
    public static function salvar(Aluno $aluno, array $payload): AlunoAvatar
    {
        $genero = $payload['genero'] ?? null;
        if (! in_array($genero, ['masculino', 'feminino'], true)) {
            throw ValidationException::withMessages([
                'genero' => ['Informe o gênero do personagem (masculino ou feminino).'],
            ]);
        }

        $avatar = $aluno->avatar;
        $trocouGenero = $avatar && $avatar->genero !== $genero;

        if (! $avatar) {
            $avatar = self::obterOuCriar($aluno, $genero);
        } elseif ($trocouGenero) {
            self::concederStarters($aluno, $genero);
            $avatar->genero = $genero;
            $avatar->configuracao_json = self::montarConfigPadrao($aluno, $genero);
            $avatar->thumbnail_url = self::thumbnailDaConfig($avatar->configuracao_json);
            $avatar->save();
        }

        $slotsInput = $payload['slots'] ?? [];
        if (! is_array($slotsInput)) {
            $slotsInput = [];
        }

        $config = $avatar->configuracao_json ?? [];

        // Garante que existe ao menos uma base do gênero (corpo vestido)
        $basePadrao = AvatarPeca::query()
            ->where('slot', 'base')
            ->where('status', 'ativo')
            ->where(function ($q) use ($genero) {
                $q->where('genero', $genero)->orWhere('genero', 'unissex');
            })
            ->orderByRaw("CASE WHEN genero = ? THEN 0 ELSE 1 END", [$genero])
            ->orderByDesc('is_starter')
            ->orderBy('id')
            ->first();

        if (! $basePadrao) {
            throw ValidationException::withMessages([
                'genero' => ['Não há corpo/personagem cadastrado para este gênero.'],
            ]);
        }

        foreach (self::SLOTS_EQUIPAVEIS as $slot) {
            if (! array_key_exists($slot, $slotsInput)) {
                continue;
            }

            $pecaId = $slotsInput[$slot];
            if ($pecaId === null || $pecaId === '' || $pecaId === 0) {
                // Base não pode ficar vazia
                if ($slot === 'base') {
                    continue;
                }
                $config[$slot] = null;
                continue;
            }

            $pecaId = (int) $pecaId;
            $peca = AvatarPeca::query()->find($pecaId);

            if (! $peca || $peca->status !== 'ativo' || $peca->slot !== $slot) {
                throw ValidationException::withMessages([
                    "slots.$slot" => ['Peça inválida para este slot.'],
                ]);
            }

            if (! $peca->compativelComGenero($genero)) {
                throw ValidationException::withMessages([
                    "slots.$slot" => ['Peça incompatível com o gênero escolhido.'],
                ]);
            }

            if (! self::alunoPossui($aluno, $peca)) {
                throw ValidationException::withMessages([
                    "slots.$slot" => ['Você ainda não desbloqueou esta peça.'],
                ]);
            }

            $config[$slot] = $peca->id;
        }

        if (empty($config['base'])) {
            $config['base'] = $basePadrao->id;
            self::alunoPossui($aluno, $basePadrao);
        }

        // Limpa camadas de roupa legadas (corpo já vem vestido na base)
        $config['roupa_superior'] = null;
        $config['roupa_inferior'] = null;

        $avatar->genero = $genero;
        $avatar->configuracao_json = $config;
        $avatar->thumbnail_url = self::thumbnailDaConfig($config);
        $avatar->save();

        return $avatar->fresh();
    }

    /**
     * Preenche slots faltantes com a primeira peça starter compatível.
     */
    public static function completarConfig(Aluno $aluno, AlunoAvatar $avatar): AlunoAvatar
    {
        $genero = $avatar->genero;
        $config = $avatar->configuracao_json ?? [];
        $changed = false;

        foreach (AvatarPeca::SLOTS_ATIVOS as $slot) {
            if (! empty($config[$slot])) {
                continue;
            }

            $peca = AvatarPeca::query()
                ->where('status', 'ativo')
                ->where('slot', $slot)
                ->where('is_starter', true)
                ->where(function ($q) use ($genero) {
                    $q->where('genero', $genero)->orWhere('genero', 'unissex');
                })
                ->orderBy('id')
                ->first();

            if ($peca) {
                $config[$slot] = $peca->id;
                self::alunoPossui($aluno, $peca);
                $changed = true;
            }
        }

        if (($config['roupa_superior'] ?? null) !== null || ($config['roupa_inferior'] ?? null) !== null) {
            $config['roupa_superior'] = null;
            $config['roupa_inferior'] = null;
            $changed = true;
        }

        if ($changed) {
            $avatar->configuracao_json = $config;
            $avatar->thumbnail_url = self::thumbnailDaConfig($config);
            $avatar->save();
        }

        return $avatar;
    }

    public static function alunoPossui(Aluno $aluno, AvatarPeca $peca): bool
    {
        $tem = AlunoAvatarPeca::query()
            ->where('aluno_id', $aluno->id)
            ->where('avatar_peca_id', $peca->id)
            ->exists();

        if ($tem) {
            return true;
        }

        if ($peca->is_starter) {
            AlunoAvatarPeca::query()->firstOrCreate(
                ['aluno_id' => $aluno->id, 'avatar_peca_id' => $peca->id],
                ['desbloqueado_em' => now()]
            );

            return true;
        }

        return false;
    }

    /**
     * @param  array<string, int|null>  $config
     */
    public static function thumbnailDaConfig(array $config): ?string
    {
        $baseId = $config['base'] ?? null;
        if (! $baseId) {
            return null;
        }

        $base = AvatarPeca::query()->find($baseId);

        return $base?->thumbnail_url ?: $base?->asset_url;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatarAvatar(?AlunoAvatar $avatar): ?array
    {
        if (! $avatar) {
            return null;
        }

        $config = $avatar->configuracao_json ?? [];
        $ids = array_values(array_filter($config));
        $pecas = AvatarPeca::query()->whereIn('id', $ids)->get()->keyBy('id');

        $slots = [];
        foreach (self::slotsRender() as $slot) {
            $id = $config[$slot] ?? null;
            $peca = $id ? ($pecas[$id] ?? null) : null;
            $slots[$slot] = $peca ? self::formatarPeca($peca, true) : null;
        }

        return [
            'genero' => $avatar->genero,
            'thumbnail_url' => $avatar->thumbnail_url
                ? (str_starts_with($avatar->thumbnail_url, 'http')
                    ? $avatar->thumbnail_url
                    : asset(ltrim($avatar->thumbnail_url, '/')))
                : null,
            'configuracao' => $config,
            'slots' => $slots,
            'updated_at' => optional($avatar->updated_at)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatarPeca(AvatarPeca $peca, bool $possui = false): array
    {
        return [
            'id' => $peca->id,
            'titulo' => $peca->titulo,
            'slot' => $peca->slot,
            'genero' => $peca->genero,
            'asset_url' => $peca->assetPublicUrl(),
            'thumbnail_url' => $peca->thumbnailPublicUrl(),
            'tipo_asset' => $peca->tipo_asset,
            'raridade' => $peca->raridade,
            'is_starter' => (bool) $peca->is_starter,
            'meta' => $peca->meta_json,
            'possui' => $possui,
        ];
    }

    /**
     * @return array{genero: ?string, thumbnail_url: ?string}
     */
    public static function resumoParaAuth(?Aluno $aluno): ?array
    {
        if (! $aluno) {
            return null;
        }

        $avatar = $aluno->avatar;
        if (! $avatar) {
            return [
                'genero' => null,
                'thumbnail_url' => null,
            ];
        }

        $thumb = $avatar->thumbnail_url;
        if ($thumb && ! str_starts_with($thumb, 'http')) {
            $thumb = asset(ltrim($thumb, '/'));
        }

        return [
            'genero' => $avatar->genero,
            'thumbnail_url' => $thumb,
        ];
    }
}
