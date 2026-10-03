<?php
/**
 * "Other Skoolyst Apps" — a card per active Skoolyst app except this one,
 * shown just above the footer. The list lives in config/skoolyst_apps.php.
 */
$skoolystRegistry = require __DIR__ . '/../../../config/skoolyst_apps.php';
$skoolystCurrent = $skoolystRegistry['current'] ?? '';
$skoolystIcons = $skoolystRegistry['icons'] ?? 'initials';
$skoolystE = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<nav class="skoolyst-apps" aria-labelledby="skoolyst-apps-title">
    <div class="skoolyst-apps-inner">
        <h2 id="skoolyst-apps-title" class="skoolyst-apps-title">Other Skoolyst Apps</h2>
        <ul class="skoolyst-apps-grid">
            <?php foreach ($skoolystRegistry['apps'] ?? [] as $app): ?>
                <?php if (empty($app['active']) || ($app['key'] ?? '') === $skoolystCurrent) continue; ?>
                <li>
                    <a href="<?= $skoolystE($app['url']) ?>" class="skoolyst-app-card">
                        <span class="skoolyst-app-icon" aria-hidden="true">
                            <?php if ($skoolystIcons === 'fontawesome'): ?>
                                <i class="<?= $skoolystE($app['icon'] ?? 'fa-solid fa-link') ?>"></i>
                            <?php else: ?>
                                <?= $skoolystE(mb_substr($app['name'], 0, 1)) ?>
                            <?php endif; ?>
                        </span>
                        <span class="skoolyst-app-name"><?= $skoolystE($app['name']) ?></span>
                        <span class="skoolyst-app-desc"><?= $skoolystE($app['description'] ?? '') ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
