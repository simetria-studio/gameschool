<?php

namespace App\Support;

use App\Models\Aluno;
use App\Models\Roleta;
use App\Models\RoletaBauItem;
use App\Models\RoletaGiro;
use App\Models\RoletaSegmento;
use App\Notifications\RoletaPremioGanho;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoletaGiroProcessor
{
    public static function girar(Roleta $roleta, Aluno $aluno, string $tipo): RoletaGiro
    {
        $tipo = $tipo === 'pago' ? 'pago' : 'gratis';

        $roleta->load(['segmentos.item', 'segmentos.bauItens.item']);

        if (! $roleta->isAtiva()) {
            throw ValidationException::withMessages([
                'roleta' => ['Esta roleta não está disponível.'],
            ]);
        }

        $segmentos = $roleta->segmentos->filter(fn (RoletaSegmento $s) => (int) $s->peso > 0);

        if ($segmentos->isEmpty()) {
            throw ValidationException::withMessages([
                'roleta' => ['A roleta ainda não possui prêmios configurados.'],
            ]);
        }

        return DB::transaction(function () use ($roleta, $aluno, $tipo, $segmentos) {
            $aluno = Aluno::query()->lockForUpdate()->findOrFail($aluno->id);
            $custo = 0;

            if ($tipo === 'gratis') {
                self::assertGiroGratisDisponivel($roleta, $aluno);
            } else {
                $custo = (int) $roleta->custo_coins;
                if ($custo <= 0) {
                    throw ValidationException::withMessages([
                        'tipo' => ['Giro pago não configurado para esta roleta.'],
                    ]);
                }
                if ((int) $aluno->coins < $custo) {
                    throw ValidationException::withMessages([
                        'tipo' => ['Coins insuficientes para girar a roleta.'],
                    ]);
                }
                $aluno->decrement('coins', $custo);
            }

            /** @var RoletaSegmento $segmento */
            $segmento = SorteioPonderado::sortear($segmentos);
            $resultado = self::aplicarSegmento($segmento, $aluno);

            $aluno->increment('coins', $resultado['coins_ganho']);
            $aluno->increment('xp', $resultado['xp_ganho']);

            $giro = RoletaGiro::create([
                'roleta_id' => $roleta->id,
                'aluno_id' => $aluno->id,
                'segmento_id' => $segmento->id,
                'tipo' => $tipo,
                'custo_coins' => $custo,
                'coins_ganho' => $resultado['coins_ganho'],
                'xp_ganho' => $resultado['xp_ganho'],
                'premios_json' => $resultado['premios'],
            ]);

            $aluno->notify(new RoletaPremioGanho(
                roletaTitulo: $roleta->titulo,
                roletaId: $roleta->id,
                segmentoTitulo: $segmento->titulo,
                coins: $resultado['coins_ganho'],
                xp: $resultado['xp_ganho'],
                itens: $resultado['premios'],
            ));

            return $giro->load(['segmento', 'roleta']);
        });
    }

    public static function statusGiroGratis(Roleta $roleta, Aluno $aluno): array
    {
        $inicioSemana = now()->startOfWeek();
        $usou = RoletaGiro::query()
            ->where('roleta_id', $roleta->id)
            ->where('aluno_id', $aluno->id)
            ->where('tipo', 'gratis')
            ->where('created_at', '>=', $inicioSemana)
            ->exists();

        return [
            'disponivel' => ! $usou,
            'proximo_gratis_em' => $usou ? $inicioSemana->copy()->addWeek()->toIso8601String() : null,
        ];
    }

    private static function assertGiroGratisDisponivel(Roleta $roleta, Aluno $aluno): void
    {
        $status = self::statusGiroGratis($roleta, $aluno);

        if (! $status['disponivel']) {
            throw ValidationException::withMessages([
                'tipo' => ['Você já usou seu giro grátis desta semana.'],
            ]);
        }
    }

    /**
     * @return array{coins_ganho: int, xp_ganho: int, premios: array<int, array<string, mixed>>}
     */
    private static function aplicarSegmento(RoletaSegmento $segmento, Aluno $aluno): array
    {
        $premios = [];
        $coins = 0;
        $xp = 0;

        switch ($segmento->tipo) {
            case 'coins':
                $coins = (int) $segmento->coins;
                break;

            case 'xp':
                $xp = (int) $segmento->xp;
                break;

            case 'item':
                $item = $segmento->item;
                if ($item && $item->status === 'ativo') {
                    InventarioAluno::adicionar($aluno, $item);
                    $premios[] = self::formatPremioItem($item);
                }
                break;

            case 'bau':
                $premios = array_merge($premios, self::abrirBau($segmento, $aluno));
                break;
        }

        return [
            'coins_ganho' => $coins,
            'xp_ganho' => $xp,
            'premios' => $premios,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function abrirBau(RoletaSegmento $segmento, Aluno $aluno): array
    {
        $pool = $segmento->bauItens->filter(fn (RoletaBauItem $b) => $b->item && $b->item->status === 'ativo');

        if ($pool->isEmpty()) {
            return [];
        }

        $quantidade = random_int(2, min(4, max(2, $pool->count())));
        $premios = [];
        $sorteados = [];

        for ($i = 0; $i < $quantidade; $i++) {
            /** @var RoletaBauItem $bauItem */
            $bauItem = SorteioPonderado::sortear($pool);
            $item = $bauItem->item;

            if (! $item) {
                continue;
            }

            InventarioAluno::adicionar($aluno, $item);
            $premios[] = self::formatPremioItem($item);
            $sorteados[] = $item->id;
        }

        return $premios;
    }

    private static function formatPremioItem($item): array
    {
        return [
            'id' => $item->id,
            'titulo' => $item->titulo,
            'label' => $item->label(),
            'tipo' => $item->tipo,
            'emoji' => $item->emoji,
            'imagem' => $item->imagem,
            'raridade' => $item->raridade,
        ];
    }
}
