<script>
    window.menuData = <?= json_encode($menuData ?? []) ?>;
    
    // Xác định menu items cần mở rộng từ backend
    window.expandedMenus = [];
    <?php if (isset($menuData)): ?>
        <?php foreach ($menuData as $item): ?>
            <?php if (isset($item['expanded']) && $item['expanded']): ?>
                window.expandedMenus.push('<?= $item['id'] ?>');
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    // Sidebar Search - Alpine.js
    function sidebarSearch() {
      return {
        query: '',
        showResults: false,
        suggestions: [],
        flat: [],
        searchTimeout: null,
        
        init() {
          this.flat = this.flattenMenu(window.menuData || []);
        },
        
        onKeyup(event) {
          // Clear timeout cũ nếu có
          if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
          }
          
          // Nếu là Enter thì xử lý ngay
          if (event.key === 'Enter') {
            event.preventDefault();
            this.onEnter();
            return;
          }
          
          // Debounce search với delay 200ms
          this.searchTimeout = setTimeout(() => {
            this.search();
          }, 200);
        },
        
        search() {
          const q = (this.query || '').trim().toLowerCase();
          
          if (!q) { 
            this.showResults = false;
            this.suggestions = [];
            return; 
          }
          
          const matches = this.flat.filter(x => {
            const labelMatch = x.label && x.label.toLowerCase().includes(q);
            const pathMatch = x.path && x.path.toLowerCase().includes(q);
            return labelMatch || pathMatch;
          });
          
          // Sắp xếp theo độ ưu tiên: label match trước, sau đó path match
          matches.sort((a, b) => {
            const aLabelMatch = a.label && a.label.toLowerCase().includes(q);
            const bLabelMatch = b.label && b.label.toLowerCase().includes(q);
            if (aLabelMatch && !bLabelMatch) return -1;
            if (!aLabelMatch && bLabelMatch) return 1;
            return 0;
          });
          
          this.suggestions = matches.slice(0, 8);
          this.showResults = this.suggestions.length > 0;
        },
        
        onEnter() {
          const q = (this.query || '').trim();
          
          // Nếu có suggestions thì chọn menu đầu tiên
          if (this.suggestions.length > 0) {
            window.location.href = this.suggestions[0].href;
            return;
          }
          
          // Chỉ chuyển đến URL tìm kiếm posts khi không có suggestions
          if (q) {
            const url = (window.ADMIN_URL || '/admin/') + 'posts/?type=posts&q=' + encodeURIComponent(q);
            window.location.href = url;
          }
        },
        
        flattenMenu(menu, trail = []) {
          const out = [];
          (menu || []).forEach(item => {
            if (item && item.type === 'menu') {
              const nextTrail = [...trail, item.label || ''];
              
              // Thêm menu item chính nếu có href
              if (item.href && item.href !== '#') {
                out.push({
                  label: item.label || '',
                  href: item.href,
                  icon: item.icon || 'chevron-right',
                  path: nextTrail.filter(Boolean).join(' › ')
                });
              }
              
              // Thêm các children items
              if (Array.isArray(item.children)) {
                item.children.forEach(child => {
                  if (child && child.type === 'menu' && child.href) {
                    out.push({
                      label: child.label || '',
                      href: child.href,
                      icon: child.icon || 'chevron-right',
                      path: [...nextTrail, child.label || ''].filter(Boolean).join(' › ')
                    });
                  }
                });
              }
            }
          });
          return out;
        }
      }
    }
</script>

<div class="flex min-h-[90vh] w-full">
  <!-- Sidebar -->
  <nav :class="sidebarClasses()" @mouseenter="isHovered = true" @mouseleave="isHovered = false" class="fixed inset-y-0 left-0 z-[60] transition-all duration-300 ease-in-out bg-menu-background border-r border-menu-border">
    <div x-show="menuState !== 'hidden'" class="h-full flex flex-col">
      <div class="h-12 px-3 flex items-center border-b border-menu-border flex-shrink-0">
        <a href="<?= admin_url('home'); ?>" class="flex items-center gap-3 w-full" x-show="showText()"><img src="<?= theme_assets('favicon/apple-icon-180x180.png'); ?>" alt="<?= option('site_brand') ?>" width="32" height="32" class="flex-shrink-0 hidden dark:block" /><img src="<?= theme_assets('favicon/apple-icon-180x180.png'); ?>" alt="<?= option('site_brand') ?>" width="32" height="32" class="hidden flex-shrink-0 block dark:hidden" /><span class="text-lg font-semibold text-gray-900 dark:text-white transition-opacity duration-200">CMS</span></a>
        <div class="flex justify-center w-full" x-show="!showText()"><img src="<?= theme_assets('favicon/apple-icon-180x180.png'); ?>" alt="<?= option('site_brand') ?>" width="32" height="32" class="flex-shrink-0 hidden dark:block" /><img src="<?= theme_assets('favicon/apple-icon-180x180.png'); ?>" alt="<?= option('site_brand') ?>" width="32" height="32" class="flex-shrink-0 block dark:hidden" /></div>
      </div>
      <!-- Search box -->
      <div class="px-2 py-2 border-b border-menu-border" x-data="sidebarSearch()" x-init="init()">
        <div class="relative">
          <input 
            x-model.trim="query" 
            @keyup="onKeyup($event)" 
            type="text" 
            placeholder="Search menu or press Enter" 
            class="w-full py-2 px-3 text-sm rounded-md bg-menu-background text-menu-text border border-menu-border focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" 
          />
          <div x-show="showResults && suggestions.length > 0" @mousedown.prevent class="absolute left-0 right-0 mt-1 bg-menu-background border border-menu-border rounded-md shadow-sm z-[70]">
            <template x-for="(s, idx) in suggestions" :key="s.href + idx">
              <a :href="s.href" class="block px-3 py-2 text-sm hover:bg-menu-background-hover hover:text-menu-text-hover text-menu-text">
                <div class="flex items-center gap-2">
                  <i :data-lucide="s.icon || 'search'" class="h-4 w-4 text-menu-icon"></i>
                  <span x-text="s.path"></span>
                </div>
              </a>
            </template>
          </div>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto overflow-x-hidden py-0 px-2 scrollbar-none">
        <div class="space-y-1">
          <template x-for="item in menuData" :key="item?.id || item?.order">
            <div>
              <!-- Label item -->
              <template x-if="item.type === 'label'">
                <div x-show="showText()" class="px-3 mb-2 mt-6 text-xs font-semibold uppercase tracking-wider text-menu-section-label transition-opacity duration-200" x-text="item.label"></div>
              </template>
              
              <!-- Space item -->
              <template x-if="item.type === 'space'">
                <div :style="`height: ${item.space}px`"></div>
              </template>
              
              <!-- Drive item -->
              <template x-if="item.type === 'drive'">
                <div :style="`height: ${item.width}px; margin: ${item.margin}px 0; background-color: #e5e7eb;`" class="w-full"></div>
              </template>
              
              <!-- HR item -->
              <template x-if="item.type === 'hr'">
                <hr class="border-b-2 border-menu-border my-2" style="border-bottom: 2px solid #e5e7eb;">
              </template>
              
              <!-- Menu item -->
              <template x-if="item.type === 'menu'">
                <div x-data="{ itemId: item.id }">
                  <!-- Render as link if item has href and no children, otherwise as div -->
                  <template x-if="item.href && item.href !== '#' && (!item?.children || item?.children?.length === 0)">
                    <a :href="item.href" @click="isMobile ? isMobileMenuOpen = false : null" :class="item.active ? 'bg-menu-background-hover text-menu-text-hover' : 'text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover'" :title="!showText() ? item.label : ''" class="flex items-center py-2 px-3 text-sm rounded-md transition-colors relative group">
                      <i :data-lucide="item.icon" class="h-4 w-4 flex-shrink-0 text-menu-icon group-hover:text-menu-icon-hover"></i>
                      <div x-show="showText()" class="mx-3 flex-1 flex items-center justify-between transition-opacity duration-200">
                        <span class="text-menu-text group-hover:text-menu-text-hover" x-text="item.label"></span>
                        <div class="flex items-center space-x-1"><template x-if="item.isNew"><span class="px-1.5 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded-full">New</span></template><template x-if="item.badge"><span class="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full" x-text="item.badge"></span></template></div>
                      </div>
                      <div x-show="!showText()" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50" x-text="item.label"></div>
                    </a>
                  </template>
                  <!-- Render as div if item has children or no href -->
                  <template x-if="!item.href || item.href === '#' || (item?.children && item?.children?.length > 0)">
                    <div @click="hasChildren(item) ? toggleExpanded(itemId) : (isMobile ? isMobileMenuOpen = false : null)" :class="item.active ? 'bg-menu-background-hover text-menu-text-hover' : 'text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover'" :title="!showText() ? item.label : ''" class="flex items-center py-2 px-3 text-sm rounded-md transition-colors relative group cursor-pointer">
                      <i :data-lucide="item.icon" class="h-4 w-4 flex-shrink-0 text-menu-icon group-hover:text-menu-icon-hover"></i>
                      <div x-show="showText()" class="mx-3 flex-1 flex items-center justify-between transition-opacity duration-200">
                        <span class="text-menu-text group-hover:text-menu-text-hover" x-text="item.label"></span>
                        <div class="flex items-center space-x-1"><template x-if="item.isNew"><span class="px-1.5 py-0.5 text-xs bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 rounded-full">New</span></template><template x-if="item.badge"><span class="px-1.5 py-0.5 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-full" x-text="item.badge"></span></template><template x-if="hasChildren(item)"><i data-lucide="chevron-down" class="h-3 w-3 transition-transform duration-200 text-gray-500" :class="isExpanded(itemId) ? 'rotate-180' : 'rotate-0'"></i></template></div>
                      </div>
                      <div x-show="!showText()" class="absolute left-full ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50" x-text="item.label"></div>
                    </div>
                  </template>
                  <div x-show="isExpanded(itemId) && showText()" class="mt-1 space-y-1">
                    <template x-for="child in item?.children" :key="child?.id || child?.order">
                      <a :href="child.href" :class="child.active ? 'bg-menu-background-hover text-menu-text-hover' : 'text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover'" class="flex items-center py-2 px-3 text-sm rounded-md transition-colors relative group ml-6">
                        <i :data-lucide="child.icon" class="h-4 w-4 mr-3 flex-shrink-0 text-menu-icon/80 group-hover:text-menu-icon-hover"></i>
                        <span x-text="child.label"></span>
                      </a>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </template>
          <div class="border-t border-menu-border">
            <div id="delete-cache" class="flex cursor-pointer items-center py-2 px-3 text-sm rounded-md transition-colors text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover relative group">
              <i data-lucide="delete" class="h-4 w-4 flex-shrink-0 text-menu-icon group-hover:text-menu-icon-hover"></i>
              <span x-show="showText()" class="mx-3 flex-1">Delete Cache</span>
            </div>
          </div>
        </div>
      </div>
      <div class="px-2 py-4 border-t border-menu-border flex-shrink-0">
        <div class="space-y-1">
          <a href="<?= auth_url('profile') ?>" class="flex items-center py-2 px-3 text-sm rounded-md transition-colors text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover relative group">
            <i data-lucide="user-cog" class="h-4 w-4 flex-shrink-0 text-menu-icon group-hover:text-menu-icon-hover"></i>
            <span x-show="showText()" class="ml-3 flex-1">Change Profile</span>
          </a>
          <a href="https://docs.cmsfullform.com" target="_blank" class="flex items-center py-2 px-3 text-sm rounded-md transition-colors text-menu-text hover:bg-menu-background-hover hover:text-menu-text-hover relative group">
            <i data-lucide="help-circle" class="h-4 w-4 flex-shrink-0 text-menu-icon group-hover:text-menu-icon-hover"></i>
            <span x-show="showText()" class="ml-3 flex-1">User Guide</span>
          </a>
        </div>
      </div>
    </div>
  </nav>
  <div x-show="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/50 z-[55] lg:hidden"></div>
  <div :class="mainContentMargin()" class="flex-1 flex flex-col transition-all duration-300 ease-in-out">