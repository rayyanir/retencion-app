<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
echo 'Row 82 CODE:'.trim($sheet->getCell([1, 82])->getValue())."\n";
echo 'Row 83 CODE:'.trim($sheet->getCell([1, 83])->getValue())."\n";
echo 'Row 84 CODE:'.trim($sheet->getCell([1, 84])->getValue())."\n";
