<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('comprobante_tipo_id')->constrained('comprobante_tipos');
            $table->string('serie', 4);
            $table->unsignedBigInteger('correlativo_actual')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'comprobante_tipo_id', 'serie']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
