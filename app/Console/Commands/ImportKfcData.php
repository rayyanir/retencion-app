<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\Zona;
use App\Models\Tienda;
use App\Models\Empleado;
use App\Models\Contrato;
use App\Models\MetricaOperativa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportKfcData extends Command
{
    protected $signature = 'kfc:import {--file= : Ruta del archivo DATA PARA RAY.xlsx}';
    protected $description = 'Importa datos operativos, tiendas, zonas, empleados y contratos desde el archivo Excel';

    public function handle()
    {
        $filePath = $this->option('file') ?: 'C:\retencion kfc\DATA PARA RAY.xlsx';

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe en la ruta: $filePath");
            return 1;
        }

        $this->info("Cargando libro Excel con PHPSpreadsheet...");
        $spreadsheet = IOFactory::load($filePath);

        // --- PASO 1: IMPORTAR PRODUCTIVIDAD (ZONAS Y TIENDAS) ---
        $this->info("Procesando pestaña 'PRODUCTIVIDAD 2024-2026' para inicializar Zonas y Tiendas...");
        $prodSheet = $spreadsheet->getSheetByName('PRODUCTIVIDAD 2024-2026');
        if (!$prodSheet) {
            $this->error("No se encontró la pestaña 'PRODUCTIVIDAD 2024-2026'");
            return 1;
        }

        $rowCount = $prodSheet->getHighestRow();
        
        DB::beginTransaction();
        try {
            $tiendasCreadas = 0;
            
            // Fila 5 es la cabecera del año. Fila 6 comienzan los datos de transacciones
            for ($r = 6; $r <= 44; $r++) {
                $localCode = trim($prodSheet->getCell([1, $r])->getValue()); // LOCAL (ej: K01)
                $zonaNum = trim($prodSheet->getCell([2, $r])->getValue());   // ZONA (ej: 1)
                $tipoTienda = trim($prodSheet->getCell([3, $r])->getValue()); // TIPO (ej: IL)

                if (empty($localCode) || empty($zonaNum) || empty($tipoTienda)) {
                    continue;
                }

                // Crear Zona si no existe
                $zona = Zona::firstOrCreate(
                    ['id' => intval($zonaNum)],
                    ['nombre' => "Zona $zonaNum"]
                );

                // Crear Tienda (cdc temporal que se actualizará con el nombre largo en la importación de personal)
                $tienda = Tienda::firstOrCreate(
                    ['codigo_corto' => $localCode],
                    [
                        'cdc' => "Tienda Temporal $localCode",
                        'tipo' => $tipoTienda,
                        'zona_id' => $zona->id
                    ]
                );
                
                $tiendasCreadas++;

                // Importar transacciones históricas (Fila 6 a 44)
                $this->importHistoricoTransacciones($prodSheet, $r, $tienda->id);
            }
            
            $this->info("Transacciones importadas. Ahora importando Horas Hombre...");

            for ($r = 46; $r <= 80; $r++) {
                $localCode = trim($prodSheet->getCell([1, $r])->getValue()); // LOCAL
                if (empty($localCode)) {
                    continue;
                }

                $tienda = Tienda::where('codigo_corto', $localCode)->first();
                if ($tienda) {
                    $this->importHistoricoHorasHombre($prodSheet, $r, $tienda->id);
                }
            }
            
            $this->info("Zonas y Tiendas base inicializadas ($tiendasCreadas tiendas).");
            
            // --- PASO 2: IMPORTAR PERSONAL (ACTIVOS Y EGRESOS HISTÓRICOS) ---
            $this->info("Procesando pestaña 'ACTIVOS Rot y Ret.' para importar personal...");
            $personalSheet = $spreadsheet->getSheetByName('ACTIVOS Rot y Ret.');
            if (!$personalSheet) {
                $this->error("No se encontró la pestaña 'ACTIVOS Rot y Ret.'");
                DB::rollBack();
                return 1;
            }

            $personalRows = $personalSheet->getHighestRow();
            $empleadosImportados = 0;
            $contratosImportados = 0;

            for ($r = 2; $r <= $personalRows; $r++) {
                $cedula = trim($personalSheet->getCell([2, $r])->getValue());
                $nombre = trim($personalSheet->getCell([3, $r])->getValue());
                $ingresoVal = $personalSheet->getCell([4, $r])->getValue();
                $egresoVal = $personalSheet->getCell([5, $r])->getValue();
                $status = trim($personalSheet->getCell([8, $r])->getValue());
                $banda = trim($personalSheet->getCell([9, $r])->getValue());
                $puesto = trim($personalSheet->getCell([10, $r])->getValue());
                $cdc = preg_replace('/\s+/', ' ', trim($personalSheet->getCell([11, $r])->getValue())); // Limpiar espacios extras
                $sexo = trim($personalSheet->getCell([12, $r])->getValue());
                $turno = trim($personalSheet->getCell([13, $r])->getValue());

                if (empty($cedula) || empty($nombre)) {
                    continue;
                }

                // Intentar extraer el número de tienda del CDC (ej: "KFC - 001 LOS CORTIJOS" -> "1")
                $tiendaId = $this->obtenerTiendaIdDesdeCdc($cdc, $personalSheet->getCell([11, $r])->getCoordinate());

                // Crear o actualizar empleado
                $empleado = Empleado::updateOrCreate(
                    ['cedula' => $cedula],
                    [
                        'nombre' => $nombre,
                        'sexo' => in_array(strtolower($sexo), ['femenino', 'f', 'mujer']) ? 'Femenino' : 'Masculino',
                        'pcd' => false // Por defecto, se puede configurar luego
                    ]
                );

                $fechaIngreso = $this->parseDate($ingresoVal);
                $fechaEgreso = $this->parseDate($egresoVal);

                // Crear o actualizar contrato
                Contrato::updateOrCreate(
                    [
                        'empleado_id' => $empleado->cedula,
                        'tienda_id' => $tiendaId,
                        'fecha_ingreso' => $fechaIngreso
                    ],
                    [
                        'fecha_egreso' => $fechaEgreso,
                        'banda' => $banda ?: 'Asociado',
                        'puesto' => $puesto ?: 'ASOCIADO',
                        'turno' => $turno ?: 'DIURNO',
                        'status' => in_array(strtoupper($status), ['EGRESO', 'INACTIVO']) ? 'EGRESO' : 'ACTIVO'
                    ]
                );

                $empleadosImportados++;
                $contratosImportados++;
            }

            // --- PASO 3: IMPORTAR EGRESOS 2023 DESDE EL OTRO EXCEL ---
            $egresosFilePath = 'C:\retencion kfc\RETENCION Y ROTACON_ABRIL 2026_0405.xlsx';
            if (file_exists($egresosFilePath)) {
                $this->info("Procesando pestaña 'EGRESOS' de $egresosFilePath para históricos de 2023...");
                $egresosSpreadsheet = IOFactory::load($egresosFilePath);
                $egresosSheet = $egresosSpreadsheet->getSheetByName('EGRESOS');
                if ($egresosSheet) {
                    $egRows = $egresosSheet->getHighestRow();
                    $egresos2023Importados = 0;
                    for ($r = 3; $r <= $egRows; $r++) {
                        $cedula = trim($egresosSheet->getCell([2, $r])->getValue());
                        $nombre = trim($egresosSheet->getCell([3, $r])->getValue());
                        $cdc = preg_replace('/\s+/', ' ', trim($egresosSheet->getCell([4, $r])->getValue()));
                        $cargo = trim($egresosSheet->getCell([5, $r])->getValue());

                        if (empty($cedula) || empty($nombre)) {
                            continue;
                        }

                        $tiendaId = $this->obtenerTiendaIdDesdeCdc($cdc, $egresosSheet->getCell([4, $r])->getCoordinate());

                        // Crear o actualizar empleado
                        $empleado = Empleado::updateOrCreate(
                            ['cedula' => $cedula],
                            [
                                'nombre' => $nombre,
                                'sexo' => 'Masculino',
                                'pcd' => false
                            ]
                        );

                        // Crear contrato histórico de 2023
                        Contrato::updateOrCreate(
                            [
                                'empleado_id' => $empleado->cedula,
                                'tienda_id' => $tiendaId,
                                'fecha_ingreso' => Carbon::parse('2023-01-01'),
                                'status' => 'EGRESO'
                            ],
                            [
                                'fecha_egreso' => Carbon::parse('2023-12-31'),
                                'banda' => 'Asociado',
                                'puesto' => $cargo ?: 'ASOCIADO',
                                'turno' => 'DIURNO'
                            ]
                        );
                        $egresos2023Importados++;
                    }
                    $this->info("  - Egresos históricos 2023 cargados: $egresos2023Importados");
                }
            }

            DB::commit();
            $this->info("Importación completada con éxito:");
            $this->info("  - Empleados procesados: $empleadosImportados");
            $this->info("  - Contratos creados/actualizados: $contratosImportados");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error en la importación: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function obtenerTiendaIdDesdeCdc($cdc, $coordinate)
    {
        if (empty($cdc)) {
            // Asignar a tienda genérica
            return $this->getGenericTiendaId();
        }

        // Buscar números en la cadena (ej: 001 o 1)
        if (preg_match('/KFC\s*-\s*0*(\d+)/i', $cdc, $matches)) {
            $num = intval($matches[1]);
            $codigoCorto = sprintf("K%02d", $num);
            
            $tienda = Tienda::where('codigo_corto', $codigoCorto)->first();
            if ($tienda) {
                // Actualizar el nombre CDC real de la tienda
                if ($tienda->cdc === "Tienda Temporal $codigoCorto" || $tienda->cdc !== $cdc) {
                    $tienda->update(['cdc' => $cdc]);
                }
                return $tienda->id;
            }
        }

        // Si no coincide por número, buscar CDC directamente
        $tienda = Tienda::where('cdc', $cdc)->first();
        if ($tienda) {
            return $tienda->id;
        }

        // Si no existe, crear tienda temporal
        $zona = Zona::firstOrCreate(['id' => 99], ['nombre' => 'Zona General']);
        $nuevaTienda = Tienda::create([
            'cdc' => $cdc,
            'codigo_corto' => 'KTMP',
            'tipo' => 'IL',
            'zona_id' => $zona->id
        ]);
        
        return $nuevaTienda->id;
    }

    private function getGenericTiendaId()
    {
        $zona = Zona::firstOrCreate(['id' => 99], ['nombre' => 'Zona General']);
        $tienda = Tienda::firstOrCreate(
            ['codigo_corto' => 'KGEN'],
            [
                'cdc' => 'KFC - TIENDA GENÉRICA',
                'tipo' => 'IL',
                'zona_id' => $zona->id
            ]
        );
        return $tienda->id;
    }

    private function importHistoricoTransacciones($sheet, $row, $tiendaId)
    {
        // 2024: Columnas D a O (Col 4 a 15)
        // 2025: Columnas P a AA (Col 16 a 27)
        // 2026: Columnas AB a AM (Col 28 a 39)
        $years = [
            2024 => ['start' => 4, 'end' => 15],
            2025 => ['start' => 16, 'end' => 27],
            2026 => ['start' => 29, 'end' => 40]
        ];

        foreach ($years as $anio => $cols) {
            $mes = 1;
            for ($c = $cols['start']; $c <= $cols['end']; $c++) {
                $val = $sheet->getCellByColumnAndRow($c, $row)->getCalculatedValue();
                
                if ($tiendaId == 1 && $anio == 2024 && $mes == 1) {
                    \Log::info("Row: $row, Col: $c, Val: $val");
                }
                
                if (is_numeric($val) && $val > 0) {
                    MetricaOperativa::updateOrCreate(
                        [
                            'tienda_id' => $tiendaId,
                            'mes' => $mes,
                            'anio' => $anio
                        ],
                        [
                            'transacciones' => intval($val)
                        ]
                    );
                }
                $mes++;
            }
        }
    }

    private function importHistoricoHorasHombre($sheet, $row, $tiendaId)
    {
        $years = [
            2024 => ['start' => 4, 'end' => 15],
            2025 => ['start' => 16, 'end' => 27],
            2026 => ['start' => 29, 'end' => 40]
        ];

        foreach ($years as $anio => $cols) {
            $mes = 1;
            for ($c = $cols['start']; $c <= $cols['end']; $c++) {
                $val = $sheet->getCellByColumnAndRow($c, $row)->getCalculatedValue();
                
                if (is_numeric($val) && $val > 0) {
                    MetricaOperativa::updateOrCreate(
                        [
                            'tienda_id' => $tiendaId,
                            'mes' => $mes,
                            'anio' => $anio
                        ],
                        [
                            'horas_hombre' => floatval($val)
                        ]
                    );
                }
                $mes++;
            }
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            // Excel serial date number
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                return null;
            }
        }

        $strValue = trim($value);
        try {
            // dd/mm/yyyy
            return Carbon::createFromFormat('d/m/Y', $strValue);
        } catch (\Exception $e) {
            try {
                return Carbon::parse($strValue);
            } catch (\Exception $ex) {
                return null;
            }
        }
    }
}
