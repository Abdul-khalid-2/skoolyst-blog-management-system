<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= clean($title ?? 'Skoolyst Blog') ?></title>
<?php if (!empty($description)): ?><meta name="description" content="<?= clean($description) ?>"><?php endif; ?>
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
<?php if (!empty($extraCss)): ?><link rel="stylesheet" href="<?= url($extraCss) ?>"><?php endif; ?>
