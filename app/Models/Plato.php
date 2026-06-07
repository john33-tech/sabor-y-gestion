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
    
    // Método: Descontar ingredientes del inventario (escandallo).
    // BLINDADO contra concurrencia: bloquea la fila del inventario con
    // lockForUpdate (debe correr dentro de una transacción — todos los callers
    // la abren) y re-verifica el stock DENTRO del lock. Si no alcanza, lanza una
    // excepción para que la transacción haga rollback (en vez de descontar a 0
    // en silencio y sobre-vender). Así dos pedidos simultáneos del mismo
    // ingrediente se serializan y nunca se vende stock que no existe.
    public function descontarInventario($cantidad = 1)
    {
        foreach ($this->ingredientes as $ingrediente) {
            $inventario = $ingrediente->inventario()->lockForUpdate()->first();

            if (!$inventario) {
                continue;
            }

            $cantidadADescontar = $ingrediente->pivot->cantidad * $cantidad;

            if ($inventario->cantidad_actual < $cantidadADescontar) {
                throw new \RuntimeException(
                    "Stock insuficiente de «{$ingrediente->nombre}»: disponible {$inventario->cantidad_actual}, se necesitan {$cantidadADescontar}."
                );
            }

            $inventario->cantidad_actual -= $cantidadADescontar;
            $inventario->save();
        }
    }

    // Método para revertir el inventario (cuando se cancela un pedido).
    // También bloquea la fila para evitar perder actualizaciones por concurrencia.
    public function revertirInventario($cantidad = 1)
    {
        foreach ($this->ingredientes as $ingrediente) {
            $inventario = $ingrediente->inventario()->lockForUpdate()->first();

            if (!$inventario) {
                continue;
            }

            $cantidadARevertir = $ingrediente->pivot->cantidad * $cantidad;
            $inventario->cantidad_actual += $cantidadARevertir;
            $inventario->save();
        }
    }
    
    // Método para verificar si hay suficiente stock (Solo UNA vez)
public function verificarStock($cantidad = 1)
{
    // Control estricto: un plato sin receta (sin ingredientes asignados) no se
    // puede vender. Asigná sus ingredientes en Platos → Editar.
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