<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Contrato;
use App\Models\MetricaOperativa;
use App\Models\Tienda;
use Carbon\Carbon;

class ProductivityChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Productividad & Volumen de Transacciones';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'md';

    protected function getData(): array
    {
        $anio = intval($this->filters['anio'] ?? 2026);
        $tiendaId = $this->filters['tienda_id'] ?? null;

        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        $transaccionesData = [];
        $productividadData = [];

        // Preload contracts for optimization
        $contracts = Contrato::query()
            ->whereYear('fecha_ingreso', '<=', $anio)
            ->get();

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($anio, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            if ($startDate->isAfter(Carbon::now())) {
                $transaccionesData[] = null;
                $productividadData[] = null;
                continue;
            }

            // Headcount
            if ($tiendaId) {
                $hc = $contracts->filter(function ($c) use ($endDate, $tiendaId) {
                    return $c->tienda_id == $tiendaId && 
                           $c->fecha_ingreso->lte($endDate) && 
                           (is_null($c->fecha_egreso) || Carbon::parse($c->fecha_egreso)->gt($endDate));
                })->count();
            } else {
                $hc = $contracts->filter(function ($c) use ($endDate) {
                    return $c->fecha_ingreso->lte($endDate) && 
                           (is_null($c->fecha_egreso) || Carbon::parse($c->fecha_egreso)->gt($endDate));
                })->count();
            }

            // Transacciones
            $transQuery = MetricaOperativa::query()->where('mes', $month)->where('anio', $anio);
            if ($tiendaId) {
                $transQuery->where('tienda_id', $tiendaId);
            }
            $trans = $transQuery->sum('transacciones');
            $transaccionesData[] = $trans > 0 ? $trans : null;

            // Productivity (Transacciones / Headcount)
            $prod = ($hc > 0 && $trans > 0) ? round($trans / $hc, 0) : null;
            $productividadData[] = $prod;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Volumen de Transacciones (Barras)',
                    'data' => $transaccionesData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.25)', // KFC Red translucent
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 1,
                    'type' => 'bar',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Productividad (Trans / Empleado Lineal)',
                    'data' => $productividadData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)', // Blue line
                    'borderWidth' => 3,
                    'type' => 'line',
                    'yAxisID' => 'y1',
                    'tension' => 0.25,
                    'fill' => false,
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Transacciones (Eje Izquierdo)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false, // Prevents duplicate grid lines overlaying
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Productividad (Eje Derecho)',
                        'font' => [
                            'weight' => 'bold',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Root type must be bar to support mixed charts
    }
}
