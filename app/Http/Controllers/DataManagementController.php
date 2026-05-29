<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\MetricaReportada;
use App\Models\MetricaOperativa;

class DataManagementController extends Controller
{
    /**
     * Show the file upload form.
     */
    public function showUploadForm()
    {
        return view('data.upload');
    }

    /**
     * Handle the file uploads and run the import commands.
     */
    public function uploadData(Request $request)
    {
        $request->validate([
            'file_kfc' => 'nullable|file|mimes:xlsx,xls',
            'file_reporte' => 'nullable|file|mimes:xlsx,xls',
        ]);

        if (!$request->hasFile('file_kfc') && !$request->hasFile('file_reporte')) {
            return back()->with('error', 'Debes seleccionar al menos un archivo para cargar.');
        }

        $messages = [];

        try {
            if ($request->hasFile('file_kfc')) {
                $path = $request->file('file_kfc')->storeAs('imports', 'data_kfc_' . time() . '.xlsx');
                $fullPath = storage_path('app/' . $path);
                
                Artisan::call('kfc:import', ['--file' => $fullPath]);
                $output = Artisan::output();
                $messages[] = "Archivo de Productividad y Nómina procesado correctamente.";
            }

            if ($request->hasFile('file_reporte')) {
                $path = $request->file('file_reporte')->storeAs('imports', 'reporte_rotacion_' . time() . '.xlsx');
                $fullPath = storage_path('app/' . $path);
                
                Artisan::call('kfc:import-reporte', ['--file' => $fullPath]);
                $output = Artisan::output();
                $messages[] = "Archivo de Reporte Mensual (Rotación y Retención) procesado correctamente.";
            }

            return back()->with('success', implode(' | ', $messages));

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar los archivos: ' . $e->getMessage());
        }
    }

    /**
     * Show the manual editor.
     */
    public function showEditor(Request $request)
    {
        $anio = $request->input('anio', date('Y'));
        $mes = $request->input('mes', date('n'));
        $seccion = $request->input('seccion', 'ZONA');

        // Fetch the metrics for this year, month, seccion (only 'tienda' level, no 'total_zona' or 'total_seccion')
        $metricas = MetricaReportada::where('anio', $anio)
            ->where('mes', $mes)
            ->where('seccion', $seccion)
            ->where('tipo_registro', 'tienda')
            ->orderBy('codigo')
            ->get();

        // Inject MetricaOperativa (transacciones, horas_hombre) into each item
        foreach ($metricas as $item) {
            $item->transacciones = 0;
            $item->horas_hombre = 0;
            
            $codigoCorto = is_numeric($item->codigo) ? 'K' . sprintf('%02d', intval($item->codigo)) : $item->codigo;
            $tienda = \App\Models\Tienda::where('codigo_corto', $codigoCorto)->first();
            
            if ($tienda) {
                $mo = MetricaOperativa::where('tienda_id', $tienda->id)
                    ->where('anio', $anio)
                    ->where('mes', $mes)
                    ->first();
                if ($mo) {
                    $item->transacciones = $mo->transacciones;
                    $item->horas_hombre = $mo->horas_hombre;
                }
            }
        }

        $disponiblesAnios = MetricaReportada::distinct()->pluck('anio')->toArray();
        if (empty($disponiblesAnios)) $disponiblesAnios = [date('Y')];
        
        return view('data.editor', compact('metricas', 'anio', 'mes', 'seccion', 'disponiblesAnios'));
    }

    /**
     * Handle manual updates.
     */
    public function updateMetric(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:metricas_reportadas,id',
            'field' => 'required|string|in:activos,salidas,rotacion,salidas_0_12,retencion,transacciones,horas_hombre',
            'value' => 'required|numeric'
        ]);

        try {
            $metrica = MetricaReportada::findOrFail($request->id);
            $field = $request->field;
            
            if (in_array($field, ['transacciones', 'horas_hombre'])) {
                // Update or Create MetricaOperativa
                $codigoCorto = is_numeric($metrica->codigo) ? 'K' . sprintf('%02d', intval($metrica->codigo)) : $metrica->codigo;
                $tienda = \App\Models\Tienda::where('codigo_corto', $codigoCorto)->first();
                
                if (!$tienda) {
                    throw new \Exception("No se encontró la tienda (CDC) asociada al código {$metrica->codigo} para actualizar operatividad.");
                }

                $mo = MetricaOperativa::firstOrCreate(
                    ['tienda_id' => $tienda->id, 'anio' => $metrica->anio, 'mes' => $metrica->mes],
                    ['transacciones' => 0, 'horas_hombre' => 0]
                );
                
                $mo->$field = $request->value;
                $mo->save();
                
                $newValue = $mo->$field;
            } else {
                // Update MetricaReportada
                $metrica->$field = $request->value;
                $metrica->save();
                
                $newValue = $metrica->$field;
            }

            return response()->json([
                'success' => true,
                'message' => 'Actualizado correctamente',
                'new_value' => $newValue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
