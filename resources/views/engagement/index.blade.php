@extends('layouts.app')

@section('title', 'KFC Venezuela - Módulo de Engagement')
@section('page_title', 'Engagement de Personal')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 dark:text-white">🤝 Módulo de Engagement</h2>
            <p class="text-xs text-gray-400 mt-0.5">Compromiso · Experiencia vs. Expectativas · Intención de Permanecer</p>
        </div>
        {{-- Year filter --}}
        <form method="GET" action="/engagement" class="flex items-center gap-2">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Año:</label>
            <select name="anio" onchange="this.form.submit()"
                class="bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-brand-primary">
                @foreach($aniosDisponibles as $a)
                    <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
                @if(!in_array(date('Y'), $aniosDisponibles))
                    <option value="{{ date('Y') }}" {{ $anio == date('Y') ? 'selected' : '' }}>{{ date('Y') }}</option>
                @endif
            </select>
        </form>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800/30 rounded-xl px-5 py-3.5">
            <span class="text-green-500 text-lg">✅</span>
            <span class="text-sm font-semibold text-green-700 dark:text-green-400">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/30 rounded-xl px-5 py-3.5">
            <span class="text-red-500 text-lg">❌</span>
            <span class="text-sm font-semibold text-red-700 dark:text-red-400">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Two-column layout: Form + Import --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Manual Entry Form ── --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Manual form card --}}
            <div class="vuexy-card p-6">
                <h3 class="text-sm font-bold text-slate-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                    ✏️ <span>Registrar / Actualizar Local</span>
                </h3>
                <form method="POST" action="{{ route('engagement.store') }}" class="space-y-4" id="engagement-form">
                    @csrf

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Año *</label>
                            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="2099" required
                                class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mes (opcional)</label>
                            <select name="mes" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                                <option value="">— Sin mes —</option>
                                @foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $m => $mn)
                                    <option value="{{ $m }}">{{ $mn }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Local *</label>
                        <select name="codigo_local" id="select-local" required
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary"
                            onchange="fillNombre(this)">
                            <option value="">— Seleccionar local —</option>
                            @foreach($locales as $loc)
                                <option value="{{ $loc->codigo }}" data-nombre="{{ $loc->nombre }}">
                                    [{{ $loc->codigo }}] {{ str_replace('KFC - ', '', $loc->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="nombre_local" id="hidden-nombre">

                    {{-- Scores --}}
                    <div class="space-y-3 pt-1">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                                🤝 Compromiso (0–100%)
                            </label>
                            <div class="relative">
                                <input type="number" name="compromiso" step="0.1" min="0" max="100" placeholder="ej. 87.5"
                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 pr-8 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                                ⭐ Experiencia vs. Expectativas (0–100%)
                            </label>
                            <div class="relative">
                                <input type="number" name="experiencia" step="0.1" min="0" max="100" placeholder="ej. 74.2"
                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 pr-8 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">
                                💙 Intención de Permanecer (0–100%)
                            </label>
                            <div class="relative">
                                <input type="number" name="intencion_permanecer" step="0.1" min="0" max="100" placeholder="ej. 91.0"
                                    class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 pr-8 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                                <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">N° Encuestados</label>
                            <input type="number" name="n_encuestados" min="0" placeholder="ej. 25"
                                class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Notas (opcional)</label>
                            <textarea name="notas" rows="2" placeholder="Observaciones..."
                                class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary resize-none"></textarea>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs transition-colors duration-200 flex items-center justify-center gap-2">
                        💾 Guardar Registro
                    </button>
                </form>
            </div>

            {{-- Excel Import Card --}}
            <div class="vuexy-card p-6">
                <h3 class="text-sm font-bold text-slate-700 dark:text-gray-200 mb-1 flex items-center gap-2">
                    📥 <span>Importar desde Excel</span>
                </h3>
                <p class="text-[10px] text-gray-400 mb-4">
                    El Excel debe tener las columnas: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">Codigo, Nombre, Compromiso, Experiencia, Intencion, N_Encuestados</code>
                </p>
                <form method="POST" action="{{ route('engagement.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Año *</label>
                            <input type="number" name="anio" value="{{ $anio }}" min="2020" max="2099" required
                                class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Mes (opcional)</label>
                            <select name="mes" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:border-brand-primary">
                                <option value="">— Sin mes —</option>
                                @foreach([1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'] as $m => $mn)
                                    <option value="{{ $m }}">{{ $mn }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Archivo Excel *</label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-600 dark:text-gray-300 focus:outline-none focus:border-brand-primary file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-brand-primaryLight file:text-brand-primary">
                    </div>
                    <button type="submit"
                        class="w-full bg-slate-700 hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold py-2.5 px-4 rounded-lg text-xs transition-colors duration-200 flex items-center justify-center gap-2">
                        📤 Importar Excel
                    </button>
                </form>
            </div>

        </div>

        {{-- ── Records Table ── --}}
        <div class="lg:col-span-2">
            <div class="vuexy-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-slate-700 dark:text-gray-200 flex items-center gap-2">
                        📋 <span>Registros Cargados — {{ $anio }}</span>
                    </h3>
                    <span class="text-xs bg-slate-100 dark:bg-slate-800 text-gray-500 dark:text-gray-400 font-semibold px-3 py-1 rounded-full">
                        {{ $registros->count() }} locales
                    </span>
                </div>

                @if($registros->isEmpty())
                    <div class="text-center py-16 text-gray-400">
                        <div class="text-5xl mb-3">📭</div>
                        <p class="font-semibold">No hay registros de engagement para {{ $anio }}</p>
                        <p class="text-xs mt-1">Carga datos manualmente o importa un Excel desde el panel izquierdo.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                                    <th class="px-4 py-3">Local</th>
                                    <th class="px-3 py-3 text-center">Período</th>
                                    <th class="px-3 py-3 text-center">🤝 Compromiso</th>
                                    <th class="px-3 py-3 text-center">⭐ Experiencia</th>
                                    <th class="px-3 py-3 text-center">💙 Int. Permanecer</th>
                                    <th class="px-3 py-3 text-center">Encuestados</th>
                                    <th class="px-3 py-3 text-center w-16">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                                @foreach($registros as $reg)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                        <td class="px-4 py-3">
                                            <span class="font-mono text-[10px] text-gray-400 mr-1">K{{ sprintf('%02d', intval($reg->codigo_local)) }}</span>
                                            <span class="font-semibold text-slate-800 dark:text-white text-xs">
                                                {{ str_replace('KFC - ', '', $reg->nombre_local ?? $reg->codigo_local) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center text-[10px] font-semibold text-gray-400">
                                            {{ $reg->anio }}{{ $reg->mes ? ' / ' . str_pad($reg->mes, 2, '0', STR_PAD_LEFT) : '' }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($reg->compromiso !== null)
                                                @php $c = $reg->compromiso; @endphp
                                                <span class="inline-flex items-center gap-1 font-bold font-mono
                                                    {{ $c >= 80 ? 'text-green-600 dark:text-green-400' : ($c >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                                    {{ number_format($c, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($reg->experiencia !== null)
                                                @php $e = $reg->experiencia; @endphp
                                                <span class="font-bold font-mono
                                                    {{ $e >= 80 ? 'text-green-600 dark:text-green-400' : ($e >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                                    {{ number_format($e, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($reg->intencion_permanecer !== null)
                                                @php $i = $reg->intencion_permanecer; @endphp
                                                <span class="font-bold font-mono
                                                    {{ $i >= 80 ? 'text-green-600 dark:text-green-400' : ($i >= 60 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                                    {{ number_format($i, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center font-mono text-gray-400">
                                            {{ $reg->n_encuestados > 0 ? $reg->n_encuestados : '—' }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <form method="POST" action="{{ route('engagement.destroy', $reg->id) }}"
                                                onsubmit="return confirm('¿Eliminar este registro?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-400 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1 rounded"
                                                    title="Eliminar">
                                                    🗑️
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Summary averages --}}
                    @php
                        $avgComp = $registros->whereNotNull('compromiso')->avg('compromiso');
                        $avgExp  = $registros->whereNotNull('experiencia')->avg('experiencia');
                        $avgInt  = $registros->whereNotNull('intencion_permanecer')->avg('intencion_permanecer');
                        $totalEnc = $registros->sum('n_encuestados');
                    @endphp
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl px-4 py-3 text-center border border-gray-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Prom. Compromiso</p>
                            <p class="text-lg font-black font-mono {{ $avgComp >= 80 ? 'text-green-600' : ($avgComp >= 60 ? 'text-yellow-600' : 'text-red-600') }} mt-1">
                                {{ $avgComp ? number_format($avgComp, 1) . '%' : '—' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl px-4 py-3 text-center border border-gray-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Prom. Experiencia</p>
                            <p class="text-lg font-black font-mono {{ $avgExp >= 80 ? 'text-green-600' : ($avgExp >= 60 ? 'text-yellow-600' : 'text-red-600') }} mt-1">
                                {{ $avgExp ? number_format($avgExp, 1) . '%' : '—' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl px-4 py-3 text-center border border-gray-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Prom. Int. Permanecer</p>
                            <p class="text-lg font-black font-mono {{ $avgInt >= 80 ? 'text-green-600' : ($avgInt >= 60 ? 'text-yellow-600' : 'text-red-600') }} mt-1">
                                {{ $avgInt ? number_format($avgInt, 1) . '%' : '—' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl px-4 py-3 text-center border border-gray-100 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Encuestados</p>
                            <p class="text-lg font-black font-mono text-slate-700 dark:text-white mt-1">
                                {{ number_format($totalEnc) }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function fillNombre(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('hidden-nombre').value = opt.dataset.nombre || '';
}
</script>
@endsection
