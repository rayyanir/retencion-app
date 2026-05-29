<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$anio = 2026; $mes = 4;

echo "=== TOTAL ZONA total_seccion Abr 2026 ===\n";
$rows = App\Models\MetricaReportada::where('anio',$anio)->where('mes',$mes)
    ->where('seccion','ZONA')->where('tipo_registro','total_seccion')
    ->get(['categoria','activos','salidas','rotacion','salidas_0_12','retencion','ingresos']);
foreach($rows as $r) echo "  [{$r->categoria}] activos={$r->activos} salidas={$r->salidas} rot=".round($r->rotacion*100,2)."% ret=".round($r->retencion*100,2)."%\n";

echo "\n=== Histórico TOTAL/ZONA/total_seccion por año (mes máx) ===\n";
foreach([2024,2025,2026] as $y) {
    $maxMes = App\Models\MetricaReportada::where('anio',$y)->max('mes');
    $rec = App\Models\MetricaReportada::where('anio',$y)->where('mes',$maxMes)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->where('categoria','TOTAL')->first();
    $recAdm = App\Models\MetricaReportada::where('anio',$y)->where('mes',$maxMes)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->where('categoria','ADM')->first();
    $recMe = App\Models\MetricaReportada::where('anio',$y)->where('mes',$maxMes)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->where('categoria','ASOCIADOS')->first();
    echo "  Año $y (mes máx=$maxMes):\n";
    if($rec) echo "    TOTAL: activos={$rec->activos} rot=".round($rec->rotacion*100,2)."% ret=".round($rec->retencion*100,2)."%\n";
    if($recAdm) echo "    ADM:   activos={$recAdm->activos} rot=".round($recAdm->rotacion*100,2)."%\n";
    if($recMe)  echo "    ME:    activos={$recMe->activos} rot=".round($recMe->rotacion*100,2)."%\n";
}

echo "\n=== YTD sums Rotación/Retención 2026 hasta mes=4 (ZONA/TOTAL) ===\n";
foreach(['TOTAL','ADM','ASOCIADOS'] as $cat) {
    $salidas = App\Models\MetricaReportada::where('categoria',$cat)->where('anio',2026)->where('mes','<=',4)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->sum('salidas');
    $sal012  = App\Models\MetricaReportada::where('categoria',$cat)->where('anio',2026)->where('mes','<=',4)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->sum('salidas_0_12');
    $activos = App\Models\MetricaReportada::where('categoria',$cat)->where('anio',2026)->where('mes',4)
        ->where('seccion','ZONA')->where('tipo_registro','total_seccion')->value('activos') ?? 0;
    $rot = $activos > 0 ? round(($salidas/$activos)*100,2) : 0;
    $ret = $salidas > 0 ? round((($salidas-$sal012)/$salidas)*100,2) : 100;
    echo "  $cat: salidas=$salidas sal012=$sal012 activos=$activos => rot=$rot% ret=$ret%\n";
}
