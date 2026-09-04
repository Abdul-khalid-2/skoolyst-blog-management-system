<?php
/** Edit profile: name + optional password change. $errors from ProfileController. */
$user = auth_user();
?>
<?php ob_start(); ?>
<form method="post" action="<?= url('/dashboard/profile') ?>">
  <?= csrf_field() ?>
  <?php component('input', ['name' => 'name', 'label' => 'Name', 'value' => $user['name'] ?? '', 'required' => true, 'error' => $errors['name'] ?? null]); ?>
  <div class="form-group">
    <label>Email</label>
    <p class="form-static"><?= clean($user['email'] ?? '') ?></p>
  </div>

  <h3>Change Password</h3>
  <p class="auth-switch">Leave blank to keep your current password.</p>
  <?php component('input', ['type' => 'password', 'name' => 'current_password', 'label' => 'Current Password', 'error' => $errors['current_password'] ?? null, 'autocomplete' => 'current-password']); ?>
  <?php component('input', ['type' => 'password', 'name' => 'new_password', 'label' => 'New Password', 'error' => $errors['new_password'] ?? null, 'autocomplete' => 'new-password']); ?>
  <?php component('input', ['type' => 'password', 'name' => 'new_password_confirmation', 'label' => 'Confirm New Password', 'error' => $errors['new_password_confirmation'] ?? null, 'autocomplete' => 'new-password']); ?>

  <?php component('button', ['label' => 'Save Changes', 'type' => 'submit']); ?>
</form>
<?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
