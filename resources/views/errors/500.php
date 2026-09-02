<?php $title = 'Something Went Wrong'; ?>
<div class="container">
  <?php component('empty-state', [
      'title' => '500 — Something went wrong',
      'message' => 'An unexpected error occurred. Please try again shortly.',
      'actionLabel' => 'Back to home',
      'actionUrl' => url('/'),
  ]); ?>
</div>
