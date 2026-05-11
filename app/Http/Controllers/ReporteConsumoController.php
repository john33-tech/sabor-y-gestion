<?php

namespace App\Http\Controllers;

use App\Models\Consumo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteConsumoController extends Controller
{
    public function index(Request $request)
    {
        $query = Consumo::with('usuario');
        
        // Filtros
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_consumo', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_consumo', '<=', $request->fecha_hasta);
        }
        
        if ($request->filled('tipo_pedido')) {
            $query->where('tipo_pedido', $request->tipo_pedido);
        }
        
        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }
        
        $consumos = $query->orderBy('fecha_consumo', 'desc')->paginate(15);
        
        // Estadísticas
        $stats = [
            'total_consumos' => $query->count(),
            'total_ingresos' => $query->sum('total'),
            'total_platos_vendidos' => $this->getTotalPlatosVendidos($query->get()),
            'promedio_por_consumo' => $query->avg('total') ?? 0,
        ];
        
        // Tipo de pedido
        $tipoStats = [
            'mesa' => $query->clone()->where('tipo_pedido', 'mesa')->sum('total'),
            'delivery' => $query->clone()->where('tipo_pedido', 'delivery')->sum('total'),
            'para_llevar' => $query->clone()->where('tipo_pedido', 'para_llevar')->sum('total'),
        ];
        
        // Top platos más vendidos
        $topPlatos = $this->getTopPlatos($query->get());
        
        $usuarios = User::whereIn('role', ['admin', 'cocinero', 'mesero'])->get();
        $tiposPedido = ['mesa' => 'Mesa', 'delivery' => 'Delivery', 'para_llevar' => 'Para Llevar'];
        
        return view('reportes.consumos', compact('consumos', 'stats', 'tipoStats', 'topPlatos', 'usuarios', 'tiposPedido'));
    }
    
    private function getTotalPlatosVendidos($consumos)
    {
        $total = 0;
        foreach ($consumos as $consumo) {
            foreach ($consumo->detalles as $detalle) {
                $total += $detalle['cantidad'];
            }
        }
        return $total;
    }
    
    private function getTopPlatos($consumos, $limit = 5)
    {
        $platos = [];
        foreach ($consumos as $consumo) {
            foreach ($consumo->detalles as $detalle) {
                $nombre = $detalle['plato_nombre'];
                if (!isset($platos[$nombre])) {
                    $platos[$nombre] = [
                        'nombre' => $nombre,
                        'cantidad' => 0,
                        'total' => 0
                    ];
                }
                $platos[$nombre]['cantidad'] += $detalle['cantidad'];
                $platos[$nombre]['total'] += $detalle['subtotal'];
            }
        }
        
        // Ordenar por cantidad
        usort($platos, function($a, $b) {
            return $b['cantidad'] <=> $a['cantidad'];
        });
        
        return array_slice($platos, 0, $limit);
    }
    
    public function export(Request $request)
    {
        // Para exportar a Excel/CSV (opcional)
        $query = Consumo::with('usuario');
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_consumo', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_consumo', '<=', $request->fecha_hasta);
        }
        
        $consumos = $query->orderBy('fecha_consumo', 'desc')->get();
        
        $filename = 'reporte_consumos_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://temp', 'w');
        
        // Cabeceras
        fputcsv($handle, ['N° Pedido', 'Tipo', 'Usuario', 'Fecha', 'Subtotal', 'Impuesto', 'Descuento', 'Total']);
        
        foreach ($consumos as $consumo) {
            fputcsv($handle, [
                $consumo->numero_pedido,
                $consumo->tipo_pedido,
                $consumo->usuario->name ?? 'N/A',
                $consumo->fecha_consumo->format('d/m/Y H:i'),
                $consumo->subtotal,
                $consumo->impuesto,
                $consumo->descuento,
                $consumo->total
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}