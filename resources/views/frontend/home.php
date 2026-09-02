<?php
/**
 * Frontend home page. Static placeholder content for Phase 3 (Shared UI System) —
 * real post data comes from PostController@home + Post model in Phase 6/7.
 */
$title = 'Skoolyst Blog — Home';
$activeNav = 'home';
?>
<section class="hero container">
  <h1>Insights, updates and stories from Skoolyst</h1>
  <p>Product news, teaching resources and community stories — all in one place.</p>
  <form class="hero-search" action="<?= url('/blog') ?>" method="get">
    <input type="text" name="q" class="form-control" placeholder="Search articles...">
    <?php component('button', ['label' => 'Search', 'type' => 'submit']); ?>
  </form>
</section>

<section class="container">
  <h2>Featured Articles</h2>
  <div class="post-grid">
    <?php for ($i = 1; $i <= 3; $i++): ob_start(); ?>
      <p class="post-card-category"><?php component('badge', ['label' => 'Category', 'variant' => 'info']); ?></p>
      <h3>Sample featured post title #<?= $i ?></h3>
      <p>A short teaser of the article content goes here once real posts are wired up in Phase 7.</p>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endfor; ?>
  </div>
</section>

<section class="container newsletter">
  <?php ob_start(); ?>
  <h3>Stay in the loop</h3>
  <p>Get new articles in your inbox.</p>
  <form method="post" action="<?= url('/newsletter') ?>">
    <?= csrf_field() ?>
    <?php component('input', ['type' => 'email', 'name' => 'email', 'placeholder' => 'you@example.com', 'required' => true]); ?>
    <?php component('button', ['label' => 'Subscribe', 'type' => 'submit', 'variant' => 'secondary']); ?>
  </form>
  <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
</section>
