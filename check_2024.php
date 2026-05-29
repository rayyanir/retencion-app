<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$anio = 2024;
$mes = 2;
$m = App\Models\MetricaReportada::where('anio', $anio)
    ->where('mes', $mes)
    ->where('categoria', 'TOTAL')
    ->where('seccion', 'TOTAL')
    ->where('tipo_registro', 'total_general')
    ->first();
echo json_encode($m);

$ho = App\Models\MetricaOperativa::where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
echo "\nHoras hombre totales: " . $ho;

$t = App\Models\MetricaOperativa::where('anio', $anio)->where('mes', $mes)->sum('transacciones');
echo "\nTransacciones totales: " . $t;
