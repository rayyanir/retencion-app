<?php
require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = 'C:\retencion kfc\DATA PARA RAY.xlsx';
$spreadsheet = IOFactory::load($filePath);

$sheet = $spreadsheet->getSheetByName('PRODUCTIVIDAD 2024-2026');
$highestCol = $sheet->getHighestColumn();
$maxColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

echo "Row 4 (Headers):\n";
$headers = [];
for ($c = 1; $c <= $maxColIdx; $c++) {
    $headers[$c] = $sheet->getCell([$c, 4])->getValue();
}
echo json_encode($headers) . "\n\n";

echo "Row 6 (K01):\n";
$k01Data = [];
for ($c = 1; $c <= $maxColIdx; $c++) {
    $cell = $sheet->getCell([$c, 6]);
    $val = $cell->getValue();
    if ($val && str_starts_with((string)$val, '=')) {
        $val = "Fórmula: " . $val . " (Val: " . $cell->getCalculatedValue() . ")";
    }
    $k01Data[$c] = $val;
}
echo json_encode($k01Data, JSON_UNESCAPED_UNICODE) . "\n";
