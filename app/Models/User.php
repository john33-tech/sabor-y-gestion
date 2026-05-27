<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'celular',
        'direccion',
        'score',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isMesero()
    {
        return $this->role === 'mesero';
    }

    public function isCocinero()
    {
        return $this->role === 'cocinero';
    }

    public function isCajero()
    {
        return $this->role === 'cajero';
    }

    public function isCliente()
    {
        return $this->role === 'cliente';
    }

    public function cashClosures(): HasMany
    {
        return $this->hasMany(CashClosure::class);
    }
}
