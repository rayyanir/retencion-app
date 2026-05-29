<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encuestas_engagement', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('anio');
            $table->tinyInteger('mes')->nullable(); // null = aplica a todo el periodo
            $table->string('codigo_local', 20);
            $table->string('nombre_local', 200)->nullable();
            $table->decimal('compromiso', 5, 2)->nullable()->comment('0-100%');
            $table->decimal('experiencia', 5, 2)->nullable()->comment('0-100%');
            $table->decimal('intencion_permanecer', 5, 2)->nullable()->comment('0-100%');
            $table->integer('n_encuestados')->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['anio', 'mes', 'codigo_local'], 'unique_engagement');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas_engagement');
    }
};
