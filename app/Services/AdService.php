<?php
declare(strict_types=1);

namespace Skoolyst\Services;

/**
 * Client for the shared Skoolyst ad platform (ads.skoolyst.com). Mirrors the
 * server-side-only integration pattern used by teachers.skoolyst.com: the API
 * key never leaves the server, a fetched ad is cached briefly on disk, and
 * impression/click tracking is relayed through this app's own endpoints
 * (AdController) instead of the browser calling ads.skoolyst.com directly.
 */
class AdService {
    private string $baseUrl;
    private string $apiKey;
    private int $cacheTtl;

    public function __construct() {
        $config = require dirname(__DIR__, 2) . '/config/ads.php';
        $this->baseUrl = (string) $config['base_url'];
        $this->apiKey = (string) $config['api_key'];
        $this->cacheTtl = (int) $config['cache_ttl'];
    }

    /** @return array{id:int,title:string,description:?string,image_path:?string,cta_text:?string,click_url:string}|null */
    public function getAd(string $placementCode): ?array {
        if ($this->baseUrl === '' || $this->apiKey === '') return null;

        $cacheFile = sys_get_temp_dir() . '/skoolyst_ad_' . md5($placementCode) . '.json';
        if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile)) < $this->cacheTtl) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            return is_array($cached) ? ($cached['ad'] ?? null) : null;
        }

        $response = $this->request('GET', '/ads/serve?placement=' . urlencode($placementCode));
        if ($response === null) {
            // Transport-level failure (timeout, DNS, TLS, etc.) — don't cache this
            // as "no ad"; a brief network hiccup shouldn't blank the slot for a
            // full cache_ttl when the API is otherwise fine.
            return null;
        }
        $ad = $response['data']['ad'] ?? null;

        // Cache the result (including a confirmed "no ad") so an empty placement
        // doesn't cost a round trip on every pageview until the TTL expires.
        file_put_contents($cacheFile, json_encode(['ad' => $ad]));

        return $ad;
    }

    /** Resolves the placement code configured for a friendly slot name (see config/ads.php). */
    public function placementCode(string $slot): ?string {
        $config = require dirname(__DIR__, 2) . '/config/ads.php';
        return $config['placements'][$slot] ?? null;
    }

    public function trackImpression(int $adId): void {
        $this->track('impression', $adId);
    }

    public function trackClick(int $adId): void {
        $this->track('click', $adId);
    }

    private function track(string $type, int $adId): void {
        if ($this->baseUrl === '' || $this->apiKey === '' || $adId <= 0) return;
        // The ad server's {id} path segment is documented as illustrative only —
        // it actually reads ad_id from the request body, so both are sent.
        $this->request('POST', "/ads/{$adId}/{$type}", ['ad_id' => $adId]);
    }

    /** A relative image_path from the API is served from the ad app's own document root, not its /api/vN path. */
    public function imageUrl(?string $path): ?string {
        if (!$path) return null;
        if (preg_match('#^https?://#i', $path)) return $path;

        $assetBase = preg_replace('#/api/v\d+/?$#', '', $this->baseUrl);
        return $assetBase . '/' . ltrim($path, '/');
    }

    /** @return array|null Decoded response body, or null on a transport-level failure (timeout/DNS/TLS/etc). */
    private function request(string $method, string $path, array $body = []): ?array {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            // ads.skoolyst.com has been observed taking 1-7+ seconds to respond
            // (occasional slow requests, not just the usual case) — timing out
            // shorter than that turns a slow-but-fine response into a false
            // "no ad", so this errs generous over keeping pageloads snappy.
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->apiKey],
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));
        }

        $raw = curl_exec($ch);
        $failed = $raw === false || curl_errno($ch) !== 0;
        curl_close($ch);
        if ($failed) return null;

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
