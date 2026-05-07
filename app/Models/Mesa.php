<?php
// app/Models/Mesa.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'numero_mesa',
        'estado',
        'area',
        'capacidad',
        'hora_reserva',
        'cliente_reserva',
        'telefono_reserva'
    ];

    protected $casts = [
        'hora_reserva' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function isLibre()
    {
        return $this->estado === 'libre';
    }

    public function isOcupado()
    {
        return $this->estado === 'ocupado';
    }

    public function isReservado()
    {
        return $this->estado === 'reservado';
    }
    public function isFueraServicio()
    {
        return $this->estado === 'fuera_servicio';
    }
}