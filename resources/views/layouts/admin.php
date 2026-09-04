<?php
$__topbarUser = auth_user();
$__topbarAuthorId = ($__topbarUser['role'] ?? '') === 'author' ? (int) $__topbarUser['id'] : null;
$pendingCommentsCount = (new \Skoolyst\Models\Comment())->countPending($__topbarAuthorId);
?>
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
      <div class="admin-topbar-actions">
        <a href="<?= url('/dashboard/comments') ?>" class="admin-icon-btn" aria-label="<?= $pendingCommentsCount ?> pending comments">
          <span aria-hidden="true">&#128276;</span>
          <?php if ($pendingCommentsCount > 0): ?><span class="admin-badge"><?= $pendingCommentsCount > 99 ? '99+' : $pendingCommentsCount ?></span><?php endif; ?>
        </a>
        <div class="admin-profile" data-dropdown>
          <button type="button" class="admin-profile-trigger" data-dropdown-toggle aria-haspopup="true" aria-expanded="false">
            <span class="admin-avatar"><?= clean(mb_strtoupper(mb_substr(auth_user()['name'] ?? 'U', 0, 1))) ?></span>
            <span class="admin-profile-name"><?= clean(auth_user()['name'] ?? 'Account') ?></span>
          </button>
          <div class="admin-dropdown-menu" data-dropdown-menu hidden>
            <a href="<?= url('/dashboard/profile') ?>">Edit Profile</a>
            <a href="<?= url('/logout') ?>">Logout</a>
          </div>
        </div>
      </div>
    </header>
    <div class="admin-view" data-spa-view>
      <div class="admin-loader" data-admin-loader hidden><span class="admin-spinner"></span></div>
      <?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
        <?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?>
      <?php endif; endforeach; ?>
      <main class="admin-main"><?= $content ?? '' ?></main>
    </div>
  </div>
</div>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/admin.js') ?>"></script>
<script src="<?= url('assets/js/admin-spa.js') ?>"></script>
<script src="<?= url('assets/js/tag-input.js') ?>"></script>
<?php if (!empty($extraJs)): ?><script src="<?= url($extraJs) ?>"></script><?php endif; ?>
</body>
</html>
