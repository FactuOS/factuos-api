<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['nombre' => 'Dashboard', 'icon' => 'pi pi-home', 'orden' => 1, 'rute' => 'dashboard'],
            ['nombre' => 'Clientes', 'icon' => 'pi pi-users', 'orden' => 2, 'rute' => 'clientes'],
            ['nombre' => 'Servicios', 'icon' => 'pi pi-briefcase', 'orden' => 3, 'rute' => 'servicios'],
            ['nombre' => 'Boletas', 'icon' => 'pi pi-receipt', 'orden' => 4, 'rute' => 'boletas'],
            ['nombre' => 'Facturas', 'icon' => 'pi pi-file', 'orden' => 5, 'rute' => 'facturas'],
            ['nombre' => 'Reportes', 'icon' => 'pi pi-chart-bar', 'orden' => 6, 'rute' => 'reportes'],
            ['nombre' => 'Usuarios', 'icon' => 'pi pi-user', 'orden' => 7, 'rute' => 'usuarios'],
            ['nombre' => 'Permisos', 'icon' => 'pi pi-lock', 'orden' => 8, 'rute' => 'permisos'],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['nombre' => $module['nombre']],
                $module
            );
        }
    }
}
