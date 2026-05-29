<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metricas_operativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tienda_id')->constrained('tiendas')->onDelete('cascade');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->decimal('participacion_encuesta', 5, 4)->nullable();
            $table->decimal('compromiso_encuesta', 5, 4)->nullable();
            $table->integer('transacciones')->default(0);
            $table->timestamps();
            
            $table->unique(['tienda_id', 'mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metricas_operativas');
    }
};
