(function () {
  'use strict';

  var root = document.querySelector('[data-systemconf-whatsapp]');
  if (!root || typeof window.systemconfWhatsapp === 'undefined') {
    return;
  }

  var settings = window.systemconfWhatsapp;
  var box = root.querySelector('#scwa-box');
  var toggle = root.querySelector('[data-scwa-toggle]');
  var closeButton = root.querySelector('[data-scwa-close]');
  var openButton = root.querySelector('[data-scwa-open]');

  function show() {
    root.classList.remove('scwa--hidden');
  }

  function setBoxOpen(open) {
    if (open) {
      box.removeAttribute('hidden');
    } else {
      box.setAttribute('hidden', '');
    }
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  }

  function whatsappUrl() {
    var url = 'https://wa.me/' + encodeURIComponent(settings.phone);
    if (settings.prefill) {
      url += '?text=' + encodeURIComponent(settings.prefill);
    }
    return url;
  }

  toggle.addEventListener('click', function () {
    setBoxOpen(box.hasAttribute('hidden'));
  });

  closeButton.addEventListener('click', function () {
    setBoxOpen(false);
  });

  openButton.addEventListener('click', function () {
    window.open(whatsappUrl(), '_blank', 'noopener');
  });

  var delay = Math.max(0, parseInt(settings.delaySeconds, 10) || 0) * 1000;
  window.setTimeout(show, delay);
})();
