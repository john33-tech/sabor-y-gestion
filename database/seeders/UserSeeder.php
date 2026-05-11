<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // updateOrCreate hace al seeder idempotente: si el email ya existe,
        // actualiza name/password/role sin lanzar error de constraint UNIQUE.
        $usuarios = [
            ['email' => 'admin@saborgestion.com',    'name' => 'Administrador', 'role' => 'admin'],
            ['email' => 'mesero@saborgestion.com',   'name' => 'Mesero',        'role' => 'mesero'],
            ['email' => 'cocinero@saborgestion.com', 'name' => 'Cocinero',      'role' => 'cocinero'],
            ['email' => 'cajero@saborgestion.com',   'name' => 'Cajero',        'role' => 'cajero'],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
