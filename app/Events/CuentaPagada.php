<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se emite cuando una cuenta se cobra/cierra (por el cajero o admin, vía
 * cierre de cuenta o factura, o por pago QR del cliente). Sirve para que la
 * caja, el mesero y el admin se actualicen en vivo sin refrescar.
 *
 * Se transmite por el canal 'pedidos.meseros' (al que ya están suscritos
 * mesero, cajero y admin) con el alias '.cuenta.pagada'.
 */
class CuentaPagada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $pago;

    public function __construct(array $pago)
    {
        $this->pago = $pago;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('pedidos.meseros'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'cuenta.pagada';
    }

    public function broadcastWith(): array
    {
        return $this->pago;
    }
}
