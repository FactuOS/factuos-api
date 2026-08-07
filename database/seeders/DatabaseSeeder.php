<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ModuleSeeder::class,
            RoleModuleSeeder::class,
            PermisoSeeder::class,
            RolePermisoSeeder::class,
            ModulesRolesSeeder::class,
            UserSeeder::class,
            EmpresaSeeder::class,
            ClienteSeeder::class,
            ItemSeeder::class,
            EmpresaSunatConfigSeeder::class,
            ComprobanteTipoSeeder::class,
            SerieSeeder::class,
        ]);
    }
}
