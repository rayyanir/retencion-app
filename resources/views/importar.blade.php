@extends('layouts.app')

@section('title', 'KFC Venezuela - Carga de Datos y Consola')
@section('page_title', 'Consola de Carga')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Success & Error Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-950/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-400 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span>✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-lg bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-400 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <span>⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Excel Uploader Card -->
    <section class="vuexy-card p-6 md:p-8">
        <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2.5 mb-2">
            <span>📂</span> Cargar Archivo de Datos (Excel)
        </h3>
        <p class="text-xs text-gray-400 dark:text-gray-500 leading-relaxed mb-6">
            Sube el archivo Excel original (`DATA PARA RAY.xlsx`) para importar y actualizar de manera automática la dotación de personal, los centros de costos (CDC), el histórico de bajas y el volumen de transacciones de cada sucursal.
        </p>

        <form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="import-form">
            @csrf
            <div class="border-2 border-dashed border-gray-200 dark:border-slate-700 hover:border-brand-primary/50 dark:hover:border-brand-primary/50 rounded-xl p-10 text-center cursor-pointer transition-all relative" id="drop-zone">
                <input type="file" name="excel_file" id="excel_file_input" required class="absolute inset-0 opacity-0 cursor-pointer" onchange="handleFileSelected(this)">
                <span class="text-4xl block mb-2 select-none">📊</span>
                <span id="file-chosen" class="text-xs font-bold text-slate-500 dark:text-gray-400 block select-none">Haga clic o arrastre el archivo .xlsx aquí</span>
                <span id="file-size" class="text-[10px] text-gray-400 block mt-1 select-none">Soporta archivos .xlsx y .xls hasta 20MB</span>
            </div>

            <!-- Loading Spinner Indicator (Hidden by default) -->
            <div id="loading-indicator" class="hidden flex items-center justify-center gap-3 py-4 text-xs font-bold text-brand-primary">
                <svg class="animate-spin h-5 w-5 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Procesando archivo e recalculando base de datos. Por favor espere...</span>
            </div>

            <button type="submit" id="btn-submit" class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold uppercase tracking-wider text-xs py-3.5 rounded-xl transition-all shadow-md">
                🚀 Iniciar Importación
            </button>
        </form>
    </section>

    <!-- Upload History Card -->
    <section class="vuexy-card p-6">
        <h4 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-gray-300 mb-4">
            Historial de Archivos Almacenados
        </h4>
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-slate-700">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/50 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-slate-700 tracking-wider">
                        <th class="px-6 py-3">Nombre del Archivo</th>
                        <th class="px-6 py-3">Fecha de Carga</th>
                        <th class="px-4 py-3 text-right">Tamaño</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-800 text-slate-600 dark:text-gray-300">
                    @forelse($importLog as $log)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors duration-150">
                            <td class="px-6 py-2.5 font-semibold text-slate-900 dark:text-white">{{ $log['name'] }}</td>
                            <td class="px-6 py-2.5 font-mono text-gray-500 dark:text-gray-400">{{ $log['date'] }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-gray-400">{{ $log['size'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-6 text-center text-gray-400">
                                📂 Aún no se han registrado cargas desde la consola.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Dangerous Database Reset Card -->
    <section class="vuexy-card p-6 border-l-4 border-l-brand-danger bg-red-500/5 dark:bg-red-950/5">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <h4 class="text-sm font-bold uppercase tracking-wider text-red-600 dark:text-red-400 flex items-center gap-1.5">
                    <span>⚠️</span> ZONA DE PELIGRO
                </h4>
                <p class="text-xs text-slate-600 dark:text-gray-400 mt-1 leading-relaxed max-w-lg">
                    Restablecer la base de datos vaciará por completo todas las tablas de empleados, sucursales, contratos e histórico de transacciones. El usuario administrador básico se creará de nuevo automáticamente.
                </p>
            </div>
            <div>
                <button onclick="confirmReset()" class="whitespace-nowrap bg-red-600 hover:bg-red-700 text-white font-bold uppercase tracking-wider text-xs px-5 py-3 rounded-lg transition-all shadow-sm">
                    🗑️ Vaciar Base de Datos
                </button>
                <form id="reset-form" action="{{ route('database.reset') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </section>

</div>
@endsection

@section('scripts')
<script>
    function handleFileSelected(input) {
        const fileChosen = document.getElementById('file-chosen');
        if (input.files.length > 0) {
            fileChosen.textContent = '📄 ' + input.files[0].name;
            fileChosen.classList.remove('text-slate-500', 'dark:text-gray-400');
            fileChosen.classList.add('text-brand-primary', 'font-semibold');
        } else {
            fileChosen.textContent = 'Haga clic o arrastre el archivo .xlsx aquí';
            fileChosen.classList.add('text-slate-500', 'dark:text-gray-400');
            fileChosen.classList.remove('text-brand-primary', 'font-semibold');
        }
    }

    // Set loading indicator upon submit
    document.getElementById('import-form').addEventListener('submit', function() {
        document.getElementById('btn-submit').classList.add('hidden');
        document.getElementById('drop-zone').classList.add('opacity-40', 'pointer-events-none');
        document.getElementById('loading-indicator').classList.remove('hidden');
    });

    // Handle Drag & Drop styling
    const dropZone = document.getElementById('drop-zone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-brand-primary', 'bg-red-500/5');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-brand-primary', 'bg-red-500/5');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if(files.length > 0) {
            document.getElementById('excel_file_input').files = files;
            handleFileSelected(document.getElementById('excel_file_input'));
        }
    });

    function confirmReset() {
        if (confirm("🚨 ¿ESTÁ SEGURO DE BORRAR LA BASE DE DATOS?\n\nEsta acción eliminará de forma irreversible toda la información importada de empleados, contratos y sucursales. El usuario 'admin@kfc.com' con clave 'admin1234' volverá a crearse para ingresar al sistema.")) {
            document.getElementById('reset-form').submit();
        }
    }
</script>
@endsection
