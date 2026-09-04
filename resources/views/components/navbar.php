<!-- Shared Skoolyst navbar component. Expects optional $activeNav (string) from the calling view. -->
<header class="site-navbar">
  <div class="container navbar-inner">
    <a href="<?= url('/') ?>" class="navbar-brand">Skoolyst<span>Blog</span></a>

    <button type="button" class="navbar-toggle" aria-label="Toggle navigation" aria-expanded="false" data-nav-toggle>
      <span></span><span></span><span></span>
    </button>

    <nav class="navbar-links" data-nav-menu>
      <a href="<?= url('/') ?>" class="<?= ($activeNav ?? '') === 'home' ? 'is-active' : '' ?>">Home</a>
      <a href="<?= url('/blog') ?>" class="<?= ($activeNav ?? '') === 'blog' ? 'is-active' : '' ?>">Articles</a>
      <a href="<?= url('/about') ?>" class="<?= ($activeNav ?? '') === 'about' ? 'is-active' : '' ?>">About</a>
      <a href="<?= url('/contact') ?>" class="<?= ($activeNav ?? '') === 'contact' ? 'is-active' : '' ?>">Contact</a>
      <?php if (is_authenticated()): ?>
        <?php if (in_array(auth_user()['role'] ?? '', ['admin', 'editor', 'author'], true)): ?>
          <a href="<?= url('/dashboard') ?>" class="btn btn-primary btn-sm">Dashboard</a>
        <?php else: ?>
          <span class="navbar-user"><?= clean(auth_user()['name'] ?? 'Account') ?></span>
          <a href="<?= url('/logout') ?>" class="btn btn-outline btn-sm">Logout</a>
        <?php endif; ?>
      <?php else: ?>
        <a href="<?= url('/login') ?>" class="btn btn-outline btn-sm">Login</a>
        <a href="<?= url('/signup') ?>" class="btn btn-primary btn-sm">Sign up</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
