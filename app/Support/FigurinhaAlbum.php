<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\AlunoItem;
use App\Models\RoletaItem;

class FigurinhaAlbum
{
    public static function montar(Aluno $aluno): array
    {
        $figurinhas = RoletaItem::query()
            ->where('unidade_id', $aluno->unidade_id)
            ->where('tipo', 'figurinha')
            ->where('status', 'ativo')
            ->orderBy('id')
            ->get();

        $possuidos = AlunoItem::query()
            ->where('aluno_id', $aluno->id)
            ->where('quantidade', '>', 0)
            ->whereIn('roleta_item_id', $figurinhas->pluck('id'))
            ->get()
            ->keyBy('roleta_item_id');

        $lista = $figurinhas->values()->map(function (RoletaItem $item, int $index) use ($possuidos) {
            return self::formatarFigurinha($item, $index + 1, $possuidos->get($item->id));
        });

        $total = $lista->count();
        $possuiCount = $lista->where('possui', true)->count();

        return [
            'aluno' => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
            ],
            'resumo' => [
                'total' => $total,
                'possui' => $possuiCount,
                'faltam' => max(0, $total - $possuiCount),
                'percentual' => $total > 0 ? round($possuiCount / $total * 100, 1) : 0.0,
            ],
            'figurinhas' => $lista->values()->all(),
        ];
    }

    public static function formatarFigurinha(RoletaItem $item, ?int $numero = null, ?AlunoItem $registro = null): array
    {
        $possui = $registro !== null && (int) $registro->quantidade > 0;
        $imagemUrl = RoletaImagemStorage::urlPublica($item->imagem);
        $imagemBloqueadaUrl = RoletaImagemStorage::urlPublica($item->imagem_bloqueada);

        return [
            'id' => $item->id,
            'numero' => $numero ?? $item->id,
            'titulo' => $item->titulo,
            'raridade' => $item->raridade,
            'raridade_label' => InventarioAluno::raridadeLabel($item->raridade),
            'possui' => $possui,
            'quantidade' => $possui ? (int) $registro->quantidade : 0,
            'imagem_url' => $imagemUrl,
            'imagem_bloqueada_url' => $imagemBloqueadaUrl,
            'imagem_exibicao_url' => $possui ? $imagemUrl : ($imagemBloqueadaUrl ?? $imagemUrl),
        ];
    }
}
