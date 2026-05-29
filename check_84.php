<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
echo 'Row 84 CODE:'.trim($sheet->getCell([1, 84])->getValue()).', ZONA:'.trim($sheet->getCell([2, 84])->getValue()).', TIPO:'.trim($sheet->getCell([3, 84])->getValue());
