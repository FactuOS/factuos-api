<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesRolesSeeder extends Seeder
{
    private const ACTIONS = [
        ['nombre' => 'Crear', 'slug' => 'create'],
        ['nombre' => 'Editar', 'slug' => 'edit'],
        ['nombre' => 'Leer', 'slug' => 'read'],
        ['nombre' => 'Eliminar', 'slug' => 'delete'],
    ];

    public function run(): void
    {
        $modules = [
            ['nombre' => 'Modulos', 'icon' => 'pi pi-th-large', 'orden' => 9, 'rute' => 'modules'],
            ['nombre' => 'Roles', 'icon' => 'pi pi-id-card', 'orden' => 10, 'rute' => 'roles'],
        ];

        $moduleIds = [];
        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['rute' => $module['rute']],
                $module
            );

            $moduleIds[] = DB::table('modules')->where('rute', $module['rute'])->value('id');
        }

        foreach ($moduleIds as $moduleId) {
            foreach (self::ACTIONS as $action) {
                DB::table('permisos')->updateOrInsert(
                    ['modulo_id' => $moduleId, 'slug' => $action['slug']],
                    ['modulo_id' => $moduleId, 'slug' => $action['slug'], 'nombre' => $action['nombre'], 'is_active' => true]
                );
            }
        }

        $adminRoleId = DB::table('roles')->where('jerarquia', 1)->value('id');

        if (! $adminRoleId) {
            return;
        }

        foreach ($moduleIds as $moduleId) {
            DB::table('role_modules')->updateOrInsert(
                ['role_id' => $adminRoleId, 'module_id' => $moduleId],
                ['role_id' => $adminRoleId, 'module_id' => $moduleId]
            );
        }

        $permisoIds = DB::table('permisos')->whereIn('modulo_id', $moduleIds)->pluck('id');

        foreach ($permisoIds as $permisoId) {
            DB::table('role_permisos')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permiso_id' => $permisoId],
                ['role_id' => $adminRoleId, 'permiso_id' => $permisoId]
            );
        }
    }
}
