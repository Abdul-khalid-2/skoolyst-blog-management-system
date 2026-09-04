<?php
/**
 * Server-side HTML sanitization for rich-text fields (the post editor's Body,
 * via CKEditor). This is the actual security boundary against stored XSS —
 * the editor runs entirely in the author's own browser and can be bypassed
 * by anyone posting straight to the controller, so nothing client-side is
 * trusted. Every post body is purified here before it's ever written to the
 * database, using a whitelist of the tags/attributes a rich-text editor
 * legitimately produces; everything else (script tags, event handler
 * attributes, javascript:/data: URIs, embeds, forms, inline styles) is
 * stripped rather than escaped, so the stored HTML is safe to render as-is.
 */
function sanitize_html(string $html): string {
    static $purifier = null;

    if ($purifier === null) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Cache.SerializerPath', dirname(__DIR__, 2) . '/storage/cache/htmlpurifier');
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'hr',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            'ul', 'ol', 'li',
            'blockquote', 'code', 'pre',
            'a[href|title]',
            'img[src|alt|title|width|height]',
            'table', 'thead', 'tbody', 'tr',
            'th[colspan|rowspan]', 'td[colspan|rowspan]',
        ]));
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.EnableID', false);
        $config->set('HTML.SafeIframe', false);

        $purifier = new HTMLPurifier($config);
    }

    $purified = $purifier->purify($html);

    // Wrap tables so a wide one scrolls horizontally instead of blowing out the
    // article's layout, reusing the same .table-wrap pattern the admin already
    // uses — the table itself keeps its normal table/row/cell layout.
    return preg_replace('/<table\b[^>]*>.*?<\/table>/is', '<div class="table-wrap">$0</div>', $purified);
}
