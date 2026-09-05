<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/../components/head.php'; ?>
</head>
<body class="auth-body">
<main class="auth-layout">
  <div class="auth-card">
    <a href="<?= url('/') ?>" class="auth-back">&larr; Back to site</a>
    <p class="auth-brand">
      <img src="<?= url('assets/images/skoolyst-blog.png') ?>" alt="Skoolyst" class="brand-logo-img">
      <span class="brand-logo-word">Blogs</span>
    </p>
    <?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
      <?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?>
    <?php endif; endforeach; ?>
    <?= $content ?? '' ?>
  </div>
</main>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
