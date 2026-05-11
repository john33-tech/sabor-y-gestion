<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plato extends Model
{
    protected $fillable = [
        'nombre',
        'precio',
        'categoria_id',
        'descripcion',
        'disponible',
        'imagen',
        'score'
    ];
    
    protected $casts = [
        'precio' => 'decimal:2',
        'disponible' => 'boolean',
        'score' => 'decimal:1'
    ];
    
    // Relación con categoría
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    
    // Relación con ingredientes (pivote con cantidad)
    public function ingredientes(): BelongsToMany
    {
        return $this->belongsToMany(Ingrediente::class, 'plato_ingrediente')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
    
    // Método: Descontar ingredientes del inventario
    public function descontarInventario()
    {
        foreach ($this->ingredientes as $ingrediente) {
            $inventario = $ingrediente->inventario;
            
            if ($inventario) {
                $cantidadADescontar = $ingrediente->pivot->cantidad;
                $nuevaCantidad = $inventario->cantidad_actual - $cantidadADescontar;
                
                // Actualizar el inventario (no permite negativo)
                $inventario->cantidad_actual = max(0, $nuevaCantidad);
                $inventario->save();
            }
        }
    }
    
    // Método para revertir el inventario (cuando se cancela un pedido)
    public function revertirInventario()
    {
        foreach ($this->ingredientes as $ingrediente) {
            $inventario = $ingrediente->inventario;
            
            if ($inventario) {
                $cantidadARevertir = $ingrediente->pivot->cantidad;
                $nuevaCantidad = $inventario->cantidad_actual + $cantidadARevertir;
                
                $inventario->cantidad_actual = $nuevaCantidad;
                $inventario->save();
            }
        }
    }
    
    // Método para verificar si hay suficiente stock (Solo UNA vez)
public function verificarStock($cantidad = 1)
{
    // Si el plato no tiene ingredientes, no se puede vender
    if ($this->ingredientes->count() === 0) {
        return false;
    }
    
    foreach ($this->ingredientes as $ingrediente) {
        $inventario = $ingrediente->inventario;
        $cantidadNecesaria = $ingrediente->pivot->cantidad * $cantidad;
        
        if (!$inventario || $inventario->cantidad_actual < $cantidadNecesaria) {
            return false;
        }
    }
    return true;
}
}