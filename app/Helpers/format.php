<?php
function format_date(?string $date): string { return $date ? date('Y-m-d', strtotime($date)) : ''; }

function format_bytes(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    $value = $bytes;
    while ($value >= 1024 && $i < count($units) - 1) { $value /= 1024; $i++; }
    return round($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
}
