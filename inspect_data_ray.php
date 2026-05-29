<?php
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\retencion kfc\DATA PARA RAY.xlsx';

if (!file_exists($filePath)) {
    echo "Archivo no encontrado: $filePath\n";
    exit(1);
}

echo "Cargando Excel...\n";
$spreadsheet = IOFactory::load($filePath);

$sheetsToAnalyze = ['ACTIVOS Rot y Ret.', 'PRODUCTIVIDAD 2024-2026'];

foreach ($sheetsToAnalyze as $sheetName) {
    $sheet = $spreadsheet->getSheetByName($sheetName);
    if (!$sheet) {
        echo "No se encontró la pestaña: $sheetName\n";
        continue;
    }
    
    echo "=== Pestaña: $sheetName ===\n";
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();
    
    echo "Rango: A1 a {$highestCol}{$highestRow}\n";
    
    // Print first 5 rows to understand structure
    for ($r = 1; $r <= min(5, $highestRow); $r++) {
        $rowData = [];
        // Just print first 20 columns max to avoid massive output
        for ($c = 1; $c <= min(30, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol)); $c++) {
            $cell = $sheet->getCell([$c, $r]);
            $val = $cell->getValue();
            // Si es fórmula, obtenerla
            if ($val && str_starts_with((string)$val, '=')) {
                $val = "Fórmula: " . $val . " (Calc: " . $cell->getCalculatedValue() . ")";
            }
            $rowData[] = $val;
        }
        echo "Fila $r: " . json_encode($rowData, JSON_UNESCAPED_UNICODE) . "\n";
    }
    echo "\n";
}
