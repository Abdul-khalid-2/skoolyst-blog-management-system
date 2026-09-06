<?php
return [
    'base_url' => rtrim((string) ($_ENV['ADS_API_BASE'] ?? ''), '/'),
    'api_key' => (string) ($_ENV['ADS_API_KEY'] ?? ''),
    // How long a fetched (or empty) ad response is cached on disk per placement,
    // so a slow/down ads.skoolyst.com can't add latency to every single pageview.
    'cache_ttl' => (int) ($_ENV['ADS_CACHE_TTL'] ?? 30),
    // Friendly slot name => placement code registered on ads.skoolyst.com for this app.
    'placements' => [
        'home_top' => (string) ($_ENV['ADS_PLACEMENT_HOME_TOP'] ?? 'home_top'),
    ],
];
