<?php
/** Single post. $post, $category, $tags, $comments from PostController@show. */
?>
<article class="container post-detail">
  <?php if ($category): ?><p><?php component('badge', ['label' => $category['name'], 'variant' => 'info']); ?></p><?php endif; ?>
  <h1><?= clean($post['title']) ?></h1>
  <p class="post-meta"><?= format_date($post['published_date'] ?? $post['created_at']) ?> &middot; <?= (int) $post['read_time_minutes'] ?> min read &middot; <?= (int) $post['views'] ?> views</p>

  <?php if (!empty($post['cover_image'])): ?><img src="<?= clean($post['cover_image']) ?>" alt="<?= clean($post['title']) ?>" class="post-cover"><?php endif; ?>

  <div class="post-body"><?= $post['body'] ?></div>

  <?php if (!empty($tags)): ?>
    <p class="post-tags"><?php foreach ($tags as $tag): component('badge', ['label' => $tag['name'], 'variant' => 'default']); endforeach; ?></p>
  <?php endif; ?>

  <section class="comments">
    <h2>Comments (<?= count($comments) ?>)</h2>
    <?php if (empty($comments)): ?>
      <p>Be the first to comment.</p>
    <?php else: foreach ($comments as $comment): ?>
      <div class="comment">
        <p class="comment-author"><?= clean($comment['author_name']) ?></p>
        <p><?= clean($comment['body']) ?></p>
      </div>
    <?php endforeach; endif; ?>

    <?php ob_start(); ?>
    <h3>Leave a comment</h3>
    <form method="post" action="<?= url('/post/' . $post['slug'] . '/comments') ?>">
      <?= csrf_field() ?>
      <?php component('input', ['name' => 'author_name', 'label' => 'Name', 'required' => true]); ?>
      <?php component('input', ['type' => 'email', 'name' => 'author_email', 'label' => 'Email', 'required' => true]); ?>
      <?php component('input', ['type' => 'textarea', 'name' => 'body', 'label' => 'Comment', 'required' => true]); ?>
      <?php component('button', ['label' => 'Post Comment', 'type' => 'submit']); ?>
    </form>
    <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
  </section>
</article>
