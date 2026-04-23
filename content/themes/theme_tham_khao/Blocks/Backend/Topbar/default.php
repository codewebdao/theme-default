<header class="flex items-center justify-between h-12 px-4 lg:px-6 border-b border-border flex-shrink-0">
  <div class="flex items-center space-x-4">
    <button @click="isMobile ? isMobileMenuOpen = true : handleMenuToggle()" class="p-2 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground" title="Toggle Menu"><i data-lucide="menu" class="h-4 w-4"></i></button>
    <nav class="hidden sm:flex items-center space-x-2 text-sm text-muted-foreground">
      <a href="<?= admin_url('home') ?>" class="flex items-center hover:text-foreground"><i data-lucide="home" class="h-4 w-4 mr-1"></i></a>
      <?php foreach ($breadcrumb as $item): ?>
        <span>/</span>
        <a href="<?= $item['url']; ?>" class="<?= isset($item['active']) && $item['active'] ? 'text-foreground ':'text-muted-foreground'; ?> font-medium"><?= $item['name']; ?></a>
      <?php endforeach; ?>
    </nav>
  </div>
  <!-- <div class="hidden md:flex flex-1 max-w-md mx-4">
    <div class="relative w-full">
      <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"></i>
      <input type="search" placeholder="Search..." class="pl-10 w-full h-8 rounded-md border border-input bg-secondary px-3 py-2 text-sm" />
    </div>
  </div> -->
  <div class="flex items-center space-x-2">
    <!-- select switch language -->
    <form method="get" onchange="if(this.language.value) window.location.href=this.language.value;" style="margin-right: 8px;">
      <select name="language" class="px-2 py-1 rounded border border-input bg-background text-sm focus:ring-ring focus:ring-1 focus:outline-none">
        <?php foreach (APP_LANGUAGES as $lang => $langData): ?>
          <option value="<?= lang_url($lang) ?>" <?= $lang === APP_LANG ? 'selected' : '' ?>>
            <?= lang_name($lang) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <button @click="theme = (theme === 'light' ? 'dark' : 'light');"
      class="p-2 rounded-md text-muted-foreground hover:bg-accent" title="Toggle Theme">
      <i data-lucide="sun" class="h-4 w-4 block dark:hidden"></i>
      <i data-lucide="moon" class="h-4 w-4 hidden dark:block"></i>
    </button>
    <div x-data="{ notificationOpen: false }" class="relative">
      <button @click="notificationOpen = !notificationOpen" class="relative p-2 rounded-md text-muted-foreground hover:bg-accent" title="Notifications">
        <i data-lucide="bell" class="h-4 w-4"></i>
        <span class="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs bg-red-500 text-white rounded-full">3</span>
      </button>
      
      <!-- Notification Dropdown -->
      <div x-show="notificationOpen" @click.away="notificationOpen = false" x-transition class="absolute right-0 mt-2 w-80 text-popover-foreground border border-border rounded-md shadow-lg z-10 bg-background">
        <div class="p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-foreground">Notifications</h3>
            <button class="text-xs text-primary hover:underline">Mark all as read</button>
          </div>
          <div class="space-y-3 max-h-86 overflow-y-auto overflow-x-hidden">
            <!-- Notification Item 1 -->
            <div class="flex items-start space-x-3 p-3 hover:bg-accent/50 rounded-lg transition-colors">
              <div class="p-2 rounded-full bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 flex-shrink-0">
                <i data-lucide="check-circle" class="h-4 w-4"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="mb-1">
                  <span class="text-sm font-medium text-foreground">New User Registered</span>
                </div>
                <div class="text-sm text-muted-foreground mb-1">
                  <span class="font-medium">John Doe</span> has registered as a new user
                </div>
                <div class="text-xs text-muted-foreground">2 minutes ago</div>
              </div>
            </div>
            
            <!-- Notification Item 2 -->
            <div class="flex items-start space-x-3 p-3 hover:bg-accent/50 rounded-lg transition-colors">
              <div class="p-2 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 flex-shrink-0">
                <i data-lucide="file-text" class="h-4 w-4"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="mb-1">
                  <span class="text-sm font-medium text-foreground">New Article Published</span>
                </div>
                <div class="text-sm text-muted-foreground mb-1">
                  <span class="font-medium">Sarah Johnson</span> published "AI in Healthcare"
                </div>
                <div class="text-xs text-muted-foreground">15 minutes ago</div>
              </div>
            </div>
            
            <!-- Notification Item 3 -->
            <div class="flex items-start space-x-3 p-3 hover:bg-accent/50 rounded-lg transition-colors">
              <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400 flex-shrink-0">
                <i data-lucide="alert-triangle" class="h-4 w-4"></i>
              </div>
              <div class="flex-1 min-w-0">
                <div class="mb-1">
                  <span class="text-sm font-medium text-foreground">System Update</span>
                </div>
                <div class="text-sm text-muted-foreground mb-1">
                  System maintenance scheduled for tomorrow at 2:00 AM
                </div>
                <div class="text-xs text-muted-foreground">1 hour ago</div>
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-border">
            <a href="<?= admin_url('notifications') ?>" class="text-sm text-primary hover:underline">View all notifications</a>
          </div>
        </div>
      </div>
    </div>

    <!-- user info -->
    <div x-data="{ open: false }" class="relative">
      <button @click="open = !open" class="flex items-center space-x-2 p-0 rounded-md hover:bg-accent">
        <?php if($user_info['avatar']): ?>
          <img src="<?= $user_info['avatar'] ?>" alt="Avatar" class="w-8 h-8 rounded-full">
        <?php else: ?>
          <!-- Name first letter -->
          <div class="w-8 h-8 rounded-full bg-muted flex items-center justify-center">
            <span class="text-sm font-medium text-foreground"><?= substr($user_info['fullname'], 0, 1) ?></span>
          </div>
        <?php endif; ?>
        <div class="hidden lg:flex flex-col items-start">
          <span class="text-sm font-medium text-foreground"><?= $user_info['fullname'] ?></span>
          <span class="text-xs text-muted-foreground"><?= $user_info['role'] ?></span>
        </div>
        <i data-lucide="chevron-down" class="hidden lg:block h-4 w-4 text-muted-foreground"></i>
      </button>
      <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-72 text-popover-foreground border border-border rounded-md shadow-lg z-10 bg-background">
        <div class="p-2">

        <div class="flex items-center space-x-4 p-2 bg-muted/50 rounded-lg">
            <?php if($user_info['avatar']): ?>
              <img src="<?= $user_info['avatar'] ?>" alt="Avatar" class="w-16 h-16 rounded-full">
            <?php else: ?>
              <!-- Name first letter -->
              <div class="w-16 h-16 rounded-full bg-muted flex items-center justify-center">
                <span class="text-sm font-medium text-foreground"><?= substr($user_info['fullname'], 0, 1) ?></span>
              </div>
            <?php endif; ?>
            <div>
                <h3 class="font-medium"><?= $user_info['fullname'] ?></h3>
                <p class="text-sm text-muted-foreground"><?= $user_info['email'] ?></p>
            </div>
        </div>


          <a href="<?= auth_url('profile') ?>" class="flex items-center px-2 py-1.5 text-sm rounded-md hover:bg-accent"><i data-lucide="user-cog" class="mr-2 h-4 w-4"></i> Profile</a>
          <!-- <a href="#"
            class="flex items-center px-2 py-1.5 text-sm rounded-md hover:bg-accent"><i data-lucide="lock-keyhole" class="mr-2 h-4 w-4"></i> Password</a> -->
          <div class="my-1 h-px bg-border"></div>
          <a href="<?= auth_url('logout') ?>" class="flex items-center px-2 py-1.5 text-sm rounded-md text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"><i data-lucide="log-out" class="mr-2 h-4 w-4"></i> Sign out</a>
        </div>
      </div>
    </div>
  </div>
</header>
<main class="flex-1 p-4 sm:p-6 overflow-y-auto">