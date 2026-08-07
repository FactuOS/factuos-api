<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermisoSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('jerarquia', 1)->value('id');
        $userRoleId = DB::table('roles')->where('jerarquia', 2)->value('id');

        if (! $adminRoleId || ! $userRoleId) {
            return;
        }

        $this->grantAll($adminRoleId);

        $this->grant($userRoleId, 'dashboard', ['read']);
        $this->grant($userRoleId, 'clientes', ['read', 'create', 'edit']);
        $this->grant($userRoleId, 'servicios', ['read', 'create', 'edit']);
    }

    private function grantAll(int $roleId): void
    {
        $permisoIds = DB::table('permisos')->pluck('id');

        foreach ($permisoIds as $permisoId) {
            DB::table('role_permisos')->updateOrInsert(
                ['role_id' => $roleId, 'permiso_id' => $permisoId],
                ['role_id' => $roleId, 'permiso_id' => $permisoId]
            );
        }
    }

    private function grant(int $roleId, string $moduleRute, array $slugs): void
    {
        $permisoIds = DB::table('permisos')
            ->join('modules', 'modules.id', '=', 'permisos.modulo_id')
            ->where('modules.rute', $moduleRute)
            ->whereIn('permisos.slug', $slugs)
            ->pluck('permisos.id');

        foreach ($permisoIds as $permisoId) {
            DB::table('role_permisos')->updateOrInsert(
                ['role_id' => $roleId, 'permiso_id' => $permisoId],
                ['role_id' => $roleId, 'permiso_id' => $permisoId]
            );
        }
    }
}
