<?php
// Links a local account to a Google account ("Continue with Google"). Holds Google's stable
// `sub` id — a numeric string up to 255 chars per the OIDC spec (in practice ~21 digits).
// Re-logins match on this, never on email alone.
return [
    'up' => 'ALTER TABLE blog_users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER skoolyst_id',
    'down' => 'ALTER TABLE blog_users DROP COLUMN google_id',
];
