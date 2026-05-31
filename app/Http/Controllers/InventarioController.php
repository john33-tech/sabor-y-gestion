<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Ingrediente;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        // Obtener todos los ingredientes con su inventario
        $ingredientes = Ingrediente::with('inventario')->get();
        
        // Calcular estadísticas
        $totalIngredientes = $ingredientes->count();
        $ingredientesConInventario = $ingredientes->filter(fn($i) => $i->inventario)->count();
        $ingredientesSinInventario = $totalIngredientes - $ingredientesConInventario;
        
        // Ingredientes con stock bajo
        $stockBajo = $ingredientes->filter(fn($i) => $i->hasLowStock())->count();
        $stockAgotado = $ingredientes->filter(fn($i) => $i->cantidad_actual <= 0)->count();
        
        return view('inventario.index', compact(
            'ingredientes',
            'totalIngredientes',
            'ingredientesConInventario',
            'ingredientesSinInventario',
            'stockBajo',
            'stockAgotado'
        ));
    }
    
    public function create()
    {
        $ingredientes = Ingrediente::doesntHave('inventario')->get();
        return view('inventario.create', compact('ingredientes'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ingrediente_id' => 'required|exists:ingredientes,id',
            'cantidad_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255'
        ]);
        
        Inventario::create($validated);
        
        return redirect()->route('inventario.index')
            ->with('success', 'Inventario creado exitosamente');
    }
    
    public function edit(Inventario $inventario)
    {
        return view('inventario.edit', compact('inventario'));
    }
    
    public function update(Request $request, Inventario $inventario)
    {
        $validated = $request->validate([
            'cantidad_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'stock_maximo' => 'nullable|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255'
        ]);
        
        $inventario->update($validated);
        
        return redirect()->route('inventario.index')
            ->with('success', 'Inventario actualizado exitosamente');
    }
    
    public function destroy(Inventario $inventario)
    {
        $inventario->delete();

        return redirect()->route('inventario.index')
            ->with('success', 'Registro de inventario eliminado');
    }

    /**
     * Reposición rápida: rellena el stock del ingrediente hasta su stock máximo
     * con un solo clic (si no hay máximo definido, usa 5x el mínimo). Pensado
     * para que el admin reponga fácil cuando un insumo está bajo.
     */
    public function reponer(Inventario $inventario)
    {
        $objetivo = $inventario->stock_maximo > 0
            ? (float) $inventario->stock_maximo
            : max((float) $inventario->stock_minimo * 5, 1000);

        $inventario->cantidad_actual = $objetivo;
        $inventario->save();

        $nombre = $inventario->ingrediente->nombre ?? 'ingrediente';
        return redirect()->route('inventario.index')
            ->with('success', 'Stock de "' . $nombre . '" repuesto a ' . number_format($objetivo, 0) . '.');
    }

    /**
     * Repone al máximo TODOS los ingredientes con stock bajo, de una sola vez.
     */
    public function reponerTodos()
    {
        $repuestos = 0;
        Inventario::all()->each(function ($inv) use (&$repuestos) {
            if ($inv->isLowStock()) {
                $inv->cantidad_actual = $inv->stock_maximo > 0
                    ? (float) $inv->stock_maximo
                    : max((float) $inv->stock_minimo * 5, 1000);
                $inv->save();
                $repuestos++;
            }
        });

        return redirect()->route('inventario.index')
            ->with('success', $repuestos . ' ingrediente(s) con stock bajo repuesto(s) al máximo.');
    }
}