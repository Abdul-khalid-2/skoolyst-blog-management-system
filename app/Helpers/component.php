<?php
declare(strict_types=1);

/**
 * Render a reusable UI component from resources/views/components/ with an
 * isolated variable scope, e.g. component('button', ['label' => 'Save']).
 */
function component(string $name, array $data = []): void {
    extract($data, EXTR_SKIP);
    require dirname(__DIR__, 2) . '/resources/views/components/' . ltrim($name, '/') . '.php';
}
