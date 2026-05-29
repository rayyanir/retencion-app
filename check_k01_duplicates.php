<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
for ($r = 6; $r <= 150; $r++) {
    $c = trim($sheet->getCell([1, $r])->getValue());
    if ($c == 'K01') {
        echo "Row $r is K01\n";
        for ($col=4; $col<=15; $col++) {
            echo $sheet->getCell([$col, $r])->getCalculatedValue() . " ";
        }
        echo "\n";
    }
}
