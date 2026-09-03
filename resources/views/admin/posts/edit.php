<?php
/** Create/edit post form. $post (null when creating), $categories, $selectedTagIds, $errors from PostController. */
$isEdit = !empty($post['id']);
?>
<form method="post" action="<?= $isEdit ? url('/dashboard/posts/' . $post['id']) : url('/dashboard/posts') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['name' => 'title', 'label' => 'Title', 'value' => $post['title'] ?? '', 'required' => true, 'error' => $errors['title'] ?? null]); ?>
  <?php component('input', ['name' => 'slug', 'label' => 'Slug (optional — auto-generated if blank)', 'value' => $post['slug'] ?? '']); ?>
  <?php component('input', ['type' => 'textarea', 'name' => 'excerpt', 'label' => 'Excerpt', 'value' => $post['excerpt'] ?? '']); ?>
  <?php component('input', ['type' => 'textarea', 'name' => 'body', 'label' => 'Body', 'value' => $post['body'] ?? '', 'required' => true, 'error' => $errors['body'] ?? null]); ?>
  <?php component('input', ['name' => 'cover_image', 'label' => 'Cover Image URL', 'value' => $post['cover_image'] ?? '']); ?>
  <?php component('input', [
      'type' => 'select', 'name' => 'category_id', 'label' => 'Category',
      'value' => $post['category_id'] ?? '',
      'options' => ['' => '— None —'] + array_column($categories, 'name', 'id'),
  ]); ?>
  <?php component('input', [
      'type' => 'select', 'name' => 'status', 'label' => 'Status',
      'value' => $post['status'] ?? 'draft',
      'options' => ['draft' => 'Draft', 'published' => 'Published'],
      'required' => true, 'error' => $errors['status'] ?? null,
  ]); ?>
  <?php component('input', ['name' => 'seo_title', 'label' => 'SEO Title', 'value' => $post['seo_title'] ?? '']); ?>
  <?php component('input', ['type' => 'textarea', 'name' => 'seo_description', 'label' => 'SEO Description', 'value' => $post['seo_description'] ?? '']); ?>
  <?php component('button', ['label' => $isEdit ? 'Save Changes' : 'Create Post', 'type' => 'submit']); ?>
</form>
