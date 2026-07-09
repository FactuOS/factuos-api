<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('descripcion');
            $table->decimal('cantidad', 12, 3)->default(1);
            $table->string('unidad_medida')->default('NIU');
            $table->decimal('valor_unitario', 18, 2)->default(0);
            $table->decimal('precio_unitario', 18, 2)->default(0);
            $table->decimal('descuento', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('igv', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->string('afectacion_igv', 2)->default('10');
            $table->decimal('porcentaje_igv', 5, 2)->default(18);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante_detalles');
    }
};
