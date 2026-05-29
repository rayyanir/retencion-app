<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tienda = App\Models\Tienda::where('codigo_corto', 'K01')->first();
$mo = App\Models\MetricaOperativa::where('tienda_id', $tienda->id)->where('anio', 2024)->where('mes', 2)->first();
echo json_encode($mo);
