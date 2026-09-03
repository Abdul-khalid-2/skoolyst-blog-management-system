<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= clean($title ?? 'Skoolyst Blog') ?></title>
<?php if (!empty($description)): ?><meta name="description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($canonical)): ?><link rel="canonical" href="<?= clean($canonical) ?>"><?php endif; ?>
<meta property="og:title" content="<?= clean($title ?? 'Skoolyst Blog') ?>">
<?php if (!empty($description)): ?><meta property="og:description" content="<?= clean($description) ?>"><?php endif; ?>
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= clean($ogImage) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
<?php if (!empty($extraCss)): ?><link rel="stylesheet" href="<?= url($extraCss) ?>"><?php endif; ?>
