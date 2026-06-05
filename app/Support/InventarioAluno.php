<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\AlunoItem;
use App\Models\RoletaItem;
use Illuminate\Support\Collection;

class InventarioAluno
{
    public const TIPOS_ORDEM = ['personagem', 'figurinha', 'emote'];

    private const TIPOS_LABEL = [
        'personagem' => 'Personagens',
        'figurinha' => 'Figurinhas',
        'emote' => 'Emotes',
    ];

    private const RARIDADE_LABEL = [
        'comum' => 'Comum',
        'raro' => 'Raro',
        'epico' => 'Épico',
        'lendario' => 'Lendário',
    ];

    public static function adicionar(Aluno $aluno, RoletaItem $item, int $quantidade = 1): AlunoItem
    {
        $registro = AlunoItem::query()
            ->where('aluno_id', $aluno->id)
            ->where('roleta_item_id', $item->id)
            ->first();

        if ($registro) {
            $registro->increment('quantidade', $quantidade);

            return $registro->fresh(['item']);
        }

        return AlunoItem::create([
            'aluno_id' => $aluno->id,
            'roleta_item_id' => $item->id,
            'quantidade' => $quantidade,
        ])->load('item');
    }

    public static function remover(Aluno $aluno, RoletaItem $item, int $quantidade = 1): void
    {
        $registro = AlunoItem::query()
            ->where('aluno_id', $aluno->id)
            ->where('roleta_item_id', $item->id)
            ->lockForUpdate()
            ->first();

        if (! $registro || (int) $registro->quantidade < $quantidade) {
            throw new \InvalidArgumentException('Quantidade insuficiente no inventário.');
        }

        if ((int) $registro->quantidade === $quantidade) {
            $registro->delete();

            return;
        }

        $registro->decrement('quantidade', $quantidade);
    }

    public static function formatarRoletaItem(?RoletaItem $item): ?array
    {
        if (! $item) {
            return null;
        }

        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'label' => $item->label(),
            'tipo' => $item->tipo,
            'tipo_label' => self::tipoLabel($item->tipo),
            'emoji' => $item->emoji,
            'imagem' => $item->imagem,
            'imagem_url' => RoletaImagemStorage::urlPublica($item->imagem),
            'raridade' => $item->raridade,
            'raridade_label' => self::raridadeLabel($item->raridade),
        ];
    }

    public static function formatarItem(AlunoItem $registro, bool $detalhado = false): array
    {
        $payload = [
            'id' => $registro->id,
            'quantidade' => (int) $registro->quantidade,
            'pode_enviar' => (int) $registro->quantidade > 0,
            'item' => self::formatarRoletaItem($registro->item),
            'updated_at' => $registro->updated_at?->toIso8601String(),
        ];

        if ($detalhado) {
            $payload['created_at'] = $registro->created_at?->toIso8601String();
        }

        return $payload;
    }

    /**
     * @param  Collection<int, AlunoItem>  $registros
     */
    public static function montarResumo(Collection $registros): array
    {
        $porTipo = [];

        foreach (self::TIPOS_ORDEM as $tipo) {
            $porTipo[$tipo] = ['unicos' => 0, 'quantidade' => 0];
        }

        $totalQuantidade = 0;

        foreach ($registros as $registro) {
            $tipo = $registro->item?->tipo ?? 'emote';
            $qtd = (int) $registro->quantidade;
            $totalQuantidade += $qtd;

            if (! isset($porTipo[$tipo])) {
                $porTipo[$tipo] = ['unicos' => 0, 'quantidade' => 0];
            }

            $porTipo[$tipo]['unicos']++;
            $porTipo[$tipo]['quantidade'] += $qtd;
        }

        return [
            'total_quantidade' => $totalQuantidade,
            'total_unicos' => $registros->count(),
            'por_tipo' => $porTipo,
        ];
    }

    /**
     * @param  Collection<int, AlunoItem>  $registros
     */
    public static function montarCategorias(Collection $registros): array
    {
        $agrupados = $registros
            ->groupBy(fn (AlunoItem $r) => $r->item?->tipo ?? 'emote')
            ->sortBy(fn ($_, $tipo) => array_search($tipo, self::TIPOS_ORDEM, true) ?? 99);

        return $agrupados->map(function (Collection $grupo, string $tipo) {
            $itens = $grupo
                ->sortByDesc(fn (AlunoItem $r) => $r->updated_at)
                ->values()
                ->map(fn (AlunoItem $r) => self::formatarItem($r));

            return [
                'tipo' => $tipo,
                'titulo' => self::tipoLabel($tipo, plural: true),
                'total' => (int) $itens->sum('quantidade'),
                'unicos' => $itens->count(),
                'itens' => $itens->values()->all(),
            ];
        })->values()->all();
    }

    public static function tipoLabel(?string $tipo, bool $plural = false): string
    {
        if ($plural && $tipo && isset(self::TIPOS_LABEL[$tipo])) {
            return self::TIPOS_LABEL[$tipo];
        }

        return match ($tipo) {
            'personagem' => 'Personagem',
            'figurinha' => 'Figurinha',
            'emote' => 'Emote',
            default => ucfirst((string) $tipo),
        };
    }

    public static function raridadeLabel(?string $raridade): string
    {
        return self::RARIDADE_LABEL[$raridade ?? ''] ?? ucfirst((string) $raridade);
    }
}
