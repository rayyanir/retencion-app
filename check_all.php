<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$t = App\Models\Tienda::where('codigo_corto', 'K01')->first();
$all = App\Models\MetricaOperativa::where('tienda_id', $t->id)->get(['anio', 'mes', 'transacciones'])->toArray();
echo json_encode($all);
