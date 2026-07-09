<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('tipo_documento')->default('RUC');
            $table->string('numero_documento')->unique();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('logo')->nullable();
            $table->string('representante_legal')->nullable();
            $table->string('dni_representante')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
