(() => {
  'use strict';

  const script = document.currentScript || document.querySelector('script[data-pwa-runtime]');
  if (!script || window.PwaRuntime) return;

  const config = {
    appId: script.dataset.appId || 'app',
    appName: script.dataset.appName || 'Aplikacja',
    version: script.dataset.appVersion || '0',
    statusUrl: script.dataset.statusUrl || 'pwa-status.php',
    workerUrl: script.dataset.workerUrl || 'service-worker.js',
    workerScope: script.dataset.workerScope || './'
  };

  const nativeFetch = window.fetch.bind(window);
  const installRequested = new URLSearchParams(window.location.search).get('install') === '1';
  const startedWithController = Boolean(navigator.serviceWorker?.controller);
  const safeMethods = new Set(['GET', 'HEAD', 'OPTIONS']);
  let connectionState = 'checking';
  let criticalUpdate = false;
  let updateAvailable = false;
  let deferredInstallPrompt = null;
  let registration = null;
  let updateRequested = false;
  let reloading = false;
  let toastAction = null;
  let toastDismissAction = null;
  let ui = null;

  const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  const isIos = () => /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
  const versionParts = (value) => String(value || '0').split(/[^0-9]+/).filter(Boolean).map(Number);
  const compareVersions = (left, right) => {
    const a = versionParts(left);
    const b = versionParts(right);
    const length = Math.max(a.length, b.length);
    for (let index = 0; index < length; index += 1) {
      const difference = (a[index] || 0) - (b[index] || 0);
      if (difference !== 0) return difference > 0 ? 1 : -1;
    }
    return 0;
  };
  const isMutation = (method) => !safeMethods.has(String(method || 'GET').toUpperCase());
  const toUrl = (input) => {
    try {
      return new URL(input instanceof Request ? input.url : String(input), window.location.href);
    } catch {
      return null;
    }
  };
  const sameOrigin = (url) => Boolean(url && url.origin === window.location.origin);
  const canWrite = () => connectionState === 'online' && !criticalUpdate;

  function emit(name, detail = {}) {
    window.dispatchEvent(new CustomEvent('pwa-runtime:' + name, { detail: { ...detail, appId: config.appId } }));
  }

  function createUi() {
    if (ui || !document.body) return ui;
    const host = document.createElement('div');
    host.className = 'pwa-runtime-ui';
    host.innerHTML =
      '<section class="pwa-runtime-network" data-pwa-network hidden role="status" aria-live="polite">' +
        '<span class="pwa-runtime-dot" aria-hidden="true"></span>' +
        '<div><strong data-pwa-network-title></strong><small data-pwa-network-body></small></div>' +
        '<button type="button" data-pwa-retry>Spróbuj ponownie</button>' +
      '</section>' +
      '<section class="pwa-runtime-toast" data-pwa-toast hidden role="status" aria-live="polite">' +
        '<span class="pwa-runtime-mark" aria-hidden="true">↗</span>' +
        '<div class="pwa-runtime-copy"><strong data-pwa-toast-title></strong><p data-pwa-toast-body></p></div>' +
        '<button class="pwa-runtime-primary" type="button" data-pwa-toast-primary></button>' +
        '<button class="pwa-runtime-close" type="button" data-pwa-toast-close aria-label="Zamknij">×</button>' +
      '</section>' +
      '<section class="pwa-runtime-blocker" data-pwa-blocker hidden role="alertdialog" aria-modal="true" aria-labelledby="pwa-runtime-blocker-title">' +
        '<div class="pwa-runtime-blocker-card">' +
          '<span class="pwa-runtime-blocker-icon" aria-hidden="true">↻</span>' +
          '<p class="pwa-runtime-eyebrow">WYMAGANA AKTUALIZACJA</p>' +
          '<h2 id="pwa-runtime-blocker-title"></h2>' +
          '<p data-pwa-blocker-body></p>' +
          '<button type="button" data-pwa-update-now>Zaktualizuj teraz</button>' +
          '<small>Twoje konto i dane pozostaną zachowane. Odświeżymy wyłącznie pliki tej aplikacji.</small>' +
        '</div>' +
      '</section>';
    document.body.appendChild(host);

    ui = {
      host,
      network: host.querySelector('[data-pwa-network]'),
      networkTitle: host.querySelector('[data-pwa-network-title]'),
      networkBody: host.querySelector('[data-pwa-network-body]'),
      retry: host.querySelector('[data-pwa-retry]'),
      toast: host.querySelector('[data-pwa-toast]'),
      toastTitle: host.querySelector('[data-pwa-toast-title]'),
      toastBody: host.querySelector('[data-pwa-toast-body]'),
      toastPrimary: host.querySelector('[data-pwa-toast-primary]'),
      toastClose: host.querySelector('[data-pwa-toast-close]'),
      blocker: host.querySelector('[data-pwa-blocker]'),
      blockerTitle: host.querySelector('#pwa-runtime-blocker-title'),
      blockerBody: host.querySelector('[data-pwa-blocker-body]'),
      updateNow: host.querySelector('[data-pwa-update-now]')
    };

    ui.retry.addEventListener('click', () => checkStatus(true));
    ui.toastPrimary.addEventListener('click', () => toastAction?.());
    ui.toastClose.addEventListener('click', dismissToast);
    ui.updateNow.addEventListener('click', activateUpdate);
    return ui;
  }

  function hideToast() {
    if (!ui) return;
    ui.toast.hidden = true;
    toastAction = null;
    toastDismissAction = null;
  }

  function dismissToast() {
    const dismissAction = toastDismissAction;
    hideToast();
    dismissAction?.();
  }

  function showToast(title, body, label, action, dismissible = true, onDismiss = null) {
    createUi();
    ui.toastTitle.textContent = title;
    ui.toastBody.textContent = body;
    ui.toastPrimary.textContent = label;
    ui.toastClose.hidden = !dismissible;
    toastAction = action;
    toastDismissAction = onDismiss;
    ui.toast.hidden = false;
  }

  function setConnection(nextState) {
    connectionState = nextState;
    document.documentElement.dataset.pwaConnection = nextState;
    createUi();
    if (nextState === 'online') {
      ui.network.hidden = true;
    } else {
      ui.networkTitle.textContent = nextState === 'checking' ? 'Sprawdzamy połączenie' : 'Brak połączenia z serwerem';
      ui.networkBody.textContent = nextState === 'checking'
        ? 'Zapis będzie dostępny po potwierdzeniu połączenia.'
        : 'Możesz przeglądać otwartą zawartość, ale zmiany są tymczasowo zablokowane.';
      ui.network.hidden = false;
    }
    emit('connection', { state: nextState });
  }

  function showCriticalUpdate(message) {
    criticalUpdate = true;
    document.documentElement.dataset.pwaUpdateRequired = 'true';
    createUi();
    ui.blockerTitle.textContent = 'Zaktualizuj ' + config.appName + ', aby kontynuować';
    ui.blockerBody.textContent = message || 'Ta wersja jest już nieaktualna. Aktualizacja jest potrzebna przed dalszym zapisywaniem zmian.';
    ui.blocker.hidden = false;
    hideToast();
    emit('update-required', { version: config.version });
  }

  function hideCriticalUpdate() {
    criticalUpdate = false;
    delete document.documentElement.dataset.pwaUpdateRequired;
    if (ui) ui.blocker.hidden = true;
  }

  function writeBlockedMessage() {
    if (criticalUpdate) {
      showCriticalUpdate();
      return 'Wymagana jest aktualizacja aplikacji.';
    }
    if (connectionState === 'checking') return 'Poczekaj chwilę — sprawdzamy połączenie z serwerem.';
    return 'Brak połączenia z serwerem. Zapis jest chwilowo zablokowany.';
  }

  window.fetch = async (input, init = {}) => {
    const request = input instanceof Request ? input : null;
    const method = String(init.method || request?.method || 'GET').toUpperCase();
    const url = toUrl(input);
    const guarded = isMutation(method) && sameOrigin(url);

    if (guarded && !canWrite()) {
      const message = writeBlockedMessage();
      emit('write-blocked', { reason: criticalUpdate ? 'update' : connectionState, url: url?.href || '' });
      throw new DOMException(message, 'NetworkError');
    }

    let requestInput = input;
    let requestInit = init;
    if (guarded) {
      const headers = new Headers(init.headers || request?.headers || {});
      headers.set('X-App-Version', config.version);
      requestInit = { ...init, headers };
      if (request) requestInput = new Request(request, requestInit);
    }

    const response = await nativeFetch(requestInput, request ? undefined : requestInit);
    if (response.status === 426) {
      let message = '';
      try {
        const payload = await response.clone().json();
        message = payload.error || payload.message || '';
      } catch {
        message = '';
      }
      showCriticalUpdate(message);
    }
    return response;
  };

  if (window.XMLHttpRequest) {
    const nativeOpen = XMLHttpRequest.prototype.open;
    const nativeSend = XMLHttpRequest.prototype.send;
    XMLHttpRequest.prototype.open = function (method, url, ...rest) {
      this.__pwaRuntimeRequest = { method: String(method || 'GET').toUpperCase(), url: toUrl(url) };
      return nativeOpen.call(this, method, url, ...rest);
    };
    XMLHttpRequest.prototype.send = function (...args) {
      const request = this.__pwaRuntimeRequest;
      if (request && isMutation(request.method) && sameOrigin(request.url)) {
        if (!canWrite()) throw new DOMException(writeBlockedMessage(), 'NetworkError');
        this.setRequestHeader('X-App-Version', config.version);
      }
      return nativeSend.apply(this, args);
    };
  }

  if (navigator.sendBeacon) {
    const nativeBeacon = navigator.sendBeacon.bind(navigator);
    navigator.sendBeacon = (url, data) => {
      const target = toUrl(url);
      if (sameOrigin(target)) {
        if (!canWrite()) return false;
        target.searchParams.set('__app_version', config.version);
        return nativeBeacon(target.href, data);
      }
      return nativeBeacon(url, data);
    };
  }

  function stampForm(form) {
    if (!(form instanceof HTMLFormElement)) return;
    const method = String(form.method || 'get').toUpperCase();
    const target = toUrl(form.action || window.location.href);
    if (!isMutation(method) || !sameOrigin(target)) return;
    let versionField = form.querySelector('input[name="__app_version"]');
    if (!versionField) {
      versionField = document.createElement('input');
      versionField.type = 'hidden';
      versionField.name = '__app_version';
      form.appendChild(versionField);
    }
    versionField.value = config.version;
  }

  function stampForms(root = document) {
    root.querySelectorAll?.('form').forEach(stampForm);
  }

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-pwa-bypass-guard')) return;
    stampForm(form);
    const method = String(event.submitter?.formMethod || form.method || 'get').toUpperCase();
    const target = toUrl(event.submitter?.formAction || form.action || window.location.href);
    if (isMutation(method) && sameOrigin(target) && !canWrite()) {
      event.preventDefault();
      writeBlockedMessage();
      emit('write-blocked', { reason: criticalUpdate ? 'update' : connectionState, url: target?.href || '' });
    }
  }, true);

  async function installApp() {
    if (isStandalone()) return;
    if (deferredInstallPrompt) {
      deferredInstallPrompt.prompt();
      const choice = await deferredInstallPrompt.userChoice;
      deferredInstallPrompt = null;
      hideToast();
      if (choice?.outcome === 'dismissed') markInstallPromptDismissed();
      return;
    }
    if (isIos()) {
      showToast(
        'Dodaj ' + config.appName + ' do ekranu głównego',
        'W Safari dotknij Udostępnij, a następnie „Do ekranu początkowego”.',
        'Rozumiem',
        hideToast
      );
      return;
    }
    showToast(
      'Zainstaluj ' + config.appName,
      'Otwórz menu przeglądarki i wybierz „Zainstaluj aplikację” lub „Dodaj do ekranu głównego”.',
      'Rozumiem',
      hideToast
    );
  }

  function dismissedInstallVersion() {
    try {
      return localStorage.getItem('pwa-install-dismissed-' + config.appId) || '';
    } catch {
      return '';
    }
  }

  function markInstallPromptDismissed() {
    try {
      localStorage.setItem('pwa-install-dismissed-' + config.appId, config.version);
    } catch {
      // Storage can be unavailable in private browser modes.
    }
  }

  function clearInstallPromptDismissed() {
    try {
      localStorage.removeItem('pwa-install-dismissed-' + config.appId);
    } catch {
      // Nothing to clear.
    }
  }

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    emit('install-available');
    if (installRequested || dismissedInstallVersion() !== config.version) {
      showToast(
        'Zainstaluj ' + config.appName + ' na tym urządzeniu',
        'Aplikacja otworzy się osobno z własną ikoną i nie nadpisze pozostałych aplikacji.',
        'Zainstaluj',
        installApp,
        true,
        markInstallPromptDismissed
      );
    }
  });

  window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    hideToast();
    clearInstallPromptDismissed();
    emit('installed');
  });

  async function activateUpdate() {
    updateRequested = true;
    createUi();
    ui.updateNow.disabled = true;
    ui.updateNow.textContent = 'Aktualizowanie…';
    try {
      if (registration) {
        await registration.update();
        if (registration.waiting) {
          registration.waiting.postMessage({ type: 'SKIP_WAITING' });
          return;
        }
      }
      const url = new URL(window.location.href);
      url.searchParams.set('_pwa_refresh', String(Date.now()));
      window.location.replace(url.href);
    } catch {
      ui.updateNow.disabled = false;
      ui.updateNow.textContent = 'Spróbuj ponownie';
    }
  }

  function showNormalUpdate() {
    if (criticalUpdate) return;
    updateAvailable = true;
    showToast(
      'Nowa wersja ' + config.appName + ' jest gotowa',
      'Zaktualizuj pliki aplikacji. Twoje konto i zapisane dane pozostaną bez zmian.',
      'Aktualizuj',
      activateUpdate
    );
    emit('update-available');
  }

  function watchRegistration(nextRegistration) {
    registration = nextRegistration;
    if (registration.waiting) showNormalUpdate();
    registration.addEventListener('updatefound', () => {
      const installing = registration.installing;
      if (!installing) return;
      installing.addEventListener('statechange', () => {
        if (installing.state === 'installed' && navigator.serviceWorker.controller) {
          if (criticalUpdate) showCriticalUpdate();
          else showNormalUpdate();
        }
      });
    });
  }

  async function registerWorker() {
    if (!('serviceWorker' in navigator)) return;
    try {
      const nextRegistration = await navigator.serviceWorker.register(config.workerUrl, {
        scope: config.workerScope,
        updateViaCache: 'none'
      });
      watchRegistration(nextRegistration);
      await nextRegistration.update();
    } catch (error) {
      emit('worker-error', { message: error?.message || String(error) });
    }
  }

  async function checkStatus(announce = false) {
    if (!navigator.onLine) {
      setConnection('offline');
      return false;
    }

    setConnection('checking');
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 6000);
    try {
      const response = await nativeFetch(config.statusUrl, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { Accept: 'application/json', 'X-App-Version': config.version },
        signal: controller.signal
      });
      if (!response.ok) throw new Error('Status HTTP ' + response.status);
      const status = await response.json();
      if (status.ok === false) throw new Error(status.message || 'Serwer nie jest gotowy.');
      setConnection('online');

      const latest = String(status.latestVersion || status.version || config.version);
      const minimum = String(status.minimumVersion || status.minVersion || '0');
      const mustUpdate = compareVersions(config.version, minimum) < 0
        || (Boolean(status.critical) && compareVersions(config.version, latest) < 0);
      updateAvailable = compareVersions(config.version, latest) < 0;

      if (mustUpdate) {
        showCriticalUpdate(status.message);
      } else {
        hideCriticalUpdate();
        if (updateAvailable || registration?.waiting) showNormalUpdate();
        else if (announce) {
          showToast('Połączenie działa', config.appName + ' ma aktualną wersję.', 'OK', hideToast);
        }
      }
      return true;
    } catch {
      setConnection('offline');
      return false;
    } finally {
      window.clearTimeout(timeout);
    }
  }

  navigator.serviceWorker?.addEventListener('controllerchange', () => {
    if (!updateRequested || reloading || (!startedWithController && !registration?.waiting)) return;
    reloading = true;
    window.location.reload();
  });

  window.addEventListener('offline', () => setConnection('offline'));
  window.addEventListener('online', () => checkStatus());
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      checkStatus();
      registration?.update().catch(() => {});
    }
  });

  window.PwaRuntime = Object.freeze({
    install: installApp,
    updateNow: activateUpdate,
    checkNow: () => checkStatus(true),
    canWrite,
    config: Object.freeze({ ...config })
  });

  const init = () => {
    createUi();
    stampForms();
    setConnection(navigator.onLine ? 'checking' : 'offline');
    registerWorker();
    checkStatus();
    window.setInterval(() => checkStatus(), 60000);
    if (installRequested && !isStandalone()) {
      window.setTimeout(() => {
        if (!deferredInstallPrompt) installApp();
      }, 1200);
    }
    document.querySelectorAll('[data-pwa-install]').forEach((button) => {
      button.hidden = isStandalone();
      button.addEventListener('click', installApp);
    });
    document.documentElement.dataset.pwaRuntime = config.appId;
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
