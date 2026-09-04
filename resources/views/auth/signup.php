<?php
/**
 * Public signup page. Submits to AuthController@signup (POST /signup), which
 * uses AuthService::register(). Only creates 'author' or 'reader' accounts —
 * admin/editor stay internally-provisioned (seeder/CLI).
 */
$title = 'Sign Up';
?>
<h2>Create your account</h2>
<form method="post" action="<?= url('/signup') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['type' => 'text', 'name' => 'name', 'label' => 'Name', 'required' => true, 'error' => $errors['name'] ?? null, 'autocomplete' => 'name']); ?>
  <?php component('input', ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true, 'error' => $errors['email'] ?? null, 'autocomplete' => 'username']); ?>
  <?php component('input', ['type' => 'password', 'name' => 'password', 'label' => 'Password', 'required' => true, 'error' => $errors['password'] ?? null, 'value' => '', 'autocomplete' => 'new-password']); ?>
  <?php component('input', ['type' => 'password', 'name' => 'password_confirmation', 'label' => 'Confirm password', 'required' => true, 'error' => $errors['password_confirmation'] ?? null, 'value' => '', 'autocomplete' => 'new-password']); ?>
  <?php component('input', [
      'type' => 'select', 'name' => 'role', 'label' => 'Account type', 'required' => true, 'error' => $errors['role'] ?? null,
      'value' => 'reader',
      'options' => ['reader' => 'Reader — comment on posts', 'author' => 'Author — write and manage my own posts'],
  ]); ?>
  <?php component('button', ['label' => 'Sign up', 'type' => 'submit']); ?>
</form>
<p class="auth-switch">Already have an account? <a href="<?= url('/login') ?>">Sign in</a></p>
