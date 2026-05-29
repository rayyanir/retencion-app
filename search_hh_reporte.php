<?php
require 'vendor/autoload.php';
$filePath = 'C:\retencion kfc\RETENCION Y ROTACON_ABRIL 2026_0405.xlsx';
if (!file_exists($filePath)) {
    echo "No existe $filePath\n";
    exit;
}
$s = PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
foreach ($s->getSheetNames() as $sheetName) {
    echo "Pestaña: $sheetName\n";
    $sheet = $s->getSheetByName($sheetName);
    for ($i = 1; $i <= 5; $i++) {
        $row = [];
        for ($c = 1; $c <= 40; $c++) {
            $val = $sheet->getCell([$c, $i])->getValue();
            if ($val !== null && $val !== '') {
                $row[] = $val;
            }
        }
        if (count($row) > 0) {
            echo "Fila $i: " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
    echo "\n";
}
