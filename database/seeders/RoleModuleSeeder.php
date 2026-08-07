<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleModuleSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('roles')->where('jerarquia', 1)->value('id');
        $userId = DB::table('roles')->where('jerarquia', 2)->value('id');

        $modules = DB::table('modules')->pluck('id');

        foreach ($modules as $moduleId) {
            DB::table('role_modules')->updateOrInsert(
                ['role_id' => $adminId, 'module_id' => $moduleId],
                ['role_id' => $adminId, 'module_id' => $moduleId]
            );
        }

        $userModuleIds = DB::table('modules')
            ->whereIn('nombre', ['Dashboard', 'Clientes', 'Servicios'])
            ->pluck('id');

        foreach ($userModuleIds as $moduleId) {
            DB::table('role_modules')->updateOrInsert(
                ['role_id' => $userId, 'module_id' => $moduleId],
                ['role_id' => $userId, 'module_id' => $moduleId]
            );
        }
    }
}
