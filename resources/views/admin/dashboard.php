<?php
/** Admin dashboard overview. $stats, $recentPosts, $ownOnly (true = numbers cover only the signed-in user's posts) from DashboardController@index. */
$ownOnly = !empty($ownOnly);
$statCards = [
    ['label' => $ownOnly ? 'My Published Posts' : 'Published Posts', 'value' => $stats['published']],
    ['label' => $ownOnly ? 'My Draft Posts' : 'Draft Posts', 'value' => $stats['draft']],
    ['label' => $ownOnly ? 'Pending Comments on My Posts' : 'Pending Comments', 'value' => $stats['comments']],
    ['label' => $ownOnly ? 'Views on My Posts' : 'Total Views', 'value' => $stats['views']],
];
?>
<div class="stat-grid">
  <?php foreach ($statCards as $stat): ob_start(); ?>
    <p class="stat-value"><?= clean((string) $stat['value']) ?></p>
    <p class="stat-label"><?= clean($stat['label']) ?></p>
    <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
  <?php endforeach; ?>
</div>

<h2><?= $ownOnly ? 'My Recent Posts' : 'Recent Posts' ?></h2>
<?php
component('table', [
    'headers' => ['Title', 'Status', 'Updated'],
    'rows' => array_map(fn ($p) => [
        clean($p['title']),
        '<span class="badge badge-' . ($p['status'] === 'published' ? 'success' : 'default') . '">' . clean($p['status']) . '</span>',
        format_date($p['updated_at']),
    ], $recentPosts),
    'emptyMessage' => 'No posts yet — create your first one from the Posts page.',
]);
?>
