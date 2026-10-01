<?php
// "Login with Skoolyst" — OAuth2 authorization-code client for the central identity
// provider (skoolyst.com). client_id/secret come from skoolyst.com → Dashboard →
// Connected Apps; redirect_uri must match the registered URL byte-for-byte.
return [
    'base_url' => rtrim((string) ($_ENV['SKOOLYST_AUTH_BASE'] ?? 'https://skoolyst.com'), '/'),
    'client_id' => (string) ($_ENV['SKOOLYST_AUTH_CLIENT_ID'] ?? ''),
    'client_secret' => (string) ($_ENV['SKOOLYST_AUTH_CLIENT_SECRET'] ?? ''),
    'redirect_uri' => (string) (($_ENV['SKOOLYST_AUTH_REDIRECT_URI'] ?? '') ?: url('/auth/skoolyst/callback')),
];
