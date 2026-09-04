<?php
/** Pending comment moderation. $comments from CommentController@adminIndex. */
?>
<?php
component('table', [
    'headers' => ['Comment', 'Author', 'Post', 'Submitted', 'Actions'],
    'rows' => array_map(function ($c) {
        $actions = '<form method="post" action="' . url('/dashboard/comments/' . $c['id'] . '/approve') . '" style="display:inline">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-primary">Approve</button></form> ';
        $actions .= '<form method="post" action="' . url('/dashboard/comments/' . $c['id'] . '/reject') . '" style="display:inline" data-confirm="Reject this comment?">' . csrf_field() . '<button type="submit" class="btn-ghost">Reject</button></form>';
        return [
            clean($c['body']),
            clean($c['author_name']) . '<br><span class="stat-label">' . clean($c['author_email']) . '</span>',
            clean($c['post_title']),
            format_date($c['created_at']),
            $actions,
        ];
    }, $comments),
    'emptyMessage' => 'No comments awaiting moderation.',
]);
?>
