<?php

use App\Models\Plato;
use App\Models\Inventario;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// ========== API RUTAS ==========

// API para obtener el contador de stock bajo
Route::get('/stock-count', function () {
    $count = Cache::get('low_stock_count_direct', 0);
    
    if ($count === 0 && !Cache::has('low_stock_count_direct')) {
        try {
            $count = Inventario::with('ingrediente')
                ->get()
                ->filter(fn($inv) => $inv->isLowStock())
                ->count();
            Cache::put('low_stock_count_direct', $count, 300);
        } catch (\Exception $e) {
            $count = 0;
        }
    }
    
    return response()->json(['count' => $count]);
});

// API para verificar stock de un plato
Route::post('/verificar-stock-plato', function (Request $request) {
    $request->validate([
        'plato_id' => 'required|exists:platos,id',
        'cantidad' => 'required|integer|min:1'
    ]);
    
    $plato = Plato::with('ingredientes.inventario')->find($request->plato_id);
    
    if (!$plato) {
        return response()->json(['disponible' => false, 'mensaje' => 'Plato no encontrado']);
    }
    
    $cantidadSolicitada = $request->cantidad ?? 1;
    $disponible = true;
    $mensaje = 'Stock disponible';
    
    foreach ($plato->ingredientes as $ingrediente) {
        $inventario = $ingrediente->inventario;
        $cantidadNecesaria = $ingrediente->pivot->cantidad * $cantidadSolicitada;
        
        if (!$inventario) {
            $disponible = false;
            $mensaje = "Ingrediente '{$ingrediente->nombre}' sin inventario registrado";
            break;
        }
        
        if ($inventario->cantidad_actual < $cantidadNecesaria) {
            $disponible = false;
            $mensaje = "Stock insuficiente de '{$ingrediente->nombre}'";
            break;
        }
    }
    
    return response()->json([
        'disponible' => $disponible,
        'mensaje' => $mensaje
    ]);
});

// API para obtener estadísticas de pedidos
Route::get('/pedidos-estadisticas', function () {
    try {
        $stats = [
            'pendientes' => Pedido::where('estado', 'pendiente')->count(),
            'en_preparacion' => Pedido::where('estado', 'en_preparacion')->count(),
            'listos' => Pedido::where('estado', 'listo')->count(),
            'entregados' => Pedido::where('estado', 'entregado')->count(),
        ];
        
        return response()->json($stats);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// API para limpiar caché de stock
Route::post('/limpiar-cache-stock', function () {
    Cache::forget('low_stock_count_direct');
    return response()->json([
        'success' => true,
        'mensaje' => 'Caché de stock limpiado correctamente'
    ]);
})->middleware('auth');