<?php
declare(strict_types=1);

/**
 * Usage:
 *   php bin/migrate.php            — run pending migrations
 *   php bin/migrate.php rollback   — roll back the last batch
 */
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Core\Migrator;

$action = $argv[1] ?? 'migrate';

if ($action === 'rollback') {
    $rolledBack = Migrator::rollback();
    echo $rolledBack ? "Rolled back:\n - " . implode("\n - ", $rolledBack) . "\n" : "Nothing to roll back.\n";
    exit;
}

$run = Migrator::run();
echo $run ? "Migrated:\n - " . implode("\n - ", $run) . "\n" : "Nothing to migrate.\n";
