<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\User;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::first();
        $adminUser = User::where('username', 'admin')->first();

        Employee::firstOrCreate(
            ['name' => 'Juan Pérez Gómez'],
            [
                'phone'           => '4431234567',
                'position'        => 'Podólogo Principal',
                'salary'          => 8500.00,
                'commission_rate' => 10.00,
                'branch_id'       => $branch?->id,
                'user_id'         => $adminUser?->id,
                'status'          => true,
                'hired_at'        => now()->subMonths(6),
            ]
        );
    }
}