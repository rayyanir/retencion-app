<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
echo 'Col 15: '.trim($sheet->getCellByColumnAndRow(15, 4)->getValue())."\n";
echo 'Col 16: '.trim($sheet->getCellByColumnAndRow(16, 4)->getValue())."\n";
echo 'Col 17: '.trim($sheet->getCellByColumnAndRow(17, 4)->getValue())."\n";
