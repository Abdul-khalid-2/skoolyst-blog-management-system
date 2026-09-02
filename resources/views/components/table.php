<?php
/**
 * Table component.
 * $headers (array<string>), $rows (array<array<string>> of already-rendered/escaped cell HTML),
 * $emptyMessage (optional string shown via the empty-state component when $rows is empty).
 */
?>
<div class="table-wrap">
  <?php if (empty($rows)): ?>
    <?php component('empty-state', ['title' => 'Nothing here yet', 'message' => $emptyMessage ?? 'No records to show.']); ?>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr><?php foreach ($headers ?? [] as $head): ?><th><?= clean($head) ?></th><?php endforeach; ?></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr><?php foreach ($row as $cell): ?><td><?= $cell ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
