/**
 * FAQ Accordion strip – chỉ 1 câu hỏi mở tại một thời điểm
 */
(function() {
  function init() {
    document.querySelectorAll('.faq-accordion').forEach(function(accordion) {
      var items = accordion.querySelectorAll('.faq-item');
      items.forEach(function(item) {
        var btn = item.querySelector('.faq-btn');
        var content = item.querySelector('.faq-content');
        var chevron = item.querySelector('.faq-chevron');
        if (!btn || !content) return;
        btn.addEventListener('click', function() {
          var isOpen = content.classList.contains('is-open');
          // Đóng hết các item khác trong cùng accordion
          items.forEach(function(other) {
            if (other === item) return;
            var c = other.querySelector('.faq-content');
            var ch = other.querySelector('.faq-chevron');
            if (c) { c.classList.remove('is-open'); c.setAttribute('aria-hidden', 'true'); }
            if (ch) ch.classList.remove('is-open');
          });
          // Mở hoặc đóng item đang click
          if (!isOpen) {
            content.classList.add('is-open');
            content.setAttribute('aria-hidden', 'false');
            if (chevron) chevron.classList.add('is-open');
          } else {
            content.classList.remove('is-open');
            content.setAttribute('aria-hidden', 'true');
            if (chevron) chevron.classList.remove('is-open');
          }
        });
      });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
