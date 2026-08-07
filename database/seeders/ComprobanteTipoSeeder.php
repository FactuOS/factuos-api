<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprobanteTipoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['codigo' => '01', 'nombre' => 'Factura', 'abreviacion' => 'F'],
            ['codigo' => '02', 'nombre' => 'Recibo por honorarios', 'abreviacion' => 'RH'],
            ['codigo' => '03', 'nombre' => 'Boleta de venta', 'abreviacion' => 'B'],
            ['codigo' => '04', 'nombre' => 'Liquidación de compra', 'abreviacion' => 'LC'],
            ['codigo' => '07', 'nombre' => 'Nota de crédito', 'abreviacion' => 'NC'],
            ['codigo' => '08', 'nombre' => 'Nota de débito', 'abreviacion' => 'ND'],
            ['codigo' => '09', 'nombre' => 'Guía de remisión remitente', 'abreviacion' => 'GR'],
            ['codigo' => '20', 'nombre' => 'Comprobante de retención', 'abreviacion' => 'CR'],
            ['codigo' => '31', 'nombre' => 'Guía de remisión de bienes fiscalizados', 'abreviacion' => 'GRF'],
            ['codigo' => '40', 'nombre' => 'Comprobante de percepción', 'abreviacion' => 'CP'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('comprobante_tipos')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                array_merge($tipo, ['is_active' => true])
            );
        }
    }
}
