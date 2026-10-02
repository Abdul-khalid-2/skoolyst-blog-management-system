<?php
// "Continue with Google" — this app's own Google OAuth2 client (Google Cloud Console →
// APIs & Services → Credentials → OAuth client ID, type "Web application"). The redirect
// URI must be listed under "Authorized redirect URIs" there byte-for-byte.
return [
    'client_id' => (string) ($_ENV['GOOGLE_CLIENT_ID'] ?? ''),
    'client_secret' => (string) ($_ENV['GOOGLE_CLIENT_SECRET'] ?? ''),
    'redirect_uri' => (string) (($_ENV['GOOGLE_REDIRECT_URI'] ?? '') ?: url('/auth/google/callback')),
];
