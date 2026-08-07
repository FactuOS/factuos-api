<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    public function run(): void
    {
        $actions = [
            ['nombre' => 'Crear', 'slug' => 'create'],
            ['nombre' => 'Editar', 'slug' => 'edit'],
            ['nombre' => 'Leer', 'slug' => 'read'],
            ['nombre' => 'Eliminar', 'slug' => 'delete'],
        ];

        $moduleIds = DB::table('modules')->pluck('id');

        foreach ($moduleIds as $moduleId) {
            foreach ($actions as $action) {
                DB::table('permisos')->updateOrInsert(
                    ['modulo_id' => $moduleId, 'slug' => $action['slug']],
                    ['modulo_id' => $moduleId, 'slug' => $action['slug'], 'nombre' => $action['nombre'], 'is_active' => true]
                );
            }
        }
    }
}
