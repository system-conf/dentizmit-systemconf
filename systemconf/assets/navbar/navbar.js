/* Yeni üst menü: telefonda sağdan açılan panel ve yapışkan durumda küçülen logo.
   ES5; bağımlılık yok. */
(function () {
  'use strict';

  var root = document.getElementById('scnb');

  if (!root || root.getAttribute('data-ready') === '1') {
    return;
  }
  root.setAttribute('data-ready', '1');

  var nav = root.querySelector('.scnb__nav');
  var toggle = root.querySelector('.scnb__toggle');
  var closeButton = root.querySelector('.scnb__close');
  var backdrop = root.querySelector('.scnb__backdrop');
  var DESKTOP_MIN = 1025;
  var isOpen = false;
  var lastFocused = null;

  /* ---------- Panel ---------- */

  function openPanel() {
    if (isOpen || !nav) {
      return;
    }
    isOpen = true;
    lastFocused = document.activeElement;
    root.classList.add('scnb--open');
    document.documentElement.classList.add('scnb-lock');
    if (backdrop) {
      backdrop.hidden = false;
    }
    toggle.setAttribute('aria-expanded', 'true');
    document.addEventListener('keydown', onKeydown);
    if (closeButton) {
      closeButton.focus();
    }
  }

  function closePanel() {
    if (!isOpen) {
      return;
    }
    isOpen = false;
    root.classList.remove('scnb--open');
    document.documentElement.classList.remove('scnb-lock');
    if (backdrop) {
      backdrop.hidden = true;
    }
    toggle.setAttribute('aria-expanded', 'false');
    document.removeEventListener('keydown', onKeydown);
    if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
  }

  function onKeydown(event) {
    if (event.key === 'Escape' || event.key === 'Esc') {
      closePanel();
    }
  }

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      if (isOpen) {
        closePanel();
      } else {
        openPanel();
      }
    });
  }

  if (closeButton) {
    closeButton.addEventListener('click', closePanel);
  }

  if (backdrop) {
    backdrop.addEventListener('click', closePanel);
  }

  // Paneldeki bir bağlantıya basılınca (ör. aynı sayfadaki #bölüm) panel kapanır.
  if (nav) {
    nav.addEventListener('click', function (event) {
      var target = event.target;
      while (target && target !== nav) {
        if (target.tagName === 'A') {
          closePanel();
          return;
        }
        target = target.parentNode;
      }
    });
  }

  /* ---------- Yapışkan durum ---------- */

  function cssPx(name) {
    var value = parseFloat(window.getComputedStyle(root).getPropertyValue(name));
    return isNaN(value) ? 0 : value;
  }

  // Üst satır ekrandan çıkıp koyu satır üstte kaldığında logo küçülür.
  function updateStuck() {
    if (!root.classList.contains('scnb--sticky')) {
      return;
    }
    var topRow = cssPx('--scnb-top-h');
    var offset = cssPx('--scnb-offset');
    var stuck = topRow > 0 && root.getBoundingClientRect().top <= offset - topRow + 0.5;
    if (stuck !== root.classList.contains('scnb--stuck')) {
      root.classList.toggle('scnb--stuck', stuck);
    }
  }

  var ticking = false;
  function onScroll() {
    if (ticking) {
      return;
    }
    ticking = true;
    window.requestAnimationFrame(function () {
      updateStuck();
      ticking = false;
    });
  }

  function onResize() {
    if (isOpen && window.innerWidth >= DESKTOP_MIN) {
      closePanel();
    }
    updateStuck();
  }

  updateStuck();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onResize);
})();
