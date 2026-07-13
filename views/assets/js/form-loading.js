(function () {
  function ensureOverlay() {
    var overlay = document.getElementById('formLoadingOverlay');
    if (overlay) return overlay;

    overlay = document.createElement('div');
    overlay.id = 'formLoadingOverlay';
    overlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;' +
      'background:rgba(0,0,0,0.6);z-index:9999;justify-content:center;align-items:center;';
    overlay.innerHTML =
      '<div style="text-align:center;color:#fff;">' +
      '<div class="spinner-border text-light" style="width:3rem;height:3rem;" role="status"></div>' +
      '<p style="margin-top:10px;">Please wait...</p>' +
      '</div>';
    document.body.appendChild(overlay);
    return overlay;
  }

  // Capture phase so this always runs, even if a form's own submit handler
  // later calls stopPropagation and skips bubble-phase listeners.
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-loading')) return;

    var overlay = ensureOverlay();
    overlay.style.display = 'flex';

    var submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
    if (submitter) {
      submitter.disabled = true;
      if (submitter.tagName === 'BUTTON') {
        submitter.dataset.originalText = submitter.innerHTML;
        submitter.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Please wait...';
      }
    }

    // Safety net: if the page never navigates away or updates the DOM
    // (e.g. an AJAX handler errors silently), restore the form instead of
    // leaving it stuck disabled forever.
    setTimeout(function () {
      if (!document.body.contains(form)) return;
      overlay.style.display = 'none';
      if (submitter) {
        submitter.disabled = false;
        if (submitter.dataset.originalText !== undefined) {
          submitter.innerHTML = submitter.dataset.originalText;
        }
      }
    }, 15000);
  }, true);
})();
