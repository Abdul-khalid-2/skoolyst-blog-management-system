<?php
/**
 * Shared content for a post-grid card: cover image (or the default banner
 * when the post has none), title, excerpt. $post (required, array).
 */
?>
<div class="post-card-image">
  <img src="<?= clean($post['cover_image'] ?: url('assets/images/post-placeholder.svg')) ?>" alt="" loading="lazy">
</div>
<h3><a href="<?= url('/post/' . $post['slug']) ?>"><?= clean($post['title']) ?></a></h3>
<p><?= clean($post['excerpt'] ?? '') ?></p>
