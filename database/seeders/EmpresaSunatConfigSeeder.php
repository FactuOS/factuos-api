<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSunatConfigSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')
            ->where('numero_documento', '20123456789')
            ->value('id');

        if (! $empresaId) {
            return;
        }

        DB::table('empresa_sunat_configs')->updateOrInsert(
            ['empresa_id' => $empresaId],
            [
                'sol_user' => 'CSDEMO123',
                'sol_pass' => 'clave-sol-demo',
                'certificate_path' => null,
                'certificate_password' => null,
                'production' => false,
            ]
        );
    }
}