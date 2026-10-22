<?php
/**
 * Pagination component.
 * $currentPage (int), $totalPages (int), $baseUrl (string; may already contain
 * a query string). Page 1 links to the bare $baseUrl so ?page=1 never exists as
 * a duplicate of the canonical listing.
 */
$currentPage = max(1, (int) ($currentPage ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$baseUrl ??= url('/');
$sep = str_contains($baseUrl, '?') ? '&' : '?';
$__pageUrl = static fn (int $n): string => $n <= 1 ? $baseUrl : $baseUrl . $sep . 'page=' . $n;
?>
<?php if ($totalPages > 1): ?>
<nav class="pagination" aria-label="Pagination">
  <?php if ($currentPage <= 1): ?><span class="pagination-item is-disabled" aria-disabled="true">Prev</span>
  <?php else: ?><a class="pagination-item" rel="prev" href="<?= clean($__pageUrl($currentPage - 1)) ?>">Prev</a><?php endif; ?>
  <?php for ($page = 1; $page <= $totalPages; $page++): ?>
    <a class="pagination-item<?= $page === $currentPage ? ' is-active' : '' ?>" href="<?= clean($__pageUrl($page)) ?>"<?= $page === $currentPage ? ' aria-current="page"' : '' ?> aria-label="Page <?= $page ?>"><?= $page ?></a>
  <?php endfor; ?>
  <?php if ($currentPage >= $totalPages): ?><span class="pagination-item is-disabled" aria-disabled="true">Next</span>
  <?php else: ?><a class="pagination-item" rel="next" href="<?= clean($__pageUrl($currentPage + 1)) ?>">Next</a><?php endif; ?>
</nav>
<?php endif; ?>
