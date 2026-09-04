<?php
/**
 * Frontend home page. $title, $activeNav, $featured, $latest come from
 * PostController@home via PostService::forHomepage().
 */
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
    <?php if (empty($featured)): ?>
      <?php component('empty-state', ['title' => 'No posts yet', 'message' => 'Published posts will appear here.']); ?>
    <?php else: foreach ($featured as $post): ob_start(); ?>
      <?php component('post-card-body', ['post' => $post]); ?>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; endif; ?>
  </div>
</section>

<?php if (!empty($latest)): ?>
<section class="container">
  <h2>Latest Articles</h2>
  <div class="post-grid">
    <?php foreach ($latest as $post): ob_start(); ?>
      <?php component('post-card-body', ['post' => $post]); ?>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

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
