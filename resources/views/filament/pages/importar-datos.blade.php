<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Area (Left / 2 Cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                📂 Cargar Archivo de Datos
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Selecciona el archivo Excel original (`DATA PARA RAY.xlsx`) para actualizar el registro completo de la nómina activa, ingresos, egresos y transacciones mensuales de la cadena.
            </p>

            <form wire:submit="importar" class="space-y-6">
                {{ $this->form }}
                
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    @foreach($this->getFormActions() as $action)
                        {{ $action }}
                    @endforeach
                </div>
            </form>
        </div>

        <!-- Help/Guidelines Area (Right / 1 Col) -->
        <div class="bg-gray-50 dark:bg-gray-850 rounded-xl p-6 border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    💡 Instrucciones de Formato
                </h3>
                <ul class="space-y-3 text-xs text-gray-600 dark:text-gray-400 list-disc list-inside">
                    <li>El archivo debe ser un libro <strong>Excel (.xlsx)</strong> válido.</li>
                    <li>Debe contener la pestaña <strong>'PRODUCTIVIDAD 2024-2026'</strong> con las transacciones por local en el formato histórico de doble entrada.</li>
                    <li>Debe contener la pestaña <strong>'ACTIVOS Rot y Ret.'</strong> con la nómina activa y las bajas de personal, incluyendo Cédula, CDC, Turno, y Fechas de Ingreso/Egreso.</li>
                    <li>El proceso actualizará los registros de sucursales existentes y creará nuevos colaboradores sin borrar datos anteriores históricos.</li>
                </ul>
            </div>

            <div class="mt-6 p-4 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 rounded-lg text-xs text-red-800 dark:text-red-300">
                ⚠️ <strong>Aviso Importante:</strong> El proceso de ingestión lee miles de registros en tiempo real. Puede tardar entre 15 y 30 segundos en completarse. Por favor no cierres ni recargues la pestaña mientras se ejecuta.
            </div>
        </div>

    </div>
</x-filament-panels::page>
