<!-- Shared admin sidebar component. Expects optional $activeNav (string) from the calling view. -->
<div class="admin-sidebar-backdrop" data-sidebar-backdrop></div>
<aside class="admin-sidebar" data-sidebar>
  <a href="<?= url('/') ?>" class="admin-sidebar-brand">Skoolyst<span>Blog</span></a>
  <nav class="admin-sidebar-nav">
    <a href="<?= url('/dashboard') ?>" class="<?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>">Overview</a>
    <a href="<?= url('/dashboard/posts') ?>" class="<?= ($activeNav ?? '') === 'posts' ? 'is-active' : '' ?>">Posts</a>
    <a href="<?= url('/dashboard/categories') ?>" class="<?= ($activeNav ?? '') === 'categories' ? 'is-active' : '' ?>">Categories</a>
    <a href="<?= url('/dashboard/media') ?>" class="<?= ($activeNav ?? '') === 'media' ? 'is-active' : '' ?>">Media</a>
    <a href="<?= url('/dashboard/comments') ?>" class="<?= ($activeNav ?? '') === 'comments' ? 'is-active' : '' ?>">Comments</a>
  </nav>
  <div class="admin-sidebar-footer">
    <span class="admin-user-name"><?= clean(auth_user()['name'] ?? 'Account') ?></span>
    <a href="<?= url('/logout') ?>" class="admin-logout">Logout</a>
  </div>
</aside>
