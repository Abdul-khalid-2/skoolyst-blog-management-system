<?php
/** Categories CRUD. $categories from CategoryController@adminIndex. */
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
        $actions = '<form method="post" action="' . url('/dashboard/categories/' . $c['id'] . '/delete') . '" style="display:inline" data-confirm="Delete this category?">' . csrf_field() . '<button type="submit" class="btn-ghost">Delete</button></form>';
        return [clean($c['name']), clean($c['slug']), $actions];
    }, $categories),
    'emptyMessage' => 'No categories yet.',
]);
?>
