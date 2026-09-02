<?php $title = 'Page Not Found'; ?>
<div class="container">
  <?php component('empty-state', [
      'title' => '404 — Page not found',
      'message' => "The page you're looking for doesn't exist or has moved.",
      'actionLabel' => 'Back to home',
      'actionUrl' => url('/'),
  ]); ?>
</div>
