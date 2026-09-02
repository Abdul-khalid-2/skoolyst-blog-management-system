// Shared admin JavaScript: sidebar, tables, filters, confirmations.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelector('[data-sidebar]')?.classList.toggle('is-open');
    });
  });

  // Confirm before destructive actions, e.g. <button data-confirm="Delete this post?">
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!window.confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
  });
});
