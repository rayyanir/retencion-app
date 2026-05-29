<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');
for ($i=1; $i<=6; $i++) {
    $row=[];
    for ($c=1; $c<=60; $c++) {
        $row[] = $sheet->getCell([$c, $i])->getValue();
    }
    echo json_encode($row) . "\n";
}
