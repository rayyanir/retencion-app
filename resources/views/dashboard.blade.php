@extends('layouts.app')

@section('title', 'KFC Venezuela - Dashboard de Métricas de Personal')
@section('page_title', 'Dashboard de Métricas')

@section('content')
<div class="space-y-6">

    <!-- Filter Panel -->
    <section class="vuexy-card p-6">
        <form method="GET" action="/" class="grid grid-cols-1 sm:grid-cols-5 items-end gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Año</label>
                <select name="anio" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-2.5 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    @foreach($disponiblesAnios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Mes</label>
                <select name="mes" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-2.5 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    @php
                        $mesesNombres = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
                    @endphp
                    @foreach($disponiblesMeses as $mVal)
                        <option value="{{ $mVal }}" {{ $mes == $mVal ? 'selected' : '' }}>{{ $mesesNombres[$mVal] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Categoría (Cargo)</label>
                <select name="categoria" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-2.5 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    <option value="TOTAL" {{ $categoria == 'TOTAL' ? 'selected' : '' }}>Toda la Cadena</option>
                    <option value="ASOCIADOS" {{ $categoria == 'ASOCIADOS' ? 'selected' : '' }}>Asociados</option>
                    <option value="ADM" {{ $categoria == 'ADM' ? 'selected' : '' }}>Administradores / GTES</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Área (Sección)</label>
                <select name="seccion" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-2.5 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    <option value="ZONA" {{ $seccion == 'ZONA' ? 'selected' : '' }}>Sucursales (Zonas 1-6)</option>
                    <option value="OPERACIONES" {{ $seccion == 'OPERACIONES' ? 'selected' : '' }}>Operaciones Soporte</option>
                    <option value="PLANTA" {{ $seccion == 'PLANTA' ? 'selected' : '' }}>Planta Producción</option>
                    <option value="CAR" {{ $seccion == 'CAR' ? 'selected' : '' }}>Administración CAR</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Filtro Local</label>
                <select name="codigo" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-2.5 py-2.5 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    <option value="TOTAL" {{ $codigo == 'TOTAL' ? 'selected' : '' }}>Consolidado (Total Área)</option>
                    @foreach($localesList as $loc)
                        <option value="{{ $loc->tipo_registro === 'total_zona' ? $loc->nombre : $loc->codigo }}" {{ $codigo == ($loc->tipo_registro === 'total_zona' ? $loc->nombre : $loc->codigo) ? 'selected' : '' }}>
                            @if($loc->tipo_registro === 'total_zona')
                                📈 {{ $loc->nombre }}
                            @else
                                [{{ $loc->codigo }}] {{ str_replace('KFC - ', '', $loc->nombre) }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </section>

    <!-- Dashboard Row 1: Gestión Humana & Rotación y Retención -->
    {{-- ═══ SECCIÓN: Gestión Humana ═══ --}}
    <section class="vuexy-card overflow-hidden">

        {{-- Dark header --}}
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-4">
            <h3 class="text-base font-black text-white tracking-tight uppercase">🏢 Gestión Humana</h3>
            <p class="text-xs text-gray-400 mt-0.5">Distribución de plantilla · {{ ucfirst($mesNombre) }} {{ $anio }}</p>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">

            {{-- ── Columna 1: Colaboradores + comparativo YE ── --}}
            <div class="flex flex-col items-center justify-center text-center space-y-3 border-r border-gray-100 dark:border-slate-800 pr-6">
                {{-- Person icons row --}}
                <div class="flex items-end justify-center gap-1 text-brand-primary">
                    <span class="text-4xl">👤</span>
                    <span class="text-5xl">👤</span>
                    <span class="text-5xl">👤</span>
                    <span class="text-4xl">👤</span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-gray-400">Colaboradores</p>
                    <p class="text-[11px] font-bold text-gray-400">a {{ ucfirst($mesNombre) }}'{{ substr($anio, 2) }}</p>
                    <p class="text-6xl font-black font-mono text-slate-800 dark:text-white leading-none mt-1">{{ number_format($chainHeadcount) }}</p>
                </div>
                @if($prevYeHeadcount)
                <div class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 rounded-full px-4 py-1.5">
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400">YE {{ $anio - 1 }} =</span>
                    <span class="text-sm font-black font-mono text-slate-700 dark:text-white">{{ number_format($prevYeHeadcount) }}</span>
                    @php $diff = $chainHeadcount - $prevYeHeadcount; @endphp
                    <span class="text-[10px] font-bold {{ $diff >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ $diff }}
                    </span>
                </div>
                @endif
            </div>

            {{-- ── Columna 2: Donut chart by puesto ── --}}
            <div class="flex flex-col items-center">
                <div class="relative w-48 h-48">
                    <canvas id="puestoDonutChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl font-black font-mono text-slate-800 dark:text-white">{{ number_format($chainHeadcount) }}</span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Total</span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-1.5 w-full max-w-xs">
                    @foreach($topPuestos as $p)
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $p['color'] }}"></span>
                        <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $p['label'] }}</span>
                        <span class="text-[10px] font-bold text-slate-700 dark:text-gray-200 ml-auto font-mono">{{ $p['pct'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Columna 3: Género + PCD ── --}}
            <div class="flex flex-col items-center justify-center space-y-4 border-l border-gray-100 dark:border-slate-800 pl-6">

                {{-- Mujeres --}}
                <div class="w-full">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">👩</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wide">Mujeres</span>
                        </div>
                        <span class="text-2xl font-black font-mono text-brand-primary">{{ $femalePct }}%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-primary rounded-full" style="width: {{ $femalePct }}%"></div>
                    </div>
                </div>

                {{-- Hombres --}}
                <div class="w-full">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">👨</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wide">Hombres</span>
                        </div>
                        <span class="text-2xl font-black font-mono text-brand-info">{{ $malePct }}%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-info rounded-full" style="width: {{ $malePct }}%"></div>
                    </div>
                </div>

                {{-- PCD --}}
                <div class="w-full">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">♿</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-gray-300 uppercase tracking-wide">PCD</span>
                        </div>
                        <span class="text-2xl font-black font-mono text-brand-success">{{ $pcdPct }}%</span>
                    </div>
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-brand-success rounded-full" style="width: {{ $pcdPct }}%"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══ SECCIÓN: Rotación y Retención — Resumen de Locales ═══ --}}
    <section class="vuexy-card overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Rotación y Retención</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Locales (Zonas 1–6) · Acumulado del año a <strong class="text-white">{{ $rr_mesLabel }}</strong>
                </p>
            </div>
            <a href="/engagement" class="text-[10px] bg-white/10 hover:bg-white/20 text-white font-bold px-3 py-1.5 rounded-lg border border-white/10 transition-colors">
                ⚙️ Configurar Objetivos
            </a>
        </div>

        {{-- ── Metrics columns ── --}}
        <div class="p-6">
            @php
                $rrCols = [
                    [
                        'key'   => 'rotacion',
                        'label' => 'Rotación',
                        'sub'   => 'Total locales',
                        'val'   => $rrTotal['rotacion'],
                        'obj'   => $rrObjetivos['rotacion'],
                        'icon'  => '🔄',
                        'color' => 'text-brand-warning',
                        'bg'    => 'bg-brand-warningLight dark:bg-yellow-950/20',
                    ],
                    [
                        'key'   => 'rgm',
                        'label' => 'RGM',
                        'sub'   => 'Rotación Gerentes',
                        'val'   => $rrRgm['rotacion'],
                        'obj'   => $rrObjetivos['rgm'],
                        'icon'  => '👔',
                        'color' => 'text-brand-info',
                        'bg'    => 'bg-brand-infoLight dark:bg-cyan-950/20',
                    ],
                    [
                        'key'   => 'me',
                        'label' => 'Miembros de Equipo',
                        'sub'   => 'Rotación Asociados',
                        'val'   => $rrMe['rotacion'],
                        'obj'   => $rrObjetivos['me'],
                        'icon'  => '👥',
                        'color' => 'text-brand-primary',
                        'bg'    => 'bg-brand-primaryLight dark:bg-red-950/20',
                    ],
                    [
                        'key'   => 'retencion',
                        'label' => 'Retención',
                        'sub'   => 'Acumulado del año',
                        'val'   => $rrTotal['retencion'],
                        'obj'   => $rrObjetivos['retencion'],
                        'icon'  => '🛡️',
                        'color' => 'text-brand-success',
                        'bg'    => 'bg-brand-successLight dark:bg-green-950/20',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 divide-x divide-y lg:divide-y-0 divide-gray-100 dark:divide-slate-800 border border-gray-100 dark:border-slate-800 rounded-xl overflow-hidden">
                @foreach($rrCols as $col)
                <div class="p-5">

                    {{-- Column Header --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="p-1.5 {{ $col['bg'] }} {{ $col['color'] }} rounded-lg text-sm">{{ $col['icon'] }}</span>
                        <div>
                            <span class="text-xs font-bold text-slate-700 dark:text-gray-200 block">{{ $col['label'] }}</span>
                            <span class="text-[10px] text-gray-400">{{ $col['sub'] }}</span>
                        </div>
                    </div>

                    {{-- Current Value (big) --}}
                    <div class="mb-4">
                        <span class="text-4xl font-black font-mono {{ $col['color'] }} leading-none">
                            {{ $col['val'] !== null ? number_format($col['val'], 2) : '—' }}
                        </span>
                        @if($col['val'] !== null)
                            <span class="text-lg font-bold {{ $col['color'] }}">%</span>
                        @endif
                    </div>

                    {{-- Historical rows --}}
                    <div class="space-y-1.5 mb-4">
                        @foreach($rrHistorico as $histYear => $histVals)
                            @php $histVal = $histVals[$col['key']]; @endphp
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-gray-400">'{{ substr($histYear, 2) }}</span>
                                <div class="flex items-center gap-2 flex-1 mx-2">
                                    <div class="h-1 flex-1 bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                        @if($histVal !== null)
                                            <div class="h-full {{ $col['color'] === 'text-brand-success' ? 'bg-brand-success' : ($col['color'] === 'text-brand-warning' ? 'bg-brand-warning' : ($col['color'] === 'text-brand-info' ? 'bg-brand-info' : 'bg-brand-primary')) }} rounded-full opacity-60"
                                                style="width: {{ min(100, $histVal) }}%"></div>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold font-mono text-slate-600 dark:text-gray-300 w-14 text-right">
                                    {{ $histVal !== null ? number_format($histVal, 1) . '%' : '—' }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Objective Box (red) --}}
                    @if($col['obj'] !== null)
                    <div class="bg-red-600 text-white rounded-xl px-4 py-3 text-center border-2 border-red-500 shadow-md shadow-red-500/20">
                        <span class="text-[10px] font-bold uppercase tracking-wider block text-red-200">Obj. {{ $rr_anio }}</span>
                        <span class="text-[10px] font-bold text-red-100 block">{{ $col['label'] }}</span>
                        <span class="text-2xl font-black font-mono">{{ number_format($col['obj'], 0) }}%</span>
                    </div>
                    @else
                    <div class="border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-xl px-4 py-3 text-center">
                        <a href="/objetivos" class="text-[10px] text-gray-400 hover:text-brand-primary font-semibold">+ Agregar objetivo</a>
                    </div>
                    @endif

                </div>
                @endforeach
            </div>

            {{-- Bottom note --}}
            <p class="text-[10px] text-gray-400 mt-3 text-center">
                Los valores históricos corresponden al cierre del año completo (Dic). El valor actual es acumulado a <strong>{{ $rr_mesLabel }}</strong>.
                Objetivos configurables en <a href="/engagement" class="text-brand-primary hover:underline font-semibold">Módulo Engagement</a>.
            </p>
        </div>
    </section>

    {{-- ── Engagement Cards (solo para locales individuales) ── --}}
    @if($esLocalIndividual)
    <section>
        <div class="flex items-center gap-3 mb-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-gray-400">Clima Laboral — Encuesta de Engagement</h4>
            @if($engagementData)
                <span class="text-[10px] bg-green-100 dark:bg-green-950/20 text-green-700 dark:text-green-400 font-bold px-2 py-0.5 rounded-full border border-green-200 dark:border-green-800/30">
                    {{ $engagementData->n_encuestados > 0 ? $engagementData->n_encuestados . ' encuestados' : 'Datos cargados' }}
                    · {{ $engagementData->anio }}{{ $engagementData->mes ? '/' . str_pad($engagementData->mes,2,'0',STR_PAD_LEFT) : '' }}
                </span>
            @else
                <a href="/engagement" class="text-[10px] bg-yellow-50 dark:bg-yellow-950/20 text-yellow-700 dark:text-yellow-400 font-bold px-2 py-0.5 rounded-full border border-yellow-200 dark:border-yellow-800/30 hover:underline">
                    ⚠️ Sin datos · Cargar en módulo Engagement →
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

            {{-- Card 1: Compromiso --}}
            <div class="vuexy-card p-5 border-l-4 {{ $engagementData && $engagementData->compromiso !== null ? ($engagementData->compromiso >= 80 ? 'border-l-brand-success' : ($engagementData->compromiso >= 60 ? 'border-l-brand-warning' : 'border-l-brand-danger')) : 'border-l-gray-200 dark:border-l-slate-700' }}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Compromiso</span>
                        @if($engagementData && $engagementData->compromiso !== null)
                            <span class="text-3xl font-black font-mono leading-none mt-1
                                {{ $engagementData->compromiso >= 80 ? 'text-brand-success' : ($engagementData->compromiso >= 60 ? 'text-brand-warning' : 'text-brand-danger') }}">
                                {{ number_format($engagementData->compromiso, 1) }}<span class="text-base font-bold">%</span>
                            </span>
                        @else
                            <span class="text-2xl font-bold text-gray-300 dark:text-slate-600 mt-1 font-mono">—</span>
                        @endif
                    </div>
                    <span class="p-2 bg-slate-100 dark:bg-slate-800 text-xl rounded-lg">🤝</span>
                </div>
                @if($engagementData && $engagementData->compromiso !== null)
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mt-2">
                        <div class="h-full rounded-full transition-all duration-700
                            {{ $engagementData->compromiso >= 80 ? 'bg-brand-success' : ($engagementData->compromiso >= 60 ? 'bg-brand-warning' : 'bg-brand-danger') }}"
                            style="width: {{ $engagementData->compromiso }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1.5 block">
                        {{ $engagementData->compromiso >= 80 ? '✅ Nivel alto' : ($engagementData->compromiso >= 60 ? '⚠️ Nivel medio' : '🔴 Nivel bajo') }}
                    </span>
                @else
                    <span class="text-[10px] text-gray-400 mt-2 block">Sin datos de encuesta para este local</span>
                @endif
            </div>

            {{-- Card 2: Experiencia vs. Expectativas --}}
            <div class="vuexy-card p-5 border-l-4 {{ $engagementData && $engagementData->experiencia !== null ? ($engagementData->experiencia >= 80 ? 'border-l-brand-success' : ($engagementData->experiencia >= 60 ? 'border-l-brand-warning' : 'border-l-brand-danger')) : 'border-l-gray-200 dark:border-l-slate-700' }}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Exp. vs. Expectativas</span>
                        @if($engagementData && $engagementData->experiencia !== null)
                            <span class="text-3xl font-black font-mono leading-none mt-1
                                {{ $engagementData->experiencia >= 80 ? 'text-brand-success' : ($engagementData->experiencia >= 60 ? 'text-brand-warning' : 'text-brand-danger') }}">
                                {{ number_format($engagementData->experiencia, 1) }}<span class="text-base font-bold">%</span>
                            </span>
                        @else
                            <span class="text-2xl font-bold text-gray-300 dark:text-slate-600 mt-1 font-mono">—</span>
                        @endif
                    </div>
                    <span class="p-2 bg-slate-100 dark:bg-slate-800 text-xl rounded-lg">⭐</span>
                </div>
                @if($engagementData && $engagementData->experiencia !== null)
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mt-2">
                        <div class="h-full rounded-full transition-all duration-700
                            {{ $engagementData->experiencia >= 80 ? 'bg-brand-success' : ($engagementData->experiencia >= 60 ? 'bg-brand-warning' : 'bg-brand-danger') }}"
                            style="width: {{ $engagementData->experiencia }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1.5 block">
                        {{ $engagementData->experiencia >= 80 ? '✅ Supera expectativas' : ($engagementData->experiencia >= 60 ? '⚠️ Cumple parcialmente' : '🔴 Por debajo') }}
                    </span>
                @else
                    <span class="text-[10px] text-gray-400 mt-2 block">Sin datos de encuesta para este local</span>
                @endif
            </div>

            {{-- Card 3: Intención de Permanecer --}}
            <div class="vuexy-card p-5 border-l-4 {{ $engagementData && $engagementData->intencion_permanecer !== null ? ($engagementData->intencion_permanecer >= 80 ? 'border-l-brand-success' : ($engagementData->intencion_permanecer >= 60 ? 'border-l-brand-warning' : 'border-l-brand-danger')) : 'border-l-gray-200 dark:border-l-slate-700' }}">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Intención de Permanecer</span>
                        @if($engagementData && $engagementData->intencion_permanecer !== null)
                            <span class="text-3xl font-black font-mono leading-none mt-1
                                {{ $engagementData->intencion_permanecer >= 80 ? 'text-brand-success' : ($engagementData->intencion_permanecer >= 60 ? 'text-brand-warning' : 'text-brand-danger') }}">
                                {{ number_format($engagementData->intencion_permanecer, 1) }}<span class="text-base font-bold">%</span>
                            </span>
                        @else
                            <span class="text-2xl font-bold text-gray-300 dark:text-slate-600 mt-1 font-mono">—</span>
                        @endif
                    </div>
                    <span class="p-2 bg-slate-100 dark:bg-slate-800 text-xl rounded-lg">💙</span>
                </div>
                @if($engagementData && $engagementData->intencion_permanecer !== null)
                    <div class="h-2 w-full bg-gray-100 dark:bg-slate-800 rounded-full overflow-hidden mt-2">
                        <div class="h-full rounded-full transition-all duration-700
                            {{ $engagementData->intencion_permanecer >= 80 ? 'bg-brand-success' : ($engagementData->intencion_permanecer >= 60 ? 'bg-brand-warning' : 'bg-brand-danger') }}"
                            style="width: {{ $engagementData->intencion_permanecer }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1.5 block">
                        {{ $engagementData->intencion_permanecer >= 80 ? '✅ Alta retención esperada' : ($engagementData->intencion_permanecer >= 60 ? '⚠️ Riesgo moderado' : '🔴 Alto riesgo de fuga') }}
                    </span>
                @else
                    <span class="text-[10px] text-gray-400 mt-2 block">Sin datos de encuesta para este local</span>
                @endif
            </div>

        </div>
    </section>
    @endif

    {{-- ═══ SECCIÓN: Productividad Operativa ═══ --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Card: Transacciones --}}
        <div class="vuexy-card p-5 border-l-4 border-l-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Volumen de Transacciones</span>
                    <span class="text-3xl font-black font-mono leading-none text-slate-800 dark:text-white">
                        {{ number_format($transaccionesLocal) }}
                    </span>
                    <p class="text-[10px] text-gray-500 mt-2">Transacciones acumuladas en {{ ucfirst($mesNombre) }}</p>
                </div>
                <span class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-500 text-xl rounded-lg border border-blue-100 dark:border-blue-800">🛒</span>
            </div>
        </div>

        {{-- Card: Productividad (Trans/HH) --}}
        <div class="vuexy-card p-5 border-l-4 border-l-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Productividad</span>
                    <span class="text-3xl font-black font-mono leading-none text-purple-600 dark:text-purple-400">
                        {{ number_format($productividadLocal, 2) }}
                    </span>
                    <p class="text-[10px] text-gray-500 mt-2">Fórmula: Transacciones / Horas Hombre (HH: {{ number_format($horasHombreLocal, 2) }})</p>
                </div>
                <span class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-500 text-xl rounded-lg border border-purple-100 dark:border-purple-800">⚡</span>
            </div>
        </div>
    </section>

    {{-- Dashboard Row 2: Charts Area --}}

    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Rotation Line Chart -->
        <div class="vuexy-card p-6">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    Tendencia de Rotación Mensual (%)
                </h4>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold">Año {{ $anio }}</span>
            </div>
            <div class="h-[280px]">
                <canvas id="rotationChart"></canvas>
            </div>
        </div>

        <!-- Productivity Mixed Chart -->
        <div class="vuexy-card p-6">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    Volumen de Transacciones & Productividad
                </h4>
                <span class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold">Año {{ $anio }}</span>
            </div>
            <div class="h-[280px]">
                <canvas id="productivityChart"></canvas>
            </div>
        </div>
    </section>

    <!-- Dashboard Row 3: Leaderboard (Top Podium & Data Table) -->
    <section class="vuexy-card p-6">
        
        <!-- Title header -->
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-slate-800 pb-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>🏆</span> Escalafón de Rendimiento (Sección: {{ $seccion }})
                </h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Ranking ponderado acumulado del año de los locales y áreas de esta sección.</p>
            </div>
            <span class="text-xs bg-brand-primaryLight dark:bg-red-950/20 text-brand-primary font-bold px-3 py-1 rounded-full border border-brand-primary/10">
                {{ ucfirst($mesNombre) }} {{ $anio }}
            </span>
        </div>

        <!-- Table Leaderboard List -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                        <th class="px-5 py-3 text-center w-16">Puesto</th>
                        <th class="px-6 py-3">Código</th>
                        <th class="px-6 py-3">Nombre del Local / Área</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Plantilla</th>
                        <th class="px-4 py-3 text-center">Bajas Acum.</th>
                        <th class="px-4 py-3 text-center">Rotación Acum.</th>
                        <th class="px-4 py-3 text-center">Retención Acum.</th>

                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                    @forelse(array_merge($podium, $restLeaderboard) as $index => $tItem)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150 {{ $codigo == $tItem['codigo'] ? 'bg-red-500/5 dark:bg-red-950/10 font-semibold text-slate-800 dark:text-white border-l-4 border-l-brand-primary' : '' }}">
                            <td class="px-5 py-2.5 text-center font-bold">
                                @if($index == 0)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-950/50 text-yellow-600 dark:text-yellow-400 font-bold">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 font-bold">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-500 font-bold">3</span>
                                @else
                                    <span class="text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-2.5 font-mono text-gray-500">
                                @if(is_numeric($tItem['codigo'])) K{{ sprintf('%02d', intval($tItem['codigo'])) }} @else {{ $tItem['codigo'] }} @endif
                            </td>
                            <td class="px-6 py-2.5">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ str_replace('KFC - ', '', $tItem['cdc']) }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $tObj = \App\Models\Tienda::where('codigo_corto', 'K' . sprintf('%02d', intval($tItem['codigo'])))->first();
                                    $tipoT = $tObj ? $tObj->tipo : 'SUP';
                                @endphp
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border border-gray-200 dark:border-slate-700
                                    {{ $tipoT === 'FS' ? 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400' : '' }}
                                    {{ $tipoT === 'FC' ? 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400' : '' }}
                                    {{ $tipoT === 'IL' ? 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400' : '' }}
                                    {{ $tipoT === 'SUP' ? 'bg-purple-100 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400' : '' }}
                                ">
                                    {{ $tipoT }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['headcount'] }}</td>
                            <td class="px-4 py-2.5 text-center font-mono text-gray-400">{{ $tItem['egresos'] }}</td>
                            <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['rotacion'] }}</td>
                            <td class="px-4 py-2.5 text-center font-mono">{{ $tItem['retencion'] }}</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400 font-semibold">
                                🚫 No se encontraron sucursales en esta sección para este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>

    <!-- Dashboard Row 4: Productivity Leaderboard -->
    <section class="vuexy-card p-6 mt-6">
        
        <!-- Title header -->
        <div class="flex justify-between items-center border-b border-gray-100 dark:border-slate-800 pb-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>⚡</span> Escalafón de Productividad (Sección: {{ $seccion }})
                </h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Ranking de locales según su productividad (Transacciones / Horas Hombre).</p>
            </div>
            <span class="text-xs bg-brand-primaryLight dark:bg-red-950/20 text-brand-primary font-bold px-3 py-1 rounded-full border border-brand-primary/10">
                {{ ucfirst($mesNombre) }} {{ $anio }}
            </span>
        </div>

        <!-- Table Leaderboard List -->
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                        <th class="px-5 py-3 text-center w-16">Puesto</th>
                        <th class="px-6 py-3">Código</th>
                        <th class="px-6 py-3">Nombre del Local / Área</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Productividad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                    @forelse($leaderboardProd as $index => $tItem)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150 {{ $codigo == $tItem['codigo'] ? 'bg-red-500/5 dark:bg-red-950/10 font-semibold text-slate-800 dark:text-white border-l-4 border-l-brand-primary' : '' }}">
                            <td class="px-5 py-2.5 text-center font-bold">
                                @if($index == 0)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-950/50 text-yellow-600 dark:text-yellow-400 font-bold">1</span>
                                @elseif($index == 1)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-400 font-bold">2</span>
                                @elseif($index == 2)
                                    <span class="inline-flex w-5.5 h-5.5 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-950/30 text-amber-700 dark:text-amber-500 font-bold">3</span>
                                @else
                                    <span class="text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-2.5 font-mono text-gray-500">
                                @if(is_numeric($tItem['codigo'])) K{{ sprintf('%02d', intval($tItem['codigo'])) }} @else {{ $tItem['codigo'] }} @endif
                            </td>
                            <td class="px-6 py-2.5">
                                <span class="font-semibold text-slate-900 dark:text-white">{{ str_replace('KFC - ', '', $tItem['cdc']) }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $tObj = \App\Models\Tienda::where('codigo_corto', 'K' . sprintf('%02d', intval($tItem['codigo'])))->first();
                                    $tipoT = $tObj ? $tObj->tipo : 'SUP';
                                @endphp
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border border-gray-200 dark:border-slate-700
                                    {{ $tipoT === 'FS' ? 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400' : '' }}
                                    {{ $tipoT === 'FC' ? 'bg-yellow-100 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400' : '' }}
                                    {{ $tipoT === 'IL' ? 'bg-blue-100 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400' : '' }}
                                    {{ $tipoT === 'SUP' ? 'bg-purple-100 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400' : '' }}
                                ">
                                    {{ $tipoT }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-center font-mono font-bold text-brand-primary">
                                {{ number_format($tItem['prod'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400 font-semibold">
                                🚫 No se encontraron sucursales en esta sección para este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>

</div>
@endsection

@section('scripts')
<script>
    let rotationChartInstance = null;
    let productivityChartInstance = null;

    function buildCharts() {
        const isDark = document.documentElement.classList.contains('dark');
        const fontColor = isDark ? '#a5a8ad' : '#5d596c';
        const gridColor = isDark ? '#404656' : '#dbdade';

        Chart.defaults.color = fontColor;
        Chart.defaults.borderColor = gridColor;
        Chart.defaults.font.family = 'Public Sans';

        // 1. Rotation chart
        const rotCtx = document.getElementById('rotationChart').getContext('2d');
        if (rotationChartInstance) {
            rotationChartInstance.destroy();
        }

        const rotGradient = rotCtx.createLinearGradient(0, 0, 0, 260);
        rotGradient.addColorStop(0, 'rgba(220, 38, 38, 0.22)');
        rotGradient.addColorStop(1, 'rgba(220, 38, 38, 0.0)');

        rotationChartInstance = new Chart(rotCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Rotación Local (%)',
                        data: @json($chartRotationLocal),
                        borderColor: '#dc2626',
                        backgroundColor: rotGradient,
                        borderWidth: 3,
                        tension: 0.35,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Promedio Cadena (%)',
                        data: @json($chartRotationChain),
                        borderColor: isDark ? '#82868b' : '#a5a8ad',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.35,
                        fill: false,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 15, font: { size: 10, weight: 600 } }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => value + '%' },
                        grid: { color: gridColor }
                    }
                }
            }
        });

        // 2. Productivity chart
        const prodCtx = document.getElementById('productivityChart').getContext('2d');
        if (productivityChartInstance) {
            productivityChartInstance.destroy();
        }

        productivityChartInstance = new Chart(prodCtx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Horas Hombre (Mano de Obra)',
                        data: @json($chartHorasHombre),
                        type: 'bar',
                        backgroundColor: isDark ? 'rgba(59, 130, 246, 0.8)' : 'rgba(59, 130, 246, 0.7)',
                        hoverBackgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barThickness: 14,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Productividad (Trans/HH)',
                        data: @json($chartProductivity),
                        type: 'line',
                        borderColor: '#dc2626',
                        backgroundColor: 'transparent',
                        borderWidth: 3,
                        tension: 0.3,
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 15, font: { size: 10, weight: 600 } }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: gridColor },
                        ticks: { font: { size: 9 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { font: { size: 9 } }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', buildCharts);
    window.addEventListener('theme-changed', buildCharts);

    // ── Puesto Donut Chart ──
    (function() {
        const puestoData = @json($topPuestos);
        const ctx = document.getElementById('puestoDonutChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: puestoData.map(p => p.label),
                datasets: [{
                    data: puestoData.map(p => p.count),
                    backgroundColor: puestoData.map(p => p.color),
                    borderWidth: 2,
                    borderColor: document.documentElement.classList.contains('dark') ? '#242b3d' : '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} (${puestoData[ctx.dataIndex].pct}%)`
                        }
                    }
                }
            }
        });
    })();
</script>
@endsection
