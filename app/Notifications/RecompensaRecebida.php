<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class RecompensaRecebida extends Notification
{
    public function __construct(
        public int $coins,
        public int $xp,
        public string $origemTipo,
        public string $origemTitulo,
        public ?int $origemId = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'recompensa',
            'titulo' => 'Recompensa recebida',
            'mensagem' => sprintf(
                'Você recebeu %d coins e %d XP por: %s.',
                $this->coins,
                $this->xp,
                $this->origemTitulo
            ),
            'coins' => $this->coins,
            'xp' => $this->xp,
            'origem' => $this->origemTipo,
            'origem_id' => $this->origemId,
            'origem_titulo' => $this->origemTitulo,
        ];
    }
}
