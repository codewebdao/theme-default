<section class="py-12 sm:py-24 bg-[#05234F]">
  <div class="container mx-auto">
    <h2
      class="sr sr--fade-up w-full text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] sm:text-center text-left font-medium leading-tight sm:leading-snug md:leading-[61px]  text-white mb-3 sm:mb-2 font-space" style="--sr-delay: 0ms">
      <?php echo e(__('features_laragon.tk_heading')); ?>
    </h2>
    <p
      class="sr sr--fade-up mb-8 sm:mb-12 text-white text-sm sm:text-center text-left sm:text-sm md:text-base max-w-3xl mx-auto leading-relaxed font-plus" style="--sr-delay: 60ms">
      <?php echo e(__('features_laragon.tk_intro')); ?>
    </p>

  <!-- Mobile: scroll ngang, vùng nhìn ~1.4 card (1 full + peek). Tablet/PC: grid bình thường -->
  <div class="sr sr--fade-up mx-auto sm:max-w-none" style="--sr-delay: 100ms">
      <div id="dev-toolkit-cards-slider"
        class="flex flex-row gap-6 overflow-x-auto no-scrollbar sm:overflow-visible sm:grid sm:grid-cols-2 lg:grid-cols-4">
      <!-- Card 1 -->
      <div
        class="bg-[#FFFFFF]/5 backdrop-blur-md rounded-home-lg p-6 border border-white/20 flex-shrink-0 w-64 min-w-64 sm:min-w-0 sm:w-auto">
        <svg width="55" height="55" class="mb-4" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="54.3333" height="54.3333" rx="5.5" fill="#05234F" />
          <path d="M27.1667 37.6667H39.1667M15.1667 34.6667L24.1667 25.6667L15.1667 16.6667" stroke="var(--home-success)"
            stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <h3 class="text-white font-semibold text-lg mb-4 font-plus"><?php echo e(__('features_laragon.tk_cmder_title')); ?></h3>
        <p class="text-white text-sm leading-relaxed font-normal font-plus"><?php echo e(__('features_laragon.tk_cmder_desc')); ?></p>
      </div>

      <!-- Card 2 -->
      <div
        class="bg-[#FFFFFF]/5 backdrop-blur-md rounded-home-lg p-6 border border-white/20 flex-shrink-0 w-64 min-w-64 sm:min-w-0 sm:w-auto">
        <svg width="60" height="60" class="mb-4" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="2.5" y="2.5" width="54.3333" height="54.3333" rx="5.5" fill="#05234F" />
          <path
            d="M44.6665 22.1667L31.18 30.7572C30.7223 31.0231 30.2025 31.1631 29.6733 31.1631C29.144 31.1631 28.6242 31.0231 28.1665 30.7572L14.6665 22.1667M17.6665 17.6667H41.6665C43.3234 17.6667 44.6665 19.0099 44.6665 20.6667V38.6667C44.6665 40.3236 43.3234 41.6667 41.6665 41.6667H17.6665C16.0096 41.6667 14.6665 40.3236 14.6665 38.6667V20.6667C14.6665 19.0099 16.0096 17.6667 17.6665 17.6667Z"
            stroke="var(--home-success)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <h3 class="text-white font-semibold text-lg mb-4 font-plus"><?php echo e(__('features_laragon.tk_mailpit_title')); ?></h3>
        <p class="text-white text-sm leading-relaxed font-normal font-plus"><?php echo e(__('features_laragon.tk_mailpit_desc')); ?></p>
      </div>

      <!-- Card 3 -->
      <div
        class="bg-[#FFFFFF]/5 backdrop-blur-md rounded-home-lg p-6 border border-white/20 flex-shrink-0 w-64 min-w-64 sm:min-w-0 sm:w-auto">
        <svg width="55" height="55" class="mb-4" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="54.3333" height="54.3333" rx="5.5" fill="#05234F" />
          <path
            d="M40.6665 16.6667C40.6665 19.152 34.6223 21.1667 27.1665 21.1667C19.7107 21.1667 13.6665 19.152 13.6665 16.6667M40.6665 16.6667C40.6665 14.1815 34.6223 12.1667 27.1665 12.1667C19.7107 12.1667 13.6665 14.1815 13.6665 16.6667M40.6665 16.6667V37.6667C40.6665 38.8602 39.2442 40.0048 36.7124 40.8487C34.1807 41.6926 30.7469 42.1667 27.1665 42.1667C23.5861 42.1667 20.1523 41.6926 17.6206 40.8487C15.0888 40.0048 13.6665 38.8602 13.6665 37.6667V16.6667M13.6665 27.1667C13.6665 28.3602 15.0888 29.5048 17.6206 30.3487C20.1523 31.1926 23.5861 31.6667 27.1665 31.6667C30.7469 31.6667 34.1807 31.1926 36.7124 30.3487C39.2442 29.5048 40.6665 28.3602 40.6665 27.1667"
            stroke="var(--home-success)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <h3 class="text-white font-semibold text-lg mb-4 font-plus"><?php echo e(__('features_laragon.tk_heidisql_title')); ?></h3>
        <p class="text-white text-sm leading-relaxed font-normal font-plus"><?php echo e(__('features_laragon.tk_heidisql_desc')); ?></p>
      </div>

      <!-- Card 4 -->
      <div
        class="bg-[#FFFFFF]/5 backdrop-blur-md rounded-home-lg p-6 border border-white/20 flex-shrink-0 w-64 min-w-64 sm:min-w-0 sm:w-auto">
        <svg width="60" height="60" class="mb-4" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="2.5" y="2.5" width="54.3333" height="54.3333" rx="5.5" fill="#05234F" />
          <path
            d="M14.6665 29.6668C14.6658 29.9537 14.7474 30.2348 14.9016 30.4767C15.0558 30.7187 15.2761 30.9113 15.5365 31.0318L28.4365 36.8968C28.8253 37.0729 29.2472 37.1639 29.674 37.1639C30.1008 37.1639 30.5227 37.0729 30.9115 36.8968L43.7815 31.0468C44.047 30.9275 44.2721 30.7334 44.4292 30.4884C44.5864 30.2434 44.6688 29.9579 44.6665 29.6668M14.6665 37.1668C14.6658 37.4537 14.7474 37.7348 14.9016 37.9767C15.0558 38.2187 15.2761 38.4113 15.5365 38.5318L28.4365 44.3968C28.8253 44.5729 29.2472 44.6639 29.674 44.6639C30.1008 44.6639 30.5227 44.5729 30.9115 44.3968L43.7815 38.5468C44.047 38.4275 44.2721 38.2334 44.4292 37.9884C44.5864 37.7434 44.6688 37.4579 44.6665 37.1668M30.9115 14.9368C30.5207 14.7585 30.0961 14.6663 29.6665 14.6663C29.2369 14.6663 28.8124 14.7585 28.4215 14.9368L15.5665 20.7868C15.3003 20.9042 15.074 21.0964 14.9152 21.3401C14.7563 21.5838 14.6717 21.8684 14.6717 22.1593C14.6717 22.4502 14.7563 22.7348 14.9152 22.9785C15.074 23.2222 15.3003 23.4144 15.5665 23.5318L28.4365 29.3968C28.8274 29.5751 29.2519 29.6673 29.6815 29.6673C30.1111 29.6673 30.5357 29.5751 30.9265 29.3968L43.7965 23.5468C44.0627 23.4294 44.289 23.2372 44.4479 22.9935C44.6067 22.7498 44.6913 22.4652 44.6913 22.1743C44.6913 21.8834 44.6067 21.5988 44.4479 21.3551C44.289 21.1114 44.0627 20.9192 43.7965 20.8018L30.9115 14.9368Z"
            stroke="var(--home-success)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <h3 class="text-white font-semibold text-lg mb-4 font-plus"><?php echo e(__('features_laragon.tk_redis_title')); ?></h3>
        <p class="text-white text-sm leading-relaxed font-normal font-plus"><?php echo e(__('features_laragon.tk_redis_desc')); ?></p>
      </div>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var track = document.getElementById('dev-toolkit-cards-slider');
      if (!track || !track.firstElementChild) return;
      var STEP_MS = 3000;
      var SCROLL_MS = 560;
      var mq = window.matchMedia('(min-width: 640px)');
      var motionMq = window.matchMedia('(prefers-reduced-motion: reduce)');
      var timer = null;
      var rafId = null;

      function cardStep() {
        var card = track.children[0];
        if (!card) return 0;
        var gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap) || 24;
        return card.getBoundingClientRect().width + gap;
      }

      function easeInOutCubic(t) {
        return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
      }

      function cancelAnim() {
        if (rafId !== null) {
          cancelAnimationFrame(rafId);
          rafId = null;
        }
      }

      function smoothScrollTo(targetLeft) {
        cancelAnim();
        var start = track.scrollLeft;
        var delta = targetLeft - start;
        if (Math.abs(delta) < 0.5) return;
        if (motionMq.matches) {
          track.scrollLeft = targetLeft;
          return;
        }
        var t0 = performance.now();
        function frame(now) {
          if (mq.matches) {
            rafId = null;
            return;
          }
          var p = Math.min(1, (now - t0) / SCROLL_MS);
          track.scrollLeft = start + delta * easeInOutCubic(p);
          if (p < 1) {
            rafId = requestAnimationFrame(frame);
          } else {
            rafId = null;
          }
        }
        rafId = requestAnimationFrame(frame);
      }

      function tick() {
        if (mq.matches) return;
        var step = cardStep();
        if (step <= 0) return;
        var max = track.scrollWidth - track.clientWidth;
        if (max <= 4) return;
        var next = track.scrollLeft + step;
        if (next >= max - 4) {
          cancelAnim();
          track.scrollLeft = 0;
        } else {
          smoothScrollTo(next);
        }
      }

      function start() {
        cancelAnim();
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
        if (mq.matches) return;
        timer = setInterval(tick, STEP_MS);
      }

      if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', start);
      } else {
        mq.addListener(start);
      }
      start();
    })();
  </script>
</section>
<section class="py-12 sm:py-24 bg-white">
  <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-start">

      <!-- Right Content Area (Powerful Context Menu) -->
      <div class="w-full order-1 lg:order-2 lg:w-[551px] lg:ml-[200px]">
        <!-- Category Tag -->
        <div
          class="feature-badge-emerald-aa inline-flex items-center gap-2 text-xs font-medium px-4 py-2 rounded-home-lg mb-6">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg" class="shrink-0" aria-hidden="true">
            <circle cx="8.00558" cy="8.00533" r="7" transform="rotate(-171.034 8.00558 8.00533)" fill="#EEFCFF" />
            <circle cx="8.00518" cy="8.00539" r="3" transform="rotate(-171.034 8.00518 8.00539)" fill="var(--home-success)" />
          </svg>
          <span class="font-semibold font-plus"><?php echo e(__('features_laragon.ctx_badge')); ?></span>
        </div>

        <!-- Main Heading -->
        <h2 class="text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium text-gray-900 mb-4 leading-tight font-space">
          <?php echo e(__('features_laragon.ctx_heading')); ?>
        </h2>

        <!-- Description -->
        <p class="text-gray-600 text-sm sm:text-base mb-12 leading-relaxed font-plus">
          <?php echo e(__('features_laragon.ctx_desc')); ?>
        </p>

        <!-- Feature List -->
        <div class="space-y-3">
          <div class="bg-gray-50 rounded-home-md border border-gray-200 shadow-sm p-4 flex items-start gap-3">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="flex-shrink-0 mt-0.5"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="var(--home-success)" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <span class="text-gray-800 text-sm sm:text-base font-plus"><?php echo e(__('features_laragon.ctx_ex_php')); ?></span>
          </div>
          <div class="bg-gray-50 rounded-home-md border border-gray-200 shadow-sm p-4 flex items-start gap-3">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="flex-shrink-0 mt-0.5"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="var(--home-success)" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <span class="text-gray-800 text-sm sm:text-base font-plus"><?php echo e(__('features_laragon.ctx_ex_apache')); ?></span>
          </div>
          <div class="bg-gray-50 rounded-home-md border border-gray-200 shadow-sm p-4 flex items-start gap-3">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="flex-shrink-0 mt-0.5"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="var(--home-success)" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <span class="text-gray-800 text-sm sm:text-base font-plus"><?php echo e(__('features_laragon.ctx_ex_ngrok')); ?></span>
          </div>
          <div class="bg-gray-50 rounded-home-md border border-gray-200 shadow-sm p-4 flex items-start gap-3">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="flex-shrink-0 mt-0.5"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="var(--home-success)" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <span class="text-gray-800 text-sm sm:text-base font-plus "><?php echo e(__('features_laragon.ctx_ex_laravel')); ?></span>
          </div>
        </div>
      </div>

      <!-- Left Sidebar Menu -->
      <div class="w-64 order-2 lg:order-1 lg:w-[287px] flex-shrink-0 relative mt-10">
        <div class="bg-white rounded-[12px] p-2" style="box-shadow: 0 77.333px 21.333px 0 rgba(43,140,238,0),
                   0 49.333px 20px 0 rgba(43,140,238,0.01),
                   0 28px 17.333px 0 rgba(43,140,238,0.05),
                   0 12px 12px 0 rgba(43,140,238,0.09),
                   0 2.667px 6.667px 0 rgba(43,140,238,0.10);">
          <nav class="space-y-1">
            <a href="#"
              class="block font-medium p-4 text-gray-700 rounded-home-md hover:bg-home-primary hover:text-white transition-colors font-plus"><?php echo e(__('features_laragon.menu_start_all')); ?></a>
            <a href="#"
              class="block font-medium p-4 text-gray-700 rounded-home-md hover:bg-home-primary hover:text-white transition-colors font-plus"><?php echo e(__('features_laragon.menu_stop')); ?></a>
            <a href="#"
              class="block font-medium p-4 bg-home-primary text-white rounded-home-md flex items-center justify-between">
              <span class="font-plus"><?php echo e(__('features_laragon.menu_php')); ?></span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 4L10 8L6 12" stroke="white" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </a>
            <a href="#"
              class="group block font-medium p-4 text-gray-700 rounded-home-md flex items-center justify-between hover:bg-home-primary hover:text-white transition-colors">
              <span class="font-plus"><?php echo e(__('features_laragon.menu_apache')); ?></span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                class="text-[#9CA3AF] group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </a>
            <a href="#"
              class="group block font-medium p-4 text-gray-700 rounded-home-md flex items-center justify-between hover:bg-home-primary hover:text-white transition-colors">
              <span class="font-plus"><?php echo e(__('features_laragon.menu_mysql')); ?></span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                class="text-[#9CA3AF] group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </a>
            <a href="#"
              class="group block font-medium p-4 text-gray-700 rounded-home-md flex items-center justify-between hover:bg-home-primary hover:text-white transition-colors relative">
              <span class="font-plus"><?php echo e(__('features_laragon.menu_quick_app')); ?></span>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                class="text-[#9CA3AF] group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" />
              </svg>
            </a>
          </nav>
        </div>

        <!-- Cursor Icon -->
        <div class="absolute -right-10 top-[170px]">
          <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M8.0738 9.3758C7.99487 9.19363 7.97252 8.99194 8.00968 8.79691C8.04683 8.60189 8.14177 8.42254 8.28215 8.28215C8.42254 8.14177 8.60189 8.04683 8.79691 8.00968C8.99194 7.97252 9.19363 7.99487 9.3758 8.0738L41.3758 21.0738C41.5704 21.1531 41.735 21.2916 41.8464 21.4698C41.9578 21.6479 42.0102 21.8566 41.9963 22.0663C41.9823 22.2759 41.9027 22.4758 41.7687 22.6377C41.6347 22.7995 41.4532 22.915 41.2498 22.9678L29.0018 26.1278C28.3098 26.3057 27.678 26.6657 27.1723 27.1705C26.6665 27.6752 26.3052 28.3061 26.1258 28.9978L22.9678 41.2498C22.915 41.4532 22.7995 41.6347 22.6377 41.7687C22.4758 41.9027 22.2759 41.9823 22.0663 41.9963C21.8566 42.0102 21.6479 41.9578 21.4698 41.8464C21.2916 41.735 21.1531 41.5704 21.0738 41.3758Z"
              stroke="var(--home-primary)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>

      </div>

    </div>
  </div>
</section>