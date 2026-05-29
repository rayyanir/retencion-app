<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');

echo "getCell([4, 6]): " . $sheet->getCell([4, 6])->getCalculatedValue() . "\n";
echo "getCellByColumnAndRow(4, 6): " . $sheet->getCellByColumnAndRow(4, 6)->getCalculatedValue() . "\n";
