<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="auth-body">
<main class="auth-layout">
  <div class="auth-card">
    <p class="auth-brand">Skoolyst<span>Blog</span></p>
    <?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
      <?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?>
    <?php endif; endforeach; ?>
    <?= $content ?? '' ?>
  </div>
</main>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
