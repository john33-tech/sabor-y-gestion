<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consumo extends Model
{
    protected $fillable = [
        'numero_pedido',
        'pedido_id',
        'usuario_id',
        'tipo_pedido',
        'estado',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'detalles',
        'fecha_consumo'
    ];
    
    protected $casts = [
        'detalles' => 'array',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_consumo' => 'datetime'
    ];
    
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
    
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}