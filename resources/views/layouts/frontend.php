<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/../components/navbar.php'; ?>
<?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
  <div class="container"><?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?></div>
<?php endif; endforeach; ?>
<main class="site-main"><?= $content ?? '' ?></main>
<?php require __DIR__ . '/../components/footer.php'; ?>
<script src="<?= url('assets/js/app.js') ?>"></script>
<?php if (!empty($extraJs)): ?><script src="<?= url($extraJs) ?>"></script><?php endif; ?>
</body>
</html>
