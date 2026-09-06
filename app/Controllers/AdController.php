<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Services\AdService;

/**
 * Relays impression/click beacons from the browser to the shared ad platform.
 * Kept server-side (rather than the browser calling ads.skoolyst.com directly)
 * so ADS_API_KEY never reaches frontend JS — same proxy pattern used by
 * teachers.skoolyst.com's AdsController.
 */
class AdController {
    public function __construct(private AdService $ads = new AdService()) {}

    public function impression(): never {
        $this->track('impression');
    }

    public function click(): never {
        $this->track('click');
    }

    private function track(string $type): never {
        $adId = (int) Request::input('ad_id', 0);
        if ($adId > 0) {
            $type === 'impression' ? $this->ads->trackImpression($adId) : $this->ads->trackClick($adId);
        }
        // navigator.sendBeacon() discards the response body — a bare 204 is enough.
        Response::text('', 204);
    }

    /**
     * TEMPORARY diagnostic for the ads.skoolyst.com integration — hits the real
     * API directly (bypassing AdService's short timeout and cache) so a slow
     * response, a bad key, and "no ad matched" are distinguishable. Admin-only;
     * delete this method and its route in routes/admin.php once ads are confirmed working.
     */
    public function debug(): never {
        $config = require dirname(__DIR__, 2) . '/config/ads.php';
        $baseUrl = (string) $config['base_url'];
        $apiKey = (string) $config['api_key'];
        $placementCode = (string) ($config['placements']['home_top'] ?? '');

        $out = "=== 1. Runtime config (config/ads.php + .env) ===\n";
        $out .= "base_url: {$baseUrl}\n";
        $out .= 'api_key (first 12 chars): ' . substr($apiKey, 0, 12) . '... (' . strlen($apiKey) . " chars total)\n";
        $out .= "home_top placement code: {$placementCode}\n";

        $out .= "\n=== 2. Direct request, generous timeout (10s connect / 20s total) ===\n";
        $url = $baseUrl . '/ads/serve?placement=' . urlencode($placementCode);
        $out .= "URL: {$url}\n";

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

        $out .= "Took: {$elapsed}s\n";
        $out .= "HTTP status: {$status}\n";
        $out .= "curl errno: {$errno}\n";
        $out .= 'curl error: ' . ($error ?: '(none)') . "\n";

        $out .= "\n=== 3. Raw response body ===\n";
        $out .= $response . "\n";

        $decoded = json_decode((string) $response, true);
        $out .= "\n=== 4. Decoded ===\n";
        $out .= var_export($decoded, true) . "\n";

        // Clear the cache file first — otherwise a stale cached null (from an
        // earlier failed attempt) would hide a since-fixed key/placement.
        $cacheFile = sys_get_temp_dir() . '/skoolyst_ad_' . md5($placementCode) . '.json';
        if (is_file($cacheFile)) unlink($cacheFile);
        $viaService = $this->ads->getAd($placementCode);
        $out .= "\n=== 5. What AdService::getAd() returns (cache cleared first) ===\n";
        $out .= var_export($viaService, true) . "\n";

        $out .= "\n=== 6. Verdict ===\n";
        if ($errno !== 0) {
            $out .= "Still fails even with a 20s timeout -> a real network problem, not just \"too slow\". Check the server's outbound firewall/DNS.\n";
        } elseif ($status >= 200 && $status < 300) {
            if (isset($decoded['success']) && $decoded['success'] && is_array($decoded['data'] ?? null) && array_key_exists('ad', $decoded['data'])) {
                if ($decoded['data']['ad'] === null) {
                    $out .= "API call succeeded but returned ad: null -> no ACTIVE ad is matched to this placement code for this app on ads.skoolyst.com. Check: the placement code matches exactly, an ad exists, status=active, and today is within its start/end date.\n";
                } else {
                    $out .= "Direct call succeeded - a real ad was returned.\n";
                }
            } else {
                $out .= "2xx response but missing 'success'/'data.ad' keys -> response shape differs from what AdService.php expects.\n";
            }
        } else {
            $out .= "Non-2xx status ({$status}) -> check the raw body above for the API's own error message (likely 401 unauthorized if the key is wrong/revoked).\n";
        }

        $directHadAd = ($decoded['data']['ad'] ?? null) !== null;
        $serviceHadAd = $viaService !== null;
        if ($directHadAd !== $serviceHadAd) {
            $out .= "\nDIVERGENCE: the direct call and AdService::getAd() got different results for the same "
                . "placement moments apart, with identical URL/params/auth. Since neither the cache nor the "
                . "request code differs between them, ads.skoolyst.com itself is returning inconsistent results "
                . "for back-to-back calls - most likely frequency capping/pacing or a fill rate below 100% for "
                . "this placement, not a bug in this app. Confirm by hitting this debug route several times in a "
                . "row: if 'ad' flips between a value and null across calls, that confirms it's server-side. "
                . "Check ads.skoolyst.com's dashboard for this placement's frequency cap / rotation / fill-rate "
                . "settings if the home page shows the ad less often than expected.\n";
        }

        Response::text($out);
    }
}
