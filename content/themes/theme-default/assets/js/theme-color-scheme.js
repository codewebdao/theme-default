/**
 * Light / dark: class `dark` on <html>, localStorage key theme-color-pref = light | dark.
 * First paint: inline script in header runs before CSS; this file wires checkboxes + changes.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'theme-color-pref';

  function readStored() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      return null;
    }
  }

  function prefersDark() {
    try {
      return window.matchMedia('(prefers-color-scheme: dark)').matches;
    } catch (e) {
      return false;
    }
  }

  function isDarkMode() {
    var v = readStored();
    if (v === 'dark') {
      return true;
    }
    if (v === 'light') {
      return false;
    }
    return prefersDark();
  }

  function apply(dark) {
    document.documentElement.classList.toggle('dark', !!dark);
    document.querySelectorAll('.theme-color-toggle').forEach(function (el) {
      el.checked = !!dark;
      el.setAttribute('aria-checked', dark ? 'true' : 'false');
    });
  }

  function persist(dark) {
    try {
      localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    } catch (e) {}
  }

  function init() {
    apply(isDarkMode());
    document.querySelectorAll('.theme-color-toggle').forEach(function (el) {
      if (el._themeBound) {
        return;
      }
      el._themeBound = true;
      el.addEventListener('change', function () {
        var dark = !!this.checked;
        persist(dark);
        apply(dark);
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
