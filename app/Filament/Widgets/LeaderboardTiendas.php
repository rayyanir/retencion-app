<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Tienda;
use App\Models\Contrato;
use App\Models\MetricaOperativa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardTiendas extends Widget
{
    use InteractsWithPageFilters;

    protected static string $view = 'filament.widgets.leaderboard-tiendas';

    protected static ?int $sort = 2;

    // Make the widget full width
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $anio = intval($this->filters['anio'] ?? 2026);
        $mes = intval($this->filters['mes'] ?? 4);

        $startDate = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $yearStartDate = Carbon::createFromDate($anio, 1, 1)->startOfDay();

        // 1. Fetch active headcount grouped by tienda_id
        $activeCounts = Contrato::query()
            ->where('fecha_ingreso', '<=', $endDate)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('fecha_egreso')
                    ->orWhere('fecha_egreso', '>', $endDate);
            })
            ->select('tienda_id', DB::raw('count(*) as count'))
            ->groupBy('tienda_id')
            ->pluck('count', 'tienda_id')
            ->toArray();

        // 2. Fetch egresos YTD grouped by tienda_id
        $egresosCounts = Contrato::query()
            ->whereBetween('fecha_egreso', [$yearStartDate, $endDate])
            ->select('tienda_id', DB::raw('count(*) as count'))
            ->groupBy('tienda_id')
            ->pluck('count', 'tienda_id')
            ->toArray();

        // 3. Fetch egresos YTD 0-12 grouped by tienda_id
        $egresosYtdAll = Contrato::query()
            ->whereBetween('fecha_egreso', [$yearStartDate, $endDate])
            ->get();

        $egresos0_12Counts = $egresosYtdAll->filter(function ($c) {
            $ingreso = Carbon::parse($c->fecha_ingreso);
            $egreso = Carbon::parse($c->fecha_egreso);
            return $ingreso->diffInDays($egreso) <= 365;
        })
        ->groupBy('tienda_id')
        ->map(fn($group) => $group->count())
        ->toArray();

        // 4. Fetch transactions grouped by tienda_id
        $transaccionesCounts = MetricaOperativa::query()
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->pluck('transacciones', 'tienda_id')
            ->toArray();

        // Calculate chain averages
        $totalHeadcount = array_sum($activeCounts);
        $totalTransacciones = array_sum($transaccionesCounts);
        $chainProductivity = $totalHeadcount > 0 ? ($totalTransacciones / $totalHeadcount) : 0;

        // Load all stores except the generic one
        $tiendas = Tienda::where('codigo_corto', '!=', 'KGEN')->get();

        $leaderboard = [];

        foreach ($tiendas as $tienda) {
            $hc = $activeCounts[$tienda->id] ?? 0;
            $eg = $egresosCounts[$tienda->id] ?? 0;
            $eg0_12 = $egresos0_12Counts[$tienda->id] ?? 0;
            $tr = $transaccionesCounts[$tienda->id] ?? 0;

            $rotacion = $hc > 0 ? ($eg / $hc) : 0;
            $retencion = $eg > 0 ? (($eg - $eg0_12) / $eg) : 1.0;
            $productivity = $hc > 0 ? ($tr / $hc) : 0;

            // Score calculation
            $scoreRetention = $retencion * 40;
            $scoreRotation = max(0, 1 - $rotacion) * 40;
            $ratioProductivity = $chainProductivity > 0 ? ($productivity / $chainProductivity) : 1.0;
            $scoreProductividad = min(1.0, $ratioProductivity) * 20;

            $score = round($scoreRetention + $scoreRotation + $scoreProductividad, 1);

            $leaderboard[] = [
                'id' => $tienda->id,
                'codigo' => $tienda->codigo_corto,
                'cdc' => $tienda->cdc,
                'formato' => $tienda->tipo,
                'headcount' => $hc,
                'egresos' => $eg,
                'rotacion' => round($rotacion * 100, 1) . '%',
                'retencion' => round($retencion * 100, 1) . '%',
                'score' => $score,
            ];
        }

        // Sort by score desc
        usort($leaderboard, fn($a, $b) => $b['score'] <=> $a['score']);

        // Split into podium and rest
        $podium = array_slice($leaderboard, 0, 3);
        $rest = array_slice($leaderboard, 3);

        return [
            'podium' => $podium,
            'leaderboard' => $rest,
            'mesNombre' => $startDate->translatedFormat('F'),
            'anio' => $anio,
        ];
    }
}
