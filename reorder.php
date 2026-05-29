<?php
$file = __DIR__.'/resources/views/dashboard.blade.php';
$content = file_get_contents($file);

// 1. Extract Gestión Humana
$ghStartTag = '{{-- ═══ SECCIÓN: Gestión Humana ═══ --}}';
$ghStart = strpos($content, $ghStartTag);
$ghEnd = strpos($content, '</section>', $ghStart) + 10;
$ghBlock = substr($content, $ghStart, $ghEnd - $ghStart);

// 2. Extract Rotación y Retención
$rrStartTag = '{{-- ═══ SECCIÓN: Rotación y Retención — Resumen de Locales ═══ --}}';
$rrStart = strpos($content, $rrStartTag);
$rrEnd = strpos($content, '</section>', $rrStart) + 10;
$rrBlock = substr($content, $rrStart, $rrEnd - $rrStart);

// 3. Remove them from bottom
$content = str_replace($ghBlock, '', $content);
$content = str_replace($rrBlock, '', $content);

// 4. Find Resumen de Metricas (Row 1)
$rmStartTag = '<!-- Dashboard Row 1: Congratulations Card & KPIs Stats -->';
$rmStart = strpos($content, $rmStartTag);
$rmEnd = strpos($content, '</section>', $rmStart) + 10;
$rmBlock = substr($content, $rmStart, $rmEnd - $rmStart);

// 5. Replace Resumen de Metricas with GH and RR
$newTopBlocks = "<!-- Dashboard Row 1: Gestión Humana & Rotación y Retención -->\n    " . $ghBlock . "\n\n    " . $rrBlock;
$content = str_replace($rmBlock, $newTopBlocks, $content);

// Clean up any double empty lines left behind at the bottom
$content = preg_replace("/\n\s*\n\s*\n/", "\n\n", $content);

file_put_contents($file, $content);
echo "Reordered sections successfully.\n";
