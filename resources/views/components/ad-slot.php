<?php
/**
 * Renders one ad slot, or nothing at all if no ad is eligible for it.
 * $placement (required string): a friendly slot name from config/ads.php's
 * 'placements' map (e.g. 'home_top'), not the raw ads.skoolyst.com code.
 */
$__ads = new \Skoolyst\Services\AdService();
$__code = $__ads->placementCode($placement ?? '');
$__ad = $__code ? $__ads->getAd($__code) : null;
if (!$__ad) return;
?>
<div class="ad-slot" data-ad-id="<?= (int) $__ad['id'] ?>" data-ad-track-url="<?= clean(url('/ads/track/')) ?>" data-ad-csrf="<?= clean(csrf_token()) ?>">
  <span class="ad-slot-label">Sponsored</span>
  <a class="ad-slot-card" href="<?= clean($__ad['click_url']) ?>" target="_blank" rel="noopener sponsored" data-ad-click>
    <?php if (!empty($__ad['image_path'])): ?>
      <img src="<?= clean($__ads->imageUrl($__ad['image_path'])) ?>" alt="<?= clean($__ad['title'] ?? '') ?>" loading="lazy">
    <?php endif; ?>
    <div class="ad-slot-body">
      <h3><?= clean($__ad['title'] ?? '') ?></h3>
      <?php if (!empty($__ad['description'])): ?><p><?= clean($__ad['description']) ?></p><?php endif; ?>
      <span class="btn btn-primary btn-sm"><?= clean($__ad['cta_text'] ?: 'Learn More') ?></span>
    </div>
  </a>
</div>
