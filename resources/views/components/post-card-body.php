<?php
/**
 * Shared content for a post-grid card: cover image (or the default banner
 * when the post has none), title, excerpt. $post (required, array).
 */
?>
<div class="post-card-image">
  <img src="<?= clean($post['cover_image'] ?: url('assets/images/post-placeholder.svg')) ?>" alt="<?= clean($post['title']) ?>" loading="lazy" width="400" height="225">
</div>
<h3><a href="<?= url('/post/' . $post['slug']) ?>"><?= clean($post['title']) ?></a></h3>
<p><?= clean($post['excerpt'] ?? '') ?></p>
<p class="post-card-meta"><?php if ((int) ($post['read_time_minutes'] ?? 0) > 0): ?><span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= (int) $post['read_time_minutes'] ?> min read</span><?php endif; ?><span><i class="fa-regular fa-eye" aria-hidden="true"></i> <?= number_format((int) ($post['views'] ?? 0)) ?> <?= (int) ($post['views'] ?? 0) === 1 ? 'view' : 'views' ?></span></p>
