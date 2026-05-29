<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiendas', function (Blueprint $table) {
            $table->id();
            $table->string('cdc')->unique()->comment('Centro de Costos, ej: KFC - 001 LOS CORTIJOS');
            $table->string('codigo_corto')->nullable()->comment('Ej: K01');
            $table->string('tipo')->nullable()->comment('Ej: FC, FS, IL');
            $table->foreignId('zona_id')->constrained('zonas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiendas');
    }
};
