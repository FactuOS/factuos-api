<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('comprobante_tipo_id')->constrained('comprobante_tipos');
            $table->string('serie', 4);
            $table->unsignedBigInteger('numero');
            $table->date('fecha_emision');
            $table->time('hora_emision')->nullable();
            $table->string('motivo_traslado', 2);
            $table->string('motivo_descripcion')->nullable();
            $table->decimal('peso_bruto', 12, 3);
            $table->string('unidad_peso')->default('KGM');
            $table->integer('numero_bultos')->nullable();
            $table->string('partida_ubigeo', 6);
            $table->string('partida_direccion');
            $table->string('llegada_ubigeo', 6);
            $table->string('llegada_direccion');
            $table->string('destinatario_tipo_documento');
            $table->string('destinatario_numero_documento');
            $table->string('destinatario_razon_social');
            $table->string('modalidad_transporte', 2);
            $table->string('transportista_tipo_documento')->nullable();
            $table->string('transportista_numero_documento')->nullable();
            $table->string('transportista_razon_social')->nullable();
            $table->string('conductor_tipo_documento')->nullable();
            $table->string('conductor_numero_documento')->nullable();
            $table->string('conductor_nombres')->nullable();
            $table->string('conductor_apellidos')->nullable();
            $table->string('conductor_licencia')->nullable();
            $table->string('vehiculo_placa')->nullable();
            $table->foreignId('comprobante_id_relacionado')->nullable()->constrained('comprobantes')->nullOnDelete();
            $table->string('documento_referencia')->nullable();
            $table->string('estado')->default('borrador');
            $table->string('hash_cdr')->nullable();
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
        Schema::dropIfExists('guias_remision');
    }
};