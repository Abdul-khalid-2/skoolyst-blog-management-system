// Lightweight SPA-style navigation for the admin dashboard.
// The sidebar and topbar are never touched: only the [data-spa-view] region
// (flash messages + <main>) is fetched and swapped in, so the shell never reloads.
// A page that doesn't render [data-spa-view] (e.g. we got redirected out to
// /login) falls back to a normal full navigation.
(function () {
  if (!document.body.classList.contains('admin-body')) return;

  var view = document.querySelector('[data-spa-view]');
  var loader = document.querySelector('[data-admin-loader]');
  var titleEl = document.querySelector('.admin-page-title');
  if (!view) return;

  var pending = 0;

  function showLoader() {
    pending++;
    if (loader) loader.hidden = false;
    view.classList.add('is-loading');
  }

  function hideLoader() {
    pending = Math.max(0, pending - 1);
    if (pending > 0) return;
    if (loader) loader.hidden = true;
    view.classList.remove('is-loading');
  }

  function sameOrigin(url) {
    try {
      return new URL(url, window.location.href).origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  function isSpaLink(a) {
    if (!a || !a.href) return false;
    if (a.target && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    if (a.hasAttribute('data-no-spa')) return false;
    if (!sameOrigin(a.href)) return false;
    var url = new URL(a.href, window.location.href);
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return false;
    return true;
  }

  function swapContent(html, finalUrl) {
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var newView = doc.querySelector('[data-spa-view]');
    if (!newView) return false;

    view.innerHTML = newView.innerHTML;
    if (doc.title) document.title = doc.title;

    // Widgets that build their own DOM from data attributes (e.g. the tag picker)
    // only run on DOMContentLoaded, which doesn't fire again for a swapped-in view.
    if (window.initTagInputs) window.initTagInputs(view);
    if (window.initPostEditor) window.initPostEditor(view);

    var newHeading = doc.querySelector('.admin-page-title');
    if (titleEl && newHeading) titleEl.textContent = newHeading.textContent;

    // Notification badge / profile name live outside [data-spa-view], but can
    // change as a result of the very action just submitted (e.g. approving a
    // comment, renaming the account) — so re-sync them from the fetched page.
    var newActions = doc.querySelector('.admin-topbar-actions');
    var curActions = document.querySelector('.admin-topbar-actions');
    if (newActions && curActions) curActions.innerHTML = newActions.innerHTML;

    var newUrl = new URL(finalUrl, window.location.href);
    document.querySelectorAll('.admin-sidebar-nav a').forEach(function (a) {
      var linkUrl = new URL(a.getAttribute('href'), window.location.href);
      a.classList.toggle('is-active', linkUrl.pathname === newUrl.pathname);
    });

    view.scrollTop = 0;
    window.scrollTo(0, 0);
    return true;
  }

  function navigate(url, options, pushState) {
    showLoader();
    fetch(url, Object.assign({ headers: { 'X-Requested-With': 'XMLHttpRequest' } }, options))
      .then(function (res) {
        return res.text().then(function (html) {
          return { html: html, url: res.url, ok: res.ok };
        });
      })
      .then(function (result) {
        if (!result.ok) {
          window.location.href = url;
          return;
        }
        var swapped = swapContent(result.html, result.url);
        if (!swapped) {
          window.location.href = result.url;
          return;
        }
        if (pushState) history.pushState({ spa: true }, '', result.url);
      })
      .catch(function () {
        window.location.href = url;
      })
      .finally(hideLoader);
  }

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest('a');
    if (!a || !(a.closest('.admin-sidebar') || a.closest('.admin-content'))) return;
    if (!isSpaLink(a)) return;
    e.preventDefault();
    navigate(a.href, { method: 'GET' }, true);
  });

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-spa') || e.defaultPrevented) return;
    if (!form.closest('.admin-content')) return;

    var action = form.getAttribute('action') || window.location.href;
    if (!sameOrigin(action)) return;
    var url = new URL(action, window.location.href);
    var method = (form.getAttribute('method') || 'GET').toUpperCase();

    e.preventDefault();
    if (method === 'GET') {
      url.search = new URLSearchParams(new FormData(form)).toString();
      navigate(url.toString(), { method: 'GET' }, true);
    } else {
      navigate(url.toString(), { method: 'POST', body: new FormData(form) }, true);
    }
  });

  window.addEventListener('popstate', function () {
    navigate(window.location.href, { method: 'GET' }, false);
  });
})();
