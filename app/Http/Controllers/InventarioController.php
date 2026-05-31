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

        // Platos (con receta) para la reposición por producción.
        $platos = \App\Models\Plato::has('ingredientes')->orderBy('nombre')->get(['id', 'nombre']);

        return view('inventario.index', compact(
            'ingredientes',
            'totalIngredientes',
            'ingredientesConInventario',
            'ingredientesSinInventario',
            'stockBajo',
            'stockAgotado',
            'platos'
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
     * Repone el inventario con EXACTAMENTE lo necesario para producir N unidades
     * de un plato: suma (receta × cantidad) de cada ingrediente. Así el admin
     * agrega solo lo que necesita para X unidades de un producto, no "al máximo".
     */
    public function reponerPorProducto(Request $request)
    {
        $request->validate([
            'plato_id' => 'required|exists:platos,id',
            'cantidad' => 'required|integer|min:1|max:1000',
        ]);

        $plato = \App\Models\Plato::with('ingredientes')->find($request->plato_id);
        $cantidad = (int) $request->cantidad;

        if ($plato->ingredientes->isEmpty()) {
            return redirect()->route('inventario.index')
                ->with('error', 'El plato "' . $plato->nombre . '" no tiene receta; no se puede calcular.');
        }

        $detalle = [];
        foreach ($plato->ingredientes as $ing) {
            $necesario = (float) $ing->pivot->cantidad * $cantidad;

            $inv = Inventario::firstOrNew(['ingrediente_id' => $ing->id]);
            $inv->cantidad_actual = (float) ($inv->cantidad_actual ?? 0) + $necesario;
            if (!$inv->stock_minimo) { $inv->stock_minimo = 100; }
            if (!$inv->stock_maximo) { $inv->stock_maximo = 10000; }
            $inv->save();

            $detalle[] = '+' . number_format($necesario, 0) . $ing->unidad_medida . ' ' . $ing->nombre;
        }

        return redirect()->route('inventario.index')
            ->with('success', 'Repuesto para ' . $cantidad . ' x ' . $plato->nombre . ': ' . implode(', ', $detalle) . '.');
    }
}