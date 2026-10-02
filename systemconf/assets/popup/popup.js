(function () {
  'use strict';

  var config = window.systemconfPopup;
  var root = document.getElementById('scpu');

  if (!config || !root || root.getAttribute('data-ready') === '1') {
    return;
  }
  root.setAttribute('data-ready', '1');

  var box = root.querySelector('.scpu__box');
  var closeButton = root.querySelector('[data-scpu-close]');
  var DAY_MS = 24 * 60 * 60 * 1000;
  var repeatDays = parseFloat(config.repeatDays) || 0;
  var delaySeconds = parseFloat(config.delaySeconds) || 0;
  var lastFocused = null;
  var isOpen = false;
  var hideTimer = null;

  /** Depolama kapalıysa (gizli pencere vb.) null döner; popup yine de çalışır. */
  function readClosedAt() {
    try {
      var value = window.localStorage.getItem(config.storageKey);
      var parsed = value === null ? NaN : parseInt(value, 10);
      return isNaN(parsed) ? null : parsed;
    } catch (error) {
      return null;
    }
  }

  function writeClosedAt() {
    try {
      window.localStorage.setItem(config.storageKey, String(Date.now()));
    } catch (error) {
      // Depolama yazılamıyorsa popup bu ziyarette kapalı kalır, sonrakinde yine çıkar.
      return;
    }
  }

  function isSuppressed() {
    if (repeatDays <= 0) {
      return false;
    }
    var closedAt = readClosedAt();
    return closedAt !== null && Date.now() - closedAt < repeatDays * DAY_MS;
  }

  function focusables() {
    return box.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])');
  }

  function open() {
    if (isOpen) {
      return;
    }
    isOpen = true;
    if (hideTimer !== null) {
      window.clearTimeout(hideTimer);
      hideTimer = null;
    }
    lastFocused = document.activeElement;
    root.hidden = false;
    document.documentElement.classList.add('scpu-lock');
    // Gizli durumdan çıkışın geçiş animasyonla oynaması için bir kare bekle.
    window.requestAnimationFrame(function () {
      root.classList.remove('scpu--hidden');
      closeButton.focus();
    });
    document.addEventListener('keydown', onKeydown);
  }

  function close() {
    if (!isOpen) {
      return;
    }
    isOpen = false;
    root.classList.add('scpu--hidden');
    document.documentElement.classList.remove('scpu-lock');
    document.removeEventListener('keydown', onKeydown);
    writeClosedAt();
    hideTimer = window.setTimeout(function () {
      root.hidden = true;
      hideTimer = null;
    }, 200);
    if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
  }

  function onKeydown(event) {
    if (event.key === 'Escape' || event.key === 'Esc') {
      close();
      return;
    }
    if (event.key !== 'Tab') {
      return;
    }
    var items = focusables();
    if (items.length === 0) {
      event.preventDefault();
      return;
    }
    var first = items[0];
    var last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  function armLoadTrigger() {
    window.setTimeout(open, Math.max(0, delaySeconds) * 1000);
  }

  /** Fare sayfanın üst kenarından dışarı çıkınca (sekme/adres çubuğuna giderken) açılır. */
  function armExitTrigger() {
    var armed = false;
    window.setTimeout(function () {
      armed = true;
    }, Math.max(0, delaySeconds) * 1000);

    function onMouseOut(event) {
      if (armed && !event.relatedTarget && event.clientY <= 0) {
        document.removeEventListener('mouseout', onMouseOut);
        open();
      }
    }

    document.addEventListener('mouseout', onMouseOut);
  }

  closeButton.addEventListener('click', close);
  root.addEventListener('click', function (e) {
    if (e.target === root) {
      close();
    }
  });

  if (isSuppressed()) {
    return;
  }

  if (config.trigger === 'exit') {
    armExitTrigger();
  } else {
    armLoadTrigger();
  }
})();
