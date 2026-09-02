<?php
/**
 * Admin dashboard overview. Static placeholder content for Phase 3 —
 * real stats/posts come from DashboardController + Post/Comment models in Phase 6/7.
 */
$title = 'Dashboard';
$activeNav = 'dashboard';
$stats = [
    ['label' => 'Published Posts', 'value' => '—'],
    ['label' => 'Draft Posts', 'value' => '—'],
    ['label' => 'Comments', 'value' => '—'],
    ['label' => 'Views (30d)', 'value' => '—'],
];
?>
<div class="stat-grid">
  <?php foreach ($stats as $stat): ob_start(); ?>
    <p class="stat-value"><?= clean($stat['value']) ?></p>
    <p class="stat-label"><?= clean($stat['label']) ?></p>
    <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
  <?php endforeach; ?>
</div>

<h2>Recent Posts</h2>
<?php
component('table', [
    'headers' => ['Title', 'Category', 'Status', 'Updated'],
    'rows' => [],
    'emptyMessage' => 'No posts yet — create your first one from the Posts page.',
]);
?>
