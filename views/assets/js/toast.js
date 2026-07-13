(function () {
  function ensureContainer() {
    var container = document.getElementById('toastContainer');
    if (container) return container;

    container = document.createElement('div');
    container.id = 'toastContainer';
    container.style.cssText =
      'position:fixed;top:1rem;right:1rem;z-index:1080;' +
      'display:flex;flex-direction:column;align-items:flex-end;gap:0.5rem;' +
      'max-width:90vw;';
    document.body.appendChild(container);
    return container;
  }

  var typeToClass = {
    success: 'text-bg-success',
    danger: 'text-bg-danger',
    error: 'text-bg-danger',
    warning: 'text-bg-warning',
    info: 'text-bg-info',
  };

  // Self-contained: only needs document.body (always present by the time
  // this is called) and Bootstrap's CSS utility classes (loaded in <head>,
  // so available immediately) - no dependency on Bootstrap's JS bundle or
  // load order relative to other scripts.
  window.showToast = function (message, type) {
    if (!message) return;

    // An error/danger toast means whatever form action was in flight failed
    // - stop its spinner right away instead of waiting on form-loading.js's
    // 15s safety-net timeout.
    if ((type === 'danger' || type === 'error') && typeof window.stopFormLoading === 'function') {
      window.stopFormLoading();
    }

    var container = ensureContainer();
    var bgClass = typeToClass[type] || 'text-bg-secondary';

    var toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center ' + bgClass + ' border-0 show';
    // Override Bootstrap's default fixed `.toast { width: 350px }` so the
    // toast sizes to its text instead of always being a fixed wide block.
    toastEl.style.cssText =
      'opacity:0;transition:opacity .3s ease;' +
      'width:auto;max-width:min(420px, 90vw);';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML =
      '<div class="d-flex">' +
      '<div class="toast-body"></div>' +
      '<button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close"></button>' +
      '</div>';
    toastEl.querySelector('.toast-body').textContent = message;

    function remove() {
      toastEl.style.opacity = '0';
      setTimeout(function () {
        toastEl.remove();
      }, 300);
    }

    toastEl.querySelector('.btn-close').addEventListener('click', remove);
    container.appendChild(toastEl);

    requestAnimationFrame(function () {
      toastEl.style.opacity = '1';
    });

    setTimeout(remove, 4000);
  };
})();
