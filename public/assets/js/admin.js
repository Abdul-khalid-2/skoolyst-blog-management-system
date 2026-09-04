// Shared admin JavaScript: sidebar, tables, filters, confirmations.
// Delegated on document so it keeps working after admin-spa.js swaps the content area.
function closeAdminSidebar() {
  document.querySelector('[data-sidebar]')?.classList.remove('is-open');
  document.querySelector('[data-sidebar-backdrop]')?.classList.remove('is-open');
}

document.addEventListener('click', function (e) {
  if (e.target.closest('[data-sidebar-toggle]')) {
    document.querySelector('[data-sidebar]')?.classList.toggle('is-open');
    document.querySelector('[data-sidebar-backdrop]')?.classList.toggle('is-open');
    return;
  }

  // Tapping the backdrop, or choosing a nav/logout link, closes the mobile sidebar.
  if (e.target.closest('[data-sidebar-backdrop]') || e.target.closest('.admin-sidebar a')) {
    closeAdminSidebar();
    if (e.target.closest('[data-sidebar-backdrop]')) return;
  }

  // Confirm before destructive actions, e.g. <form data-confirm="Delete this post?">
  var confirmEl = e.target.closest('[data-confirm]');
  if (confirmEl && !window.confirm(confirmEl.getAttribute('data-confirm'))) e.preventDefault();
});
