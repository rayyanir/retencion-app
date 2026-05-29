<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

App\Models\MetricaOperativa::truncate();
echo "Truncated metricas_operativas.\n";

Artisan::call('kfc:import', ['--file' => 'C:\retencion kfc\DATA PARA RAY.xlsx']);
echo "Import finished.\n";

$t = App\Models\Tienda::where('codigo_corto', 'K01')->first();
$all = App\Models\MetricaOperativa::where('tienda_id', $t->id)->get(['anio', 'mes', 'transacciones'])->toArray();
echo json_encode($all);
