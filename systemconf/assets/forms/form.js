(function () {
  'use strict';

  if (typeof window.systemconfForms === 'undefined') {
    return;
  }

  var settings = window.systemconfForms;

  function newToken() {
    if (window.crypto && window.crypto.randomUUID) {
      return window.crypto.randomUUID();
    }
    return 'tok-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 12);
  }

  function clearErrors(form) {
    form.querySelectorAll('.scf__field.is-invalid').forEach(function (field) {
      field.classList.remove('is-invalid');
      var err = field.querySelector('[data-error]');
      if (err) { err.textContent = ''; }
    });
  }

  function showErrors(form, errors) {
    Object.keys(errors || {}).forEach(function (id) {
      var field = form.querySelector('.scf__field[data-field="' + id + '"]');
      if (!field) { return; }
      field.classList.add('is-invalid');
      var err = field.querySelector('[data-error]');
      if (err) { err.textContent = errors[id]; }
    });
    var first = form.querySelector('.scf__field.is-invalid');
    if (first) { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
  }

  function showMessage(form, text, ok) {
    var box = form.querySelector('.scf__message');
    if (!box) { return; }
    box.textContent = text;
    box.className = 'scf__message ' + (ok ? 'is-success' : 'is-error');
  }

  function bind(form) {
    var formId = form.getAttribute('data-systemconf-form');
    var tokenInput = form.querySelector('input[name="sc_token"]');
    var button = form.querySelector('.scf__submit');
    var buttonLabel = button ? button.textContent : '';

    tokenInput.value = newToken();

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors(form);
      showMessage(form, '', true);
      form.querySelector('.scf__message').className = 'scf__message';

      if (button) { button.disabled = true; button.textContent = settings.sending; }

      var request = new XMLHttpRequest();
      request.open('POST', settings.endpoint + formId, true);
      request.setRequestHeader('Accept', 'application/json');

      request.onload = function () {
        var data = null;
        try { data = JSON.parse(request.responseText); } catch (e) { data = null; }

        if (button) { button.disabled = false; button.textContent = buttonLabel; }

        if (request.status === 200 && data && data.ok) {
          showMessage(form, data.message, true);
          form.reset();
          tokenInput.value = newToken();
          return;
        }

        if (data && data.errors) {
          showErrors(form, data.errors);
        }
        showMessage(form, (data && data.message) ? data.message : settings.errorMessage, false);
      };

      request.onerror = function () {
        if (button) { button.disabled = false; button.textContent = buttonLabel; }
        showMessage(form, settings.errorMessage, false);
      };

      request.send(new FormData(form));
    });
  }

  document.querySelectorAll('form[data-systemconf-form]').forEach(bind);
})();
