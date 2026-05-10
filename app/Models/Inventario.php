<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Inventario extends Model
{
    protected $table = 'inventario';
    
    protected $fillable = [
        'ingrediente_id',
        'cantidad_actual',
        'stock_minimo',
        'stock_maximo',
        'ubicacion'
    ];
    
    protected $casts = [
        'cantidad_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'stock_maximo' => 'decimal:2'
    ];
    
    // Limpiar caché cuando se guarda o elimina inventario
    protected static function booted()
    {
        static::saved(function ($inventario) {
            Cache::forget('low_stock_count_direct');
        });
        
        static::deleted(function ($inventario) {
            Cache::forget('low_stock_count_direct');
        });
    }
    
    public function ingrediente()
    {
        return $this->belongsTo(Ingrediente::class);
    }
    
    public function isLowStock(): bool
    {
        return $this->cantidad_actual <= $this->stock_minimo;
    }
    
    public function getStockStatusAttribute(): string
    {
        if ($this->cantidad_actual <= 0) {
            return 'agotado';
        }
        if ($this->isLowStock()) {
            return 'bajo';
        }
        if ($this->stock_maximo && $this->cantidad_actual >= $this->stock_maximo) {
            return 'maximo';
        }
        return 'normal';
    }
    
    public function getStockStatusColorAttribute(): string
    {
        return match($this->stock_status) {
            'agotado' => 'red',
            'bajo' => 'yellow',
            'maximo' => 'green',
            default => 'blue'
        };
    }
}