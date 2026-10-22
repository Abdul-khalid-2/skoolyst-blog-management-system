<?php
declare(strict_types=1);

/**
 * SEO / i18n helpers.
 *
 * PHP's str_word_count() only understands ASCII letters, so Urdu/Arabic-script
 * text counts as ~0 words (every Urdu post showed "1 min read"). These helpers
 * are Unicode-aware.
 */

/** Words in an HTML/plain string, any script. */
function word_count_unicode(string $html): int {
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return (int) preg_match_all('/[\p{L}\p{N}]+/u', $text);
}

/** Minutes to read at ~200 wpm, minimum 1. */
function reading_minutes(string $html): int {
    return max(1, (int) ceil(word_count_unicode($html) / 200));
}

/** True when the text is mostly Arabic-script (Urdu, Arabic, Persian...). */
function text_is_rtl(string $text): bool {
    $text = strip_tags($text);
    $letters = preg_match_all('/\p{L}/u', $text);
    if (!$letters) return false;
    $rtl = preg_match_all('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $text);
    return ($rtl / $letters) > 0.4;
}

/** BCP-47 language code for a piece of content: 'ur' for Arabic-script text, else 'en'. */
function content_lang(string $text): string {
    return text_is_rtl($text) ? 'ur' : 'en';
}

/** Trim to a meta-description-friendly length on a word boundary. */
function seo_trim(string $text, int $max = 158): string {
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $max) return $text;
    $cut = mb_substr($text, 0, $max - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $max * 0.6) $cut = mb_substr($cut, 0, $space);
    return rtrim($cut, " ,.;:-—،۔") . '…';
}

/**
 * Inline SVG icon (no Font Awesome / external request needed).
 * Names: clock, eye, hourglass.
 */
function icon(string $name, string $class = ''): string {
    static $paths = [
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'eye' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'hourglass' => '<path d="M6 3h12M6 21h12M7 3v4l5 5-5 5v4M17 3v4l-5 5 5 5v4"/>',
    ];
    if (!isset($paths[$name])) return '';
    return '<svg class="icon' . ($class ? ' ' . htmlspecialchars($class, ENT_QUOTES) : '') . '" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}
