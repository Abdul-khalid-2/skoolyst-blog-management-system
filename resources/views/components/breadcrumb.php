<?php
/**
 * Breadcrumb navigation component.
 * $items (array, required): each item has 'label' (string) and optional 'url' (string).
 * Last item without 'url' is treated as the current page (aria-current="page").
 * Renders nothing when fewer than 2 items are provided.
 */
if (empty($items) || count($items) < 2) return;
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
  <ol class="breadcrumb-list">
    <?php foreach ($items as $i => $item):
      $isLast = $i === array_key_last($items);
    ?>
      <li class="breadcrumb-item<?= $isLast ? ' is-current' : '' ?>">
        <?php if (!$isLast && !empty($item['url'])): ?>
          <a href="<?= clean($item['url']) ?>"><?= clean($item['label']) ?></a>
        <?php else: ?>
          <span<?= $isLast ? ' aria-current="page"' : '' ?>><?= clean($item['label']) ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
