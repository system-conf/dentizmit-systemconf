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
  var lastFocused = null;
  var isOpen = false;

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
    if (!config.repeatDays || config.repeatDays <= 0) {
      return false;
    }
    var closedAt = readClosedAt();
    return closedAt !== null && Date.now() - closedAt < config.repeatDays * DAY_MS;
  }

  function focusables() {
    return box.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])');
  }

  function open() {
    if (isOpen) {
      return;
    }
    isOpen = true;
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
    window.setTimeout(function () {
      root.hidden = true;
    }, 350);
    if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
  }

  function onKeydown(event) {
    if (event.key === 'Escape') {
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
    window.setTimeout(open, Math.max(0, config.delaySeconds) * 1000);
  }

  /** Fare sayfanın üst kenarından dışarı çıkınca (sekme/adres çubuğuna giderken) açılır. */
  function armExitTrigger() {
    var armed = false;
    window.setTimeout(function () {
      armed = true;
    }, Math.max(0, config.delaySeconds) * 1000);

    document.addEventListener('mouseout', function (event) {
      if (armed && !event.relatedTarget && event.clientY <= 0) {
        open();
      }
    });
  }

  closeButton.addEventListener('click', close);

  if (isSuppressed()) {
    return;
  }

  if (config.trigger === 'exit') {
    armExitTrigger();
  } else {
    armLoadTrigger();
  }
})();
