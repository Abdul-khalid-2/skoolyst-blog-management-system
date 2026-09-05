<?php
/** Create/edit post form. $post (null when creating), $categories, $selectedTagIds, $errors from PostController. */
$isEdit = !empty($post['id']);
$coverImageTab = !empty($post['cover_image']) ? 'url' : 'upload';
?>
<form method="post" action="<?= $isEdit ? url('/dashboard/posts/' . $post['id']) : url('/dashboard/posts') ?>" class="post-editor" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="post-editor-grid">
    <div class="post-editor-main">
      <div class="card">
        <div class="card-body">
          <?php component('input', ['name' => 'title', 'label' => 'Title', 'value' => $post['title'] ?? '', 'required' => true, 'error' => $errors['title'] ?? null, 'help' => 'The headline shown on the article page, in listings, and in the browser tab.']); ?>
          <?php component('input', ['name' => 'slug', 'label' => 'Slug (optional — auto-generated if blank)', 'value' => $post['slug'] ?? '', 'help' => 'The URL-friendly identifier, e.g. /post/your-slug. Leave blank to generate one from the title automatically.']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'excerpt', 'label' => 'Excerpt', 'value' => $post['excerpt'] ?? '', 'help' => 'A short summary shown on listing/card pages and used as the SEO description if that field is left blank. Not shown on the article itself.']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'body', 'label' => 'Body', 'value' => $post['body'] ?? '', 'required' => true, 'error' => $errors['body'] ?? null, 'help' => 'The main article content. Use the toolbar to format text, add links, images and tables.']); ?>
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
              'help' => 'Draft posts are only visible in this dashboard. Published posts go live on the public site immediately.',
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
              'help' => 'Groups this post under a category on the public site\'s category pages and filters.',
          ]); ?>
          <div class="form-group cover-image-field" data-tabs>
            <label>Cover Image <span class="field-help" tabindex="0" role="img" aria-label="Shown at the top of the article and in post cards on the homepage/blog listing. Upload a file or paste an external image URL." data-tooltip="Shown at the top of the article and in post cards on the homepage/blog listing. Upload a file or paste an external image URL.">i</span></label>
            <div class="cover-tab-buttons">
              <button type="button" class="btn btn-sm btn-outline<?= $coverImageTab === 'upload' ? ' is-active' : '' ?>" data-tab-target="upload">Upload Image</button>
              <button type="button" class="btn btn-sm btn-outline<?= $coverImageTab === 'url' ? ' is-active' : '' ?>" data-tab-target="url">Image URL</button>
            </div>

            <div data-tab-panel="upload"<?= $coverImageTab !== 'upload' ? ' hidden' : '' ?>>
              <input type="file" name="cover_image_file" class="form-control" accept="image/*">
              <?php foreach ((array) ($errors['cover_image_file'] ?? []) as $msg): ?>
                <p class="form-error"><?= clean($msg) ?></p>
              <?php endforeach; ?>
            </div>

            <div data-tab-panel="url"<?= $coverImageTab !== 'url' ? ' hidden' : '' ?>>
              <input type="text" name="cover_image" class="form-control" value="<?= clean($post['cover_image'] ?? '') ?>" placeholder="https://example.com/image.jpg">
            </div>

            <?php if (!empty($post['cover_image'])): ?>
              <p class="stat-label">Current: <img src="<?= clean($post['cover_image']) ?>" alt="" style="height:32px;vertical-align:middle;border-radius:4px;margin-left:.4rem"></p>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label>Tags <span class="field-help" tabindex="0" role="img" aria-label="Helps readers find related content. Type to search existing tags, or type a new name and press Enter to create one." data-tooltip="Helps readers find related content. Type to search existing tags, or type a new name and press Enter to create one.">i</span></label>
            <div
              class="tag-input"
              data-tag-input
              data-all-tags="<?= clean(json_encode(array_map(fn ($t) => ['id' => (int) $t['id'], 'name' => $t['name']], $allTags ?? []))) ?>"
              data-selected-tags="<?= clean(json_encode(array_values(array_map('intval', $selectedTagIds ?? [])))) ?>"
            >
              <div class="tag-input-chips" data-tag-chips></div>
              <input type="text" data-tag-search autocomplete="off" placeholder="Type to search or add a tag...">
              <div class="tag-input-suggestions" data-tag-suggestions hidden></div>
              <span data-tag-hidden></span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3>SEO</h3></div>
        <div class="card-body">
          <?php component('input', ['name' => 'seo_title', 'label' => 'SEO Title', 'value' => $post['seo_title'] ?? '', 'help' => 'Overrides the browser tab title and search-result headline. Falls back to the post Title if left blank.']); ?>
          <?php component('input', ['type' => 'textarea', 'name' => 'seo_description', 'label' => 'SEO Description', 'value' => $post['seo_description'] ?? '', 'help' => 'Shown in search engine results and social share previews. Falls back to the Excerpt if left blank.']); ?>
        </div>
      </div>
    </div>
  </div>
</form>
