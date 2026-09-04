<?php
/**
 * Login page. Submits to AuthController@login (POST /login), which uses
 * AuthService for the real session-based auth logic (Phase 4).
 */
$title = 'Login';
?>
<h2>Sign in</h2>
<form method="post" action="<?= url('/login') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true, 'error' => $errors['email'] ?? null, 'autocomplete' => 'username']); ?>
  <?php component('input', ['type' => 'password', 'name' => 'password', 'label' => 'Password', 'required' => true, 'error' => $errors['password'] ?? null, 'value' => '', 'autocomplete' => 'current-password']); ?>
  <?php component('button', ['label' => 'Sign in', 'type' => 'submit']); ?>
</form>
