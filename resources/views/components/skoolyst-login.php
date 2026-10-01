<?php
/**
 * "Login with Skoolyst" button + divider for the login/signup pages. Hidden entirely
 * when SSO isn't configured (no client id/secret in .env), so a half-set-up
 * integration never shows a button that can only fail.
 */
if (!(new \Skoolyst\Services\SkoolystAuthService())->isConfigured()) return;
?>
<a href="<?= url('/auth/skoolyst') ?>" class="btn btn-outline btn-sso">
  <img src="<?= asset('favicon-32x32.png') ?>" alt="" width="20" height="20" aria-hidden="true">
  <?= clean($label ?? 'Continue with Skoolyst') ?>
</a>
<p class="auth-divider"><span>or</span></p>
