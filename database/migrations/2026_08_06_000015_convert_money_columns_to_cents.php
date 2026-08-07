<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MONEY_COLUMNS = [
        'items' => ['precio'],
        'comprobantes' => ['subtotal', 'igv', 'total', 'descuento_total'],
        'comprobante_detalles' => ['valor_unitario', 'precio_unitario', 'descuento', 'subtotal', 'igv', 'total'],
        'comprobante_cuotas' => ['monto'],
    ];

    public function up(): void
    {
        foreach (self::MONEY_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE bigint USING ({$column} * 100)::bigint");
            }
        }
    }

    public function down(): void
    {
        foreach (self::MONEY_COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement("ALTER TABLE {$table} ALTER COLUMN {$column} TYPE numeric(18,2) USING ({$column}::numeric / 100.0)");
            }
        }
    }
};