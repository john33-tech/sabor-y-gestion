<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagoConfirmadoEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $emisor;
    public array $pago;

    public function __construct(string $emisor, array $pago)
    {
        $this->emisor = $emisor;
        $this->pago = $pago;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('emisor-' . $this->emisor),
        ];
    }

    public function broadcastAs(): string
    {
        return 'pago.confirmado';
    }

    public function broadcastWith(): array
    {
        return $this->pago;
    }
}
