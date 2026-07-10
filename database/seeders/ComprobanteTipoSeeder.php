<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Comprobantes\Models\ComprobanteTipo;
use Illuminate\Database\Seeder;

class ComprobanteTipoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => '01',
                'nombre' => 'Factura Electrónica',
                'abreviacion' => 'FACTURA',
                'is_active' => true,
            ],
            [
                'codigo' => '03',
                'nombre' => 'Boleta de Venta Electrónica',
                'abreviacion' => 'BOLETA',
                'is_active' => true,
            ],
            [
                'codigo' => '07',
                'nombre' => 'Nota de Crédito Electrónica',
                'abreviacion' => 'NC',
                'is_active' => true,
            ],
            [
                'codigo' => '08',
                'nombre' => 'Nota de Débito Electrónica',
                'abreviacion' => 'ND',
                'is_active' => true,
            ],
            [
                'codigo' => '09',
                'nombre' => 'Guía de Remisión Remitente',
                'abreviacion' => 'GRE',
                'is_active' => true,
            ],
            [
                'codigo' => '31',
                'nombre' => 'Guía de Remisión Transportista',
                'abreviacion' => 'GRT',
                'is_active' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            ComprobanteTipo::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                $tipo
            );
        }
    }
}
