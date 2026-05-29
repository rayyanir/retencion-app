<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Contrato;
use App\Models\Tienda;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RotationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Tendencia de Rotación (Mensual vs Promedio Cadena)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'md';

    protected function getData(): array
    {
        $anio = intval($this->filters['anio'] ?? 2026);
        $tiendaId = $this->filters['tienda_id'] ?? null;

        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        $localData = [];
        $chainData = [];

        // Fetch all contracts for the selected year to optimize database calls
        $contracts = Contrato::query()
            ->whereYear('fecha_ingreso', '<=', $anio)
            ->get();

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($anio, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // If the date is in the future, don't output metrics (keep it empty for clean rendering)
            if ($startDate->isAfter(Carbon::now())) {
                $localData[] = null;
                $chainData[] = null;
                continue;
            }

            // Chain calculations
            $chainHc = $contracts->filter(function ($c) use ($endDate) {
                return $c->fecha_ingreso->lte($endDate) && 
                       (is_null($c->fecha_egreso) || Carbon::parse($c->fecha_egreso)->gt($endDate));
            })->count();

            $chainEg = $contracts->filter(function ($c) use ($startDate, $endDate) {
                return !is_null($c->fecha_egreso) && 
                       Carbon::parse($c->fecha_egreso)->gte($startDate) && 
                       Carbon::parse($c->fecha_egreso)->lte($endDate);
            })->count();

            $chainRot = $chainHc > 0 ? round(($chainEg / $chainHc) * 100, 1) : 0;
            $chainData[] = $chainRot;

            // Local calculations (if store is selected)
            if ($tiendaId) {
                $localHc = $contracts->filter(function ($c) use ($endDate, $tiendaId) {
                    return $c->tienda_id == $tiendaId && 
                           $c->fecha_ingreso->lte($endDate) && 
                           (is_null($c->fecha_egreso) || Carbon::parse($c->fecha_egreso)->gt($endDate));
                })->count();

                $localEg = $contracts->filter(function ($c) use ($startDate, $endDate, $tiendaId) {
                    return $c->tienda_id == $tiendaId && 
                           !is_null($c->fecha_egreso) && 
                           Carbon::parse($c->fecha_egreso)->gte($startDate) && 
                           Carbon::parse($c->fecha_egreso)->lte($endDate);
                })->count();

                $localRot = $localHc > 0 ? round(($localEg / $localHc) * 100, 1) : 0;
                $localData[] = $localRot;
            }
        }

        $datasets = [];

        if ($tiendaId) {
            $tiendaName = str_replace('KFC - ', '', Tienda::find($tiendaId)?->cdc ?? 'Local');
            $datasets[] = [
                'label' => "Rotación en $tiendaName (%)",
                'data' => $localData,
                'backgroundColor' => 'rgba(239, 68, 68, 0.8)', // KFC Red
                'borderColor' => 'rgb(239, 68, 68)',
                'borderWidth' => 3,
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        $datasets[] = [
            'label' => 'Promedio de la Cadena (%)',
            'data' => $chainData,
            'backgroundColor' => 'rgba(156, 163, 175, 0.2)',
            'borderColor' => 'rgb(156, 163, 175)',
            'borderWidth' => 2,
            'borderDash' => [5, 5],
            'fill' => true,
            'tension' => 0.3,
        ];

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
