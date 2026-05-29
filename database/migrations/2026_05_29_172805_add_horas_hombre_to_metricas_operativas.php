<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('metricas_operativas', function (Blueprint $table) {
            $table->decimal('horas_hombre', 10, 2)->nullable()->after('transacciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metricas_operativas', function (Blueprint $table) {
            $table->dropColumn('horas_hombre');
        });
    }
};
