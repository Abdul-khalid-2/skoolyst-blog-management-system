<?php
function format_date(?string $date): string { return $date ? date('Y-m-d', strtotime($date)) : ''; }
