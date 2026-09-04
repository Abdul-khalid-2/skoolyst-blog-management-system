// Shared admin JavaScript: sidebar, dropdowns, filters, confirmations.
// Delegated on document so it keeps working after admin-spa.js swaps the content area.
function closeAdminSidebar() {
  document.querySelector('[data-sidebar]')?.classList.remove('is-open');
  document.querySelector('[data-sidebar-backdrop]')?.classList.remove('is-open');
}

function copyTextFallback(text) {
  var textarea = document.createElement('textarea');
  textarea.value = text;
  textarea.style.position = 'fixed';
  textarea.style.opacity = '0';
  document.body.appendChild(textarea);
  textarea.select();
  try { document.execCommand('copy'); } catch (e) {}
  document.body.removeChild(textarea);
}

function closeAdminDropdowns(except) {
  document.querySelectorAll('[data-dropdown-menu]').forEach(function (menu) {
    if (menu !== except) menu.hidden = true;
  });
  document.querySelectorAll('[data-dropdown-toggle]').forEach(function (btn) {
    btn.setAttribute('aria-expanded', btn === except ? 'true' : 'false');
  });
}

document.addEventListener('click', function (e) {
  var copyBtn = e.target.closest('[data-copy-url]');
  if (copyBtn) {
    var url = copyBtn.getAttribute('data-copy-url');
    var showCopied = function () {
      var original = copyBtn.textContent;
      copyBtn.textContent = 'Copied!';
      setTimeout(function () { copyBtn.textContent = original; }, 1500);
    };
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(url).then(showCopied, function () { copyTextFallback(url); showCopied(); });
    } else {
      copyTextFallback(url);
      showCopied();
    }
    return;
  }

  // Simple tab toggle, e.g. the post editor's Upload Image / Image URL switch.
  var tabBtn = e.target.closest('[data-tab-target]');
  if (tabBtn) {
    var tabGroup = tabBtn.closest('[data-tabs]');
    if (tabGroup) {
      var target = tabBtn.getAttribute('data-tab-target');
      tabGroup.querySelectorAll('[data-tab-target]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn === tabBtn);
      });
      tabGroup.querySelectorAll('[data-tab-panel]').forEach(function (panel) {
        panel.hidden = panel.getAttribute('data-tab-panel') !== target;
      });
    }
    return;
  }

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

  // Profile/notification dropdowns: toggle on trigger, close on outside click.
  var trigger = e.target.closest('[data-dropdown-toggle]');
  if (trigger) {
    var menu = trigger.closest('[data-dropdown]')?.querySelector('[data-dropdown-menu]');
    var willOpen = !!menu && menu.hidden;
    closeAdminDropdowns(willOpen ? menu : null);
    if (menu) menu.hidden = !willOpen;
    return;
  }
  if (!e.target.closest('[data-dropdown]')) closeAdminDropdowns(null);

  // Confirm before destructive actions, e.g. <form data-confirm="Delete this post?">
  var confirmEl = e.target.closest('[data-confirm]');
  if (confirmEl && !window.confirm(confirmEl.getAttribute('data-confirm'))) e.preventDefault();
});

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeAdminDropdowns(null);
});
