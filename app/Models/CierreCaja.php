<?php
// app/Models/CierreCaja.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Arqueo de caja: un cierre del turno con los totales cobrados por método
 * de pago, el efectivo contado y la diferencia. Agrupa las facturas pagadas
 * que se contabilizaron en ese cierre.
 */
class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'usuario_id',
        'fecha_cierre',
        'total_efectivo',
        'total_tarjeta',
        'total_qr',
        'total_transferencia',
        'total_general',
        'cantidad_facturas',
        'efectivo_contado',
        'diferencia',
        'observaciones',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'total_efectivo' => 'decimal:2',
        'total_tarjeta' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'total_transferencia' => 'decimal:2',
        'total_general' => 'decimal:2',
        'efectivo_contado' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];

    public function cajero()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'cierre_caja_id');
    }
}
