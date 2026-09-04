<?php
declare(strict_types=1);

/**
 * Render a reusable UI component from resources/views/components/ with an
 * isolated variable scope, e.g. component('button', ['label' => 'Save']).
 *
 * The component's own filename is deliberately kept out of $data's variable
 * scope (as __componentFile, not $name) — several components (input, select)
 * take a 'name' key of their own (the form field name), which EXTR_SKIP would
 * silently refuse to bind if this function's own parameter were also $name.
 */
function component(string $__componentFile, array $data = []): void {
    extract($data, EXTR_SKIP);
    require dirname(__DIR__, 2) . '/resources/views/components/' . ltrim($__componentFile, '/') . '.php';
}
