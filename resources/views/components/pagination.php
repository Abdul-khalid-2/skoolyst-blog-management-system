<?php
/**
 * Pagination component.
 * $currentPage (int), $totalPages (int), $baseUrl (string, page number appended as ?page=N).
 */
$currentPage = max(1, (int) ($currentPage ?? 1));
$totalPages = max(1, (int) ($totalPages ?? 1));
$baseUrl ??= url('/');
$sep = str_contains($baseUrl, '?') ? '&' : '?';
?>
<?php if ($totalPages > 1): ?>
<nav class="pagination" aria-label="Pagination">
  <a class="pagination-item<?= $currentPage <= 1 ? ' is-disabled' : '' ?>" href="<?= clean($baseUrl . $sep . 'page=' . max(1, $currentPage - 1)) ?>">Prev</a>
  <?php for ($page = 1; $page <= $totalPages; $page++): ?>
    <a class="pagination-item<?= $page === $currentPage ? ' is-active' : '' ?>" href="<?= clean($baseUrl . $sep . 'page=' . $page) ?>"><?= $page ?></a>
  <?php endfor; ?>
  <a class="pagination-item<?= $currentPage >= $totalPages ? ' is-disabled' : '' ?>" href="<?= clean($baseUrl . $sep . 'page=' . min($totalPages, $currentPage + 1)) ?>">Next</a>
</nav>
<?php endif; ?>
