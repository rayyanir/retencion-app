<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
foreach ($s->getSheetNames() as $sheetName) {
    echo "Pestaña: $sheetName\n";
    $sheet = $s->getSheetByName($sheetName);
    for ($i = 1; $i <= 10; $i++) {
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
