<?php
/**
 * Alert component: success, error, warning, info.
 * $type = success|error|warning|info, $message (string).
 */
$type ??= 'info';
?>
<?php if (!empty($message)): ?>
<div class="alert alert-<?= clean($type) ?>" role="alert">
  <span><?= clean($message) ?></span>
  <button type="button" class="alert-close" aria-label="Dismiss" data-alert-close>&times;</button>
</div>
<?php endif; ?>
