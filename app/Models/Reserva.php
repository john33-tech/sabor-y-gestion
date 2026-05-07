<?php
// app/Models/Reserva.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reservas';
    
    protected $fillable = [
        'usuario_id',
        'mesa_id',
        'fecha_reserva',
        'hora_reserva',
        'personas',
        'notas',
        'estado'
    ];
    
    protected $casts = [
        'fecha_reserva' => 'date',
        'hora_reserva' => 'string',
        'personas' => 'integer'
    ];
    
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function mesa(): BelongsTo
    {
        return $this->belongsTo(Mesa::class);
    }
    
    public static function getEstados()
    {
        return [
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'cancelada' => 'Cancelada',
            'completada' => 'Completada'
        ];
    }
    
    public function getEstadoBadgeAttribute()
    {
        $badges = [
            'pendiente' => 'warning',
            'confirmada' => 'success',
            'cancelada' => 'danger',
            'completada' => 'info'
        ];
        
        return $badges[$this->estado] ?? 'secondary';
    }
}