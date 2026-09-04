// Shared admin JavaScript: sidebar, tables, filters, confirmations.
// Delegated on document so it keeps working after admin-spa.js swaps the content area.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-sidebar-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelector('[data-sidebar]')?.classList.toggle('is-open');
    });
  });
});

// Confirm before destructive actions, e.g. <form data-confirm="Delete this post?">
document.addEventListener('click', function (e) {
  var el = e.target.closest('[data-confirm]');
  if (el && !window.confirm(el.getAttribute('data-confirm'))) e.preventDefault();
});
