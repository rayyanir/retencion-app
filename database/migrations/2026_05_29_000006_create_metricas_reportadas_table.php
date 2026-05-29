<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metricas_reportadas', function (Blueprint $table) {
            $table->id();
            $table->string('categoria'); // TOTAL, ASOCIADOS, ADM
            $table->integer('anio');
            $table->integer('mes');
            $table->string('seccion'); // ZONA, OPERACIONES, PLANTA, CAR, TOTAL
            $table->integer('zona_num')->nullable();
            $table->string('codigo')->nullable(); // Short code like "01", "94", "TOTAL"
            $table->string('nombre'); // Shop or region name
            $table->string('tipo_registro'); // tienda, total_zona, total_seccion, total_general
            $table->integer('activos')->default(0);
            $table->integer('salidas')->default(0);
            $table->double('rotacion')->default(0);
            $table->integer('salidas_0_12')->default(0);
            $table->double('retencion')->default(0);
            $table->integer('ingresos')->default(0);
            $table->timestamps();

            // Indexing for faster queries
            $table->index(['categoria', 'anio', 'mes']);
            $table->index(['seccion', 'tipo_registro']);
            $table->index(['codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metricas_reportadas');
    }
};
