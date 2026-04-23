<section class="py-12 sm:py-24 container">
  <div class=" mx-auto">
    <!-- Section Title -->
    <h2 class="sr sr--fade-up w-full text-[30px] sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-8 sm:mb-12 flex-none order-0 self-stretch flex-grow-0 font-space" style="--sr-delay: 0ms">
      <?php echo e(__('home_see_how.heading')); ?>
    </h2>

    <!-- Video Player Container -->
    <div class="sr sr--fade-up relative max-w-4xl mx-auto" style="--sr-delay: 60ms">
      <!-- Video Frame -->
      <div class="">
        <?php
        $linkIframe = trim((string) (function_exists('option') ? option('link_iframe') : ''));
        $ytEmbed = ($linkIframe !== '' && function_exists('cmsfullform_youtube_embed_url'))
            ? cmsfullform_youtube_embed_url($linkIframe)
            : '';
        $ytId = ($linkIframe !== '' && function_exists('cmsfullform_youtube_video_id'))
            ? cmsfullform_youtube_video_id($linkIframe)
            : '';
        if ($ytEmbed !== '' && $ytId !== ''):
            $posterSrc = function_exists('theme_assets') ? theme_assets('images/video1.webp') : '';
            $ytThumb = 'https://i.ytimg.com/vi/' . $ytId . '/hqdefault.jpg';
        ?>
        <div class="see-how-yt-wrap">
          <div
            id="see-how-yt-player"
            class="see-how-yt-inner"
            data-embed="<?= e($ytEmbed) ?>"
            data-title="<?= e(__('home_see_how.video_alt')) ?>">
            <img
              src="<?= e($posterSrc) ?>"
              alt="<?= e(__('home_see_how.video_alt')) ?>"
              class="see-how-yt-poster"
              width="1016"
              height="572"
              loading="eager"
              decoding="async"
              fetchpriority="high"
              data-fallback-thumb="<?= e($ytThumb) ?>"
              onerror="var t=this.getAttribute('data-fallback-thumb');if(t){this.onerror=null;this.src=t;}">
            <button
              type="button"
              class="see-how-yt-play"
              aria-label="<?= e(__('home_see_how.play')) ?>">
              <span class="see-how-yt-play-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M8 5v14l11-7L8 5z" />
                </svg>
              </span>
            </button>
          </div>
        </div>
        <script>
        (function () {
          var root = document.getElementById('see-how-yt-player');
          if (!root) return;
          var btn = root.querySelector('.see-how-yt-play');
          var base = root.getAttribute('data-embed');
          var title = root.getAttribute('data-title') || 'Video';
          var done = false;
          function play() {
            if (done || !base) return;
            done = true;
            var sep = base.indexOf('?') >= 0 ? '&' : '?';
            var src = base + sep + 'autoplay=1&mute=1&rel=0';
            var iframe = document.createElement('iframe');
            iframe.className = 'see-how-yt-iframe';
            iframe.src = src;
            iframe.title = title;
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
            iframe.setAttribute('allowfullscreen', '');
            root.innerHTML = '';
            root.appendChild(iframe);
          }
          if (btn) {
            btn.addEventListener('click', function (e) {
              e.preventDefault();
              play();
            });
          }
        })();
        </script>
        <?php
        else:
            $__v = function_exists('cmsfullform_theme_responsive_webp_img')
                ? cmsfullform_theme_responsive_webp_img('images/video1.webp', [400, 480, 720, 800, 960], [
                    'alt'               => __('home_see_how.video_alt'),
                    'class'             => 'w-full h-auto rounded-lg',
                    'sizes'             => '(max-width: 640px) min(100vw, 896px), (max-width: 896px) 100vw, 896px',
                    'mobile_webp_width' => 400,
                    'mobile_webp_bp'    => 640,
                ])
                : '';
            echo $__v !== '' ? $__v : '<img src="' . e(theme_assets('images/video1.webp')) . '" alt="' . e(__('home_see_how.video_alt')) . '" class="w-full h-auto rounded-lg" width="1016" height="572" loading="lazy" decoding="async" />';
        endif;
        ?>
      </div>
    </div>
  </div>
</section>

<!-- READY TO CODE FASTER CTA SECTION -->
<section class="relative py-12 sm:py-24 overflow-hidden">

  <div class="absolute inset-0 " style="background-image: url('<?php echo theme_assets('images/frame1.webp'); ?>'); background-size: cover; background-position: center;">
    <div class="absolute inset-0 opacity-30">
      <div class="absolute top-0 left-0 w-full h-full">
        <svg width="100%" height="100%" viewBox="0 0 1200 600" fill="none" xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="none">
          <path d="M0,300 Q300,100 600,300 T1200,300" stroke="rgba(255,255,255,0.3)" stroke-width="2" fill="none" />
          <path d="M0,200 Q400,400 800,200 T1200,200" stroke="rgba(135,206,250,0.4)" stroke-width="2" fill="none" />
          <path d="M0,400 Q200,100 500,400 T1200,400" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" />
        </svg>
      </div>
    </div>
    <div class="absolute top-20 right-20 w-64 h-64 bg-blue-400/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-cyan-300/10 rounded-full blur-3xl"></div>
  </div>
  <div class="relative mx-auto px-4 sm:px-6 text-center z-10">
    <h2 class="sr sr--fade-up text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 sm:mb-6 font-space" style="--sr-delay: 0ms">
      <?php echo e(__('home_cta_faster.heading')); ?>
    </h2>
    <p class="sr sr--fade-up text-base sm:text-lg lg:text-xl text-white/90 mb-8 sm:mb-10 max-w-2xl mx-auto font-plus" style="--sr-delay: 50ms">
      <?php echo e(__('home_cta_faster.description')); ?>
    </p>
    <div class="sr sr--fade flex flex-col items-center gap-4 sm:gap-6" style="--sr-delay: 90ms">
      <a href="<?php echo e(base_url('download')); ?>"
        class="inline-flex items-center gap-3 bg-home-primary hover:bg-home-primary-hover text-white font-semibold px-16 sm:px-8 py-4 rounded-home-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12.8572 16.0715V3.21436M12.8572 16.0715L7.50007 10.7144M12.8572 16.0715L18.2144 10.7144M22.5001 16.0715V20.3572C22.5001 20.9255 22.2743 21.4706 21.8724 21.8724C21.4706 22.2743 20.9255 22.5001 20.3572 22.5001H5.35721C4.78889 22.5001 4.24385 22.2743 3.84198 21.8724C3.44012 21.4706 3.21436 20.9255 3.21436 20.3572V16.0715"
            stroke="white" stroke-width="2.14286" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="text-lg font-plus"><?php echo e(__('home_cta_faster.button_start')); ?></span>
      </a>
      <div class="flex items-center justify-center gap-2 text-white/80 text-xs">
        <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g clip-path="url(#clip0_47_621)">
            <path
              d="M13 3H2.33333C1.59695 3 1 3.59695 1 4.33333V5.66667C1 6.40305 1.59695 7 2.33333 7H13C13.7364 7 14.3333 6.40305 14.3333 5.66667V4.33333C14.3333 3.59695 13.7364 3 13 3Z"
              stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
            <path
              d="M13 9.66667H2.33333C1.59695 9.66667 1 10.2636 1 11V12.3333C1 13.0697 1.59695 13.6667 2.33333 13.6667H13C13.7364 13.6667 14.3333 13.0697 14.3333 12.3333V11C14.3333 10.2636 13.7364 9.66667 13 9.66667Z"
              stroke="#F3F4F6" stroke-width="1.33333" stroke-linecap="round" stroke-linejoin="round" />
          </g>
          <defs>
            <clipPath id="clip0_47_621">
              <rect width="16" height="16.6667" fill="white" />
            </clipPath>
          </defs>
        </svg>
        <span class="font-plus font-xs"><?php echo e(__('home_cta_faster.system_requirements')); ?></span>
      </div>
    </div>
  </div>
</section>