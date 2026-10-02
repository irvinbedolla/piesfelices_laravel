<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rol Administrador (Todos los permisos activos)
        $adminRole = Role::firstOrCreate(
            ['name' => 'Administrador'],
            ['description' => 'Acceso total y control administrativo del sistema']
        );

        Permission::updateOrCreate(
            ['role_id' => $adminRole->id],
            [
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
            ]
        );

        // 2. Rol Recepcionista / Operador
        $recepcionRole = Role::firstOrCreate(
            ['name' => 'Recepcionista'],
            ['description' => 'Atención a clientes, ventas y agenda']
        );

        Permission::updateOrCreate(
            ['role_id' => $recepcionRole->id],
            [
                'can_sales'         => true,
                'can_inventory'     => false,
                'can_clients'       => true,
                'can_prescriptions' => false,
                'can_expenses'      => false,
                'can_credit'        => false,
                'can_cash_closing'  => true,
                'can_patients'      => true,
                'can_suppliers'     => false,
                'can_employees'     => false,
                'can_users'         => false,
            ]
        );
    }
}