<?php
/** Posts list. $posts, $page, $totalPages, $filters, $authors from PostController@adminIndex. */
$filters ??= ['search' => '', 'status' => '', 'author_id' => ''];
$authorsById = array_column($authors ?? [], 'name', 'id');

$paginationQuery = array_filter([
    'q' => $filters['search'],
    'status' => $filters['status'],
    'author_id' => $filters['author_id'],
]);
$paginationBaseUrl = url('/dashboard/posts') . ($paginationQuery ? '?' . http_build_query($paginationQuery) : '');
?>
<div class="admin-page-header">
  <?php component('button', ['label' => '+ New Post', 'type' => 'link', 'href' => url('/dashboard/posts/create')]); ?>
</div>

<form method="get" action="<?= url('/dashboard/posts') ?>" class="admin-table-filters">
  <input type="text" name="q" class="form-control" placeholder="Search by title..." value="<?= clean($filters['search']) ?>">
  <select name="status" class="form-control">
    <option value="">All statuses</option>
    <option value="draft"<?= $filters['status'] === 'draft' ? ' selected' : '' ?>>Draft</option>
    <option value="published"<?= $filters['status'] === 'published' ? ' selected' : '' ?>>Published</option>
  </select>
  <?php if (!empty($authors)): ?>
    <select name="author_id" class="form-control">
      <option value="">All authors</option>
      <?php foreach ($authors as $a): ?>
        <option value="<?= (int) $a['id'] ?>"<?= (string) $a['id'] === $filters['author_id'] ? ' selected' : '' ?>><?= clean($a['name']) ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
  <?php component('button', ['label' => 'Filter', 'type' => 'submit']); ?>
  <?php if ($filters['search'] !== '' || $filters['status'] !== '' || $filters['author_id'] !== ''): ?>
    <a href="<?= url('/dashboard/posts') ?>" class="btn btn-sm btn-ghost">Clear</a>
  <?php endif; ?>
</form>

<?php
component('table', [
    'headers' => array_filter(['Title', 'Status', empty($authors) ? null : 'Author', 'Updated', 'Actions']),
    'rows' => array_map(function ($p) use ($authors, $authorsById) {
        $actions = '<div class="admin-table-actions">';
        $actions .= '<a href="' . url('/dashboard/posts/' . $p['id'] . '/edit') . '" class="btn btn-sm btn-outline">Edit</a>';
        $actions .= '<form method="post" action="' . url('/dashboard/posts/' . $p['id'] . '/delete') . '" data-confirm="Delete this post?">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>';
        $actions .= '</div>';
        $row = [
            clean($p['title']),
            '<span class="badge badge-' . ($p['status'] === 'published' ? 'success' : 'default') . '">' . clean($p['status']) . '</span>',
        ];
        if (!empty($authors)) $row[] = clean($authorsById[$p['author_id']] ?? 'Unknown');
        $row[] = format_date($p['updated_at']);
        $row[] = $actions;
        return $row;
    }, $posts),
    'emptyMessage' => 'No posts match your filters.',
]);
component('pagination', ['currentPage' => $page ?? 1, 'totalPages' => $totalPages ?? 1, 'baseUrl' => $paginationBaseUrl]);
?>
