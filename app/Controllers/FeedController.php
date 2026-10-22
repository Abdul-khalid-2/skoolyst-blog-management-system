<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Response;
use Skoolyst\Models\Post;

/** RSS 2.0 feed of the 20 newest published posts (/feed.xml). */
class FeedController {
    public function index(): never {
        $x = static fn (string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $posts = array_slice((new Post())->where(['status' => 'published'], 'published_date DESC'), 0, 20);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>' . "\n";
        $xml .= '  <title>Skoolyst Blog</title>' . "\n";
        $xml .= '  <link>' . $x(url('/')) . '</link>' . "\n";
        $xml .= '  <description>Product news, teaching resources and community stories from the Skoolyst team.</description>' . "\n";
        $xml .= '  <language>en</language>' . "\n";
        $xml .= '  <atom:link href="' . $x(url('/feed.xml')) . '" rel="self" type="application/rss+xml"/>' . "\n";
        foreach ($posts as $p) {
            $link = url('/post/' . rawurlencode($p['slug']));
            $xml .= "  <item>\n";
            $xml .= '    <title>' . $x($p['title']) . "</title>\n";
            $xml .= '    <link>' . $x($link) . "</link>\n";
            $xml .= '    <guid isPermaLink="true">' . $x($link) . "</guid>\n";
            $xml .= '    <pubDate>' . date(DATE_RSS, strtotime($p['published_date'] ?? $p['created_at'])) . "</pubDate>\n";
            $xml .= '    <description>' . $x((string) ($p['excerpt'] ?? '')) . "</description>\n";
            $xml .= "  </item>\n";
        }
        $xml .= '</channel></rss>';

        header('Content-Type: application/rss+xml; charset=UTF-8');
        echo $xml;
        exit;
    }
}
