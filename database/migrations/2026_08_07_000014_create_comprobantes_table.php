<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('comprobante_tipo_id')->constrained('comprobante_tipos');
            $table->string('serie', 4);
            $table->unsignedBigInteger('numero');
            $table->date('fecha_emision');
            $table->time('hora_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('moneda', 3)->default('PEN');
            $table->string('tipo_operacion', 4)->default('0101');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('igv', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('porcentaje_igv', 5, 2)->default(18);
            $table->decimal('descuento_total', 18, 2)->default(0);
            $table->string('forma_pago')->default('CONTADO'); // CONTADO | CREDITO
            $table->string('estado')->default('borrador');

            // Notas de Crédito / Débito
            $table->foreignId('comprobante_id_referenciado')->nullable()->constrained('comprobantes')->nullOnDelete();
            $table->string('serie_referenciada', 4)->nullable();
            $table->string('numero_referenciado')->nullable();
            $table->string('motivo_codigo', 2)->nullable();
            $table->string('motivo_descripcion')->nullable();

            // SUNAT respuestas
            $table->string('hash_cdr')->nullable();
            $table->string('hash_ubl')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('cdr_xml_path')->nullable();
            $table->string('ticket')->nullable();
            $table->string('sunat_code')->nullable();
            $table->text('sunat_description')->nullable();

            $table->text('observacion')->nullable();
            $table->jsonb('datos_extra')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'serie', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
