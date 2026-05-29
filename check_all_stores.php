<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\MetricaOperativa::where('anio', 2024)->where('mes', 2)->get(['tienda_id', 'transacciones'])->toArray();
echo json_encode($all);
