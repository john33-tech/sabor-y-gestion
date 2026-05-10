<?php
// app/Models/Factura.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'pedido_id',
        'numero_factura',
        'cliente_nombre',
        'cliente_nit',
        'cliente_telefono',
        'subtotal',
        'impuesto',
        'descuento',
        'total',
        'metodo_pago',
        'estado',
        'fecha_emision',
        'usuario_id'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_emision' => 'datetime'
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_ANULADA = 'anulada';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($factura) {
            if (!$factura->numero_factura) {
                $ultimo = self::orderBy('id', 'desc')->whereNotNull('numero_factura')->first();
                $numero = 1;
                if ($ultimo) {
                    $ultimoNumero = str_replace('FACT-', '', $ultimo->numero_factura);
                    $numero = intval($ultimoNumero) + 1;
                }
                
                $nuevoNumero = 'FACT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
                
                while (self::where('numero_factura', $nuevoNumero)->exists()) {
                    $numero++;
                    $nuevoNumero = 'FACT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
                }
                
                $factura->numero_factura = $nuevoNumero;
            }
        });
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Recalcula el total de la factura basándose en el subtotal, impuesto y descuento.
     */
    public function recalculateTotal()
    {
        $this->total = ($this->subtotal + $this->impuesto) - $this->descuento;
        return $this;
    }

    public function generateNextNumero()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = 1;
        if ($ultimo && $ultimo->numero_factura) {
            $ultimoNumero = substr($ultimo->numero_factura, 5); // Remueve 'FACT-'
            $numero = intval($ultimoNumero) + 1;
        }
        
        $nuevoNumero = 'FACT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        
        // Verificar duplicados por si acaso
        while (self::where('numero_factura', $nuevoNumero)->exists()) {
            $numero++;
            $nuevoNumero = 'FACT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }
        
        $this->numero_factura = $nuevoNumero;
    }
}