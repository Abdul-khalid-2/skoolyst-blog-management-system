<?php
/**
 * Reusable modal component.
 * $id (string, required — used to open it via data-modal-open="$id"),
 * $title (optional string), $body (raw HTML string), $footer (optional raw HTML string).
 */
?>
<div class="modal" id="<?= clean($id) ?>" data-modal hidden>
  <div class="modal-backdrop" data-modal-close></div>
  <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="<?= clean($id) ?>-title">
    <div class="modal-header">
      <h3 id="<?= clean($id) ?>-title"><?= clean($title ?? '') ?></h3>
      <button type="button" class="modal-close" aria-label="Close" data-modal-close>&times;</button>
    </div>
    <div class="modal-body"><?= $body ?? '' ?></div>
    <?php if (!empty($footer)): ?><div class="modal-footer"><?= $footer ?></div><?php endif; ?>
  </div>
</div>
