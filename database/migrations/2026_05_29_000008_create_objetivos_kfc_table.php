<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objetivos_kfc', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('anio')->unique();
            $table->decimal('rotacion',    5, 2)->nullable()->comment('Objetivo % rotación total locales');
            $table->decimal('rgm',         5, 2)->nullable()->comment('Objetivo % rotación RGM (ADM)');
            $table->decimal('me',          5, 2)->nullable()->comment('Objetivo % rotación Miembros de Equipo');
            $table->decimal('retencion',   5, 2)->nullable()->comment('Objetivo % retención acumulada');
            $table->timestamps();
        });

        // Seed default 2026 objectives from the image
        DB::table('objetivos_kfc')->insert([
            'anio'       => 2026,
            'rotacion'   => 60.00,
            'rgm'        => 9.00,
            'me'         => 65.00,
            'retencion'  => 35.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('objetivos_kfc');
    }
};
