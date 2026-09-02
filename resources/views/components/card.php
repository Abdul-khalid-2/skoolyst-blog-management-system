<?php
/**
 * Card component.
 * $title (optional string), $body (raw HTML string), $footer (optional raw HTML string).
 */
?>
<div class="card">
  <?php if (!empty($title)): ?><div class="card-header"><h3><?= clean($title) ?></h3></div><?php endif; ?>
  <div class="card-body"><?= $body ?? '' ?></div>
  <?php if (!empty($footer)): ?><div class="card-footer"><?= $footer ?></div><?php endif; ?>
</div>
