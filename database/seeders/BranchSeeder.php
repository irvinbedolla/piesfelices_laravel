<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'MATRIZ', 'address' => 'Oficina Central', 'phone' => '4430000000', 'status' => true],
            ['name' => 'ALTOZANO', 'address' => 'Av. Montaña Monarca', 'phone' => '4431111111', 'status' => true],
            ['name' => 'CENTRO', 'address' => 'Av. Madero', 'phone' => '4432222222', 'status' => true],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['name' => $branch['name']], $branch);
        }
    }
}