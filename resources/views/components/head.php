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
<?php if (!empty($description)): ?><meta name="description" content="<?= clean($description) ?>"><?php endif; ?>
<meta name="robots" content="<?= !empty($noindex) ? 'noindex, nofollow' : 'index, follow' ?>">
<link rel="canonical" href="<?= clean($__canonical) ?>">
<meta property="og:type" content="<?= clean($__ogType) ?>">
<meta property="og:site_name" content="Skoolyst Blog">
<meta property="og:title" content="<?= clean($title ?? 'Skoolyst Blog') ?>">
<meta property="og:url" content="<?= clean($__canonical) ?>">
<?php if (!empty($description)): ?><meta property="og:description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= clean($ogImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="<?= !empty($ogImage) ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= clean($title ?? 'Skoolyst Blog') ?>">
<?php if (!empty($description)): ?><meta name="twitter:description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($ogImage)): ?><meta name="twitter:image" content="<?= clean($ogImage) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
<?php if (!empty($extraCss)): ?><link rel="stylesheet" href="<?= asset($extraCss) ?>"><?php endif; ?>
<?php if (!empty($jsonLd)): ?><script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script><?php endif; ?>
