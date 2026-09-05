<?php
return [
    'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760),
    // GD decodes the full bitmap into memory (~width*height*4 bytes, plus decode/
    // re-encode overhead) before this file size cap is even relevant — a small but
    // very high-resolution JPEG can still exhaust a modest memory_limit. 24MP covers
    // any normal photo/banner while keeping worst-case decode memory bounded.
    'max_megapixels' => (int)($_ENV['UPLOAD_MAX_MEGAPIXELS'] ?? 24),
];
