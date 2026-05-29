<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;

echo "=== Columnas de empleados ===\n";
$cols = Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('empleados');
echo implode(', ', $cols) . "\n";

echo "\n=== Columnas de contratos ===\n";
$cols2 = Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('contratos');
echo implode(', ', $cols2) . "\n";

echo "\n=== Puestos activos (fin Abr 2026) ===\n";
$endDate = Carbon::create(2026, 4, 1)->endOfMonth();
$rows = App\Models\Contrato::with('empleado')
    ->where('fecha_ingreso', '<=', $endDate)
    ->where(function($q) use ($endDate) {
        $q->whereNull('fecha_egreso')->orWhere('fecha_egreso', '>', $endDate);
    })
    ->selectRaw('puesto, count(*) as c')
    ->groupBy('puesto')
    ->orderByDesc('c')
    ->get();
foreach ($rows as $r) echo "  [{$r->puesto}] = {$r->c}\n";

echo "\n=== Headcount cierre Dic 2025 (MetricaReportada) ===\n";
$yd = App\Models\MetricaReportada::where('anio', 2025)->where('mes', 12)
    ->where('seccion', 'ZONA')->where('tipo_registro', 'total_seccion')->where('categoria', 'TOTAL')->first();
echo "  YE 2025 ZONA: " . ($yd ? $yd->activos : 'N/A') . "\n";

$yd2 = App\Models\MetricaReportada::where('anio', 2025)->where('mes', 12)
    ->where('seccion', 'TOTAL')->where('tipo_registro', 'total_general')->where('categoria', 'TOTAL')->first();
echo "  YE 2025 TOTAL: " . ($yd2 ? $yd2->activos : 'N/A') . "\n";

echo "\n=== Campos PCD/discapacidad en empleados ===\n";
$sample = App\Models\Empleado::first();
if ($sample) {
    foreach ($sample->toArray() as $k => $v) {
        if (stripos($k,'disc') !== false || stripos($k,'pcd') !== false || stripos($k,'cap') !== false) {
            echo "  $k = $v\n";
        }
    }
    echo "  (muestra completa campos): " . implode(', ', array_keys($sample->toArray())) . "\n";
}
