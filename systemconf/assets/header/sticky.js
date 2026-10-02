(function () {
  'use strict';

  if (typeof window.systemconfHeader === 'undefined') {
    return;
  }

  var targets = [];

  window.systemconfHeader.selectors.forEach(function (selector) {
    var el = document.querySelector(selector);
    if (!el) { return; }

    var spacer = document.createElement('div');
    spacer.className = 'sch-spacer';
    spacer.style.height = '0px';
    el.parentNode.insertBefore(spacer, el);

    targets.push({ el: el, spacer: spacer, top: 0, fixed: false });
  });

  if (!targets.length) { return; }

  function measure() {
    targets.forEach(function (t) {
      if (t.fixed) { return; }
      // Konum, önündeki boşluk tutucunun yerinden ölçülür; böylece başka bir
      // betik bölümü fixed yapmış olsa bile doğru eşik bulunur.
      var rect = t.spacer.getBoundingClientRect();
      t.top = rect.top + window.pageYOffset;
    });
  }

  function update() {
    var y = window.pageYOffset;
    targets.forEach(function (t) {
      if (!t.fixed && window.getComputedStyle(t.el).display === 'none') { return; } // gizli (mobil/masaüstü) bölüm
      var shouldFix = y > t.top + 1;
      if (shouldFix && !t.fixed) {
        t.spacer.style.height = t.el.offsetHeight + 'px';
        t.el.classList.add('sch-fixed');
        t.fixed = true;
      } else if (!shouldFix && t.fixed) {
        t.el.classList.remove('sch-fixed');
        t.spacer.style.height = '0px';
        t.fixed = false;
      }
    });
  }

  var ticking = false;
  function onScroll() {
    if (ticking) { return; }
    ticking = true;
    window.requestAnimationFrame(function () { update(); ticking = false; });
  }

  measure();
  update();
  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', function () { measure(); update(); });
  window.addEventListener('load', function () { measure(); update(); });
})();
