<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->string('empleado_id');
            $table->foreign('empleado_id')->references('cedula')->on('empleados')->onDelete('cascade');
            $table->foreignId('tienda_id')->constrained('tiendas')->onDelete('cascade');
            $table->date('fecha_ingreso');
            $table->date('fecha_egreso')->nullable();
            $table->string('banda')->nullable()->comment('Ej: Sindicalizado, Lider, Administrador, Gerente');
            $table->string('puesto')->nullable()->comment('Ej: ASOCIADO, ENTRENADOR, SUB GERENTE DE LOCAL');
            $table->string('turno')->nullable()->comment('Ej: DIURNO, NOCTURNO');
            $table->string('status')->index()->default('ACTIVO');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
