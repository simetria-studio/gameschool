<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class RoletaPremioGanho extends Notification
{
    /**
     * @param  array<int, array<string, mixed>>  $itens
     */
    public function __construct(
        public string $roletaTitulo,
        public int $roletaId,
        public string $segmentoTitulo,
        public int $coins,
        public int $xp,
        public array $itens = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $partes = [];

        if ($this->coins > 0) {
            $partes[] = $this->coins . ' coins';
        }
        if ($this->xp > 0) {
            $partes[] = $this->xp . ' XP';
        }
        if ($this->itens !== []) {
            $partes[] = count($this->itens) . ' item(ns)';
        }

        return [
            'event' => 'roleta',
            'titulo' => 'Prêmio na roleta!',
            'mensagem' => sprintf(
                'Você ganhou %s na roleta "%s" (%s).',
                $partes !== [] ? implode(', ', $partes) : $this->segmentoTitulo,
                $this->roletaTitulo,
                $this->segmentoTitulo
            ),
            'coins' => $this->coins,
            'xp' => $this->xp,
            'origem' => 'roleta',
            'origem_id' => $this->roletaId,
            'origem_titulo' => $this->roletaTitulo,
            'segmento_titulo' => $this->segmentoTitulo,
            'itens' => $this->itens,
        ];
    }
}
