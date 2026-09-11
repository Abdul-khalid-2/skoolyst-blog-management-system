<!doctype html>
<html lang="en">
<head>
<?php require __DIR__ . '/../components/head.php'; ?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'Organization',
      'name' => 'Skoolyst',
      'url' => url('/'),
      'logo' => url('assets/images/skoolyst-blog.png'),
    ],
    [
      '@type' => 'WebSite',
      'name' => 'Skoolyst Blog',
      'url' => url('/'),
      'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => url('/blog') . '?q={search_term_string}',
        'query-input' => 'required name=search_term_string',
      ],
    ],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body>
<?php require __DIR__ . '/../components/navbar.php'; ?>
<?php foreach (($_SESSION['_flash'] ?? []) as $flashType => $flashMessage): if ($flashMessage): ?>
  <div class="container"><?php component('alert', ['type' => in_array($flashType, ['success','error','warning','info'], true) ? $flashType : 'info', 'message' => $flashMessage]); unset($_SESSION['_flash'][$flashType]); ?></div>
<?php endif; endforeach; ?>
<main class="site-main"><?= $content ?? '' ?></main>
<?php require __DIR__ . '/../components/footer.php'; ?>
<script src="<?= asset('assets/js/app.js') ?>"></script>
<?php if (!empty($extraJs)): ?><script src="<?= asset($extraJs) ?>"></script><?php endif; ?>
</body>
</html>
