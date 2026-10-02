(function () {
  'use strict';

  var button = document.querySelector('[data-systemconf-scrolltop]');
  if (!button || typeof window.systemconfScrollTop === 'undefined') {
    return;
  }

  var settings = window.systemconfScrollTop;
  var threshold = Math.max(0, parseInt(settings.showAfter, 10) || 0);
  var ticking = false;

  function currentScroll() {
    return window.pageYOffset || document.documentElement.scrollTop || 0;
  }

  function update() {
    ticking = false;
    if (currentScroll() > threshold) {
      button.classList.remove('scst--hidden');
    } else {
      button.classList.add('scst--hidden');
    }
  }

  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  function prefersReducedMotion() {
    return typeof window.matchMedia === 'function' &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  button.addEventListener('click', function () {
    var smooth = parseInt(settings.smooth, 10) === 1 && !prefersReducedMotion();

    if (smooth && 'scrollBehavior' in document.documentElement.style) {
      window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
    } else {
      window.scrollTo(0, 0);
    }
  });

  window.addEventListener('scroll', onScroll, { passive: true });
  update();
})();
