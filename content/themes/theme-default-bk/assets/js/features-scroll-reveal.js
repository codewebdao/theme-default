/**
 * Scroll reveal cho trang Features — Intersection Observer (không phụ thuộc AOS/GSAP).
 */
(function () {
  'use strict';

  function init() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.querySelectorAll('.sr').forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    var nodes = document.querySelectorAll('.sr');
    if (!nodes.length) {
      return;
    }

    var obs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }
          entry.target.classList.add('is-visible');
          obs.unobserve(entry.target);
        });
      },
      {
        root: null,
        rootMargin: '0px 0px -6% 0px',
        threshold: 0.08,
      }
    );

    nodes.forEach(function (el) {
      obs.observe(el);
    });
  }

  function initFeaturesBannerScroll() {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.addEventListener('click', function (ev) {
      var btn = ev.target.closest && ev.target.closest('[data-features-scroll]');
      if (!btn) {
        return;
      }
      var sel = btn.getAttribute('data-features-scroll');
      if (!sel) {
        return;
      }
      var target = document.querySelector(sel);
      if (!target) {
        return;
      }
      ev.preventDefault();
      var smooth = !reduce;
      target.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
      try {
        target.focus({ preventScroll: true });
      } catch (e) {}
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      init();
      initFeaturesBannerScroll();
    });
  } else {
    init();
    initFeaturesBannerScroll();
  }
})();
