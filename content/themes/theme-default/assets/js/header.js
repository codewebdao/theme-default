(function () {
  'use strict';

  // Alpine: header component + phpTutorial store + cmsComparison filter (trang review-cms)
  document.addEventListener('alpine:init', function () {
    if (window.Alpine) {
      window.Alpine.data('headerComponent', function () {
        return {
          openMobileMenu: function () {
            if (window.jModal) window.jModal.open('mobileMenuModal');
          }
        };
      });
      window.Alpine.data('cmsComparisonFilter', function () {
        return { activeFilter: 'all' };
      });
      var path = (window.location.pathname || '');
      if (path.indexOf('/tutorial') !== -1 || path.indexOf('php-tutorial') !== -1) {
        var m = (window.location.search || '').match(/[?&]topic=([^&]+)/);
        var topic = m ? decodeURIComponent(m[1]) : (window.__PHP_TUTORIAL_DEFAULT_TOPIC__ || 'syntax');
        window.Alpine.store('phpTutorial', { activeTopic: topic });
      }
    }
  });

  // Dropdown (language, search) + mobile menu
  function Dropdown(toggleId, menuId, arrowId) {
    var t = document.getElementById(toggleId);
    var m = document.getElementById(menuId);
    var a = arrowId ? document.getElementById(arrowId) : null;
    if (!t || !m) return;
    t.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (m.classList.contains('invisible')) {
        m.classList.remove('opacity-0', 'invisible', 'scale-95');
        m.classList.add('opacity-100', 'visible', 'scale-100');
        if (a) a.style.transform = 'rotate(180deg)';
      } else {
        m.classList.remove('opacity-100', 'visible', 'scale-100');
        m.classList.add('opacity-0', 'invisible', 'scale-95');
        if (a) a.style.transform = 'rotate(0deg)';
      }
    });
    document.addEventListener('click', function (e) {
      if (!t.contains(e.target) && !m.contains(e.target)) {
        m.classList.remove('opacity-100', 'visible', 'scale-100');
        m.classList.add('opacity-0', 'invisible', 'scale-95');
        if (a) a.style.transform = 'rotate(0deg)';
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        m.classList.remove('opacity-100', 'visible', 'scale-100');
        m.classList.add('opacity-0', 'invisible', 'scale-95');
      }
    });
  }

  function mobileMenu() {
    var btn = document.getElementById('mobileMenuToggle');
    var menu = document.getElementById('mobileMenu');
    var overlay = document.getElementById('mobileMenuOverlay');
    var close = document.getElementById('closeMobileMenu');
    if (!btn || !menu) return;

    function open() {
      document.body.classList.add('mobile-menu-open');
    }

    function shut() {
      document.body.classList.remove('mobile-menu-open');
    }

    btn.addEventListener('click', open);
    if (close) close.addEventListener('click', shut);
    if (overlay) overlay.addEventListener('click', shut);
  }

  document.addEventListener('DOMContentLoaded', function () {
    Dropdown('languageDropdownToggle', 'languageDropdownMenu', 'languageDropdownArrow');
    Dropdown('searchDropdownToggle', 'searchDropdownMenu');
    mobileMenu();
  });
})();
