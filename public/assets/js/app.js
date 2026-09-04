// Shared module JavaScript: alerts, modals, mobile nav, common UI behavior.
// Delegated on document (rather than bound once on load) so this keeps working
// for content swapped in later, e.g. by admin-spa.js.
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
});

// Dismissible alerts
document.addEventListener('click', function (e) {
  var closeBtn = e.target.closest('[data-alert-close]');
  if (closeBtn) {
    closeBtn.closest('.alert')?.remove();
    return;
  }

  // Modals: open via [data-modal-open="modal-id"], close via [data-modal-close] or backdrop
  var openBtn = e.target.closest('[data-modal-open]');
  if (openBtn) {
    var modal = document.getElementById(openBtn.getAttribute('data-modal-open'));
    if (modal) modal.hidden = false;
    return;
  }

  var closeTrigger = e.target.closest('[data-modal-close]');
  if (closeTrigger) {
    closeTrigger.closest('.modal')?.setAttribute('hidden', 'true');
  }
});
