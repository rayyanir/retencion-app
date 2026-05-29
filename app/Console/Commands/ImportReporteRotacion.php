<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\MetricaReportada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportReporteRotacion extends Command
{
    protected $signature = 'kfc:import-reporte {--file= : Ruta del archivo Excel}';
    protected $description = 'Importa métricas mensuales precalculadas (rotación/retención) desde el libro Excel';

    public function handle()
    {
        $filePath = $this->option('file') ?: 'C:\retencion kfc\RETENCION Y ROTACON_ABRIL 2026_0405.xlsx';

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe en la ruta: $filePath");
            return 1;
        }

        $this->info("Cargando libro de reportes con PHPSpreadsheet: $filePath");
        $spreadsheet = IOFactory::load($filePath);

        $sheetsToParse = [
            'KFC x ZONA MENSUAL 2026' => ['categoria' => 'TOTAL'],
            'KFC x ZONA MENSUAL 2025 DICIEM' => ['categoria' => 'TOTAL'],
            'KFC x ZONA. (ASOCIADOS 2026)' => ['categoria' => 'ASOCIADOS'],
            'KFC x ZONA. (ASOCIADOS 2025)' => ['categoria' => 'ASOCIADOS'],
            'KFC x ZONA. (ADM 2026)' => ['categoria' => 'ADM'],
            'KFC x ZONA. (ADM 2025 GTES)' => ['categoria' => 'ADM']
        ];

        DB::beginTransaction();
        try {
            $recordsImported = 0;

            foreach ($sheetsToParse as $sheetName => $info) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if (!$sheet) {
                    $this->warn("No se encontró la pestaña '$sheetName' en el Excel. Saltando...");
                    continue;
                }

                $categoria = $info['categoria'];
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

                $this->info("Procesando pestaña: '$sheetName' (Filas: $highestRow, Columnas: $highestColIndex)");

                // 1. Scan Row 3 to identify the starting columns for each Month Block
                $monthBlocks = [];
                for ($c = 5; $c <= $highestColIndex; $c++) {
                    $dateVal = $sheet->getCell([$c, 3])->getValue();
                    if (empty($dateVal)) {
                        continue;
                    }

                    $parsedDate = null;
                    if (is_numeric($dateVal)) {
                        try {
                            $parsedDate = Carbon::instance(ExcelDate::excelToDateTimeObject($dateVal));
                        } catch (\Exception $e) {
                            $parsedDate = null;
                        }
                    } else {
                        try {
                            $parsedDate = Carbon::parse(trim($dateVal));
                        } catch (\Exception $e) {
                            $parsedDate = null;
                        }
                    }

                    if ($parsedDate) {
                        $monthBlocks[] = [
                            'col_idx' => $c,
                            'year' => $parsedDate->year,
                            'month' => $parsedDate->month
                        ];
                    }
                }

                $this->info("  - Encontrados " . count($monthBlocks) . " bloques mensuales en la pestaña.");

                // 2. Parse the rows and load metrics
                $currentSeccion = 'ZONA';
                $currentZona = 1;

                for ($r = 5; $r <= $highestRow; $r++) {
                    $bVal = trim($sheet->getCell([2, $r])->getValue() ?? '');
                    $cVal = trim($sheet->getCell([3, $r])->getValue() ?? '');
                    $dVal = trim($sheet->getCell([4, $r])->getValue() ?? '');

                    // Skip empty rows
                    if (empty($bVal) && empty($cVal)) {
                        continue;
                    }

                    // Section markers
                    if (strcasecmp($cVal, 'OPERACIONES') === 0) {
                        $currentSeccion = 'OPERACIONES';
                        $currentZona = null;
                        continue;
                    }
                    if (strcasecmp($cVal, 'PLANTA') === 0) {
                        $currentSeccion = 'PLANTA';
                        $currentZona = null;
                        continue;
                    }
                    if (strcasecmp($cVal, 'CAR') === 0 || strcasecmp($bVal, 'CAR') === 0) {
                        $currentSeccion = 'CAR';
                        $currentZona = null;
                        continue;
                    }

                    // Update Zone number if row has a valid numeric zone marker in column D
                    if (is_numeric($dVal)) {
                        $currentZona = intval($dVal);
                    }

                    // Classify the row type
                    $tipoRegistro = null;
                    $nombre = $cVal;
                    $codigo = $bVal;
                    $seccion = $currentSeccion;
                    $zonaNum = $currentZona;

                    if (str_starts_with(strtoupper($cVal), 'TOTAL ZONA')) {
                        $tipoRegistro = 'total_zona';
                        $codigo = 'TOTAL';
                        $seccion = 'ZONA';
                    } elseif (strcasecmp($bVal, 'TOTAL') === 0 || strcasecmp($cVal, 'TOTAL') === 0) {
                        // Check if it is the absolute overall chain total (usually at the very bottom, row index > 78)
                        if ($r >= 80) {
                            $tipoRegistro = 'total_general';
                            $codigo = 'TOTAL';
                            $seccion = 'TOTAL';
                            $zonaNum = null;
                            $nombre = 'TOTAL GENERAL';
                        } else {
                            $tipoRegistro = 'total_seccion';
                            $codigo = 'TOTAL';
                            $zonaNum = null;
                            $nombre = 'TOTAL ' . $currentSeccion;
                        }
                    } elseif (!empty($bVal)) {
                        // Regular shop or office sub-division row
                        $tipoRegistro = 'tienda';
                    }

                    // If row classified, parse all month blocks for this row
                    if ($tipoRegistro) {
                        foreach ($monthBlocks as $mb) {
                            $activos = $this->getVal($sheet, $mb['col_idx'], $r, 0);
                            $salidas = $this->getVal($sheet, $mb['col_idx'] + 1, $r, 0);
                            $rotacion = $this->getVal($sheet, $mb['col_idx'] + 2, $r, 0.0);
                            $salidas_0_12 = $this->getVal($sheet, $mb['col_idx'] + 3, $r, 0);
                            $retencion = $this->getVal($sheet, $mb['col_idx'] + 4, $r, 1.0);
                            
                            // Check bounds before accessing the 6th column (ingresos)
                            $ingresos = 0;
                            if ($mb['col_idx'] + 5 <= $highestColIndex) {
                                $ingresos = $this->getVal($sheet, $mb['col_idx'] + 5, $r, 0);
                            }

                            // Clean values
                            if (!is_numeric($activos)) $activos = 0;
                            if (!is_numeric($salidas)) $salidas = 0;
                            if (!is_numeric($rotacion)) $rotacion = 0.0;
                            if (!is_numeric($salidas_0_12)) $salidas_0_12 = 0;
                            if (!is_numeric($retencion)) $retencion = 1.0;
                            if (!is_numeric($ingresos)) $ingresos = 0;

                            MetricaReportada::updateOrCreate(
                                [
                                    'categoria' => $categoria,
                                    'anio' => $mb['year'],
                                    'mes' => $mb['month'],
                                    'seccion' => $seccion,
                                    'zona_num' => $zonaNum,
                                    'codigo' => $codigo ?: null,
                                    'nombre' => $nombre,
                                    'tipo_registro' => $tipoRegistro
                                ],
                                [
                                    'activos' => intval($activos),
                                    'salidas' => intval($salidas),
                                    'rotacion' => floatval($rotacion),
                                    'salidas_0_12' => intval($salidas_0_12),
                                    'retencion' => floatval($retencion),
                                    'ingresos' => intval($ingresos)
                                ]
                            );
                            $recordsImported++;
                        }
                    }
                }
            }

            DB::commit();
            $this->info("Importación completada: $recordsImported registros guardados/actualizados en la base de datos.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error al importar reporte: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }

        return 0;
    }

    private function getVal($sheet, $col, $row, $default = 0)
    {
        try {
            $cell = $sheet->getCell([$col, $row]);
            $val = $cell->getCalculatedValue();

            if (is_string($val) && str_starts_with($val, '#')) {
                return $default; // Division by zero error etc.
            }

            if ($val === null || $val === '') {
                return $default;
            }

            return $val;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
