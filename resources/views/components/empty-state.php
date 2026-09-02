<?php
/**
 * Empty state component.
 * $title (string), $message (optional string), $actionLabel/$actionUrl (optional CTA button).
 */
?>
<div class="empty-state">
  <p class="empty-state-title"><?= clean($title ?? 'Nothing here yet') ?></p>
  <?php if (!empty($message)): ?><p class="empty-state-message"><?= clean($message) ?></p><?php endif; ?>
  <?php if (!empty($actionLabel) && !empty($actionUrl)): ?>
    <?php component('button', ['label' => $actionLabel, 'type' => 'link', 'href' => $actionUrl]); ?>
  <?php endif; ?>
</div>
