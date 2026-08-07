<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SerieSeeder extends Seeder
{
    public function run(): void
    {
        $empresaId = DB::table('empresas')
            ->where('numero_documento', '20123456789')
            ->value('id');

        if (! $empresaId) {
            return;
        }

        $series = [
            ['comprobante_tipo_id' => $this->tipoId('01'), 'serie' => 'F001'],
            ['comprobante_tipo_id' => $this->tipoId('03'), 'serie' => 'B001'],
            ['comprobante_tipo_id' => $this->tipoId('02'), 'serie' => 'RH01'],
        ];

        foreach ($series as $serie) {
            if (! $serie['comprobante_tipo_id']) {
                continue;
            }

            DB::table('series')->updateOrInsert(
                ['empresa_id' => $empresaId, 'comprobante_tipo_id' => $serie['comprobante_tipo_id'], 'serie' => $serie['serie']],
                ['empresa_id' => $empresaId, 'comprobante_tipo_id' => $serie['comprobante_tipo_id'], 'serie' => $serie['serie'], 'correlativo_actual' => 0, 'is_active' => true]
            );
        }
    }

    private function tipoId(string $codigo): ?int
    {
        return DB::table('comprobante_tipos')->where('codigo', $codigo)->value('id');
    }
}
