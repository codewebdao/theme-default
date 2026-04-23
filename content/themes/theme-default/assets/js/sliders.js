/*  Loved-by-developers desktop slider: show 3 cards, scroll by 1 card */

let index = 0;
const LOVED_GAP_PX = 16;
const LOVED_VISIBLE = 3;

/** translate stride = cardWidth + gap; derived from viewport, avoids read after write (card.offsetWidth) */
let lovedItemStridePx = 0;

function getLovedSliderState() {
  const viewport = document.getElementById('loved-slider-viewport');
  const slider = document.getElementById('slider');
  if (!viewport || !slider) return null;
  const card = slider.querySelector('.loved-slider-card');
  if (!card) return null;
  const cards = slider.querySelectorAll('.loved-slider-card');
  const total = cards.length;
  const maxIndex = Math.max(0, total - LOVED_VISIBLE);
  return { viewport, slider, card, cards, total, maxIndex };
}

/**
 * Single layout read (viewport width), then writes only. Stride matches applied card widths + gap.
 */
function refreshLovedSliderMetrics() {
  const state = getLovedSliderState();
  if (!state) return;
  const { viewport, cards } = state;
  const w = viewport.offsetWidth;
  const cardWidth = (w - LOVED_GAP_PX * (LOVED_VISIBLE - 1)) / LOVED_VISIBLE;
  lovedItemStridePx = cardWidth + LOVED_GAP_PX;
  cards.forEach((el) => {
    el.style.width = cardWidth + 'px';
  });
}

let lovedLayoutRaf = 0;
function scheduleLovedSliderLayout() {
  if (lovedLayoutRaf) return;
  lovedLayoutRaf = requestAnimationFrame(() => {
    lovedLayoutRaf = 0;
    refreshLovedSliderMetrics();
    update();
  });
}

function update() {
  const state = getLovedSliderState();
  if (!state) return;
  const { slider, maxIndex } = state;
  index = Math.max(0, Math.min(index, maxIndex));
  if (!lovedItemStridePx) refreshLovedSliderMetrics();
  const tx = index * lovedItemStridePx;
  slider.style.transform = `translateX(-${tx}px)`;
  const dots = document.querySelectorAll('.dot');
  const dotCount = maxIndex + 1;
  dots.forEach((d, i) => {
    const ind = d.querySelector('.dot-indicator');
    if (i < dotCount) {
      d.style.display = '';
      d.className =
        'dot inline-flex items-center justify-center p-0 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2';
      if (ind) {
        ind.className = `dot-indicator block w-2 h-2 rounded-full transition-colors ${i === index ? 'bg-blue-500' : 'bg-gray-300'}`;
      }
      if (i === index) {
        d.setAttribute('aria-current', 'true');
      } else {
        d.removeAttribute('aria-current');
      }
    } else {
      d.style.display = 'none';
      d.removeAttribute('aria-current');
    }
  });
}

function next() {
  const state = getLovedSliderState();
  if (!state) return;
  const { maxIndex } = state;
  index = (index + 1) % (maxIndex + 1); // tới cuối thì quay về 0
  update();
}

function prev() {
  const state = getLovedSliderState();
  if (!state) return;
  const { maxIndex } = state;
  index = (index - 1 + (maxIndex + 1)) % (maxIndex + 1); // ở đầu thì quay về cuối
  update();
}

function goTo(i) {
  index = i;
  update();
}

// Kéo trái/phải (touch + mouse) cho loved slider
const LOVED_DRAG_THRESHOLD = 40;

function setupLovedDrag() {
  const viewport = document.getElementById('loved-slider-viewport');
  const slider = document.getElementById('slider');
  if (!viewport || !slider || !slider.querySelector('.loved-slider-card')) return;

  let startX = 0;
  let startIndex = 0;
  let startTx = 0;
  let itemWidth = 0;
  let isDragging = false;

  function getClientX(e) {
    if (e.touches && e.touches.length) return e.touches[0].clientX;
    if (e.changedTouches && e.changedTouches.length) return e.changedTouches[0].clientX;
    return e.clientX;
  }

  function onStart(e) {
    const state = getLovedSliderState();
    if (!state) return;
    isDragging = true;
    startX = getClientX(e);
    startIndex = index;
    if (!lovedItemStridePx) refreshLovedSliderMetrics();
    itemWidth = lovedItemStridePx;
    startTx = startIndex * itemWidth;
    slider.style.transition = 'none';
    viewport.style.userSelect = 'none';
    viewport.style.cursor = 'grabbing';
  }

  function onMove(e) {
    if (!isDragging) return;
    e.preventDefault();
    const state = getLovedSliderState();
    if (!state) return;
    const { maxIndex } = state;
    const dx = getClientX(e) - startX;
    // Kéo phải (dx > 0) = xem slide trước => giảm tx. Kéo trái (dx < 0) = slide sau => tăng tx.
    const tx = startTx - dx;
    const clampedTx = Math.max(0, Math.min(maxIndex * itemWidth, tx));
    slider.style.transform = `translateX(-${clampedTx}px)`;
  }

  function onEnd(e) {
    if (!isDragging) return;
    isDragging = false;
    slider.style.transition = '';
    viewport.style.userSelect = '';
    viewport.style.cursor = 'grab';
    const dx = getClientX(e) - startX;
    const state = getLovedSliderState();
    if (!state) return;
    const { maxIndex } = state;
    if (dx > LOVED_DRAG_THRESHOLD) {
      index = (startIndex - 1 + (maxIndex + 1)) % (maxIndex + 1);
    } else if (dx < -LOVED_DRAG_THRESHOLD) {
      index = (startIndex + 1) % (maxIndex + 1);
    }
    update();
  }

  viewport.addEventListener('pointerdown', onStart, { passive: true });
  viewport.addEventListener('touchstart', onStart, { passive: true });
  viewport.addEventListener('pointermove', function move(e) {
    if (isDragging) onMove(e);
  }, { passive: false });
  viewport.addEventListener('touchmove', onMove, { passive: false });
  viewport.addEventListener('pointerup', onEnd, { passive: true });
  viewport.addEventListener('pointerleave', onEnd, { passive: true });
  viewport.addEventListener('touchend', onEnd, { passive: true });
}

// Init and resize: set card widths and update transform (only when loved slider exists)
function initLovedSlider() {
  const viewport = document.getElementById('loved-slider-viewport');
  const slider = document.getElementById('slider');
  if (!viewport || !slider || !slider.querySelector('.loved-slider-card')) return;
  requestAnimationFrame(() => {
    refreshLovedSliderMetrics();
    update();
  });
  viewport.style.cursor = 'grab';
  setupLovedDrag();
  window.addEventListener('resize', scheduleLovedSliderLayout);
  if (typeof ResizeObserver !== 'undefined') {
    const ro = new ResizeObserver(() => scheduleLovedSliderLayout());
    ro.observe(viewport);
  }
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initLovedSlider);
} else {
  initLovedSlider();
}

  // Mobile slider navigation
  let mobileIndex = 0;
  function getTotalMobileCards() {
    const dots = document.querySelectorAll('.dot-mobile');
    return Math.max(1, dots.length);
  }

  function prevMobile() {
    const slider = document.getElementById('slider-mobile');
    if (slider) {
      const totalMobileCards = getTotalMobileCards();
      mobileIndex = (mobileIndex - 1 + totalMobileCards) % totalMobileCards;
      const cardWidth = slider.offsetWidth;
      slider.scrollTo({ left: mobileIndex * cardWidth, behavior: 'smooth' });
      updateMobileDots();
    }
  }

  function nextMobile() {
    const slider = document.getElementById('slider-mobile');
    if (slider) {
      const totalMobileCards = getTotalMobileCards();
      mobileIndex = (mobileIndex + 1) % totalMobileCards;
      const cardWidth = slider.offsetWidth;
      slider.scrollTo({ left: mobileIndex * cardWidth, behavior: 'smooth' });
      updateMobileDots();
    }
  }

  function goToMobile(index) {
    const slider = document.getElementById('slider-mobile');
    if (slider) {
      mobileIndex = index;
      const cardWidth = slider.offsetWidth;
      slider.scrollTo({ left: mobileIndex * cardWidth, behavior: 'smooth' });
      updateMobileDots();
    }
  }

  function updateMobileDots() {
    const dots = document.querySelectorAll('.dot-mobile');
    dots.forEach((dot, i) => {
      dot.className =
        'dot-mobile inline-flex items-center justify-center p-0 rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2';
      const ind = dot.querySelector('.dot-mobile-indicator');
      if (ind) {
        ind.className = `dot-mobile-indicator block w-2 h-2 rounded-full transition-colors ${i === mobileIndex ? 'bg-blue-500' : 'bg-gray-300'}`;
      }
      if (i === mobileIndex) {
        dot.setAttribute('aria-current', 'true');
      } else {
        dot.removeAttribute('aria-current');
      }
    });
  }

  // Update dots khi scroll
  const sliderMobile = document.getElementById('slider-mobile');
  if (sliderMobile) {
    sliderMobile.addEventListener('scroll', () => {
      const cardWidth = sliderMobile.offsetWidth;
      const newIndex = Math.round(sliderMobile.scrollLeft / cardWidth);
      if (newIndex !== mobileIndex) {
        mobileIndex = newIndex;
        updateMobileDots();
      }
    });
  }

  // FAQ accordion is now handled by Alpine.js, so this code is no longer needed
  // document.querySelectorAll(".faq-btn").forEach((btn) => {
  //   btn.addEventListener("click", () => {
  //     const content = btn.nextElementSibling;
  //     const icon = btn.querySelector(".faq-icon");
  //
  //     if (content) {
  //       content.classList.toggle("hidden");
  //     }
  //     if (icon) {
  //       icon.classList.toggle("rotate-180");
  //     }
  //   });
  // });

