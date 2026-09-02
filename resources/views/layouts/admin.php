<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/../components/head.php'; ?>
<link rel="stylesheet" href="<?= url('assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
  <?php require __DIR__ . '/../components/sidebar.php'; ?>
  <div class="admin-content">
    <header class="admin-topbar">
      <button type="button" class="admin-sidebar-toggle" aria-label="Toggle sidebar" data-sidebar-toggle>&#9776;</button>
      <h1 class="admin-page-title"><?= clean($title ?? 'Dashboard') ?></h1>
    </header>
    <?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
      <?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?>
    <?php endif; endforeach; ?>
    <main class="admin-main"><?= $content ?? '' ?></main>
  </div>
</div>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/admin.js') ?>"></script>
<?php if (!empty($extraJs)): ?><script src="<?= url($extraJs) ?>"></script><?php endif; ?>
</body>
</html>
