<?php
/**
 * Login page. Static placeholder form for Phase 3 — real submission is wired to
 * AuthController@login + AuthService in Phase 4 (Authentication & Security).
 */
$title = 'Login';
?>
<h2>Sign in</h2>
<form method="post" action="<?= url('/login') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true]); ?>
  <?php component('input', ['type' => 'password', 'name' => 'password', 'label' => 'Password', 'required' => true]); ?>
  <?php component('button', ['label' => 'Sign in', 'type' => 'submit']); ?>
</form>
