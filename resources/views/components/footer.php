<!-- Shared Skoolyst footer component. -->
<footer class="site-footer">
  <div class="container footer-inner">
    <div class="footer-brand">
      <p class="footer-logo">
        <picture><source srcset="<?= url('assets/images/skoolyst-blog-sm.webp') ?>" type="image/webp"><img src="<?= url('assets/images/skoolyst-blog-sm.png') ?>" alt="Skoolyst" class="brand-logo-img" width="90" height="28" loading="lazy" decoding="async"></picture>
        <span class="brand-logo-word">Blogs</span>
      </p>
      <p class="footer-tagline">Insights, updates and stories from the Skoolyst team.</p>
    </div>
    <nav class="footer-links">
      <a href="<?= url('/') ?>">Home</a>
      <a href="<?= url('/blog') ?>">Articles</a>
      <a href="<?= url('/about') ?>">About</a>
      <a href="<?= url('/contact') ?>">Contact</a>
      <a href="<?= url('/terms') ?>">Terms &amp; Conditions</a>
      <a href="<?= url('/privacy') ?>">Privacy Policy</a>
    </nav>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <p>&copy; <?= date('Y') ?> Skoolyst. All rights reserved.</p>
    </div>
  </div>
</footer>
