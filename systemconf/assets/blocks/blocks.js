(function () {
  'use strict';

  document.querySelectorAll('[data-scb-search]').forEach(function (root) {
    var openButton = root.querySelector('[data-scb-search-open]');
    var overlay = root.querySelector('[data-scb-search-overlay]');
    var closeButton = root.querySelector('[data-scb-search-close]');
    var input = overlay ? overlay.querySelector('input') : null;

    if (!openButton || !overlay) { return; }

    function open() {
      overlay.removeAttribute('hidden');
      document.body.classList.add('scb-overlay-open');
      openButton.setAttribute('aria-expanded', 'true');
      if (input) { window.setTimeout(function () { input.focus(); }, 50); }
    }

    function close() {
      overlay.setAttribute('hidden', '');
      document.body.classList.remove('scb-overlay-open');
      openButton.setAttribute('aria-expanded', 'false');
    }

    openButton.addEventListener('click', open);
    if (closeButton) { closeButton.addEventListener('click', close); }
    overlay.addEventListener('click', function (event) {
      if (event.target === overlay) { close(); }
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !overlay.hasAttribute('hidden')) { close(); }
    });
  });
})();
