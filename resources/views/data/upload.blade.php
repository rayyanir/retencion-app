@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Carga de Datos</h2>
            <p class="text-xs text-gray-500 mt-1">Sube los archivos Excel mensuales para actualizar las métricas y la plantilla.</p>
        </div>
        <a href="{{ route('datos.editor') }}" class="text-sm font-bold bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-slate-700 dark:text-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700 transition">
            ✏️ Editor Manual
        </a>
    </div>

    @if(session('success'))
        <div class="bg-brand-successLight border border-brand-success/20 text-brand-success px-4 py-3 rounded-xl mb-4">
            <span class="font-bold">¡Éxito!</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/30 text-red-600 dark:text-red-400 px-4 py-3 rounded-xl mb-4">
            <span class="font-bold">Error:</span> {{ session('error') }}
        </div>
    @endif

    <div class="vuexy-card p-6 border-t-4 border-t-brand-primary">
        <form action="{{ route('datos.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Archivo Productividad y Nómina -->
            <div class="p-5 border border-dashed border-gray-300 dark:border-slate-700 rounded-xl bg-gray-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                    📊 Data Productividad y Nómina (Data para Ray)
                </h3>
                <p class="text-xs text-gray-500 mb-4">Este archivo inicializa tiendas y carga los empleados activos.</p>
                
                <input type="file" name="file_kfc" accept=".xlsx,.xls" class="block w-full text-sm text-slate-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-xs file:font-bold
                    file:bg-brand-primaryLight file:text-brand-primary
                    hover:file:bg-brand-primary/20
                "/>
            </div>

            <!-- Archivo Reporte Rotación -->
            <div class="p-5 border border-dashed border-gray-300 dark:border-slate-700 rounded-xl bg-gray-50 dark:bg-slate-900/50">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-2 flex items-center gap-2">
                    🔄 Reporte Rotación y Retención Mensual
                </h3>
                <p class="text-xs text-gray-500 mb-4">Este archivo carga el resumen acumulado mensual por tienda y zona.</p>
                
                <input type="file" name="file_reporte" accept=".xlsx,.xls" class="block w-full text-sm text-slate-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-xs file:font-bold
                    file:bg-brand-infoLight file:text-brand-info
                    hover:file:bg-brand-info/20
                "/>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-brand-primary hover:bg-brand-primary/90 text-white font-bold py-2.5 px-6 rounded-xl transition shadow-lg shadow-brand-primary/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Procesar Archivos
                </button>
            </div>
            
            <p class="text-[10px] text-gray-400 text-center">Nota: Puedes subir ambos archivos al mismo tiempo o uno solo. El procesamiento puede tardar un par de minutos.</p>
        </form>
    </div>

</div>
@endsection
