<?php
/**
 * Button component.
 * $label (string, required), $variant = primary|secondary|outline|ghost|danger,
 * $size = md|sm, $type = button|submit|link, $href (required when $type = link),
 * $attrs (string, optional extra HTML attributes, already-escaped by the caller).
 */
$variant ??= 'primary';
$size ??= 'md';
$type ??= 'button';
$classes = 'btn btn-' . $variant . ($size === 'sm' ? ' btn-sm' : '');
?>
<?php if ($type === 'link'): ?>
  <a href="<?= clean($href ?? '#') ?>" class="<?= $classes ?>"<?= isset($attrs) ? ' ' . $attrs : '' ?>><?= clean($label ?? '') ?></a>
<?php else: ?>
  <button type="<?= clean($type) ?>" class="<?= $classes ?>"<?= isset($attrs) ? ' ' . $attrs : '' ?>><?= clean($label ?? '') ?></button>
<?php endif; ?>
