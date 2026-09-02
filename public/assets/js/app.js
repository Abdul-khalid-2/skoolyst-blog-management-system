// Shared module JavaScript: alerts, modals, mobile nav, common UI behavior.
document.addEventListener('DOMContentLoaded', function () {
  // Mobile nav toggle
  document.querySelectorAll('[data-nav-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var menu = document.querySelector('[data-nav-menu]');
      if (!menu) return;
      var isOpen = menu.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });

  // Dismissible alerts
  document.querySelectorAll('[data-alert-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.closest('.alert')?.remove();
    });
  });

  // Modals: open via [data-modal-open="modal-id"], close via [data-modal-close] or backdrop
  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = document.getElementById(btn.getAttribute('data-modal-open'));
      if (modal) modal.hidden = false;
    });
  });
  document.querySelectorAll('[data-modal-close]').forEach(function (el) {
    el.addEventListener('click', function () {
      el.closest('.modal')?.setAttribute('hidden', 'true');
    });
  });
});
