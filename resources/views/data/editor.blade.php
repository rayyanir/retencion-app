@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Editor Manual de Métricas</h2>
            <p class="text-xs text-gray-500 mt-1">Modifica los valores pre-calculados (Activos, Salidas, Rotación, Retención) por local y mes.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('datos.upload_form') }}" class="text-sm font-bold bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-700 dark:text-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700 transition">
                📂 Volver a Carga
            </a>
            <a href="{{ url('/') }}" class="text-sm font-bold bg-brand-primary hover:bg-brand-primary/90 text-white px-4 py-2 rounded-xl shadow-lg shadow-brand-primary/30 transition">
                Ir al Dashboard
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="vuexy-card p-4 border-l-4 border-l-brand-info">
        <form method="GET" action="{{ route('datos.editor') }}" class="flex flex-wrap gap-4 items-end">
            
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Año</label>
                <select name="anio" class="bg-slate-50 border border-gray-200 text-slate-800 text-xs rounded-lg focus:ring-brand-primary focus:border-brand-primary block w-28 p-2 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    @foreach($disponiblesAnios as $a)
                        <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Mes</label>
                <select name="mes" class="bg-slate-50 border border-gray-200 text-slate-800 text-xs rounded-lg focus:ring-brand-primary focus:border-brand-primary block w-32 p-2 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Sección</label>
                <select name="seccion" class="bg-slate-50 border border-gray-200 text-slate-800 text-xs rounded-lg focus:ring-brand-primary focus:border-brand-primary block w-40 p-2 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                    <option value="ZONA" {{ $seccion == 'ZONA' ? 'selected' : '' }}>Sucursales (Zonas)</option>
                    <option value="OPERACIONES" {{ $seccion == 'OPERACIONES' ? 'selected' : '' }}>Operaciones</option>
                    <option value="PLANTA" {{ $seccion == 'PLANTA' ? 'selected' : '' }}>Planta</option>
                    <option value="CAR" {{ $seccion == 'CAR' ? 'selected' : '' }}>CAR</option>
                </select>
            </div>

            <div>
                <button type="submit" class="bg-brand-info hover:bg-brand-info/90 text-white font-bold py-2 px-5 rounded-lg transition text-xs">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla Editable -->
    <div class="vuexy-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                        <th class="px-4 py-3">Categoría</th>
                        <th class="px-4 py-3">Código</th>
                        <th class="px-4 py-3">Local / Área</th>
                        <th class="px-4 py-3 text-center">Activos</th>
                        <th class="px-4 py-3 text-center">Salidas</th>
                        <th class="px-4 py-3 text-center">Sal. 0-12</th>
                        <th class="px-4 py-3 text-center">Rotación (%)</th>
                        <th class="px-4 py-3 text-center">Retención (%)</th>
                        <th class="px-4 py-3 text-center">Trans.</th>
                        <th class="px-4 py-3 text-center">HH</th>
                        <th class="px-4 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                    @forelse($metricas as $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors" data-id="{{ $item->id }}">
                            <td class="px-4 py-3 font-semibold text-[10px]">{{ $item->categoria }}</td>
                            <td class="px-4 py-3 font-mono text-gray-500">{{ $item->codigo }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-white">{{ $item->nombre }}</td>
                            
                            <!-- Activos -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" data-field="activos" value="{{ $item->activos }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-brand-primary focus:outline-none font-mono">
                            </td>
                            
                            <!-- Salidas -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" data-field="salidas" value="{{ $item->salidas }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-brand-primary focus:outline-none font-mono">
                            </td>

                            <!-- Salidas 0-12 -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" data-field="salidas_0_12" value="{{ $item->salidas_0_12 }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-brand-primary focus:outline-none font-mono">
                            </td>
                            
                            <!-- Rotacion -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" step="0.01" data-field="rotacion" value="{{ $item->rotacion }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-brand-warning focus:outline-none font-mono text-brand-warning font-bold">
                            </td>
                            
                            <!-- Retencion -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" step="0.01" data-field="retencion" value="{{ $item->retencion }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-brand-success focus:outline-none font-mono text-brand-success font-bold">
                            </td>

                            <!-- Transacciones -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" data-field="transacciones" value="{{ $item->transacciones }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-blue-500 focus:outline-none font-mono text-blue-600 font-bold">
                            </td>

                            <!-- Horas Hombre -->
                            <td class="px-4 py-2 text-center">
                                <input type="number" step="0.01" data-field="horas_hombre" value="{{ $item->horas_hombre }}" class="metric-input w-16 text-center bg-transparent border-b border-gray-300 focus:border-purple-500 focus:outline-none font-mono text-purple-600 font-bold">
                            </td>

                            <td class="px-4 py-2 text-center">
                                <button class="save-btn text-[10px] bg-gray-100 hover:bg-brand-primary hover:text-white text-gray-600 font-bold px-2 py-1 rounded transition hidden">
                                    Guardar
                                </button>
                                <span class="status-icon text-green-500 hidden">✔️</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-400 font-semibold">
                                No hay métricas cargadas para los filtros seleccionados. Si es un nuevo mes, sube el Excel primero.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show save button when input changes
    document.querySelectorAll('.metric-input').forEach(input => {
        input.addEventListener('input', function() {
            const tr = this.closest('tr');
            tr.querySelector('.save-btn').classList.remove('hidden');
            tr.querySelector('.status-icon').classList.add('hidden');
        });
    });

    // Save functionality
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const tr = this.closest('tr');
            const id = tr.dataset.id;
            const btnEl = this;
            const statusIcon = tr.querySelector('.status-icon');
            
            btnEl.innerText = '⏳';
            btnEl.disabled = true;

            const inputs = tr.querySelectorAll('.metric-input');
            let successAll = true;

            for (let input of inputs) {
                const field = input.dataset.field;
                const value = input.value;

                try {
                    const response = await fetch('{{ route('datos.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ id, field, value })
                    });

                    const data = await response.json();
                    if (!data.success) {
                        successAll = false;
                        alert('Error guardando ' + field + ': ' + data.message);
                    }
                } catch (e) {
                    successAll = false;
                    alert('Error de conexión.');
                }
            }

            btnEl.innerText = 'Guardar';
            btnEl.disabled = false;

            if (successAll) {
                btnEl.classList.add('hidden');
                statusIcon.classList.remove('hidden');
                setTimeout(() => {
                    statusIcon.classList.add('hidden');
                }, 2000);
            }
        });
    });
});
</script>
@endsection
