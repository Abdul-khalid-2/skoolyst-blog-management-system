<?php
/** Categories CRUD. $categories from CategoryController@adminIndex. Edit uses a modal per row. */
?>
<?php ob_start(); ?>
<h3>Add Category</h3>
<form method="post" action="<?= url('/dashboard/categories') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'help' => "The category's display name, shown on the public site and in the Posts filter."]); ?>
  <?php component('input', ['type' => 'textarea', 'name' => 'description', 'label' => 'Description', 'help' => "Optional — shown on the category's own page on the public site."]); ?>
  <?php component('button', ['label' => 'Add Category', 'type' => 'submit']); ?>
</form>
<?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>

<h2>Categories</h2>
<?php
component('table', [
    'headers' => ['Name', 'Slug', 'Actions'],
    'rows' => array_map(function ($c) {
        $actions = '<div class="admin-table-actions">';
        $actions .= '<button type="button" class="btn btn-sm btn-outline" data-modal-open="edit-category-' . $c['id'] . '">Edit</button>';
        $actions .= '<form method="post" action="' . url('/dashboard/categories/' . $c['id'] . '/delete') . '" data-confirm="Delete this category?">' . csrf_field() . '<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>';
        $actions .= '</div>';
        return [clean($c['name']), clean($c['slug']), $actions];
    }, $categories),
    'emptyMessage' => 'No categories yet.',
]);

foreach ($categories as $c) {
    ob_start();
    ?>
    <form method="post" action="<?= url('/dashboard/categories/' . $c['id']) ?>">
      <?= csrf_field() ?>
      <?php component('input', ['name' => 'name', 'label' => 'Name', 'value' => $c['name'], 'required' => true, 'help' => "The category's display name, shown on the public site and in the Posts filter."]); ?>
      <?php component('input', ['type' => 'textarea', 'name' => 'description', 'label' => 'Description', 'value' => $c['description'] ?? '', 'help' => "Optional — shown on the category's own page on the public site."]); ?>
      <?php component('button', ['label' => 'Save Changes', 'type' => 'submit']); ?>
    </form>
    <?php
    $modalBody = ob_get_clean();
    component('modal', ['id' => 'edit-category-' . $c['id'], 'title' => 'Edit ' . $c['name'], 'body' => $modalBody]);
}
?>
