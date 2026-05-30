<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Cierre de Caja (arqueo del turno).
 *
 * - index():  detalle del consumo del turno actual (facturas PAGADAS aún no
 *             arqueadas) + totales por método de pago + historial de cierres.
 * - cerrar(): hace el arqueo: registra el cierre con efectivo contado vs
 *             registrado y marca esas facturas como contabilizadas.
 * - show():   detalle de un arqueo pasado.
 *
 * El "turno" se define por las facturas pagadas todavía sin cierre_caja_id,
 * así un día sin cerrar no pierde sus ventas (se acumulan al siguiente cierre).
 */
class CajaController extends Controller
{
    public function index()
    {
        $facturas = $this->facturasDelTurno();

        return view('caja.index', [
            'facturas'  => $facturas,
            'totales'   => $this->calcularTotales($facturas),
            'historial' => CierreCaja::with('cajero')->latest('fecha_cierre')->paginate(15),
        ]);
    }

    public function cerrar(Request $request)
    {
        $request->validate([
            'efectivo_contado' => 'required|numeric|min:0',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        $facturas = $this->facturasDelTurno();

        if ($facturas->isEmpty()) {
            return redirect()->route('caja.index')
                ->with('info', 'No hay ventas pendientes de arqueo en este turno.');
        }

        $totales = $this->calcularTotales($facturas);
        $efectivoContado = (float) $request->efectivo_contado;

        DB::beginTransaction();
        try {
            $cierre = CierreCaja::create([
                'usuario_id'          => Auth::id(),
                'fecha_cierre'        => now(),
                'total_efectivo'      => $totales['efectivo'],
                'total_tarjeta'       => $totales['tarjeta'],
                'total_qr'            => $totales['qr'],
                'total_transferencia' => $totales['transferencia'],
                'total_general'       => $totales['general'],
                'cantidad_facturas'   => $facturas->count(),
                'efectivo_contado'    => $efectivoContado,
                'diferencia'          => round($efectivoContado - $totales['efectivo'], 2),
                'observaciones'       => $request->observaciones,
            ]);

            Factura::whereIn('id', $facturas->pluck('id'))
                ->update(['cierre_caja_id' => $cierre->id]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('caja.index')
                ->with('error', 'Error al cerrar la caja: ' . $e->getMessage());
        }

        return redirect()->route('caja.show', $cierre)
            ->with('success', sprintf(
                'Caja cerrada. Total registrado: Bs %s en %s factura%s. Diferencia de efectivo: Bs %s.',
                number_format($totales['general'], 2),
                $facturas->count(),
                $facturas->count() === 1 ? '' : 's',
                number_format($cierre->diferencia, 2)
            ));
    }

    public function show(CierreCaja $caja)
    {
        $caja->load(['cajero', 'facturas.pedido.mesa']);

        return view('caja.show', ['cierre' => $caja]);
    }

    /**
     * Facturas pagadas que aún no fueron arqueadas (turno en curso).
     */
    private function facturasDelTurno()
    {
        return Factura::where('estado', Factura::ESTADO_PAGADA)
            ->whereNull('cierre_caja_id')
            ->with('pedido.mesa')
            ->orderBy('fecha_emision')
            ->get();
    }

    /**
     * Totales por método de pago + total general, a partir de una colección
     * de facturas.
     */
    private function calcularTotales($facturas): array
    {
        return [
            'efectivo'      => (float) $facturas->where('metodo_pago', 'efectivo')->sum('total'),
            'tarjeta'       => (float) $facturas->where('metodo_pago', 'tarjeta')->sum('total'),
            'qr'            => (float) $facturas->where('metodo_pago', 'qr')->sum('total'),
            'transferencia' => (float) $facturas->where('metodo_pago', 'transferencia')->sum('total'),
            'general'       => (float) $facturas->sum('total'),
        ];
    }
}
