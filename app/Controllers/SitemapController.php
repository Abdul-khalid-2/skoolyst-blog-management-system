<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Response;
use Skoolyst\Models\Category;
use Skoolyst\Models\Post;

/**
 * Dynamic XML sitemap covering every publicly indexable URL: static pages,
 * category archives, and published posts (with lastmod for freshness signals).
 * Referenced from public/robots.txt.
 */
class SitemapController {
    public function index(): never {
        $urls = [];

        $urls[] = ['loc' => url('/'), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => url('/blog'), 'changefreq' => 'daily', 'priority' => '0.9'];
        foreach (['about', 'contact', 'terms', 'privacy'] as $path) {
            $urls[] = ['loc' => url('/' . $path), 'changefreq' => 'monthly', 'priority' => '0.3'];
        }

        foreach ((new Category())->all('name ASC') as $category) {
            $urls[] = ['loc' => url('/category/' . rawurlencode($category['slug'])), 'changefreq' => 'weekly', 'priority' => '0.6'];
        }

        // rawurlencode() guards against legacy rows whose slug was never run through
        // PostService::slugify() (seed/test data with raw titles as slugs, e.g. spaces
        // or mixed case) — those should be re-slugified at the data level too, but the
        // sitemap must not emit an invalid <loc> regardless.
        foreach ((new Post())->where(['status' => 'published'], 'published_date DESC') as $post) {
            $urls[] = [
                'loc' => url('/post/' . rawurlencode($post['slug'])),
                'lastmod' => date('Y-m-d', strtotime($post['updated_at'] ?? $post['created_at'])),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
            if (!empty($url['lastmod'])) $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        Response::xml($xml);
    }
}
