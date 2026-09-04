<?php
/** Media library. $items from MediaController@index. */
?>
<?php ob_start(); ?>
<h3>Upload File</h3>
<form method="post" action="<?= url('/dashboard/media') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-group"><input type="file" name="file" class="form-control" required></div>
  <?php component('button', ['label' => 'Upload', 'type' => 'submit']); ?>
</form>
<?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>

<h2>Files</h2>
<?php
component('table', [
    'headers' => ['Preview', 'Name', 'Size', 'Actions'],
    'rows' => array_map(function ($m) {
        $actions = '<div class="admin-table-actions"><form method="post" action="' . url('/dashboard/media/' . $m['id'] . '/delete') . '" data-confirm="Delete this file?">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-danger">Delete</button></form></div>';
        return ['<img src="' . clean($m['url']) . '" alt="" style="height:40px">', clean($m['name']), clean($m['size'] ?? ''), $actions];
    }, $items),
    'emptyMessage' => 'No files uploaded yet.',
]);
?>
