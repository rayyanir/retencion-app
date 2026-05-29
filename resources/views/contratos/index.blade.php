@extends('layouts.app')

@section('title', 'KFC Venezuela - Historial de Contratos')
@section('page_title', 'Historial de Contratos')

@section('content')
<div class="space-y-6">

    <!-- Search & Filters Panel -->
    <section class="vuexy-card p-6">
        <form method="GET" action="/contratos" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 items-end gap-5">
            <div class="md:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Búsqueda rápida</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por cédula o nombre de colaborador..." class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-gray-400 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-brand-primary">
                    @if($search)
                        <a href="/contratos" class="absolute right-3 top-2.5 text-xs text-gray-400 hover:text-red-500">✕</a>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Puesto / Cargo</label>
                <input type="text" name="puesto" value="{{ $puesto }}" placeholder="Ej: ASOCIADO, GERENTE..." class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white placeholder-gray-400 rounded-lg px-3 py-2 text-xs font-semibold focus:outline-none focus:border-brand-primary">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider mb-2">Sucursal (CDC)</label>
                <select name="tienda_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 text-slate-800 dark:text-white rounded-lg px-3 py-2 focus:outline-none focus:border-brand-primary text-xs font-semibold" onchange="this.form.submit()">
                    <option value="">Todas las Sucursales</option>
                    @foreach($tiendasList as $t)
                        <option value="{{ $t->id }}" {{ $tiendaId == $t->id ? 'selected' : '' }}>[{{ $t->codigo_corto }}] {{ $t->cdc }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold uppercase tracking-wider text-xs py-2.5 rounded-lg transition-all shadow-sm">
                    🔍 Filtrar
                </button>
            </div>
        </form>
    </section>

    <!-- Contracts Register List -->
    <section class="vuexy-card p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300">
                    Historial Completo de Relaciones Laborales
                </h4>
                <p class="text-[10px] text-gray-400 mt-0.5">Historial cronológico de ingresos, reingresos y egresos en el sistema.</p>
            </div>
            <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-gray-400 font-bold px-3 py-1 rounded-full border border-gray-200 dark:border-slate-700">
                Registros: {{ $contratos->total() }}
            </span>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700 mb-4">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                        <th class="px-6 py-3.5 w-28">Cédula</th>
                        <th class="px-6 py-3.5">Colaborador</th>
                        <th class="px-6 py-3.5">Sucursal (CDC)</th>
                        <th class="px-6 py-3.5">Puesto / Cargo</th>
                        <th class="px-4 py-3.5 text-center">Banda</th>
                        <th class="px-4 py-3.5 text-center">Turno</th>
                        <th class="px-4 py-3.5 text-center">Fecha Ingreso</th>
                        <th class="px-4 py-3.5 text-center">Fecha Egreso</th>
                        <th class="px-4 py-3.5 text-center">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                    @forelse($contratos as $contrato)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150">
                            <td class="px-6 py-3 font-mono font-bold text-slate-700 dark:text-gray-400">{{ optional($contrato->empleado)->cedula ?: $contrato->empleado_id }}</td>
                            <td class="px-6 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ optional($contrato->empleado)->nombre ?: 'Colaborador' }}
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-bold font-mono text-gray-500 bg-slate-100 dark:bg-slate-800 px-1 py-0.5 rounded border border-gray-200 dark:border-slate-700">{{ optional($contrato->tienda)->codigo_corto }}</span>
                                    <span class="font-medium">{{ str_replace('KFC - ', '', optional($contrato->tienda)->cdc ?: 'Tienda Genérica') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-300 font-semibold">{{ $contrato->puesto }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $contrato->banda }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $contrato->turno }}</td>
                            <td class="px-4 py-3 text-center font-mono text-gray-500 dark:text-gray-400">{{ $contrato->fecha_ingreso ? $contrato->fecha_ingreso->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 text-center font-mono text-gray-400">
                                {{ $contrato->fecha_egreso ? $contrato->fecha_egreso->format('d/m/Y') : 'Vigente' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border
                                    {{ $contrato->status === 'ACTIVO' ? 'bg-green-100 dark:bg-green-950/30 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800/30' : 'bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800/30' }}
                                ">
                                    {{ $contrato->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-400 font-semibold">
                                🚫 No se encontraron registros de contrato que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="mt-4">
            {{ $contratos->links() }}
        </div>
    </section>

</div>
@endsection
