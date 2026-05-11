<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ingrediente extends Model
{
    protected $fillable = [
        'nombre',
        'foto',
        'unidad_medida'
    ];
    
    public function platos(): BelongsToMany
    {
        return $this->belongsToMany(Plato::class, 'plato_ingrediente')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
    
    // Relación con inventario
    public function inventario(): HasOne
    {
        return $this->hasOne(Inventario::class);
    }
    
    // Helper para obtener cantidad actual
    public function getCantidadActualAttribute()
    {
        return $this->inventario?->cantidad_actual ?? 0;
    }
    
    // Helper para verificar si tiene stock bajo
    public function hasLowStock(): bool
    {
        return $this->inventario?->isLowStock() ?? false;
    }
}