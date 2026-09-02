<?php
/**
 * Form input/field component.
 * $type = text|email|password|textarea|select, $name, $label (optional), $value (optional),
 * $placeholder (optional), $required (bool), $options (array<value=>label>, for select),
 * $error (string|array|null) — validation message(s) for this field.
 */
$type ??= 'text';
$value ??= old($name ?? '', '');
$errorMessages = is_array($error ?? null) ? $error : (($error ?? null) ? [$error] : []);
$fieldId = 'field-' . clean($name ?? uniqid());
?>
<div class="form-group<?= $errorMessages ? ' has-error' : '' ?>">
  <?php if (!empty($label)): ?>
    <label for="<?= $fieldId ?>"><?= clean($label) ?><?= !empty($required) ? ' <span class="required">*</span>' : '' ?></label>
  <?php endif; ?>

  <?php if ($type === 'textarea'): ?>
    <textarea id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control" placeholder="<?= clean($placeholder ?? '') ?>"<?= !empty($required) ? ' required' : '' ?>><?= clean($value) ?></textarea>
  <?php elseif ($type === 'select'): ?>
    <select id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control"<?= !empty($required) ? ' required' : '' ?>>
      <?php foreach (($options ?? []) as $optValue => $optLabel): ?>
        <option value="<?= clean($optValue) ?>"<?= (string) $optValue === (string) $value ? ' selected' : '' ?>><?= clean($optLabel) ?></option>
      <?php endforeach; ?>
    </select>
  <?php else: ?>
    <input type="<?= clean($type) ?>" id="<?= $fieldId ?>" name="<?= clean($name ?? '') ?>" class="form-control" value="<?= clean($value) ?>" placeholder="<?= clean($placeholder ?? '') ?>"<?= !empty($required) ? ' required' : '' ?>>
  <?php endif; ?>

  <?php foreach ($errorMessages as $msg): ?>
    <p class="form-error"><?= clean($msg) ?></p>
  <?php endforeach; ?>
</div>
