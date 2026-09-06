(function () {
  'use strict';

  function beacon(slot, action) {
    var trackUrl = slot.getAttribute('data-ad-track-url');
    var adId = slot.getAttribute('data-ad-id');
    var csrf = slot.getAttribute('data-ad-csrf');
    if (!trackUrl || !adId) return;

    var body = 'ad_id=' + encodeURIComponent(adId) + '&_csrf=' + encodeURIComponent(csrf || '');
    var blob = new Blob([body], { type: 'application/x-www-form-urlencoded' });
    if (navigator.sendBeacon) {
      navigator.sendBeacon(trackUrl + action, blob);
    } else {
      fetch(trackUrl + action, { method: 'POST', body: body, headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, keepalive: true });
    }
  }

  document.querySelectorAll('.ad-slot[data-ad-id]').forEach(function (slot) {
    if ('IntersectionObserver' in window) {
      var seen = false;
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting && !seen) {
            seen = true;
            beacon(slot, 'impression');
            observer.disconnect();
          }
        });
      }, { threshold: 0.5 });
      observer.observe(slot);
    } else {
      beacon(slot, 'impression');
    }

    var link = slot.querySelector('[data-ad-click]');
    if (link) {
      link.addEventListener('click', function () { beacon(slot, 'click'); });
    }
  });
})();
