<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');

echo "Row 4 (Months):\n";
for ($c = 26; $c <= 32; $c++) {
    echo "Col $c: " . trim($sheet->getCellByColumnAndRow($c, 4)->getValue()) . "\n";
}
echo "\nRow 6 (K01):\n";
for ($c = 26; $c <= 32; $c++) {
    echo "Col $c: " . trim($sheet->getCellByColumnAndRow($c, 6)->getCalculatedValue()) . "\n";
}
