<?php /** Contact page. Submits to PageController@submitContact. */ ?>
<section class="container">
  <h1>Contact Us</h1>
  <?php ob_start(); ?>
  <form method="post" action="<?= url('/contact') ?>">
    <?= csrf_field() ?>
    <?php component('input', ['name' => 'name', 'label' => 'Name', 'required' => true]); ?>
    <?php component('input', ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true]); ?>
    <?php component('input', ['type' => 'textarea', 'name' => 'message', 'label' => 'Message', 'required' => true]); ?>
    <?php component('button', ['label' => 'Send Message', 'type' => 'submit']); ?>
  </form>
  <?php $body = ob_get_clean(); component('card', ['body' => $body]); ?>
</section>
