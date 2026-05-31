<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashClosure extends Model
{
    use HasFactory;

    protected $table = 'cash_closures';

    protected $fillable = [
        'user_id',
        'initial_amount',
        'final_amount',
        'total_sales',
        'total_cash',
        'total_card',
        'total_qr',
        'difference',
        'opening_date',
        'closing_date',
        'status',
        'observations',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
        'initial_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_cash' => 'decimal:2',
        'total_card' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'difference' => 'decimal:2',
        'status' => 'string',
    ];

    // Relación con el usuario que realizó el cierre
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'Open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'Closed');
    }

    /**
     * Get paid orders that belong to this closed shift.
     * Returns a query builder, not a relation.
     */
   // En app/Models/CashClosure.php

    public function paidOrders()
    {
        if (!$this->closing_date) {
            return Pedido::query()->whereRaw('0'); // empty query if still open
        }

        return Pedido::whereBetween('created_at', [$this->opening_date, $this->closing_date])
            ->whereHas('factura', function ($q) {
                $q->where('estado', 'pagada');
            });
    }
}
