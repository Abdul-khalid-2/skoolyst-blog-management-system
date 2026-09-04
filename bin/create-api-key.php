<?php
declare(strict_types=1);

/**
 * Usage: php bin/create-api-key.php "Some Client Name"
 * Prints the raw key ONCE — only its SHA-256 hash is stored, so save it now.
 */
require dirname(__DIR__) . '/bootstrap/app.php';

use Skoolyst\Models\ApiKey;

$name = trim($argv[1] ?? '');
if ($name === '') {
    fwrite(STDERR, "Usage: php bin/create-api-key.php \"Some Client Name\"\n");
    exit(1);
}

$rawKey = bin2hex(random_bytes(32));
$id = (new ApiKey())->create(['name' => $name, 'key_hash' => hash('sha256', $rawKey), 'created_at' => date('Y-m-d H:i:s')]);

echo "API key created (id={$id}) for \"{$name}\":\n{$rawKey}\n\nThis is shown once — store it now. Use it as: Authorization: Bearer {$rawKey}\n";
