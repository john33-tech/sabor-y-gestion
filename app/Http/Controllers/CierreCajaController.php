<?php

namespace App\Http\Controllers;

use App\Mail\FacturaPdfMail;
use App\Models\Factura;
use App\Models\Mesa;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Cierre de cuenta por mesa (punto #6 del spec).
 *
 * Listado de mesas con cuenta abierta — i.e., pedidos tipo "mesa" cuya
 * factura sigue en estado "pendiente". Permite ver la comanda consolidada
 * con todo lo consumido y cerrar la cuenta cobrando con un método de pago.
 *
 * Una mesa puede tener varios pedidos durante la visita; el cierre los
 * factura todos a la vez y libera la mesa.
 */
class CierreCajaController extends Controller
{
    public function index()
    {
        $pedidosAbiertos = Pedido::with(['mesa', 'factura', 'usuario'])
            ->where('tipo_pedido', Pedido::TIPO_MESA)
            ->whereNotNull('mesa_id')
            ->whereNotIn('estado', [Pedido::ESTADO_CANCELADO, Pedido::ESTADO_FACTURADO])
            // Mostrar mesas con cuenta abierta: factura pendiente (por cobrar) O
            // ya pagada (p. ej. por QR) pero sin cerrar — así el cajero puede
            // confirmar el cierre y liberar la mesa aunque ya esté pagada.
            ->whereHas('factura', function ($q) {
                $q->whereIn('estado', [Factura::ESTADO_PENDIENTE, Factura::ESTADO_PAGADA]);
            })
            ->orderBy('created_at')
            ->get();

        $cuentas = $pedidosAbiertos->groupBy('mesa_id')->map(function ($pedidos) {
            return [
                'mesa' => $pedidos->first()->mesa,
                'pedidos' => $pedidos,
                'total' => $pedidos->sum('total'),
                'cantidad_pedidos' => $pedidos->count(),
                'abierta_desde' => $pedidos->min('created_at'),
                // ¿Hay algo por cobrar, o ya está todo pagado y solo falta cerrar?
                'tiene_pendiente' => $pedidos->contains(
                    fn($p) => optional($p->factura)->estado === Factura::ESTADO_PENDIENTE
                ),
            ];
        })->values();

        return view('cierres.index', compact('cuentas'));
    }

    public function show(Mesa $cierre)
    {
        $pedidos = Pedido::with(['detalles.plato.categoria', 'factura', 'usuario'])
            ->where('mesa_id', $cierre->id)
            ->where('tipo_pedido', Pedido::TIPO_MESA)
            ->whereNotIn('estado', [Pedido::ESTADO_CANCELADO, Pedido::ESTADO_FACTURADO])
            ->whereHas('factura', function ($q) {
                $q->whereIn('estado', [Factura::ESTADO_PENDIENTE, Factura::ESTADO_PAGADA]);
            })
            ->orderBy('created_at')
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->route('cierres.index')
                ->with('info', 'La mesa ' . $cierre->numero_mesa . ' no tiene cuenta abierta.');
        }

        $resumen = [
            'subtotal' => $pedidos->sum('subtotal'),
            'impuesto' => $pedidos->sum('impuesto'),
            'descuento' => $pedidos->sum('descuento'),
            'total' => $pedidos->sum('total'),
            'items' => $pedidos->sum(fn($p) => $p->detalles->sum('cantidad')),
        ];

        // La primera factura pendiente se usa como referencia para generar el
        // QR cuando el cliente paga con QR desde el cierre de cuenta.
        $facturaParaQr = $pedidos->pluck('factura')->filter()->first();

        return view('cierres.show', [
            'mesa' => $cierre,
            'pedidos' => $pedidos,
            'resumen' => $resumen,
            'facturaParaQr' => $facturaParaQr,
        ]);
    }

    public function cerrar(Request $request, Mesa $cierre)
    {
        $request->validate([
            'metodo_pago' => 'required|in:efectivo,tarjeta,qr,transferencia',
        ]);

        // No filtramos por factura pendiente: si el cliente ya pagó por QR
        // antes de que el staff cierre, la factura está PAGADA y debemos
        // poder seguir cerrando la cuenta (marcar pedidos como facturados + liberar mesa).
        $pedidos = Pedido::with('factura')
            ->where('mesa_id', $cierre->id)
            ->where('tipo_pedido', Pedido::TIPO_MESA)
            ->whereNotIn('estado', [Pedido::ESTADO_CANCELADO, Pedido::ESTADO_FACTURADO])
            ->get();

        if ($pedidos->isEmpty()) {
            return redirect()->route('cierres.index')
                ->with('error', 'La mesa no tiene cuenta abierta.');
        }

        DB::beginTransaction();

        // Facturas que VAMOS a pagar en este cierre (no las que ya estaban
        // pagadas por un webhook previo). Solo a estas les enviamos correo
        // después; las ya pagadas ya recibieron correo cuando el webhook
        // disparó el pago.
        $facturasParaNotificar = [];

        try {
            foreach ($pedidos as $pedido) {
                if ($pedido->factura) {
                    if ($pedido->factura->estado === Factura::ESTADO_PENDIENTE) {
                        $pedido->factura->metodo_pago = $request->metodo_pago;
                        $pedido->factura->estado = Factura::ESTADO_PAGADA;
                        $pedido->factura->save();
                        $facturasParaNotificar[] = $pedido->factura;
                    }
                }

                $pedido->estado = Pedido::ESTADO_FACTURADO;
                $pedido->save();
            }

            $cierre->estado = 'libre';
            $cierre->save();

            DB::commit();

            // Después del commit: enviar la factura por correo solo a las
            // facturas que pagamos en este cierre (no a las que ya estaban
            // pagadas por un pago QR previo, que ya enviaron su correo).
            $correosEnviados = 0;
            foreach ($facturasParaNotificar as $factura) {
                if ($this->enviarFacturaAlCliente($factura)) {
                    $correosEnviados++;
                }
            }

            $totalCobrado = $pedidos->sum('total');
            $facturasIds = $pedidos->pluck('factura.id')->filter()->values()->all();

            return redirect()->route('cierres.index')
                ->with('success', sprintf(
                    'Cuenta de la Mesa %s cerrada. Total cobrado: Bs. %s (%s pedido%s). %s correo%s enviado%s.',
                    $cierre->numero_mesa,
                    number_format($totalCobrado, 2),
                    $pedidos->count(),
                    $pedidos->count() === 1 ? '' : 's',
                    $correosEnviados,
                    $correosEnviados === 1 ? '' : 's',
                    $correosEnviados === 1 ? '' : 's'
                ))
                ->with('facturas_cerradas', $facturasIds);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cierres.show', $cierre)
                ->with('error', 'Error al cerrar la cuenta: ' . $e->getMessage());
        }
    }

    /**
     * Envía la factura por correo al usuario dueño del pedido. Devuelve true
     * si el correo se despachó OK, false si falló o no había email destino.
     */
    private function enviarFacturaAlCliente(Factura $factura): bool
    {
        $factura->loadMissing(['pedido.usuario', 'pedido.detalles.plato']);

        // Preferimos el email del cliente capturado en el pedido (lo que el
        // mesero anotó). Si no hay, caemos al email del usuario del sistema
        // que creó el pedido (típicamente el cliente cuando se autoatiende).
        $email = $factura->pedido?->cliente_email
            ?: optional($factura->pedido?->usuario)->email;

        if (!$email) {
            Log::info('Cierre de cuenta: factura sin email destino', [
                'factura_id' => $factura->id,
            ]);
            return false;
        }

        try {
            $pdfOutput = Pdf::loadView('facturas.pdf', compact('factura'))->output();
            Mail::to($email)->send(new FacturaPdfMail($factura, $pdfOutput));
            return true;
        } catch (\Throwable $e) {
            Log::error('Error enviando factura por correo desde cierre de cuenta', [
                'factura_id' => $factura->id,
                'email'      => $email,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }
}
