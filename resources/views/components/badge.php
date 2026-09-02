<?php
/**
 * Badge/status component.
 * $label (string), $variant = default|success|warning|danger|info.
 */
$variant ??= 'default';
?>
<span class="badge badge-<?= clean($variant) ?>"><?= clean($label ?? '') ?></span>
