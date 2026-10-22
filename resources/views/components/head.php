<?php
/**
 * $canonical falls back to the current URL when a controller doesn't set one,
 * so every page (not just posts) always emits a canonical tag.
 * $noindex (bool, default false): admin/auth layouts set this directly instead
 * of routing through here — see layouts/admin.php and layouts/auth.php.
 */
$__canonical = $canonical ?? url(\Skoolyst\Core\Request::uri());
$__ogType = $ogType ?? 'website';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= clean($title ?? 'Skoolyst Blog') ?></title>
<link rel="icon" href="<?= asset('favicon.ico') ?>" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="<?= asset('favicon-32x32.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= asset('favicon-192x192.png') ?>">
<link rel="apple-touch-icon" href="<?= asset('apple-touch-icon.png') ?>">
<meta name="theme-color" content="#0b1d3a">
<?php if (!empty($description)): ?><meta name="description" content="<?= clean($description) ?>"><?php endif; ?>
<meta name="robots" content="<?= !empty($noindex) ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' ?>">
<link rel="alternate" type="application/rss+xml" title="Skoolyst Blog" href="<?= url('/feed.xml') ?>">
<?php if (!empty($prevUrl)): ?><link rel="prev" href="<?= clean($prevUrl) ?>"><?php endif; ?>
<?php if (!empty($nextUrl)): ?><link rel="next" href="<?= clean($nextUrl) ?>"><?php endif; ?>
<link rel="canonical" href="<?= clean($__canonical) ?>">
<meta property="og:type" content="<?= clean($__ogType) ?>">
<meta property="og:site_name" content="Skoolyst Blog">
<meta property="og:locale" content="<?= ($htmlLang ?? 'en') === 'ur' ? 'ur_PK' : 'en_US' ?>">
<meta property="og:title" content="<?= clean($title ?? 'Skoolyst Blog') ?>">
<meta property="og:url" content="<?= clean($__canonical) ?>">
<?php if (!empty($description)): ?><meta property="og:description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= clean($ogImage) ?>">
<meta property="og:image:alt" content="<?= clean($ogImageAlt ?? $title ?? 'Skoolyst Blog') ?>">
<?php if (!empty($ogImageW) && !empty($ogImageH)): ?><meta property="og:image:width" content="<?= (int) $ogImageW ?>"><meta property="og:image:height" content="<?= (int) $ogImageH ?>"><?php endif; ?><?php endif; ?>
<?php if (($__ogType ?? '') === 'article'): ?>
<?php if (!empty($publishedTime)): ?><meta property="article:published_time" content="<?= clean($publishedTime) ?>"><?php endif; ?>
<?php if (!empty($modifiedTime)): ?><meta property="article:modified_time" content="<?= clean($modifiedTime) ?>"><?php endif; ?>
<?php if (!empty($articleSection)): ?><meta property="article:section" content="<?= clean($articleSection) ?>"><?php endif; ?>
<?php foreach (($articleTags ?? []) as $__t): ?><meta property="article:tag" content="<?= clean($__t) ?>"><?php endforeach; ?>
<?php endif; ?>
<meta name="twitter:card" content="<?= !empty($ogImage) ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= clean($title ?? 'Skoolyst Blog') ?>">
<?php if (!empty($description)): ?><meta name="twitter:description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($ogImage)): ?><meta name="twitter:image" content="<?= clean($ogImage) ?>"><meta name="twitter:image:alt" content="<?= clean($ogImageAlt ?? $title ?? 'Skoolyst Blog') ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
<?php if (!empty($extraCss)): ?><link rel="stylesheet" href="<?= asset($extraCss) ?>"><?php endif; ?>
<?php if (!empty($jsonLd)): ?><script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script><?php endif; ?>
