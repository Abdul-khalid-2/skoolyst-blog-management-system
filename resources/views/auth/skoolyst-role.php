<?php
/**
 * One-time account-type picker after a first "Login with Skoolyst". $pending is the
 * identity parked in the session by SkoolystAuthService::resolve(). Submits to
 * AuthController@completeSkoolystSignup.
 */
$title = 'Choose your account type';
?>
<h2>Almost there, <?= clean($pending['name']) ?></h2>
<p class="auth-switch">You're signing in with Skoolyst as <strong><?= clean($pending['email']) ?></strong>. How will you use Skoolyst Blog?</p>
<form method="post" action="<?= url('/auth/skoolyst/account-type') ?>">
  <?= csrf_field() ?>
  <?php component('input', [
      'type' => 'select', 'name' => 'role', 'label' => 'Account type', 'required' => true,
      'value' => 'reader',
      'options' => ['reader' => 'Reader — comment on posts', 'author' => 'Author — write and manage my own posts'],
  ]); ?>
  <?php component('button', ['label' => 'Create my account', 'type' => 'submit']); ?>
</form>
<p class="auth-switch">Not you? <a href="<?= url('/login') ?>">Use a different account</a></p>
