<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Contrato;
use App\Models\MetricaOperativa;
use App\Models\Tienda;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $anio = intval($this->filters['anio'] ?? 2026);
        $mes = intval($this->filters['mes'] ?? 4);
        $tiendaId = $this->filters['tienda_id'] ?? null;

        $startDate = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $yearStartDate = Carbon::createFromDate($anio, 1, 1)->startOfDay();

        // 1. ACTIVE HEADCOUNT AT THE END OF THE SELECTED MONTH
        $headcountQuery = Contrato::query()
            ->where('fecha_ingreso', '<=', $endDate)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('fecha_egreso')
                    ->orWhere('fecha_egreso', '>', $endDate);
            });

        if ($tiendaId) {
            $headcountQuery->where('tienda_id', $tiendaId);
        }
        $activeHeadcount = $headcountQuery->count();

        // 2. EGRESOS MTD (MONTH) AND YTD (YEAR TO DATE)
        $egresosMtdQuery = Contrato::query()
            ->whereBetween('fecha_egreso', [$startDate, $endDate]);

        $egresosYtdQuery = Contrato::query()
            ->whereBetween('fecha_egreso', [$yearStartDate, $endDate]);

        if ($tiendaId) {
            $egresosMtdQuery->where('tienda_id', $tiendaId);
            $egresosYtdQuery->where('tienda_id', $tiendaId);
        }

        $egresosMtdList = $egresosMtdQuery->get();
        $egresosYtdList = $egresosYtdQuery->get();

        $egresosMtd = $egresosMtdList->count();
        $egresosYtd = $egresosYtdList->count();

        // 3. EGRESOS 0-12 MONTHS TENURE MTD AND YTD
        $egresosMtd0_12 = $egresosMtdList->filter(function ($c) {
            $ingreso = Carbon::parse($c->fecha_ingreso);
            $egreso = Carbon::parse($c->fecha_egreso);
            return $ingreso->diffInDays($egreso) <= 365;
        })->count();

        $egresosYtd0_12 = $egresosYtdList->filter(function ($c) {
            $ingreso = Carbon::parse($c->fecha_ingreso);
            $egreso = Carbon::parse($c->fecha_egreso);
            return $ingreso->diffInDays($egreso) <= 365;
        })->count();

        // 4. CALCULATE RATES
        $rotacionMtd = $activeHeadcount > 0 ? ($egresosMtd / $activeHeadcount) : 0;
        $rotacionYtd = $activeHeadcount > 0 ? ($egresosYtd / $activeHeadcount) : 0;

        $retencionMtd = $egresosMtd > 0 ? (($egresosMtd - $egresosMtd0_12) / $egresosMtd) : 1.0;
        $retencionYtd = $egresosYtd > 0 ? (($egresosYtd - $egresosYtd0_12) / $egresosYtd) : 1.0;

        // 5. PRODUCTIVIDAD (TRANSACCIONES / HEADCOUNT)
        // Local transactions
        $transaccionesLocalQuery = MetricaOperativa::query()->where('mes', $mes)->where('anio', $anio);
        if ($tiendaId) {
            $transaccionesLocalQuery->where('tienda_id', $tiendaId);
        }
        $transaccionesLocal = $transaccionesLocalQuery->sum('transacciones');

        $productividadLocal = $activeHeadcount > 0 ? ($transaccionesLocal / $activeHeadcount) : 0;

        // Chain average transactions for comparison
        $transaccionesChain = MetricaOperativa::query()->where('mes', $mes)->where('anio', $anio)->sum('transacciones');
        $headcountChain = Contrato::query()
            ->where('fecha_ingreso', '<=', $endDate)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('fecha_egreso')
                    ->orWhere('fecha_egreso', '>', $endDate);
            })
            ->count();

        $productividadChain = $headcountChain > 0 ? ($transaccionesChain / $headcountChain) : 0;

        // 6. SCORE TOTAL GENTE (YTD COMPOSITE INDEX)
        // Retention: 40% | Rotation: 40% (max 100%) | Productivity: 20% (relative to chain average)
        $scoreRetention = $retencionYtd * 40;
        $scoreRotation = max(0, 1 - $rotacionYtd) * 40;
        $ratioProductividad = $productividadChain > 0 ? ($productividadLocal / $productividadChain) : 1.0;
        $scoreProductividad = min(1.0, $ratioProductividad) * 20;

        $scoreTotalGente = round($scoreRetention + $scoreRotation + $scoreProductividad, 1);

        // Formatted outputs
        $rotacionMtdPct = round($rotacionMtd * 100, 1) . '%';
        $rotacionYtdPct = round($rotacionYtd * 100, 1) . '%';
        
        $retencionMtdPct = round($retencionMtd * 100, 1) . '%';
        $retencionYtdPct = round($retencionYtd * 100, 1) . '%';

        // Demographics helper: Femenino / Masculino %
        $genderQuery = Contrato::query()
            ->where('fecha_ingreso', '<=', $endDate)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('fecha_egreso')
                    ->orWhere('fecha_egreso', '>', $endDate);
            });
        if ($tiendaId) {
            $genderQuery->where('tienda_id', $tiendaId);
        }
        $genderContracts = $genderQuery->with('empleado')->get();
        $femaleCount = $genderContracts->filter(fn($c) => $c->empleado->sexo === 'Femenino')->count();
        $totalGender = $genderContracts->count();
        $femalePct = $totalGender > 0 ? round(($femaleCount / $totalGender) * 100, 1) . '%' : '0%';
        $malePct = $totalGender > 0 ? round((($totalGender - $femaleCount) / $totalGender) * 100, 1) . '%' : '0%';

        return [
            Stat::make('Rotación de Personal', $rotacionMtdPct)
                ->description("YTD: $rotacionYtdPct | Egresos: $egresosMtd en el mes")
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($rotacionMtd > 0.1 ? 'danger' : ($rotacionMtd > 0.05 ? 'warning' : 'success')),

            Stat::make('Retención de Personal', $retencionMtdPct)
                ->description("YTD: $retencionYtdPct | 0-12m: $egresosMtd0_12 salidas")
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($retencionMtd < 0.7 ? 'danger' : ($retencionMtd < 0.85 ? 'warning' : 'success')),

            Stat::make('Productividad (Trans. / Gente)', round($productividadLocal, 0))
                ->description($tiendaId 
                    ? "Chain Prom: " . round($productividadChain, 0) . " (" . round($ratioProductividad * 100, 0) . "% vs Cadena)"
                    : "Promedio Cadena global")
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color($ratioProductividad >= 1.0 ? 'success' : ($ratioProductividad >= 0.85 ? 'warning' : 'danger')),

            Stat::make('Total Gente Score', $scoreTotalGente . ' pts')
                ->description("Dotación: $activeHeadcount | Mujeres: $femalePct, Hombres: $malePct")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($scoreTotalGente >= 85 ? 'success' : ($scoreTotalGente >= 70 ? 'warning' : 'danger')),
        ];
    }
}
