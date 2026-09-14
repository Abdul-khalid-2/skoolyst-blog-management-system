<?php
/** Single post. $post, $category, $tags, $comments from PostController@show. */
?>
<article class="container post-detail">
  <?php component('breadcrumb', ['items' => array_values(array_filter([
    ['label' => 'Home', 'url' => url('/')],
    ['label' => 'Blog', 'url' => url('/blog')],
    $category ? ['label' => $category['name'], 'url' => url('/category/' . $category['slug'])] : null,
    ['label' => $post['title']],
  ]))]); ?>
  <?php if ($category): ?><p><?php component('badge', ['label' => $category['name'], 'variant' => 'info']); ?></p><?php endif; ?>
  <h1><?= clean($post['title']) ?></h1>
  <?php
  $__published = $post['published_date'] ?? $post['created_at'];
  // Only surfaces "Updated" when it's a real, meaningfully later revision (not the
  // same-second timestamp every post gets on creation) — avoids implying every post
  // was "updated" the moment it was published.
  $__showUpdated = !empty($post['updated_at']) && (strtotime($post['updated_at']) - strtotime($__published)) > 86400;
  ?>
  <p class="post-meta">
    <?= format_date($__published) ?><?= $__showUpdated ? ' &middot; updated ' . format_date($post['updated_at']) : '' ?><?= $author ? ' &middot; by ' . clean($author['name']) : '' ?><?php if ((int) $post['read_time_minutes'] > 0): ?> &middot; <i class="fa-regular fa-clock" aria-hidden="true"></i> <?= (int) $post['read_time_minutes'] ?> min read<?php endif; ?> &middot; <i class="fa-regular fa-eye" aria-hidden="true"></i> <span id="post-view-count"><?= number_format((int) $post['views']) ?></span> <?= (int) $post['views'] === 1 ? 'view' : 'views' ?> &middot; <i class="fa-regular fa-hourglass-half" aria-hidden="true"></i> <span id="post-read-minutes"><?= number_format((int) round((int) $post['read_seconds'] / 60)) ?></span> min read time
  </p>

  <?php if (!empty($post['cover_image'])): ?><img src="<?= clean($post['cover_image']) ?>" alt="<?= clean($post['title']) ?>" class="post-cover" fetchpriority="high" loading="eager"><?php endif; ?>

  <?php component('ad-slot', ['placement' => 'post_top']); ?>

  <div class="post-body"><?= $post['body'] ?></div>

  <?php if (!empty($tags)): ?>
    <p class="post-tags"><?php foreach ($tags as $tag): component('badge', ['label' => $tag['name'], 'variant' => 'default']); endforeach; ?></p>
  <?php endif; ?>

  <?php if (!empty($related)): ?>
    <section class="related-posts">
      <h2>You might also like</h2>
      <div class="post-grid">
        <?php foreach ($related as $relatedPost): ob_start(); ?>
          <?php component('post-card-body', ['post' => $relatedPost]); ?>
          <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
        <?php endforeach; ?>
      </div>
    </section>
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
<script>
(function () {
  var el = document.getElementById('post-view-count');
  if (!el) return;
  fetch('<?= url('/post/' . $post['slug'] . '/view') ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: '_csrf=<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>'
  })
  .then(function (r) { return r.ok ? r.json() : null; })
  .then(function (data) {
    if (data && typeof data.views === 'number') {
      el.textContent = data.views.toLocaleString();
    }
  })
  .catch(function () {});
}());

(function () {
  var el = document.getElementById('post-read-minutes');
  if (!el) return;
  var url = '<?= url('/post/' . $post['slug'] . '/read-time') ?>';
  var csrf = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>';

  // Time is accumulated client-side (ticked once a second while the tab is
  // visible) and flushed in one batched request every ~15-25s instead of a
  // request every 5s — same accuracy, a fraction of the server load. The
  // random spread also avoids every open tab on a busy article syncing its
  // requests to the same instant.
  var activeSeconds = 0;
  var tickTimer = null;
  var flushTimer = null;

  function scheduleFlush() {
    clearTimeout(flushTimer);
    var delay = 15000 + Math.floor(Math.random() * 10000);
    flushTimer = setTimeout(function () { flush(false); scheduleFlush(); }, delay);
  }

  function flush(useBeacon) {
    if (activeSeconds <= 0) return;
    var seconds = activeSeconds;
    activeSeconds = 0;

    if (useBeacon && navigator.sendBeacon) {
      var params = new URLSearchParams();
      params.set('_csrf', csrf);
      params.set('seconds', seconds);
      navigator.sendBeacon(url, params);
      return;
    }

    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: '_csrf=' + encodeURIComponent(csrf) + '&seconds=' + seconds,
      keepalive: true
    })
    .then(function (r) { return r.ok ? r.json() : null; })
    .then(function (data) {
      if (data && typeof data.readMinutes === 'number') {
        el.textContent = data.readMinutes.toLocaleString();
      }
    })
    .catch(function () {});
  }

  function start() {
    if (tickTimer) return;
    tickTimer = setInterval(function () { activeSeconds++; }, 1000);
    scheduleFlush();
  }

  function stop() {
    clearInterval(tickTimer);
    clearTimeout(flushTimer);
    tickTimer = null;
    // Uses sendBeacon here since the tab may be about to be backgrounded/closed
    // and a normal fetch could get cancelled before it's sent.
    flush(true);
  }

  document.addEventListener('visibilitychange', function () {
    if (document.hidden) stop(); else start();
  });
  window.addEventListener('pagehide', function () { flush(true); });

  if (!document.hidden) start();
}());
</script>
