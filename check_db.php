<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Years: " . json_encode(App\Models\MetricaReportada::distinct()->pluck('anio')) . "\n";
echo "Count by Year:\n";
foreach (App\Models\MetricaReportada::select('anio', Illuminate\Support\Facades\DB::raw('count(*) as c'))->groupBy('anio')->get() as $row) {
    echo "  Year {$row->anio}: {$row->c} records\n";
}
