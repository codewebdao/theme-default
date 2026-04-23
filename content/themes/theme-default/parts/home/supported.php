<?php
/**
 * Supported & Popular Stacks – data cho tab Top Used
 * trend: 'up' | 'down' | 'neutral'
 * featured: true = hiển thị icon sao (vd. RocketCMS)
 */
$supported_tabs = [
  'high-speed'      => 'home_stacks.tab_high_speed',
  'top-used'        => 'home_stacks.tab_top_used',
  'top-frameworks'  => 'home_stacks.tab_top_frameworks',
];

// bg-*-700 / amber-800: đủ tương phản chữ trắng (WCAG AA) kể cả text-[8px] trên mobile
$top_used_stacks = [
  ['name' => 'Laravel',   'initials' => 'La', 'color' => 'bg-blue-700',    'type' => 'cms',   'trend' => 'up',   'score' => 990, 'featured' => false],
  ['name' => 'Astro',    'initials' => 'As', 'color' => 'bg-purple-700',   'type' => 'ssg',   'trend' => 'up',   'score' => 985, 'featured' => false],
  ['name' => 'Gatsby',   'initials' => 'Ga', 'color' => 'bg-pink-700',     'type' => 'ssg',   'trend' => 'down', 'score' => 940, 'featured' => false],
  ['name' => 'Jekyll',   'initials' => 'Je', 'color' => 'bg-red-700',      'type' => 'ssg',   'trend' => 'neutral', 'score' => 928, 'featured' => false],
  ['name' => 'Eleventy', 'initials' => 'El', 'color' => 'bg-amber-800',    'type' => 'ssg',   'trend' => 'up',   'score' => 915, 'featured' => false],
  ['name' => 'Next.js',  'initials' => 'Ne', 'color' => 'bg-gray-900',     'type' => 'ssr',   'trend' => 'up',   'score' => 910, 'featured' => false],
  ['name' => 'Nuxt',     'initials' => 'Nu', 'color' => 'bg-green-700',    'type' => 'ssr',   'trend' => 'neutral', 'score' => 890, 'featured' => false],
  ['name' => 'SvelteKit','initials' => 'Sv', 'color' => 'bg-orange-700',  'type' => 'ssr',   'trend' => 'up',   'score' => 885, 'featured' => false],
  ['name' => 'Remix',    'initials' => 'Re', 'color' => 'bg-indigo-700',  'type' => 'ssr',   'trend' => 'up',   'score' => 878, 'featured' => false],
  ['name' => 'RocketCMS','initials' => 'Ro', 'color' => 'bg-blue-700',     'type' => 'hybrid','trend' => 'up',   'score' => 865, 'featured' => true],
];

/** Bảng “Tốc độ cao”: ưu tiên SSG / SSR nhẹ, chỉ số minh họa */
$high_speed_stacks = [
  ['name' => 'Hugo',       'initials' => 'Hu', 'color' => 'bg-indigo-700', 'type' => 'ssg',    'trend' => 'up',      'score' => 998, 'featured' => false],
  ['name' => 'Astro',      'initials' => 'As', 'color' => 'bg-purple-700', 'type' => 'ssg',    'trend' => 'up',      'score' => 996, 'featured' => false],
  ['name' => 'Eleventy',   'initials' => 'El', 'color' => 'bg-amber-800',  'type' => 'ssg',    'trend' => 'up',      'score' => 994, 'featured' => false],
  ['name' => 'SvelteKit',  'initials' => 'Sv', 'color' => 'bg-orange-700', 'type' => 'ssr',    'trend' => 'up',      'score' => 991, 'featured' => false],
  ['name' => 'Next.js',    'initials' => 'Ne', 'color' => 'bg-gray-900',   'type' => 'ssr',    'trend' => 'up',      'score' => 989, 'featured' => false],
  ['name' => 'Remix',      'initials' => 'Re', 'color' => 'bg-indigo-700', 'type' => 'ssr',    'trend' => 'up',      'score' => 986, 'featured' => false],
  ['name' => 'Nuxt',       'initials' => 'Nu', 'color' => 'bg-green-700',  'type' => 'ssr',    'trend' => 'neutral', 'score' => 983, 'featured' => false],
  ['name' => 'Gatsby',     'initials' => 'Ga', 'color' => 'bg-pink-700',   'type' => 'ssg',    'trend' => 'down',    'score' => 972, 'featured' => false],
  ['name' => 'Jekyll',     'initials' => 'Je', 'color' => 'bg-red-700',    'type' => 'ssg',    'trend' => 'neutral', 'score' => 965, 'featured' => false],
  ['name' => 'Laravel',    'initials' => 'La', 'color' => 'bg-blue-700',   'type' => 'cms',    'trend' => 'up',      'score' => 958, 'featured' => false],
];

/** Bảng “Framework hàng đầu”: hệ sinh thái JS + backend phổ biến */
$top_frameworks_stacks = [
  ['name' => 'React',        'initials' => 'Re', 'color' => 'bg-blue-700',   'type' => 'ssr',    'trend' => 'up',      'score' => 992, 'featured' => false],
  ['name' => 'Vue',          'initials' => 'Vu', 'color' => 'bg-green-700',  'type' => 'ssr',    'trend' => 'up',      'score' => 988, 'featured' => false],
  ['name' => 'Angular',      'initials' => 'An', 'color' => 'bg-red-700',    'type' => 'ssr',    'trend' => 'neutral', 'score' => 978, 'featured' => false],
  ['name' => 'Next.js',      'initials' => 'Ne', 'color' => 'bg-gray-900',   'type' => 'ssr',    'trend' => 'up',      'score' => 976, 'featured' => false],
  ['name' => 'Nuxt',         'initials' => 'Nu', 'color' => 'bg-indigo-700', 'type' => 'ssr',    'trend' => 'up',      'score' => 969, 'featured' => false],
  ['name' => 'Svelte',       'initials' => 'Sv', 'color' => 'bg-orange-700', 'type' => 'ssr',    'trend' => 'up',      'score' => 962, 'featured' => false],
  ['name' => 'Laravel',      'initials' => 'La', 'color' => 'bg-blue-700',   'type' => 'cms',    'trend' => 'up',      'score' => 955, 'featured' => false],
  ['name' => 'Django',       'initials' => 'Dj', 'color' => 'bg-amber-800',  'type' => 'cms',    'trend' => 'up',      'score' => 948, 'featured' => false],
  ['name' => 'Ruby on Rails','initials' => 'Ra', 'color' => 'bg-pink-700',   'type' => 'cms',    'trend' => 'neutral', 'score' => 938, 'featured' => false],
  ['name' => 'ASP.NET Core', 'initials' => 'AS', 'color' => 'bg-indigo-700', 'type' => 'hybrid', 'trend' => 'up',      'score' => 931, 'featured' => false],
];

$grid_class = 'grid grid-cols-[30px_1.2fr_0.7fr_1fr_60px] sm:grid-cols-[40px_2fr_1fr_1.5fr_90px] lg:grid-cols-[50px_2fr_1fr_1.5fr_100px] gap-1.5 sm:gap-3 lg:gap-4 items-center';
$row_class = 'grid grid-cols-[30px_1.2fr_0.7fr_1fr_60px] sm:grid-cols-[40px_2fr_1fr_1.5fr_90px] lg:grid-cols-[50px_2fr_1fr_1.5fr_100px] gap-1.5 sm:gap-3 lg:gap-4 items-center px-2 sm:px-4 lg:px-6 py-2 sm:py-3 lg:py-4 hover:bg-gray-50 transition-colors';

if (!function_exists('supported_stacks_render_table_rows')) {
    /**
     * @param list<array{name:string,initials:string,color:string,type:string,trend:string,score:int,featured?:bool}> $stacks
     */
    function supported_stacks_render_table_rows(array $stacks, string $row_class): void
    {
        foreach ($stacks as $index => $row) {
            $rank = $index + 1;
            $trend = $row['trend'];
            $stroke = $trend === 'up' ? '#10B981' : ($trend === 'down' ? '#EF4444' : '#9CA3AF');
            $linePath = $trend === 'up' ? 'M5 15L15 5L25 10L35 3L45 8L55 2' : ($trend === 'down' ? 'M5 5L15 15L25 10L35 17L45 12L55 18' : 'M5 10L15 10L25 10L35 10L45 10L55 10');
            $iconPath = $trend === 'up' ? 'M6 2L9 6H3L6 2Z' : ($trend === 'down' ? 'M6 10L9 6H3L6 10Z' : '');
            $iconTag = $trend === 'neutral' ? '<line x1="2" y1="6" x2="10" y2="6" stroke="' . $stroke . '" stroke-width="2" stroke-linecap="round" />' : '<path d="' . $iconPath . '" fill="' . $stroke . '" />';
            ?>
            <div class="<?php echo e($row_class); ?>">
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-900 font-plus"><?php echo (int) $rank; ?></div>
              <div class="flex items-center gap-1 sm:gap-2 lg:gap-3">
                <div class="w-5 h-5 sm:w-7 sm:h-7 lg:w-8 lg:h-8 <?php echo e($row['color']); ?> rounded flex items-center justify-center text-white text-[8px] sm:text-sm lg:text-xs font-bold flex-shrink-0">
                  <?php echo e($row['initials']); ?>
                </div>
                <span class="text-sm sm:text-xs lg:text-sm font-medium text-gray-900 <?php echo $row['featured'] ? 'flex items-center gap-1 sm:gap-2' : 'truncate'; ?> font-plus">
                  <span class="truncate"><?php echo e($row['name']); ?></span>
                  <?php if (!empty($row['featured'])): ?>
                  <svg width="8" height="8" class="sm:w-[12px] sm:h-[12px] lg:w-[14px] lg:h-[14px] flex-shrink-0" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 0L8.5 5H13L9 8L10.5 13L7 10L3.5 13L5 8L1 5H5.5L7 0Z" fill="#FCD34D" />
                  </svg>
                  <?php endif; ?>
                </span>
              </div>
              <div class="text-sm sm:text-xs lg:text-sm text-gray-600 font-plus"><?php echo e(__('home_stacks.type_' . $row['type'])); ?></div>
              <div class="flex items-center gap-1 sm:gap-2">
                <svg width="35" height="12" class="sm:w-[50px] sm:h-[16px] lg:w-[60px] lg:h-[20px] <?php echo $rank === 1 ? 'font-bold' : ''; ?>" viewBox="0 0 60 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="<?php echo e($linePath); ?>" stroke="<?php echo e($stroke); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
                <svg width="7" height="7" class="sm:w-[10px] sm:h-[10px] lg:w-[12px] lg:h-[12px]" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <?php echo $iconTag; ?>
                </svg>
              </div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-900 text-right font-plus"><?php echo (int) $row['score']; ?></div>
            </div>
            <?php
        }
    }
}
?>
<!-- SUPPORTED & POPULAR STACKS SECTION (tabs: data-tab / data-tab-content) -->
<section class="supported-stacks-section bg-white py-12 sm:py-24 container">
  <div class=" mx-auto">
    <!-- Section Header -->
    <h2
      class="sr sr--fade-up w-full text-[30px] font-space sm:text-3xl md:text-4xl lg:text-[48px] font-medium leading-tight sm:leading-snug md:leading-[61px] text-center text-home-heading mb-3 sm:mb-2 flex-none order-0 self-stretch flex-grow-0 " style="--sr-delay: 0ms"><?php echo e(__('home_stacks.heading')); ?></h2>
    <p class="sr sr--fade-up text-center mb-8 sm:mb-12 text-home-body text-sm md:text-base max-w-3xl mx-auto px-4 leading-relaxed font-plus" style="--sr-delay: 50ms">
      <?php echo e(__('home_stacks.intro')); ?>
    </p>

    <!-- Tabs Container (tabs.js: data-tab / data-tab-content) -->
    <div class="sr sr--fade-up tabs" style="--sr-delay: 80ms">
      <!-- Tab Menus -->
      <div class="tabs-menus sm:w-[432px] w-full mx-auto">
        <?php foreach ($supported_tabs as $tab_id => $tab_label): ?>
        <button type="button" data-tab="<?php echo e($tab_id); ?>"
          class="tab-menu px-6 py-3 text-sm font-medium font-plus<?php echo $tab_id === 'top-used' ? ' active' : ''; ?>">
          <?php echo e(__($tab_label)); ?>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Tab Content: Top Used (Default) -->
      <div data-tab-content="top-used" class="tab-content active">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
          <!-- Table Header -->
          <div class="bg-gray-50 border-b border-gray-200 px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
            <div class="<?php echo e($grid_class); ?>">
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_rank')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_name')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_type')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_trend')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 text-right font-plus"><?php echo e(__('home_stacks.col_score')); ?></div>
            </div>
          </div>

          <!-- Table Body -->
          <div class="divide-y divide-gray-200">
            <?php supported_stacks_render_table_rows($top_used_stacks, $row_class); ?>
          </div>
        </div>
      </div>

      <!-- Tab Content: High Speed -->
      <div data-tab-content="high-speed" class="tab-content">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
          <div class="bg-gray-50 border-b border-gray-200 px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
            <div class="<?php echo e($grid_class); ?>">
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_rank')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_name')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_type')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_trend')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 text-right font-plus"><?php echo e(__('home_stacks.col_score')); ?></div>
            </div>
          </div>
          <div class="divide-y divide-gray-200">
            <?php supported_stacks_render_table_rows($high_speed_stacks, $row_class); ?>
          </div>
        </div>
      </div>

      <!-- Tab Content: Top Frameworks -->
      <div data-tab-content="top-frameworks" class="tab-content">
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
          <div class="bg-gray-50 border-b border-gray-200 px-2 sm:px-4 lg:px-6 py-2 sm:py-4">
            <div class="<?php echo e($grid_class); ?>">
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_rank')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_name')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_type')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 font-plus"><?php echo e(__('home_stacks.col_trend')); ?></div>
              <div class="text-sm sm:text-xs lg:text-sm font-semibold text-gray-700 text-right font-plus"><?php echo e(__('home_stacks.col_score')); ?></div>
            </div>
          </div>
          <div class="divide-y divide-gray-200">
            <?php supported_stacks_render_table_rows($top_frameworks_stacks, $row_class); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
(function() {
  var container = document.querySelector('.supported-stacks-section');
  if (!container) return;
  var tabs = container.querySelector('.tabs');
  if (!tabs) return;
  var menus = tabs.querySelectorAll('[data-tab]');
  var contents = tabs.querySelectorAll('[data-tab-content]');
  function showTab(tabId) {
    menus.forEach(function(m) {
      m.classList.toggle('active', m.getAttribute('data-tab') === tabId);
    });
    contents.forEach(function(c) {
      c.classList.toggle('active', c.getAttribute('data-tab-content') === tabId);
    });
  }
  menus.forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = btn.getAttribute('data-tab');
      if (id) showTab(id);
    });
  });
})();
</script>
