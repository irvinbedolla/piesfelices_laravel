<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Asignamos la creación a la variable $user
        $user = User::create([
            'username'       => 'admin',
            'email'          => 'admin@piesfelices.com',
            'password'       => Hash::make('admin123'),
            'user_type'      => 0,
            'branch_name'    => 'MATRIZ',
            'has_permission' => true,
            'status'         => 'activo',
            'is_doctor'      => false,
        ]);

        // Ahora $user->id ya está disponible
        Permission::create([
            'user_id'           => $user->id,
            'can_sales'         => true,
            'can_inventory'     => true,
            'can_clients'       => true,
            'can_prescriptions' => true,
            'can_expenses'      => true,
            'can_credit'        => true,
            'can_cash_closing'  => true,
            'can_patients'      => true,
            'can_suppliers'     => true,
            'can_employees'     => true,
            'can_users'         => true,
        ]);
    }
}