<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check distinct categories
echo "=== Categorías disponibles ===\n";
$cats = App\Models\MetricaReportada::distinct()->pluck('categoria');
foreach ($cats as $c) echo "  - $c\n";

echo "\n=== Categorías x Anio=2026 Mes=4 Seccion=ZONA tipo=total_seccion ===\n";
$rows = App\Models\MetricaReportada::where('anio', 2026)->where('mes', 4)->where('seccion', 'ZONA')->where('tipo_registro', 'total_seccion')->get(['categoria','activos','salidas','retencion','rotacion']);
foreach ($rows as $r) {
    echo "  categoria={$r->categoria} activos={$r->activos} salidas={$r->salidas}\n";
}

echo "\n=== total_general ===\n";
$rows2 = App\Models\MetricaReportada::where('anio', 2026)->where('mes', 4)->where('seccion', 'TOTAL')->where('tipo_registro', 'total_general')->get(['categoria','activos']);
foreach ($rows2 as $r) {
    echo "  categoria={$r->categoria} activos={$r->activos}\n";
}
