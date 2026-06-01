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
class CierrePedidoController extends Controller
{
    public function index()
    {
        // La mesa aparece en Caja en cuanto tiene cuenta abierta: factura
        // PENDIENTE (por cobrar) o ya PAGADA por QR (falta cerrar/liberar mesa).
        $pedidosAbiertos = Pedido::with(['mesa', 'factura', 'usuario'])
            ->where('tipo_pedido', Pedido::TIPO_MESA)
            ->whereNotNull('mesa_id')
            ->whereNotIn('estado', [Pedido::ESTADO_CANCELADO, Pedido::ESTADO_FACTURADO])
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
            'impuesto' => 0, // IVA desactivado
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
        // Defensa en profundidad: el cobro es SOLO de caja (admin/cajero).
        // La ruta ya lo restringe, pero validamos también en el servidor.
        if (!in_array(auth()->user()->role ?? '', ['admin', 'cajero'])) {
            abort(403, 'Solo caja (admin o cajero) puede cobrar una cuenta.');
        }

        $request->validate([
            'metodo_pago'    => 'required|in:efectivo,tarjeta,qr,transferencia',
            'cliente_nombre' => 'required|string|max:255',  // nombre y apellido del cliente
            'cliente_nit'    => 'required|string|max:20',    // CI/NIT obligatorio al cobrar
            'cliente_email'  => 'nullable|email',            // opcional: enviar factura
        ], [
            'cliente_nombre.required' => 'El nombre y apellido del cliente es obligatorio.',
            'cliente_nit.required'    => 'El CI/NIT del cliente es obligatorio para cobrar.',
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
                    // Guardar los datos del cliente que pidió el cajero.
                    $pedido->factura->cliente_nombre = $request->cliente_nombre;
                    $pedido->factura->cliente_nit = $request->cliente_nit;
                    if ($request->filled('cliente_email')) {
                        $pedido->factura->cliente_email = $request->cliente_email;
                    }

                    if ($pedido->factura->estado === Factura::ESTADO_PENDIENTE) {
                        $pedido->factura->metodo_pago = $request->metodo_pago;
                        $pedido->factura->estado = Factura::ESTADO_PAGADA;
                    }
                    $pedido->factura->save();

                    // Solo se envía correo si el cliente dio un email (opcional).
                    if ($request->filled('cliente_email')) {
                        $facturasParaNotificar[] = $pedido->factura;
                    }
                }

                $pedido->estado = Pedido::ESTADO_FACTURADO;
                // Fase 6: al cobrar, se limpia la marca de cuenta solicitada.
                $pedido->cuenta_solicitada = false;
                $pedido->save();
            }

            // Fase 6 (spec): la mesa retorna EXACTAMENTE al estado de la Fase 0 (libre).
            $cierre->estado = 'libre';
            $cierre->save();

            DB::commit();

            // Después del commit: enviar la factura por correo solo a las
            // facturas que pagamos en este cierre (no a las que ya estaban
            // pagadas por un pago QR previo, que ya enviaron su correo).
            $correosEnviados = 0;
            foreach ($facturasParaNotificar as $factura) {
                if ($this->enviarFacturaAlCliente($factura, $request->cliente_email)) {
                    $correosEnviados++;
                }
            }

            $totalCobrado = $pedidos->sum('total');
            $facturasIds = $pedidos->pluck('factura.id')->filter()->values()->all();

            // Avisar en vivo (mesero/cajero/admin) que la mesa se cobró y liberó.
            // La cuenta ya se cerró (commit hecho); si Reverb falla, no rompemos.
            try {
                broadcast(new \App\Events\CuentaPagada([
                    'mesa'    => $cierre->numero_mesa,
                    'total'   => number_format($totalCobrado, 2),
                    'metodo'  => $request->metodo_pago,
                    'pedidos' => $pedidos->count(),
                    'origen'  => 'cierre',
                ]));
            } catch (\Throwable $e) {
                Log::warning('No se pudo emitir CuentaPagada (cierre): ' . $e->getMessage());
            }

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
    private function enviarFacturaAlCliente(Factura $factura, ?string $emailOverride = null): bool
    {
        $factura->loadMissing(['pedido.usuario', 'pedido.detalles.plato']);

        // Prioridad: el correo que el cajero ingresó al cobrar. Si no, el del
        // pedido (lo que anotó el mesero) y por último el del usuario del sistema.
        $email = $emailOverride
            ?: ($factura->pedido?->cliente_email
            ?: optional($factura->pedido?->usuario)->email);

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
