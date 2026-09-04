<?php
/** Media library. $items, $uploaders from MediaController@index. */
$uploadersById = array_column($uploaders ?? [], 'name', 'id');
?>
<?php ob_start(); ?>
<h3>Upload File</h3>
<p class="stat-label">Images only — every upload is automatically converted to WebP.</p>
<form method="post" action="<?= url('/dashboard/media') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-group"><input type="file" name="file" class="form-control" accept="image/*" required></div>
  <?php component('button', ['label' => 'Upload', 'type' => 'submit']); ?>
</form>
<?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>

<h2>Files</h2>
<?php
component('table', [
    'headers' => array_filter(['Preview', 'Name', 'Size', empty($uploaders) ? null : 'Uploaded By', 'Actions']),
    'rows' => array_map(function ($m) use ($uploaders, $uploadersById) {
        $actions = '<div class="admin-table-actions">';
        $actions .= '<button type="button" class="btn btn-sm btn-outline" data-copy-url="' . clean($m['url']) . '">Copy URL</button>';
        $actions .= '<form method="post" action="' . url('/dashboard/media/' . $m['id'] . '/delete') . '" data-confirm="Delete this file?">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>';
        $actions .= '</div>';

        $row = ['<img src="' . clean($m['url']) . '" alt="" style="height:40px">', clean($m['name']), clean($m['size'] ?? '')];
        if (!empty($uploaders)) $row[] = clean($uploadersById[$m['uploaded_by']] ?? 'Unknown');
        $row[] = $actions;
        return $row;
    }, $items),
    'emptyMessage' => 'No files uploaded yet.',
]);
?>
