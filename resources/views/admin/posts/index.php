<?php
/** Posts list. $posts, $page, $totalPages from PostController@adminIndex. */
?>
<div class="admin-page-header">
  <?php component('button', ['label' => '+ New Post', 'type' => 'link', 'href' => url('/dashboard/posts/create')]); ?>
</div>

<?php
component('table', [
    'headers' => ['Title', 'Status', 'Updated', 'Actions'],
    'rows' => array_map(function ($p) {
        $actions = '<a href="' . url('/dashboard/posts/' . $p['id'] . '/edit') . '">Edit</a> ';
        $actions .= '<form method="post" action="' . url('/dashboard/posts/' . $p['id'] . '/delete') . '" style="display:inline" data-confirm="Delete this post?">' . csrf_field() . '<button type="submit" class="btn-ghost">Delete</button></form>';
        return [
            clean($p['title']),
            '<span class="badge badge-' . ($p['status'] === 'published' ? 'success' : 'default') . '">' . clean($p['status']) . '</span>',
            format_date($p['updated_at']),
            $actions,
        ];
    }, $posts),
    'emptyMessage' => 'No posts yet.',
]);
component('pagination', ['currentPage' => $page ?? 1, 'totalPages' => $totalPages ?? 1, 'baseUrl' => url('/dashboard/posts')]);
?>
