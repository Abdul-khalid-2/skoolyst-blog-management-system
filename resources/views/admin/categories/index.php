<?php
/** Categories CRUD. $categories from CategoryController@adminIndex. Edit uses a modal per row. */
?>
<?php ob_start(); ?>
<h3>Add Category</h3>
<form method="post" action="<?= url('/dashboard/categories') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['name' => 'name', 'label' => 'Name', 'required' => true]); ?>
  <?php component('input', ['type' => 'textarea', 'name' => 'description', 'label' => 'Description']); ?>
  <?php component('button', ['label' => 'Add Category', 'type' => 'submit']); ?>
</form>
<?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>

<h2>Categories</h2>
<?php
component('table', [
    'headers' => ['Name', 'Slug', 'Actions'],
    'rows' => array_map(function ($c) {
        $actions = '<button type="button" class="btn btn-outline btn-sm" data-modal-open="edit-category-' . $c['id'] . '">Edit</button> ';
        $actions .= '<form method="post" action="' . url('/dashboard/categories/' . $c['id'] . '/delete') . '" style="display:inline" data-confirm="Delete this category?">' . csrf_field() . '<button type="submit" class="btn-ghost">Delete</button></form>';
        return [clean($c['name']), clean($c['slug']), $actions];
    }, $categories),
    'emptyMessage' => 'No categories yet.',
]);

foreach ($categories as $c) {
    ob_start();
    ?>
    <form method="post" action="<?= url('/dashboard/categories/' . $c['id']) ?>">
      <?= csrf_field() ?>
      <?php component('input', ['name' => 'name', 'label' => 'Name', 'value' => $c['name'], 'required' => true]); ?>
      <?php component('input', ['type' => 'textarea', 'name' => 'description', 'label' => 'Description', 'value' => $c['description'] ?? '']); ?>
      <?php component('button', ['label' => 'Save Changes', 'type' => 'submit']); ?>
    </form>
    <?php
    $modalBody = ob_get_clean();
    component('modal', ['id' => 'edit-category-' . $c['id'], 'title' => 'Edit ' . clean($c['name']), 'body' => $modalBody]);
}
?>
