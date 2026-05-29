<?php
require 'vendor/autoload.php';
$s = PhpOffice\PhpSpreadsheet\IOFactory::load('C:\retencion kfc\DATA PARA RAY.xlsx');
$sheet = $s->getSheetByName('PRODUCTIVIDAD 2024-2026');

for ($r = 1; $r <= 150; $r++) {
    $c = trim($sheet->getCell([1, $r])->getValue());
    if ($c == 'K01' || $c == 'LOCAL' || strpos($c, 'HORAS') !== false || strpos($c, 'HH') !== false || strpos($c, 'MANO') !== false || strpos($c, 'TRANS') !== false || strpos($c, 'PROD') !== false) {
        echo "Row $r: " . trim($sheet->getCell([1, $r])->getValue()) . "\n";
    }
}
