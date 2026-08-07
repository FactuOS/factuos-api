<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')
            ->where('numero_documento', '20123456789')
            ->value('id');

        if (! $empresaId) {
            return;
        }

        $clientes = [
            [
                'tipo_documento' => 'RUC',
                'numero_documento' => '20100047218',
                'razon_social' => 'Empresa Comercial S.A.C.',
                'nombre_comercial' => 'Comercial Norte',
                'email' => 'contacto@empresacomercial.com',
                'telefono' => '01-5551234',
                'direccion' => 'Av. Los Andes 123, Lima',
            ],
            [
                'tipo_documento' => 'RUC',
                'numero_documento' => '20512345678',
                'razon_social' => 'Tecnología Perú E.I.R.L.',
                'nombre_comercial' => 'TecnoPerú',
                'email' => 'ventas@tecnoperu.pe',
                'telefono' => '01-5554321',
                'direccion' => 'Jr. La Marina 456, Callao',
            ],
            [
                'tipo_documento' => 'DNI',
                'numero_documento' => '43123456',
                'razon_social' => 'Juan Carlos Ramírez',
                'nombre_comercial' => null,
                'email' => 'jcramirez@gmail.com',
                'telefono' => '987654321',
                'direccion' => 'Urb. Las Flores Mz B Lt 12, Arequipa',
            ],
            [
                'tipo_documento' => 'RUC',
                'numero_documento' => '20601234567',
                'razon_social' => 'Agroexport SAC',
                'nombre_comercial' => 'AgroExpo',
                'email' => 'info@agroexport.pe',
                'telefono' => '973221100',
                'direccion' => 'Carretera Panamericana Sur Km 15, Ica',
            ],
            [
                'tipo_documento' => 'CE',
                'numero_documento' => 'E00123456',
                'razon_social' => 'María González Pérez',
                'nombre_comercial' => null,
                'email' => 'mgonzalez@gmail.com',
                'telefono' => '988776655',
                'direccion' => 'Av. Grau 789, Trujillo',
            ],
        ];

        foreach ($clientes as $cliente) {
            DB::table('clientes')->updateOrInsert(
                ['tipo_documento' => $cliente['tipo_documento'], 'numero_documento' => $cliente['numero_documento']],
                array_merge($cliente, ['empresa_id' => $empresaId, 'is_active' => true])
            );
        }
    }
}
