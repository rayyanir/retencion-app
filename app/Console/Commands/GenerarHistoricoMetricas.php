<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contrato;
use App\Models\Tienda;
use App\Models\MetricaReportada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerarHistoricoMetricas extends Command
{
    protected $signature = 'kfc:generar-historico';
    protected $description = 'Genera las MetricasReportadas históricas mes a mes usando la tabla Contratos';

    public function handle()
    {
        $this->info("Iniciando generación de métricas históricas...");
        
        $startYear = 2024;
        $endYear = 2026;
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        DB::beginTransaction();
        try {
            for ($year = $startYear; $year <= $endYear; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    if ($year > $currentYear || ($year == $currentYear && $month > $currentMonth)) {
                        continue;
                    }

                    $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
                    $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

                    $this->info("Procesando $year-$month...");
                    
                    // Solo para ZONA y TOTAL por ahora, y categorias TOTAL, ASOCIADOS, ADM
                    $tiendas = Tienda::all();
                    
                    $categorias = ['TOTAL', 'ASOCIADOS', 'ADM'];
                    
                    foreach ($categorias as $cat) {
                        $totalCadenaActivos = 0;
                        $totalCadenaSalidas = 0;
                        $totalCadenaSalidas012 = 0;

                        foreach ($tiendas as $tienda) {
                            $queryActivos = Contrato::where('tienda_id', $tienda->id)
                                ->where('fecha_ingreso', '<=', $endOfMonth)
                                ->where(function($q) use ($endOfMonth) {
                                    $q->whereNull('fecha_egreso')
                                      ->orWhere('fecha_egreso', '>', $endOfMonth);
                                });
                            
                            $querySalidas = Contrato::where('tienda_id', $tienda->id)
                                ->whereBetween('fecha_egreso', [$startOfMonth, $endOfMonth]);
                                
                            if ($cat === 'ASOCIADOS') {
                                $queryActivos->where('banda', 'Asociado');
                                $querySalidas->where('banda', 'Asociado');
                            } elseif ($cat === 'ADM') {
                                $queryActivos->where('banda', '!=', 'Asociado');
                                $querySalidas->where('banda', '!=', 'Asociado');
                            }

                            $activos = $queryActivos->count();
                            
                            $salidasList = $querySalidas->get();
                            $salidas = $salidasList->count();
                            
                            $salidas012 = 0;
                            foreach ($salidasList as $s) {
                                if ($s->fecha_ingreso) {
                                    $diff = Carbon::parse($s->fecha_ingreso)->diffInDays(Carbon::parse($s->fecha_egreso));
                                    if ($diff <= 90) {
                                        $salidas012++;
                                    }
                                }
                            }

                            $totalCadenaActivos += $activos;
                            $totalCadenaSalidas += $salidas;
                            $totalCadenaSalidas012 += $salidas012;

                            $rotacion = $activos > 0 ? ($salidas / $activos) : 0;
                            $retencion = $salidas > 0 ? (($salidas - $salidas012) / $salidas) : 1;

                            // Crear metrica tienda
                            MetricaReportada::updateOrCreate([
                                'anio' => $year,
                                'mes' => $month,
                                'codigo' => $tienda->codigo_corto,
                                'categoria' => $cat,
                                'seccion' => 'ZONA', // simplificado
                                'tipo_registro' => 'tienda'
                            ], [
                                'nombre' => $tienda->cdc,
                                'activos' => $activos,
                                'salidas' => $salidas,
                                'salidas_0_12' => $salidas012,
                                'rotacion' => $rotacion,
                                'retencion' => $retencion
                            ]);
                        }

                        // Total general
                        $rotacionTotal = $totalCadenaActivos > 0 ? ($totalCadenaSalidas / $totalCadenaActivos) : 0;
                        $retencionTotal = $totalCadenaSalidas > 0 ? (($totalCadenaSalidas - $totalCadenaSalidas012) / $totalCadenaSalidas) : 1;

                        MetricaReportada::updateOrCreate([
                            'anio' => $year,
                            'mes' => $month,
                            'codigo' => 'TOTAL',
                            'categoria' => $cat,
                            'seccion' => 'TOTAL',
                            'tipo_registro' => 'total_general'
                        ], [
                            'nombre' => 'TOTAL GENERAL',
                            'activos' => $totalCadenaActivos,
                            'salidas' => $totalCadenaSalidas,
                            'salidas_0_12' => $totalCadenaSalidas012,
                            'rotacion' => $rotacionTotal,
                            'retencion' => $retencionTotal
                        ]);
                    }
                }
            }
            DB::commit();
            $this->info("Historicos generados correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
