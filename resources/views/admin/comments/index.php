<?php
/** Pending comment moderation. $comments, $filters, $authors from CommentController@adminIndex. */
$filters ??= ['search' => '', 'author_id' => ''];
$authorsById = array_column($authors ?? [], 'name', 'id');

$paginationQuery = array_filter(['q' => $filters['search'], 'author_id' => $filters['author_id']]);
?>
<form method="get" action="<?= url('/dashboard/comments') ?>" class="admin-table-filters">
  <input type="text" name="q" class="form-control" placeholder="Search comment or commenter..." value="<?= clean($filters['search']) ?>">
  <?php if (!empty($authors)): ?>
    <select name="author_id" class="form-control">
      <option value="">All authors</option>
      <?php foreach ($authors as $a): ?>
        <option value="<?= (int) $a['id'] ?>"<?= (string) $a['id'] === $filters['author_id'] ? ' selected' : '' ?>><?= clean($a['name']) ?></option>
      <?php endforeach; ?>
    </select>
  <?php endif; ?>
  <?php component('button', ['label' => 'Filter', 'type' => 'submit']); ?>
  <?php if ($filters['search'] !== '' || $filters['author_id'] !== ''): ?>
    <a href="<?= url('/dashboard/comments') ?>" class="btn btn-sm btn-ghost">Clear</a>
  <?php endif; ?>
</form>

<?php
component('table', [
    'headers' => array_filter(['Comment', 'Author', 'Post', empty($authors) ? null : 'Post Author', 'Submitted', 'Actions']),
    'rows' => array_map(function ($c) use ($authors, $authorsById) {
        $actions = '<div class="admin-table-actions">';
        $actions .= '<form method="post" action="' . url('/dashboard/comments/' . $c['id'] . '/approve') . '">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-primary">Approve</button></form>';
        $actions .= '<form method="post" action="' . url('/dashboard/comments/' . $c['id'] . '/reject') . '" data-confirm="Reject this comment?">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-outline">Reject</button></form>';
        $actions .= '</div>';
        $row = [
            clean($c['body']),
            clean($c['author_name']) . '<br><span class="stat-label">' . clean($c['author_email']) . '</span>',
            clean($c['post_title']),
        ];
        if (!empty($authors)) $row[] = clean($authorsById[$c['post_author_id']] ?? 'Unknown');
        $row[] = format_date($c['created_at']);
        $row[] = $actions;
        return $row;
    }, $comments),
    'emptyMessage' => 'No comments match your filters.',
]);
?>
