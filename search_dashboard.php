<?php
$lines = file('C:/retencion kfc/retencion-app/resources/views/dashboard.blade.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'productividad') !== false || stripos($line, 'transaccion') !== false) {
        echo ($i+1) . ': ' . trim($line) . "\n";
    }
}
