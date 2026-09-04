<?php
/** Category archive. $category, $posts, $page, $totalPages from CategoryController@show. */
?>
<section class="container">
  <h1><?= clean($category['name']) ?></h1>
  <?php if (!empty($category['description'])): ?><p><?= clean($category['description']) ?></p><?php endif; ?>

  <div class="post-grid">
    <?php if (empty($posts)): ?>
      <?php component('empty-state', ['title' => 'No articles in this category yet']); ?>
    <?php else: foreach ($posts as $post): ob_start(); ?>
      <?php component('post-card-body', ['post' => $post]); ?>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; endif; ?>
  </div>

  <?php component('pagination', ['currentPage' => $page ?? 1, 'totalPages' => $totalPages ?? 1, 'baseUrl' => url('/category/' . $category['slug'])]); ?>
</section>
