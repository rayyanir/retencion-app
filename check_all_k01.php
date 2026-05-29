<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
for ($r = 1; $r <= 200; $r++) {
    $c = trim($sheet->getCell([1, $r])->getValue());
    if ($c == 'K01') {
        echo "Row $r: TIPO='" . $sheet->getCell([3, $r])->getValue() . "', COL4=" . $sheet->getCell([4, $r])->getCalculatedValue() . "\n";
    }
}
