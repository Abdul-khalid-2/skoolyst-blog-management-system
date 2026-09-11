<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Response;

class RobotsController {
    public function index(): never {
        $sitemap = url('/sitemap.xml');
        Response::text(
            "User-agent: *\n" .
            "Allow: /\n" .
            "Disallow: /dashboard/\n" .
            "Disallow: /login\n" .
            "Disallow: /signup\n" .
            "Disallow: /ads/track/\n" .
            "\n" .
            "Sitemap: {$sitemap}\n"
        );
    }
}
