<?php
/**
 * Form input/field component.
 * $type = text|email|password|textarea|select, $name, $label (optional), $value (optional),
 * $placeholder (optional), $required (bool), $options (array<value=>label>, for select),
 * $error (string|array|null) — validation message(s) for this field.
 * $autocomplete (optional) — e.g. "username"/"current-password" on a login form, so browser
 * autofill can't misassign a saved credential to the wrong field (no autocomplete hint at all
 * is what causes that).
 * $help (optional string) — shown as an "i" icon next to the label; hover or focus it to read
 * a short explanation of the field, for guiding admin/editor/author users on the dashboard.
 * $maxlength (optional int) — only pass this when a matching server-side max: rule exists too,
 * so the client-side limit never blocks something the server would actually accept.
 */
$type ??= 'text';
$value ??= old($name ?? '', '');
$errorMessages = is_array($error ?? null) ? $error : (($error ?? null) ? [$error] : []);
$fieldId = 'field-' . clean($name ?? uniqid());
?>
<div class="form-group<?= $errorMessages ? ' has-error' : '' ?>">
  <?php if (!empty($label)): ?>
    <label for="<?= $fieldId ?>">
      <?= clean($label) ?><?= !empty($required) ? ' <span class="required">*</span>' : '' ?>
      <?php if (!empty($help)): ?><span class="field-help" tabindex="0" role="img" aria-label="<?= clean($help) ?>" data-tooltip="<?= clean($help) ?>">i</span><?php endif; ?>
    </label>
  <?php endif; ?>

  <?php if ($type === 'textarea'): ?>
    <textarea id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control" placeholder="<?= clean($placeholder ?? '') ?>"<?= !empty($required) ? ' required' : '' ?><?= !empty($maxlength) ? ' maxlength="' . (int) $maxlength . '"' : '' ?>><?= clean($value) ?></textarea>
  <?php elseif ($type === 'select'): ?>
    <select id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control"<?= !empty($required) ? ' required' : '' ?>>
      <?php foreach (($options ?? []) as $optValue => $optLabel): ?>
        <option value="<?= clean($optValue) ?>"<?= (string) $optValue === (string) $value ? ' selected' : '' ?>><?= clean($optLabel) ?></option>
      <?php endforeach; ?>
    </select>
  <?php elseif ($type === 'password'): ?>
    <div class="password-field">
      <input type="password" id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control" value="<?= clean($value) ?>" placeholder="<?= clean($placeholder ?? '') ?>"<?= !empty($required) ? ' required' : '' ?><?= !empty($maxlength) ? ' maxlength="' . (int) $maxlength . '"' : '' ?><?= !empty($autocomplete) ? ' autocomplete="' . clean($autocomplete) . '"' : '' ?>>
      <?php /* Show/hide toggle — wired up in assets/js/app.js ([data-password-toggle]). Inline SVG since Font Awesome isn't loaded. */ ?>
      <button type="button" class="password-toggle" data-password-toggle="<?= $fieldId ?>" aria-controls="<?= $fieldId ?>" aria-label="Show password" aria-pressed="false">
        <svg class="icon-eye" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
        <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="2" y1="2" x2="22" y2="22"/></svg>
      </button>
    </div>
  <?php else: ?>
    <input type="<?= clean($type) ?>" id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control" value="<?= clean($value) ?>" placeholder="<?= clean($placeholder ?? '') ?>"<?= !empty($required) ? ' required' : '' ?><?= !empty($maxlength) ? ' maxlength="' . (int) $maxlength . '"' : '' ?><?= !empty($autocomplete) ? ' autocomplete="' . clean($autocomplete) . '"' : '' ?>>
  <?php endif; ?>

  <?php foreach ($errorMessages as $msg): ?>
    <p class="form-error"><?= clean($msg) ?></p>
  <?php endforeach; ?>
</div>
