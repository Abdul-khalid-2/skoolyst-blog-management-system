<?php
declare(strict_types=1);

/**
 * TEMPORARY — standalone diagnostic for the ads.skoolyst.com integration.
 * Bootstraps the real app config/services, hits the API directly with a
 * generous timeout, and reports what AdService::getAd() returns for every
 * configured placement (clearing each placement's cache file first).
 *
 * DELETE THIS FILE once ads are confirmed working — it is reachable directly
 * by URL and echoes a slice of ADS_API_KEY.
 */

require __DIR__ . '/bootstrap/app.php';

use Skoolyst\Services\AdService;

header('Content-Type: text/plain');

if (($_ENV['APP_DEBUG'] ?? 'false') !== 'true') {
    http_response_code(403);
    echo "Disabled: set APP_DEBUG=true in .env to use this temporary script, then delete it when done.\n";
    exit;
}

$config = require __DIR__ . '/config/ads.php';
$baseUrl = (string) $config['base_url'];
$apiKey = (string) $config['api_key'];
$placements = (array) $config['placements'];

echo "=== 1. Runtime config (config/ads.php + .env) ===\n";
echo "base_url: {$baseUrl}\n";
echo 'api_key (first 12 chars): ' . substr($apiKey, 0, 12) . '... (' . strlen($apiKey) . " chars total)\n";
echo "cache_ttl: {$config['cache_ttl']}s\n";
echo "placements: " . implode(', ', array_map(fn($k, $v) => "$k => $v", array_keys($placements), $placements)) . "\n";

if ($baseUrl === '' || $apiKey === '') {
    echo "\nADS_API_BASE or ADS_API_KEY is empty — fix .env before continuing.\n";
    exit;
}

$ads = new AdService();

foreach ($placements as $slot => $placementCode) {
    echo "\n=== Placement '{$slot}' (code: {$placementCode}) ===\n";

    $url = rtrim($baseUrl, '/') . '/ads/serve?placement=' . urlencode($placementCode);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);

    $start = microtime(true);
    $response = curl_exec($ch);
    $elapsed = round(microtime(true) - $start, 2);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    curl_close($ch);

    echo "Took: {$elapsed}s | HTTP status: {$status} | curl errno: {$errno}";
    echo $error ? " | curl error: {$error}\n" : "\n";

    $decoded = json_decode((string) $response, true);
    echo "Raw response: " . $response . "\n";

    // Clear the cache file first so a stale cached null doesn't hide a since-fixed key/placement.
    $cacheFile = sys_get_temp_dir() . '/skoolyst_ad_' . md5($placementCode) . '.json';
    if (is_file($cacheFile)) unlink($cacheFile);
    $viaService = $ads->getAd($placementCode);
    echo "AdService::getAd() (cache cleared first): " . var_export($viaService, true) . "\n";

    if ($errno !== 0) {
        echo "Verdict: transport-level failure even with a 20s timeout -> check outbound firewall/DNS, not a timeout issue.\n";
    } elseif ($status >= 200 && $status < 300) {
        if (isset($decoded['success']) && $decoded['success'] && is_array($decoded['data'] ?? null) && array_key_exists('ad', $decoded['data'])) {
            echo $decoded['data']['ad'] === null
                ? "Verdict: API succeeded but ad: null -> no ACTIVE ad matched to this placement (check code, status=active, date range in Admin -> Connected Apps).\n"
                : "Verdict: success — a real ad was returned.\n";
        } else {
            echo "Verdict: 2xx response but missing 'success'/'data.ad' keys -> response shape differs from what AdService.php expects.\n";
        }
    } else {
        echo "Verdict: non-2xx status ({$status}) -> likely 401 unauthorized if the key is wrong/revoked, check raw body above.\n";
    }
}

echo "\n=== Done — delete ads_debug.php now ===\n";
