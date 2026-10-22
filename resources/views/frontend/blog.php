<?php
/**
 * Article archive. $posts, $page, $totalPages, $search, $sort, $categories,
 * $activeCategory come from PostController@index.
 */
?>

<div class="container"><?php component('ad-slot', ['placement' => 'blogs_top']); ?></div>

<section class="container">
  <?php component('breadcrumb', ['items' => [
    ['label' => 'Home', 'url' => url('/')],
    ['label' => 'Articles'],
  ]]); ?>
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

  <div class="post-grid post-grid--4">
    <?php if (empty($posts)): ?>
      <?php component('empty-state', ['title' => 'No articles found', 'message' => 'Try a different search or filter.']); ?>
    <?php else: foreach ($posts as $__i => $post): ob_start(); ?>
      <?php component('post-card-body', ['post' => $post, 'eager' => ($__i ?? 1) === 0]); ?>
      <p><?php component('badge', ['label' => format_date($post['published_date'] ?? $post['created_at']), 'variant' => 'default']); ?></p>
      <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
    <?php endforeach; endif; ?>
  </div>

  <?php
  $__qs = http_build_query(array_filter([
    'q' => $search ?? '',
    'category' => $activeCategory ?? '',
    'sort' => ($sort ?? 'newest') !== 'newest' ? $sort : '',
  ], static fn ($v) => $v !== ''));
  component('pagination', ['currentPage' => $page ?? 1, 'totalPages' => $totalPages ?? 1, 'baseUrl' => url('/blog') . ($__qs !== '' ? '?' . $__qs : '')]);
  ?>
</section>
