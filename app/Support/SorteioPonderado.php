<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SorteioPonderado
{
    /**
     * @param  Collection<int, object{peso: int}>|array<int, object{peso: int}>  $itens
     */
    public static function sortear(Collection|array $itens): mixed
    {
        $lista = $itens instanceof Collection ? $itens->values() : collect($itens)->values();

        if ($lista->isEmpty()) {
            return null;
        }

        $total = (int) $lista->sum(fn ($i) => max(0, (int) ($i->peso ?? 0)));

        if ($total <= 0) {
            return $lista->random();
        }

        $rand = random_int(1, $total);
        $acc = 0;

        foreach ($lista as $item) {
            $acc += max(0, (int) ($item->peso ?? 0));
            if ($rand <= $acc) {
                return $item;
            }
        }

        return $lista->last();
    }
}
