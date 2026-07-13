(function () {
  var activeSubmitter = null;
  var activeSafetyTimeout = null;

  function restore() {
    if (activeSafetyTimeout) {
      clearTimeout(activeSafetyTimeout);
      activeSafetyTimeout = null;
    }
    if (!activeSubmitter) return;

    var submitter = activeSubmitter;
    activeSubmitter = null;

    submitter.disabled = false;
    if (submitter.dataset.originalText !== undefined) {
      submitter.innerHTML = submitter.dataset.originalText;
      delete submitter.dataset.originalText;
    }
    if (submitter.dataset.originalValue !== undefined) {
      submitter.value = submitter.dataset.originalValue;
      delete submitter.dataset.originalValue;
    }
  }

  // Exposed so other feedback (e.g. an error toast) can cancel the loading
  // state immediately instead of waiting on the safety-net timeout below.
  window.stopFormLoading = restore;

  // Capture phase so this always runs, even if a form's own submit handler
  // later calls stopPropagation and skips bubble-phase listeners.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-loading')) return;

    var submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
    if (!submitter) return;

    // Disabling the submitter synchronously here would strip its name=value
    // pair from the form data the browser is about to send (disabled controls
    // are excluded from submission). Defer it to the next tick, after the
    // browser has already captured what to submit.
    setTimeout(function () {
      submitter.disabled = true;
      if (submitter.tagName === 'BUTTON') {
        submitter.dataset.originalText = submitter.innerHTML;
        submitter.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Please wait...';
      } else {
        submitter.dataset.originalValue = submitter.value;
        submitter.value = 'Please wait...';
      }
      activeSubmitter = submitter;
    }, 0);

    // Safety net: if the page never navigates away or updates the DOM
    // (e.g. an AJAX handler errors silently), restore the button instead of
    // leaving it stuck disabled forever.
    activeSafetyTimeout = setTimeout(function () {
      if (!document.body.contains(form)) return;
      restore();
    }, 15000);
  }, true);
})();
