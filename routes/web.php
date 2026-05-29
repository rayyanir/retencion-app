<?php

use Illuminate\Support\Facades\Route;
use App\Models\Contrato;
use App\Models\Tienda;
use App\Models\MetricaOperativa;
use App\Models\MetricaReportada;
use App\Models\EncuestaEngagement;
use App\Models\ObjetivoKfc;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

// Redirect /login to Filament login
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Custom logout route
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
})->name('logout');

// Protected application routes
Route::middleware(['auth'])->group(function () {

    // 1. Dashboard principal
    Route::get('/', function (Request $request) {
        // Fetch available years from DB (fallback to [2026, 2025, 2024] if empty)
        $disponiblesAnios = MetricaReportada::distinct()->orderBy('anio', 'desc')->pluck('anio')->toArray();
        if (empty($disponiblesAnios)) {
            $disponiblesAnios = [2026, 2025, 2024];
        }

        $anio = intval($request->input('anio', reset($disponiblesAnios)));

        // Fetch available months for the selected year
        $disponiblesMeses = MetricaReportada::where('anio', $anio)->distinct()->orderBy('mes')->pluck('mes')->toArray();
        if (empty($disponiblesMeses)) {
            $disponiblesMeses = range(1, 12);
        }

        $mes = intval($request->input('mes', end($disponiblesMeses)));
        if (!in_array($mes, $disponiblesMeses)) {
            $mes = end($disponiblesMeses);
        }

        $categoria = $request->input('categoria', 'TOTAL'); // TOTAL, ASOCIADOS, ADM
        $seccion = $request->input('seccion', 'ZONA'); // ZONA, OPERACIONES, PLANTA, CAR
        $codigo = $request->input('codigo', 'TOTAL'); // TOTAL, specific store code, or zone total name

        $startDate = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $mesNombre = $startDate->translatedFormat('F');

        // Dynamic store dropdown list for the selected filters
        $localesList = MetricaReportada::query()
            ->where('categoria', $categoria)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', $seccion)
            ->whereIn('tipo_registro', ['tienda', 'total_zona'])
            ->orderBy('tipo_registro', 'desc')
            ->orderBy('nombre')
            ->get(['codigo', 'nombre', 'tipo_registro']);

        // Find selected store metric
        $metricaQuery = MetricaReportada::query()
            ->where('categoria', $categoria)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', $seccion);

        if ($codigo === 'TOTAL') {
            $metricaQuery->where('tipo_registro', 'total_seccion');
        } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
            $metricaQuery->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
        } else {
            $metricaQuery->where('tipo_registro', 'tienda')->where('codigo', $codigo);
        }

        $metrica = $metricaQuery->first();

        // Fallback in case table has no entries
        if (!$metrica) {
            $metrica = (object)[
                'activos' => 0,
                'salidas' => 0,
                'rotacion' => 0.0,
                'salidas_0_12' => 0,
                'retencion' => 1.0,
                'ingresos' => 0,
                'nombre' => $codigo === 'TOTAL' ? 'TOTAL ' . $seccion : $codigo
            ];
        }

        $activeHeadcount = $metrica->activos;
        $egresosMtd = $metrica->salidas;
        $rotacionMtd = $metrica->rotacion;
        $retencionMtd = $metrica->retencion;

        // Headcount breakdown by category band (Asociados, ADM/GTES)
        $dotacionBandas = [];
        $categoriasBandas = [
            'ASOCIADOS' => 'Asociados',
            'ADM'       => 'Adm / Ger.',
        ];
        foreach ($categoriasBandas as $catKey => $catLabel) {
            $bQuery = MetricaReportada::query()
                ->where('categoria', $catKey)
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where('seccion', $seccion);

            if ($codigo === 'TOTAL') {
                $bQuery->where('tipo_registro', 'total_seccion');
            } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                $bQuery->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
            } else {
                $bQuery->where('tipo_registro', 'tienda')->where('codigo', $codigo);
            }

            $bRec = $bQuery->first();
            $dotacionBandas[$catKey] = [
                'label'   => $catLabel,
                'activos' => $bRec ? $bRec->activos : 0,
            ];
        }

        // Calculate YTD sums (months 1 to $mes)
        $ytdQuery = MetricaReportada::query()
            ->where('categoria', $categoria)
            ->where('anio', $anio)
            ->where('mes', '<=', $mes)
            ->where('seccion', $seccion);

        if ($codigo === 'TOTAL') {
            $ytdQuery->where('tipo_registro', 'total_seccion');
        } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
            $ytdQuery->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
        } else {
            $ytdQuery->where('tipo_registro', 'tienda')->where('codigo', $codigo);
        }

        $egresosYtd = $ytdQuery->sum('salidas');
        $egresos0_12Ytd = $ytdQuery->sum('salidas_0_12');

        // YTD Rotation: sum(salidas YTD) / assets at month-end
        $rotacionYtd = $activeHeadcount > 0 ? ($egresosYtd / $activeHeadcount) : 0.0;

        // YTD Retention: (egresos YTD - egresos 0-12 YTD) / egresos YTD
        $retencionYtd = $egresosYtd > 0 ? (($egresosYtd - $egresos0_12Ytd) / $egresosYtd) : 1.0;

        // Productivity (Transactions and Horas Hombre from metricas_operativas)
        $transaccionesLocal = 0;
        $horasHombreLocal = 0;
        if ($seccion === 'ZONA') {
            if ($codigo === 'TOTAL') {
                $transaccionesLocal = MetricaOperativa::query()->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
                $horasHombreLocal = MetricaOperativa::query()->where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
            } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                $zonaNum = intval(filter_var($codigo, FILTER_SANITIZE_NUMBER_INT));
                $tiendaIds = Tienda::where('zona_id', $zonaNum)->pluck('id');
                $transaccionesLocal = MetricaOperativa::query()->whereIn('tienda_id', $tiendaIds)->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
                $horasHombreLocal = MetricaOperativa::query()->whereIn('tienda_id', $tiendaIds)->where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
            } else {
                $codigoCorto = 'K' . sprintf('%02d', intval($codigo));
                $tiendaObj = Tienda::where('codigo_corto', $codigoCorto)->first();
                if ($tiendaObj) {
                    $transaccionesLocal = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
                    $horasHombreLocal = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
                }
            }
        }

        $productividadLocal = $horasHombreLocal > 0 ? ($transaccionesLocal / $horasHombreLocal) : 0;

        // Chain Productivity (Total General)
        $chainMetrica = MetricaReportada::query()
            ->where('categoria', 'TOTAL')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', 'TOTAL')
            ->where('tipo_registro', 'total_general')
            ->first();
        $chainHeadcount = $chainMetrica ? $chainMetrica->activos : 0;
        $transaccionesChain = MetricaOperativa::query()->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
        $horasHombreChain = MetricaOperativa::query()->where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
        $productividadChain = $horasHombreChain > 0 ? ($transaccionesChain / $horasHombreChain) : 0;

        // Total Gente Score
        $scoreRetention = $retencionYtd * 40;
        $scoreRotation = max(0.0, 1.0 - $rotacionYtd) * 40;
        $ratioProductividad = $productividadChain > 0 ? ($productividadLocal / $productividadChain) : 1.0;
        $scoreProductividad = min(1.0, $ratioProductividad) * 20;
        $scoreTotalGente = round($scoreRetention + $scoreRotation + $scoreProductividad, 1);

        // Identify which Tiendas apply based on seccion and codigo
        $tQuery = \App\Models\Tienda::query();
        if ($codigo === 'TOTAL') {
            if ($seccion === 'ZONA') {
                $tQuery->whereNotNull('zona_id')->where('zona_id', '<=', 6);
            } elseif ($seccion === 'PLANTA') {
                $tQuery->where('cdc', 'like', '%PLANTA%');
            } elseif ($seccion === 'CAR') {
                $tQuery->where('cdc', 'like', '%CAR%')->orWhere('cdc', 'like', '%C.A.R%');
            } elseif ($seccion === 'OPERACIONES') {
                $tQuery->where('cdc', 'like', '%OPERACIONES%');
            }
        } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
            $zonaId = intval(str_replace('TOTAL ZONA ', '', $codigo));
            $tQuery->where('zona_id', $zonaId);
        } else {
            $tQuery->where(function($q) use ($codigo) {
                $q->where('codigo_corto', 'K' . sprintf('%02d', intval($codigo)))
                  ->orWhere('codigo_corto', $codigo);
            });
        }
        $tiendaIds = $tQuery->pluck('id')->toArray();

        $catFilter = function($q) use ($categoria) {
            if ($categoria === 'ASOCIADOS') {
                $q->whereIn('banda', ['Asociado', 'Sindicalizado', 'Discapacidad', 'Inces', 'Lider / Entrenador', 'Pasante Universitario']);
            } elseif ($categoria === 'ADM') {
                $q->whereNotIn('banda', ['Asociado', 'Sindicalizado', 'Discapacidad', 'Inces', 'Lider / Entrenador', 'Pasante Universitario']);
            }
        };

        $baseContratoQuery = function() use ($startDate, $tiendaIds, $catFilter) {
            $q = Contrato::query()
                ->where('fecha_ingreso', '<=', $startDate->copy()->endOfMonth())
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('fecha_egreso')->orWhere('fecha_egreso', '>', $startDate->copy()->endOfMonth());
                });
            if (!empty($tiendaIds)) {
                $q->whereIn('tienda_id', $tiendaIds);
            } else {
                $q->whereRaw('1 = 0');
            }
            $catFilter($q);
            return $q;
        };

        // Recalculate chainHeadcount based on raw query to match the filtered context
        $chainHeadcount = $baseContratoQuery()->count();

        // Gender stats (from raw database for matching context)
        $femaleCount = $baseContratoQuery()
            ->whereHas('empleado', fn($q) => $q->where('sexo', 'Femenino'))
            ->count();
        $femalePct = $chainHeadcount > 0 ? round(($femaleCount / $chainHeadcount) * 100, 1) : 0;
        $malePct   = $chainHeadcount > 0 ? round((($chainHeadcount - $femaleCount) / $chainHeadcount) * 100, 1) : 0;

        // PCD count
        $pcdCount = $baseContratoQuery()
            ->whereHas('empleado', fn($q) => $q->where('pcd', true))
            ->count();
        $pcdPct = $chainHeadcount > 0 ? round(($pcdCount / $chainHeadcount) * 100, 1) : 0;

        // Puesto breakdown for pie chart (top roles + "Otros")
        $puestosRaw = $baseContratoQuery()
            ->selectRaw('puesto, count(*) as c')
            ->groupBy('puesto')
            ->orderByDesc('c')
            ->get();

        $topPuestos = [];
        $otrosCount = 0;
        $puestoColors = ['#dc2626','#f59e0b','#10b981','#3b82f6','#8b5cf6','#06b6d4','#f97316'];
        $colorIdx = 0;
        foreach ($puestosRaw as $idx => $p) {
            if ($idx < 6) {
                $topPuestos[] = [
                    'label' => ucwords(strtolower($p->puesto)),
                    'count' => $p->c,
                    'pct'   => $chainHeadcount > 0 ? round(($p->c / $chainHeadcount) * 100, 1) : 0,
                    'color' => $puestoColors[$colorIdx++] ?? '#94a3b8',
                ];
            } else {
                $otrosCount += $p->c;
            }
        }
        if ($otrosCount > 0) {
            $topPuestos[] = [
                'label' => 'Otros',
                'count' => $otrosCount,
                'pct'   => $chainHeadcount > 0 ? round(($otrosCount / $chainHeadcount) * 100, 1) : 0,
                'color' => '#94a3b8',
            ];
        }

        // Previous year-end headcount (TOTAL chain at Dec of prev year)
        $prevYear = $anio - 1;
        $prevYeRec = MetricaReportada::where('anio', $prevYear)->where('mes', 12)
            ->where('seccion', 'TOTAL')->where('tipo_registro', 'total_general')->where('categoria', 'TOTAL')
            ->first();
        $prevYeHeadcount = $prevYeRec ? $prevYeRec->activos : null;

        // Engagement data (Compromiso, Experiencia, Intención de Permanecer) — solo para locales individuales
        $engagementData = null;
        $esLocalIndividual = ($codigo !== 'TOTAL' && !str_starts_with($codigo, 'TOTAL ZONA '));
        if ($esLocalIndividual) {
            // Try exact anio+mes first, then any record for that year, then most recent overall
            $engagementData = EncuestaEngagement::where('codigo_local', $codigo)
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->first();

            if (!$engagementData) {
                $engagementData = EncuestaEngagement::where('codigo_local', $codigo)
                    ->where('anio', $anio)
                    ->orderByDesc('mes')
                    ->first();
            }

            if (!$engagementData) {
                $engagementData = EncuestaEngagement::where('codigo_local', $codigo)
                    ->orderByDesc('anio')
                    ->orderByDesc('mes')
                    ->first();
            }
        }

        // Leaderboard calculation within selected section
        $tiendasReportadas = MetricaReportada::query()
            ->where('categoria', $categoria)
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', $seccion)
            ->where('tipo_registro', 'tienda')
            ->get();

        $leaderboard = [];
        foreach ($tiendasReportadas as $trItem) {
            $tYtd = MetricaReportada::query()
                ->where('categoria', $categoria)
                ->where('anio', $anio)
                ->where('mes', '<=', $mes)
                ->where('seccion', $seccion)
                ->where('tipo_registro', 'tienda')
                ->where('codigo', $trItem->codigo);

            $tEgresosYtd = $tYtd->sum('salidas');
            $tEgresos0_12Ytd = $tYtd->sum('salidas_0_12');

            $tRot = $trItem->activos > 0 ? ($tEgresosYtd / $trItem->activos) : 0.0;
            $tRet = $tEgresosYtd > 0 ? (($tEgresosYtd - $tEgresos0_12Ytd) / $tEgresosYtd) : 1.0;

            // Productivity
            $tTrans = 0;
            $tHH = 0;
            if ($seccion === 'ZONA') {
                $codigoCorto = 'K' . sprintf('%02d', intval($trItem->codigo));
                $tiendaObj = Tienda::where('codigo_corto', $codigoCorto)->first();
                if ($tiendaObj) {
                    $tTrans = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
                    $tHH = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $anio)->where('mes', $mes)->sum('horas_hombre');
                }
            }
            $tProd = $tHH > 0 ? ($tTrans / $tHH) : 0;

            $tScoreRet = $tRet * 40;
            $tScoreRot = max(0.0, 1.0 - $tRot) * 40;
            $tRatioProd = $productividadChain > 0 ? ($tProd / $productividadChain) : 1.0;
            $tScoreProd = min(1.0, $tRatioProd) * 20;

            $tScore = round($tScoreRet + $tScoreRot + $tScoreProd, 1);

            $leaderboard[] = [
                'codigo' => $trItem->codigo,
                'cdc' => $trItem->nombre,
                'headcount' => $trItem->activos,
                'egresos' => $tEgresosYtd,
                'rotacion' => round($tRot * 100, 1) . '%',
                'retencion' => round($tRet * 100, 1) . '%',
                'score'  => $tScore,
                'prod'   => $tProd,
                'rank'   => 0
            ];
        }

        // Leaderboard Rotación y Retención (score)
        usort($leaderboard, fn($a, $b) => $b['score'] <=> $a['score']);
        foreach ($leaderboard as $idx => &$item) {
            $item['rank'] = $idx + 1;
        }
        $podium = array_slice($leaderboard, 0, 3);
        $restLeaderboard = array_slice($leaderboard, 3);

        // Leaderboard Productividad
        $leaderboardProd = $leaderboard; // Copy base data
        usort($leaderboardProd, fn($a, $b) => $b['prod'] <=> $a['prod']);
        foreach ($leaderboardProd as $idx => &$item) {
            $item['rank_prod'] = $idx + 1;
        }

        $localRank = null;
        foreach ($leaderboard as $index => $item) {
            if ($item['codigo'] == $codigo) {
                $localRank = $index + 1;
                break;
            }
        }

        // Charts Data: Continuous history starting from the selected year up to current month (April 2026)
        $maxAnio = MetricaReportada::max('anio') ?? 2026;
        $maxMes = MetricaReportada::where('anio', $maxAnio)->max('mes') ?? 4;

        $chartLabels = [];
        $chartRotationLocal = [];
        $chartRotationChain = [];
        $chartTransactions = [];
        $chartHorasHombre = [];
        $chartProductivity = [];

        $mesesNombresCortos = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];

        for ($y = $anio; $y <= $maxAnio; $y++) {
            $startMonth = 1;
            $endMonth = ($y === $maxAnio) ? $maxMes : 12;

            for ($m = $startMonth; $m <= $endMonth; $m++) {
                // Generate Label
                $chartLabels[] = $mesesNombresCortos[$m] . ' ' . substr($y, 2);

                // Fetch local metric for month $m and year $y
                $mLocal = MetricaReportada::query()
                    ->where('categoria', $categoria)
                    ->where('anio', $y)
                    ->where('mes', $m)
                    ->where('seccion', $seccion);

                if ($codigo === 'TOTAL') {
                    $mLocal->where('tipo_registro', 'total_seccion');
                } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                    $mLocal->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
                } else {
                    $mLocal->where('tipo_registro', 'tienda')->where('codigo', $codigo);
                }

                $localRec = $mLocal->first();
                $chartRotationLocal[] = $localRec ? round($localRec->rotacion * 100, 1) : null;

                // Chain general average
                $chainRec = MetricaReportada::query()
                    ->where('categoria', 'TOTAL')
                    ->where('anio', $y)
                    ->where('mes', $m)
                    ->where('seccion', 'TOTAL')
                    ->where('tipo_registro', 'total_general')
                    ->first();
                $chartRotationChain[] = $chainRec ? round($chainRec->rotacion * 100, 1) : null;

                // Transactions & Productivity
                $mActCount = $localRec ? $localRec->activos : 0;
                $mTrans = 0;
                $mHH = 0;
                if ($seccion === 'ZONA') {
                    if ($codigo === 'TOTAL') {
                        $mTrans = MetricaOperativa::query()->where('anio', $y)->where('mes', $m)->sum('transacciones');
                        $mHH = MetricaOperativa::query()->where('anio', $y)->where('mes', $m)->sum('horas_hombre');
                    } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                        $zonaNum = intval(filter_var($codigo, FILTER_SANITIZE_NUMBER_INT));
                        $tiendaIds = Tienda::where('zona_id', $zonaNum)->pluck('id');
                        $mTrans = MetricaOperativa::query()->whereIn('tienda_id', $tiendaIds)->where('anio', $y)->where('mes', $m)->sum('transacciones');
                        $mHH = MetricaOperativa::query()->whereIn('tienda_id', $tiendaIds)->where('anio', $y)->where('mes', $m)->sum('horas_hombre');
                    } else {
                        $codigoCorto = 'K' . sprintf('%02d', intval($codigo));
                        $tiendaObj = Tienda::where('codigo_corto', $codigoCorto)->first();
                        if ($tiendaObj) {
                            $mTrans = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $y)->where('mes', $m)->sum('transacciones');
                            $mHH = MetricaOperativa::query()->where('tienda_id', $tiendaObj->id)->where('anio', $y)->where('mes', $m)->sum('horas_hombre');
                        }
                    }
                }

                $chartHorasHombre[] = $mHH > 0 ? $mHH : null;
                $chartProductivity[] = ($mHH > 0 && $mTrans > 0) ? round($mTrans / $mHH, 2) : null;
            }
        }

        $tiendaSeleccionada = ($codigo !== 'TOTAL' && !str_starts_with($codigo, 'TOTAL ZONA ')) ? (object)['nombre' => $metrica->nombre] : null;

        // ─── Sección Rotación y Retención (siempre ZONA/TOTAL/locales) ───
        $rr_anio = $anio;
        $rr_mes  = $mes;

        $getYtdMetric = function(string $cat, int $y, int $m) use ($seccion, $codigo) {
            $q = MetricaReportada::where('categoria', $cat)->where('anio', $y)
                ->where('mes', '<=', $m)->where('seccion', $seccion);

            if ($codigo === 'TOTAL') {
                $q->where('tipo_registro', 'total_seccion');
            } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                $q->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
            } else {
                $q->where('tipo_registro', 'tienda')->where('codigo', $codigo);
            }

            // Sum up to month m
            $salidas = (clone $q)->sum('salidas');
            $sal012  = (clone $q)->sum('salidas_0_12');

            // For activos, we only want the value at month m specifically
            $qActivos = MetricaReportada::where('categoria', $cat)->where('anio', $y)
                ->where('mes', $m)->where('seccion', $seccion);
            if ($codigo === 'TOTAL') {
                $qActivos->where('tipo_registro', 'total_seccion');
            } elseif (str_starts_with($codigo, 'TOTAL ZONA ')) {
                $qActivos->where('tipo_registro', 'total_zona')->where('nombre', $codigo);
            } else {
                $qActivos->where('tipo_registro', 'tienda')->where('codigo', $codigo);
            }
            $activos = $qActivos->value('activos') ?? 0;

            return [
                'salidas'   => $salidas,
                'sal012'    => $sal012,
                'activos'   => $activos,
                'rotacion'  => $activos  > 0 ? round(($salidas / $activos) * 100, 2)          : null,
                'retencion' => $salidas  > 0 ? round((($salidas - $sal012) / $salidas) * 100, 2) : null,
            ];
        };

        $rrTotal = $getYtdMetric('TOTAL',     $rr_anio, $rr_mes);
        $rrRgm   = $getYtdMetric('ADM',       $rr_anio, $rr_mes);
        $rrMe    = $getYtdMetric('ASOCIADOS', $rr_anio, $rr_mes);

        $aniosHistoricosRr = MetricaReportada::distinct()->orderBy('anio')
            ->pluck('anio')->filter(fn($y) => $y < $rr_anio)->values()->toArray();

        $rrHistorico = [];
        foreach ($aniosHistoricosRr as $histYear) {
            $maxMesHist = MetricaReportada::where('anio', $histYear)->max('mes') ?? 12;
            $hTotal = $getYtdMetric('TOTAL',     $histYear, $maxMesHist);
            $hRgm   = $getYtdMetric('ADM',       $histYear, $maxMesHist);
            $hMe    = $getYtdMetric('ASOCIADOS', $histYear, $maxMesHist);
            $rrHistorico[$histYear] = [
                'rotacion'  => $hTotal['rotacion'],
                'rgm'       => $hRgm['rotacion'],
                'me'        => $hMe['rotacion'],
                'retencion' => $hTotal['retencion'],
            ];
        }

        $objetivoAnio = \App\Models\ObjetivoKfc::where('anio', $rr_anio)->first();
        $rrObjetivos = [
            'rotacion'  => $objetivoAnio ? $objetivoAnio->rotacion  : null,
            'rgm'       => $objetivoAnio ? $objetivoAnio->rgm       : null,
            'me'        => $objetivoAnio ? $objetivoAnio->me        : null,
            'retencion' => $objetivoAnio ? $objetivoAnio->retencion : null,
        ];

        $rr_mesesNombres = [
            1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',
            7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'
        ];
        $rr_mesLabel = $rr_mesesNombres[$rr_mes] . ' ' . $rr_anio;

        return view('dashboard', compact(
            'anio', 'mes', 'categoria', 'seccion', 'codigo', 'localesList', 'tiendaSeleccionada', 'mesNombre',
            'activeHeadcount', 'dotacionBandas', 'egresosMtd', 'egresosYtd', 'rotacionMtd', 'rotacionYtd', 'retencionMtd', 'retencionYtd', 'scoreTotalGente',
            'productividadLocal', 'productividadChain', 'transaccionesLocal', 'horasHombreLocal',
            'femalePct', 'malePct', 'pcdPct', 'topPuestos', 'prevYeHeadcount', 'chainHeadcount',
            'podium', 'restLeaderboard', 'leaderboardProd', 'localRank',
            'chartLabels', 'chartRotationLocal', 'chartRotationChain', 'chartTransactions', 'chartProductivity', 'chartHorasHombre',
            'disponiblesAnios', 'disponiblesMeses',
            'engagementData', 'esLocalIndividual',
            'rrTotal', 'rrRgm', 'rrMe', 'rrHistorico', 'rrObjetivos', 'rr_mesLabel', 'rr_anio'
        ));
    });

    // 2. Sucursales (Metricas de Tienda)
    Route::get('/sucursales', function (Request $request) {
        $search = $request->input('search');
        $tipo = $request->input('tipo');

        $anio = 2026;
        $mes = 4;

        $query = MetricaReportada::query()
            ->where('categoria', 'TOTAL')
            ->where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', 'ZONA')
            ->where('tipo_registro', 'tienda');

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%");
        }

        $metriList = $query->get();

        $tiendasList = [];
        foreach ($metriList as $m) {
            $codigoCorto = 'K' . sprintf('%02d', intval($m->codigo));
            $tObj = Tienda::where('codigo_corto', $codigoCorto)->first();
            
            $tipoTienda = $tObj ? $tObj->tipo : 'IL';
            $zonaNombre = $tObj && $tObj->zona ? $tObj->zona->nombre : 'Zona ' . $m->zona_num;

            if ($tipo && $tipoTienda !== $tipo) {
                continue;
            }

            $tYtd = MetricaReportada::query()
                ->where('categoria', 'TOTAL')
                ->where('anio', $anio)
                ->where('mes', '<=', $mes)
                ->where('seccion', 'ZONA')
                ->where('tipo_registro', 'tienda')
                ->where('codigo', $m->codigo);

            $tEgresosYtd = $tYtd->sum('salidas');
            $tEgresos0_12Ytd = $tYtd->sum('salidas_0_12');

            $tRot = $m->activos > 0 ? ($tEgresosYtd / $m->activos) : 0.0;
            $tRet = $tEgresosYtd > 0 ? (($tEgresosYtd - $tEgresos0_12Ytd) / $tEgresosYtd) : 1.0;

            // Productivity
            $tTrans = 0;
            if ($tObj) {
                $tTrans = MetricaOperativa::query()->where('tienda_id', $tObj->id)->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
            }
            $tProd = $m->activos > 0 ? ($tTrans / $m->activos) : 0;

            $chainMetrica = MetricaReportada::query()
                ->where('categoria', 'TOTAL')
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where('seccion', 'TOTAL')
                ->where('tipo_registro', 'total_general')
                ->first();
            $chainHeadcount = $chainMetrica ? $chainMetrica->activos : 0;
            $transaccionesChain = MetricaOperativa::query()->where('anio', $anio)->where('mes', $mes)->sum('transacciones');
            $productividadChain = $chainHeadcount > 0 ? ($transaccionesChain / $chainHeadcount) : 0;

            $tRatioProd = $productividadChain > 0 ? ($tProd / $productividadChain) : 1.0;
            $tScoreProd = min(1.0, $tRatioProd) * 20;

            $tScore = round(($tRet * 40) + (max(0.0, 1.0 - $tRot) * 40) + $tScoreProd, 1);

            $tiendasList[] = [
                'codigo' => $m->codigo,
                'cdc' => $m->nombre,
                'tipo' => $tipoTienda,
                'zona' => $zonaNombre,
                'headcount' => $m->activos,
                'egresos' => $tEgresosYtd,
                'rotacion' => round($tRot * 100, 1) . '%',
                'retencion' => round($tRet * 100, 1) . '%',
                'score' => $tScore,
            ];
        }

        usort($tiendasList, fn($a, $b) => $b['score'] <=> $a['score']);

        return view('tiendas.index', compact('tiendasList', 'search', 'tipo'));
    });

    // 3. Colaboradores
    Route::get('/colaboradores', function (Request $request) {
        $search = $request->input('search');
        $status = $request->input('status');
        $sexo = $request->input('sexo');
        $tiendaId = $request->input('tienda_id');

        $query = Contrato::query()->with(['empleado', 'tienda']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('empleado', function ($sq) use ($search) {
                    $sq->where('nombre', 'like', "%{$search}%")
                       ->orWhere('cedula', 'like', "%{$search}%");
                })->orWhere('puesto', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sexo) {
            $query->whereHas('empleado', function ($q) use ($sexo) {
                $q->where('sexo', $sexo);
            });
        }

        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        }

        $contratos = $query->orderBy('fecha_ingreso', 'desc')->paginate(25)->withQueryString();
        $tiendasList = Tienda::where('codigo_corto', '!=', 'KGEN')->orderBy('cdc')->get();

        return view('empleados.index', compact('contratos', 'tiendasList', 'search', 'status', 'sexo', 'tiendaId'));
    });

    // 4. Historial de Contratos
    Route::get('/contratos', function (Request $request) {
        $search = $request->input('search');
        $tiendaId = $request->input('tienda_id');
        $puesto = $request->input('puesto');

        $query = Contrato::query()->with(['empleado', 'tienda'])->orderBy('fecha_ingreso', 'desc');

        if ($search) {
            $query->whereHas('empleado', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        }

        if ($tiendaId) {
            $query->where('tienda_id', $tiendaId);
        }

        if ($puesto) {
            $query->where('puesto', 'like', "%{$puesto}%");
        }

        $contratos = $query->paginate(25)->withQueryString();
        $tiendasList = Tienda::where('codigo_corto', '!=', 'KGEN')->orderBy('cdc')->get();

        return view('contratos.index', compact('contratos', 'tiendasList', 'search', 'tiendaId', 'puesto'));
    });

    // 5. Carga de Excel e Importación
    Route::get('/importar', function () {
        $files = Storage::disk('local')->files('imports');
        $importLog = [];
        foreach ($files as $file) {
            $importLog[] = [
                'name' => basename($file),
                'date' => Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file))->toDateTimeString(),
                'size' => round(Storage::disk('local')->size($file) / 1024, 1) . ' KB'
            ];
        }
        return view('importar', compact('importLog'));
    });

    // POST: Importación de Excel
    Route::post('/importar-excel', function (Request $request) {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls'
        ]);

        $file = $request->file('excel_file');
        $path = $file->storeAs('imports', $file->getClientOriginalName(), 'local');
        $absolutePath = Storage::disk('local')->path($path);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($absolutePath);
            $sheetNames = $spreadsheet->getSheetNames();

            if (in_array('ACTIVOS Rot y Ret.', $sheetNames)) {
                $exitCode = Artisan::call('kfc:import', ['--file' => $absolutePath]);
                $msg = 'Base de Datos de Personal y Contratos importada con éxito desde ' . $file->getClientOriginalName() . '.';
            } elseif (in_array('KFC x ZONA MENSUAL 2026', $sheetNames)) {
                $exitCode = Artisan::call('kfc:import-reporte', ['--file' => $absolutePath]);
                $msg = 'Base de Datos de Métricas Reportadas (2025-2026) importada con éxito desde ' . $file->getClientOriginalName() . '.';
            } else {
                return redirect('/importar')->with('error', 'El archivo Excel no coincide con el formato esperado de KFC (no se encontraron pestañas conocidas).');
            }

            if ($exitCode === 0) {
                return redirect('/importar')->with('success', $msg . ' Los gráficos y estadísticas del dashboard han sido recalculados.');
            } else {
                return redirect('/importar')->with('error', 'Error al procesar el archivo: ' . Artisan::output());
            }
        } catch (\Exception $e) {
            return redirect('/importar')->with('error', 'Error del servidor al procesar el archivo: ' . $e->getMessage());
        }
    })->name('import.excel');

    // POST: Restablecer base de datos limpia
    Route::post('/database/reset', function () {
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);

            // Recreate admin user
            User::create([
                'name' => 'Administrador KFC',
                'email' => 'admin@kfc.com',
                'password' => bcrypt('admin1234'),
            ]);

            return redirect('/importar')->with('success', 'La base de datos se ha vaciado por completo. El usuario admin@kfc.com / admin1234 ha sido recreado para iniciar sesión.');
        } catch (\Exception $e) {
            return redirect('/importar')->with('error', 'Error al vaciar la base de datos: ' . $e->getMessage());
        }
    })->name('database.reset');

    // ─── Módulo de Engagement (Compromiso, Experiencia, Intención de Permanecer) ───

    // GET: Lista de encuestas
    Route::get('/engagement', function (Request $request) {
        $anio = intval($request->input('anio', date('Y')));
        $aniosDisponibles = EncuestaEngagement::distinct()->orderBy('anio', 'desc')->pluck('anio')->toArray();
        if (empty($aniosDisponibles)) $aniosDisponibles = [date('Y')];

        $registros = EncuestaEngagement::where('anio', $anio)
            ->orderBy('codigo_local')
            ->get();

        // List of available stores
        $locales = MetricaReportada::where('anio', $anio)
            ->where('mes', MetricaReportada::where('anio', $anio)->max('mes'))
            ->where('seccion', 'ZONA')
            ->where('tipo_registro', 'tienda')
            ->where('categoria', 'TOTAL')
            ->orderBy('nombre')
            ->get(['codigo', 'nombre']);

        return view('engagement.index', compact('registros', 'anio', 'aniosDisponibles', 'locales'));
    })->name('engagement.index');


    // POST: Guardar/actualizar un registro manual
    Route::post('/engagement/store', function (Request $request) {
        $request->validate([
            'anio'                 => 'required|integer|min:2020|max:2099',
            'mes'                  => 'nullable|integer|min:1|max:12',
            'codigo_local'         => 'required|string|max:20',
            'nombre_local'         => 'nullable|string|max:200',
            'compromiso'           => 'nullable|numeric|min:0|max:100',
            'experiencia'          => 'nullable|numeric|min:0|max:100',
            'intencion_permanecer' => 'nullable|numeric|min:0|max:100',
            'n_encuestados'        => 'nullable|integer|min:0',
            'notas'                => 'nullable|string|max:500',
        ]);

        EncuestaEngagement::updateOrCreate(
            [
                'anio'         => $request->anio,
                'mes'          => $request->mes ?? null,
                'codigo_local' => $request->codigo_local,
            ],
            [
                'nombre_local'         => $request->nombre_local,
                'compromiso'           => $request->compromiso,
                'experiencia'          => $request->experiencia,
                'intencion_permanecer' => $request->intencion_permanecer,
                'n_encuestados'        => $request->n_encuestados ?? 0,
                'notas'                => $request->notas,
            ]
        );

        return redirect()->route('engagement.index', ['anio' => $request->anio])
            ->with('success', 'Registro de engagement guardado correctamente.');
    })->name('engagement.store');

    // POST: Importar Excel de engagement
    Route::post('/engagement/import', function (Request $request) {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'anio'       => 'required|integer|min:2020|max:2099',
            'mes'        => 'nullable|integer|min:1|max:12',
        ]);

        $file = $request->file('excel_file');
        $path = $file->storeAs('imports', 'engagement_' . time() . '_' . $file->getClientOriginalName(), 'local');
        $absolutePath = Storage::disk('local')->path($path);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($absolutePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $header = null;
            $imported = 0;
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex === 1) {
                    // Normalize header keys
                    $header = array_map(fn($h) => mb_strtolower(trim($h ?? '')), $row);
                    continue;
                }

                if (!$header) continue;
                $data = array_combine($header, array_values($row));

                $codigo = trim($data['codigo'] ?? $data['código'] ?? $data['local'] ?? '');
                if (empty($codigo)) continue;

                // Normalize codigo (remove leading K if present)
                $codigoNorm = ltrim($codigo, 'Kk');
                $codigoNorm = is_numeric($codigoNorm) ? $codigoNorm : $codigo;

                $nombre = trim($data['nombre'] ?? $data['nombre_local'] ?? $data['local_nombre'] ?? '');
                $comp   = isset($data['compromiso']) ? floatval(str_replace(['%', ','], ['', '.'], $data['compromiso'])) : null;
                $exp    = isset($data['experiencia']) ? floatval(str_replace(['%', ','], ['', '.'], $data['experiencia'])) : null;
                $intRaw = $data['intencion'] ?? $data['intencion_permanecer'] ?? null;
                $int    = ($intRaw !== null) ? floatval(str_replace(['%', ','], ['', '.'], $intRaw)) : null;
                $n      = intval($data['n_encuestados'] ?? $data['encuestados'] ?? 0);

                EncuestaEngagement::updateOrCreate(
                    [
                        'anio'         => $request->anio,
                        'mes'          => $request->mes ?? null,
                        'codigo_local' => $codigoNorm,
                    ],
                    [
                        'nombre_local'         => $nombre ?: null,
                        'compromiso'           => $comp,
                        'experiencia'          => $exp,
                        'intencion_permanecer' => $int,
                        'n_encuestados'        => $n,
                    ]
                );
                $imported++;
            }

            return redirect()->route('engagement.index', ['anio' => $request->anio])
                ->with('success', "Se importaron {$imported} registros de engagement desde {$file->getClientOriginalName()}.");
        } catch (\Exception $e) {
            return redirect()->route('engagement.index')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    })->name('engagement.import');

    // DELETE: Eliminar un registro de engagement
    Route::delete('/engagement/{id}', function ($id) {
        $reg = EncuestaEngagement::findOrFail($id);
        $anio = $reg->anio;
        $reg->delete();
        return redirect()->route('engagement.index', ['anio' => $anio])
            ->with('success', 'Registro eliminado.');
    })->name('engagement.destroy');

    // --- Módulo de Gestión de Datos ---
    Route::get('/datos/cargar', [App\Http\Controllers\DataManagementController::class, 'showUploadForm'])->name('datos.upload_form');
    Route::post('/datos/cargar', [App\Http\Controllers\DataManagementController::class, 'uploadData'])->name('datos.upload');
    Route::get('/datos/editar', [App\Http\Controllers\DataManagementController::class, 'showEditor'])->name('datos.editor');
    Route::post('/datos/editar', [App\Http\Controllers\DataManagementController::class, 'updateMetric'])->name('datos.update');

});
