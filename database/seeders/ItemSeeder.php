<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')
            ->where('numero_documento', '20123456789')
            ->value('id');

        if (! $empresaId) {
            return;
        }

        $items = [
            ['tipo' => 'servicio', 'codigo' => 'S001', 'nombre' => 'Consultoría tributaria', 'descripcion' => 'Asesoría especializada en materia tributaria', 'unidad_medida' => 'NIU', 'precio' => 35000],
            ['tipo' => 'servicio', 'codigo' => 'S002', 'nombre' => 'Servicio de contabilidad mensual', 'descripcion' => 'Registro contable mensual de operaciones', 'unidad_medida' => 'NIU', 'precio' => 60000],
            ['tipo' => 'servicio', 'codigo' => 'S003', 'nombre' => 'Planilla de remuneraciones', 'descripcion' => 'Elaboración de planilla mensual', 'unidad_medida' => 'NIU', 'precio' => 25000],
            ['tipo' => 'servicio', 'codigo' => 'S004', 'nombre' => 'Auditoría financiera', 'descripcion' => 'Revisión de estados financieros anuales', 'unidad_medida' => 'NIU', 'precio' => 150000],
            ['tipo' => 'servicio', 'codigo' => 'S005', 'nombre' => 'Constitución de empresa', 'descripcion' => 'Trámite integral de constitución de empresas', 'unidad_medida' => 'NIU', 'precio' => 90000],
            ['tipo' => 'producto', 'codigo' => 'P001', 'nombre' => 'Software contable (licencia)', 'descripcion' => 'Licencia anual de software contable', 'unidad_medida' => 'NIU', 'precio' => 120000],
        ];

        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(
                ['empresa_id' => $empresaId, 'codigo' => $item['codigo']],
                array_merge($item, [
                    'empresa_id' => $empresaId,
                    'moneda' => 'PEN',
                    'afectacion_igv' => '10',
                    'is_active' => true,
                ])
            );
        }
    }
}