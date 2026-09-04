<?php
/**
 * Article archive. $posts, $page, $totalPages, $search, $sort, $categories,
 * $activeCategory come from PostController@index.
 */
?>
<section class="container">
  <h1>Articles</h1>

  <form method="get" action="<?= url('/blog') ?>" class="archive-filters">
    <input type="text" name="q" class="form-control" placeholder="Search articles..." value="<?= clean($search ?? '') ?>">
    <select name="category" class="form-control">
      <option value="">All categories</option>
      <?php foreach (($categories ?? []) as $cat): ?>
        <option value="<?= clean($cat['slug']) ?>"<?= ($activeCategory ?? '') === $cat['slug'] ? ' selected' : '' ?>><?= clean($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="sort" class="form-control">
      <option value="newest"<?= ($sort ?? 'newest') === 'newest' ? ' selected' : '' ?>>Newest</option>
      <option value="oldest"<?= ($sort ?? '') === 'oldest' ? ' selected' : '' ?>>Oldest</option>
    </select>
    <?php component('button', ['label' => 'Filter', 'type' => 'submit']); ?>
  </form>

  <div class="post-grid">
    <?php if (empty($posts)): ?>
      <?php component('empty-state', ['title' => 'No articles found', 'message' => 'Try a different search or filter.']); ?>
    <?php else: foreach ($posts as $post): ob_start(); ?>
      <?php component('post-card-body', ['post' => $post]); ?>
      <p><?php component('badge', ['label' => format_date($post['published_date'] ?? $post['created_at']), 'variant' => 'default']); ?></p>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; endif; ?>
  </div>

  <?php component('pagination', ['currentPage' => $page ?? 1, 'totalPages' => $totalPages ?? 1, 'baseUrl' => url('/blog') . '?' . http_build_query(['q' => $search ?? '', 'category' => $activeCategory ?? '', 'sort' => $sort ?? 'newest'])]); ?>
</section>
