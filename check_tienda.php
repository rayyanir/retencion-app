<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$countPlanta = App\Models\Tienda::where('cdc', 'LIKE', '%PLANTA%')->count();
$countZonas = App\Models\Tienda::where('zona_id', '!=', null)->count();
$countTotal = App\Models\Tienda::count();

echo "Tiendas con PLANTA: $countPlanta\n";
echo "Tiendas con zona_id: $countZonas\n";
echo "Total Tiendas: $countTotal\n";

// Muestrame la data de una tienda q no sea zona
$tienda = App\Models\Tienda::whereNull('zona_id')->first();
if ($tienda) {
    echo "Tienda sin zona: " . json_encode($tienda) . "\n";
} else {
    echo "Todas las tiendas tienen zona_id.\n";
}
