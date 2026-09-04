<?php
/** Create/edit post form. $post (null when creating), $categories, $selectedTagIds, $errors from PostController. */
$isEdit = !empty($post['id']);
?>
<form method="post" action="<?= $isEdit ? url('/dashboard/posts/' . $post['id']) : url('/dashboard/posts') ?>" class="post-editor">
  <?= csrf_field() ?>
  <div class="post-editor-grid">
    <div class="post-editor-main">
      <div class="card">
        <div class="card-body">
          <?php component('input', ['name' => 'title', 'label' => 'Title', 'value' => $post['title'] ?? '', 'required' => true, 'error' => $errors['title'] ?? null]); ?>
          <?php component('input', ['name' => 'slug', 'label' => 'Slug (optional — auto-generated if blank)', 'value' => $post['slug'] ?? '']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'excerpt', 'label' => 'Excerpt', 'value' => $post['excerpt'] ?? '']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'body', 'label' => 'Body', 'value' => $post['body'] ?? '', 'required' => true, 'error' => $errors['body'] ?? null]); ?>
        </div>
      </div>
    </div>

    <div class="post-editor-side">
      <div class="card">
        <div class="card-header"><h3>Publish</h3></div>
        <div class="card-body">
          <?php component('input', [
              'type' => 'select', 'name' => 'status', 'label' => 'Status',
              'value' => $post['status'] ?? 'draft',
              'options' => ['draft' => 'Draft', 'published' => 'Published'],
              'required' => true, 'error' => $errors['status'] ?? null,
          ]); ?>
          <?php component('button', ['label' => $isEdit ? 'Save Changes' : 'Create Post', 'type' => 'submit', 'attrs' => 'style="width:100%"']); ?>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>Organization</h3></div>
        <div class="card-body">
          <?php component('input', [
              'type' => 'select', 'name' => 'category_id', 'label' => 'Category',
              'value' => $post['category_id'] ?? '',
              'options' => ['' => '— None —'] + array_column($categories, 'name', 'id'),
          ]); ?>
          <?php component('input', ['name' => 'cover_image', 'label' => 'Cover Image URL', 'value' => $post['cover_image'] ?? '']); ?>

          <div class="form-group">
            <label>Tags</label>
            <div class="tag-picker">
              <?php foreach (($allTags ?? []) as $tag): ?>
                <label class="tag-picker-option">
                  <input type="checkbox" name="tags[]" value="<?= (int) $tag['id'] ?>"<?= in_array($tag['id'], $selectedTagIds ?? [], true) ? ' checked' : '' ?>>
                  <?= clean($tag['name']) ?>
                </label>
              <?php endforeach; ?>
            </div>
            <input type="text" name="new_tags" class="form-control" placeholder="Add new tags, comma-separated">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>SEO</h3></div>
        <div class="card-body">
          <?php component('input', ['name' => 'seo_title', 'label' => 'SEO Title', 'value' => $post['seo_title'] ?? '']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'seo_description', 'label' => 'SEO Description', 'value' => $post['seo_description'] ?? '']); ?>
        </div>
      </div>
    </div>
  </div>
</form>
