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
}