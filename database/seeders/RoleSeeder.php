<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Admin', 'is_active' => true, 'jerarquia' => 1],
            ['nombre' => 'Usuario', 'is_active' => true, 'jerarquia' => 2],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $role['nombre']],
                $role
            );
        }
    }
}
