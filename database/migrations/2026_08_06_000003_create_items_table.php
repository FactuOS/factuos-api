<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('tipo')->default('servicio');
            $table->string('codigo');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida')->default('NIU');
            $table->decimal('precio', 18, 2)->default(0);
            $table->string('moneda', 3)->default('PEN');
            $table->string('afectacion_igv', 2)->default('10');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);

            $comment = 'tipo: servicio | producto | afectacion_igv: 10=Gravado, 20=Exonerado, 30=Inafecto';
            $table->comment($comment);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};