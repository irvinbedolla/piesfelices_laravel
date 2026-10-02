<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrador')->first();

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'email'       => 'admin@piesfelices.com',
                'password'    => Hash::make('admin123'),
                'user_type'   => 0, // Administrador
                'branch_name' => 'MATRIZ',
                'role_id'     => $adminRole?->id,
                'status'      => 'activo',
                'is_doctor'   => false,
            ]
        );
    }
}