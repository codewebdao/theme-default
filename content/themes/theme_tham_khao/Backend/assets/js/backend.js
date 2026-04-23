function appState() {
    return {
      menuState: "full",
      isMobile: false,
      isMobileMenuOpen: false,
      isHovered: false,
      theme: "light",
      config: null,
      expandedItems: new Set(),
      init() {
        this.config = this.loadConfig();
        this.theme = localStorage.getItem("theme") || "light";
        this.menuState = this.config.menuState;
        this.$watch('theme', val => {
          localStorage.setItem('theme', val);
          this.updateCssVariables();
        });
        this.$watch('config', () => {
          this.updateCssVariables();
          this.menuState = this.config.menuState;
          document.documentElement.dir = this.config.layout;
        }, {
          deep: true
        });
        this.$watch('menuData', () => {
          this.initActiveStates();
        }, {
          deep: true
        });
        window.addEventListener('theme-change', (e) => this.theme = e.detail);
        const handleResize = () => {
          this.isMobile = window.innerWidth < 1024;
        };
        handleResize();
        window.addEventListener('resize', handleResize);
        this.updateCssVariables();
        this.initActiveStates();
        this.$nextTick(function(){
          if (lucide)  lucide.createIcons();
        });
      },
      initActiveStates() {
        // Ưu tiên backend data hoàn toàn
        if (window.expandedMenus && window.expandedMenus.length > 0) {
          // Clear existing expanded items first
          this.expandedItems.clear();
          window.expandedMenus.forEach(itemId => {
            this.expandedItems.add(itemId);
          });
          return;
        }
        
        // Chỉ dùng isParentActive khi không có backend data
        if (this.menuData && this.menuData.length > 0) {
          this.menuData.forEach(section => {
            if (section.items) {
              section.items.forEach(item => {
                if (this.isParentActive(item)) {
                  const itemId = `${section.id}-${item.id}`;
                  this.expandedItems.add(itemId);
                }
              });
            }
          });
        }
      },
      loadConfig() {
        const defaultConfig = {
          menuState: "full",
          layout: "ltr",
          colors: {
            light: {
              background: "0 0% 100%",
              foreground: "222.2 84% 4.9%",
              primary: "222.2 47.4% 11.2%",
              primaryForeground: "210 40% 98%",
              secondary: "210 40% 96.1%",
              secondaryForeground: "222.2 47.4% 11.2%",
              accent: "210 40% 96.1%",
              accentForeground: "222.2 47.4% 11.2%",
              muted: "210 40% 96.1%",
              mutedForeground: "215.4 16.3% 46.9%",
              border: "214.3 31.8% 91.4%",
              input: "214.3 31.8% 91.4%",
              ring: "222.2 84% 4.9%",
              card: "0 0% 100%",
              cardForeground: "222.2 84% 4.9%",
              menuBackground: "0 0% 100%",
              menuBorder: "214.3 31.8% 91.4%",
              menuSectionLabel: "215.4 16.3% 46.9%",
              menuIcon: "222.2 84% 4.9%",
              menuText: "222.2 84% 4.9%",
              menuBackgroundHover: "210 40% 96.1%",
              menuIconHover: "222.2 47.4% 11.2%",
              menuTextHover: "222.2 47.4% 11.2%"
            },
            dark: {
              background: "240 10% 3.9%",
              foreground: "0 0% 98%",
              primary: "0 0% 98%",
              primaryForeground: "240 5.9% 10%",
              secondary: "240 3.7% 15.9%",
              secondaryForeground: "0 0% 98%",
              accent: "240 3.7% 15.9%",
              accentForeground: "0 0% 98%",
              muted: "240 3.7% 15.9%",
              mutedForeground: "240 5% 64.9%",
              border: "240 3.7% 15.9%",
              input: "240 3.7% 15.9%",
              ring: "240 4.9% 83.9%",
              card: "240 10% 3.9%",
              cardForeground: "0 0% 98%",
              menuBackground: "240 10% 3.9%",
              menuBorder: "240 3.7% 15.9%",
              menuSectionLabel: "240 5% 64.9%",
              menuIcon: "240 5% 64.9%",
              menuText: "0 0% 98%",
              menuBackgroundHover: "240 3.7% 15.9%",
              menuIconHover: "0 0% 98%",
              menuTextHover: "0 0% 98%"
            }
          },
          card: {
            borderRadius: 8,
            borderWidth: 1,
            padding: 24
          },
          typography: {
            fontSize: 14,
            fontWeight: "400",
            lineHeight: 1.5
          },
          button: {
            borderRadius: 6,
            fontSize: 14,
            paddingY: 8,
            paddingX: 16
          },
        };
        try {
          const saved = localStorage.getItem("theme-config");
          if (saved) {
            const parsed = JSON.parse(saved);
            // Deep merge to ensure all keys exist, even if saved config is old
            return {
              ...defaultConfig,
              ...parsed,
              colors: {
                light: {
                  ...defaultConfig.colors.light,
                  ...(parsed.colors?.light || {})
                },
                dark: {
                  ...defaultConfig.colors.dark,
                  ...(parsed.colors?.dark || {})
                }
              },
              card: {
                ...defaultConfig.card,
                ...(parsed.card || {})
              },
              typography: {
                ...defaultConfig.typography,
                ...(parsed.typography || {})
              },
              button: {
                ...defaultConfig.button,
                ...(parsed.button || {})
              },
            };
          }
        } catch (e) {
          console.error("Failed to load or parse theme config:", e);
        }
        return defaultConfig;
      },
      updateCssVariables() {
        if (!this.config) return;
        const root = document.documentElement;
        const colors = this.theme === 'dark' ? this.config.colors.dark : this.config.colors.light;
        for (const [key, value] of Object.entries(colors)) {
          root.style.setProperty(`--${key.replace(/([A-Z])/g, "-$1").toLowerCase()}`, value);
        }
        root.style.setProperty('--radius', `${this.config.card.borderRadius}px`);
        root.style.setProperty('--card-border-width', `${this.config.card.borderWidth}px`);
        root.style.setProperty('--card-padding', `${this.config.card.padding}px`);
        root.style.setProperty('--font-size', `${this.config.typography.fontSize}px`);
        root.style.setProperty('--font-weight', this.config.typography.fontWeight);
        root.style.setProperty('--line-height', this.config.typography.lineHeight);
        root.style.setProperty('--button-border-radius', `${this.config.button.borderRadius}px`);
        root.style.setProperty('--button-font-size', `${this.config.button.fontSize}px`);
        root.style.setProperty('--button-padding-y', `${this.config.button.paddingY}px`);
        root.style.setProperty('--button-padding-x', `${this.config.button.paddingX}px`);
      },
      sidebarClasses() {
        if (this.isMobile) return `w-64 transform transition-transform duration-300 ease-in-out ${this.isMobileMenuOpen ? '-translate-x-0' : '-translate-x-full'}`;
        if (this.menuState === 'hidden') return 'w-0 border-r-0';
        if (this.menuState === 'collapsed' && !this.isHovered) return 'w-16';
        return 'w-64';
      },
      mainContentMargin() {
        if (this.isMobile || this.menuState === 'hidden') return 'ml-0';
        return this.menuState === 'collapsed' ? 'lg:ml-16' : 'lg:ml-64';
      },
      showText() {
        return this.menuState === 'full' || (this.menuState === 'collapsed' && this.isHovered) || (this.isMobile && this.isMobileMenuOpen);
      },
      handleMenuToggle() {
        const s = ['full', 'collapsed', 'hidden'];
        this.config.menuState = s[(s.indexOf(this.menuState) + 1) % s.length];
      },
      toggleExpanded(id) {
        this.expandedItems.has(id) ? this.expandedItems.delete(id) : this.expandedItems.add(id);
      },
      isExpanded(id) {
        return this.expandedItems.has(id);
      },
      hasChildren(item) {
        return item.children && item.children.length > 0;
      },
      isActive(href) {
        if (!href || href === '#') return false;
        const currentPath = window.currentUrl || window.location.pathname;

        // Normalize href to path only (remove domain if exists)
        let hrefPath = href;
        if (href.startsWith('http://') || href.startsWith('https://')) {
          try {
            const url = new URL(href);
            hrefPath = url.pathname;
          } catch (e) {
            hrefPath = href;
          }
        }

        // Remove language prefix from current path for comparison
        const supportedLangs = ['en', 'vi', 'fr', 'de', 'es'];
        let normalizedCurrentPath = currentPath;

        // Check if current path starts with language prefix
        for (const lang of supportedLangs) {
          if (currentPath.startsWith(`/${lang}/`)) {
            normalizedCurrentPath = currentPath.substring(lang.length + 1); // Remove /lang
            break;
          }
        }

        // Also normalize href (remove language prefix if exists)
        let normalizedHref = hrefPath;
        for (const lang of supportedLangs) {
          if (hrefPath.startsWith(`/${lang}/`)) {
            normalizedHref = hrefPath.substring(lang.length + 1); // Remove /lang
            break;
          }
        }

        // Ensure paths end with / for consistent comparison
        const ensureTrailingSlash = (path) => {
          if (path === '/') return path;
          return path.endsWith('/') ? path : path + '/';
        };

        const normalizedCurrentWithSlash = ensureTrailingSlash(normalizedCurrentPath);
        const normalizedHrefWithSlash = ensureTrailingSlash(normalizedHref);
        const currentPathWithSlash = ensureTrailingSlash(currentPath);
        const hrefPathWithSlash = ensureTrailingSlash(hrefPath);

        // Debug detailed comparison
        const debugInfo = {
          href,
          hrefPath,
          currentPath,
          normalizedCurrentPath,
          normalizedHref,
          currentPathWithSlash,
          hrefPathWithSlash,
          normalizedCurrentWithSlash,
          normalizedHrefWithSlash
        };

        // Exact match for current URL (with and without language prefix)
        if (currentPath === hrefPath || normalizedCurrentPath === normalizedHref) {
          return true;
        }
        if (currentPathWithSlash === hrefPathWithSlash || normalizedCurrentWithSlash === normalizedHrefWithSlash) {
          return true;
        }

        // Check if current path starts with href (for nested routes)
        if (hrefPath !== '/' && normalizedHref !== '/') {
          if (currentPath.startsWith(hrefPath) || normalizedCurrentPath.startsWith(normalizedHref)) {
            return true;
          }
          if (currentPathWithSlash.startsWith(hrefPathWithSlash) || normalizedCurrentWithSlash.startsWith(normalizedHrefWithSlash)) {
            return true;
          }
        }

        return false;
      },
      isParentActive(item) {
        if (!item.children || item.children.length === 0) return false;
        return item.children.some(child => this.isActive(child.href));
      },
      getActiveClasses(item, isChild = false) {
        let baseClasses = isChild ?
          'flex items-center py-2 pl-6 pr-3 text-sm rounded-md transition-colors' :
          'flex items-center py-2 text-sm rounded-md transition-colors relative group cursor-pointer';

        // Add padding based on whether item has children
        if (!isChild) {
          baseClasses += item.children && item.children.length > 0 ? ' pl-3' : ' px-3';
        }

        const isItemActive = this.isActive(item.href);
        const isParentOfActive = this.isParentActive(item);

        if (isItemActive) {
          return baseClasses + ' bg-menu-background-hover text-menu-text-hover';
        } else if (isParentOfActive && !isChild) {
          return baseClasses + ' bg-menu-background-hover text-menu-text-hover';
        } else if (isChild) {
          return baseClasses + ' text-menu-text/80 hover:bg-menu-background-hover hover:text-menu-text-hover';
        } else {
          return baseClasses + ' hover:bg-menu-background-hover';
        }
      },
      menuData: window.menuData || [],
    };
}

function themeCustomizer(config, theme) {
    return {
      isOpen: false,
      editingDarkMode: false,
      initCustomizer($watch) {
        $watch('isOpen', (val) => {
          if (val) this.$nextTick(function(){
            if (lucide) lucide.createIcons();
          });
        });
      },
      saveConfig() {
        localStorage.setItem("theme-config", JSON.stringify(config));
        alert("Theme configuration saved!");
      },
      resetConfig() {
        localStorage.removeItem("theme-config");
        window.location.reload();
      },
      hslToHex(hsl) {
        try {
          if (!hsl || typeof hsl !== "string") return "#000000";
          const [h, s, l] = hsl.trim().split(" ").map(v => parseFloat(v));
          const sNorm = s / 100,
            lNorm = l / 100;
          const c = (1 - Math.abs(2 * lNorm - 1)) * sNorm,
            x = c * (1 - Math.abs(((h / 60) % 2) - 1)),
            m = lNorm - c / 2;
          let r = 0,
            g = 0,
            b = 0;
          if (h < 60) {
            r = c;
            g = x;
          } else if (h < 120) {
            r = x;
            g = c;
          } else if (h < 180) {
            g = c;
            b = x;
          } else if (h < 240) {
            g = x;
            b = c;
          } else if (h < 300) {
            r = x;
            b = c;
          } else {
            r = c;
            b = x;
          }
          return `#${[r, g, b].map(v => Math.round((v + m) * 255).toString(16).padStart(2, "0")).join("")}`;
        } catch {
          return "#000000";
        }
      },
      hexToHsl(hex) {
        try {
          const r = parseInt(hex.slice(1, 3), 16) / 255,
            g = parseInt(hex.slice(3, 5), 16) / 255,
            b = parseInt(hex.slice(5, 7), 16) / 255;
          const max = Math.max(r, g, b),
            min = Math.min(r, g, b);
          let h = 0,
            s = 0,
            l = (max + min) / 2;
          if (max !== min) {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
              case r:
                h = (g - b) / d + (g < b ? 6 : 0);
                break;
              case g:
                h = (b - r) / d + 2;
                break;
              case b:
                h = (r - g) / d + 4;
                break;
            }
            h /= 6;
          }
          return `${Math.round(h * 360)} ${Math.round(s * 100)}% ${Math.round(l * 100)}%`;
        } catch {
          return "0 0% 0%";
        }
      },
    }
}

function customSelect({
    options,
    initialValue,
    onChange
  }) {
    return {
      open: false,
      options: options,
      selectedOption: options.find(o => o.value === initialValue) || options[0],
      toggle() {
        this.open = !this.open;
        if (this.open) this.$nextTick(function(){
          if (lucide) lucide.createIcons();
        });
      },
      select(value) {
        this.selectedOption = this.options.find(o => o.value === value);
        this.open = false;
        onChange(value);
      }
    }
}

function deleteCache() {
  fetch(window.API_URL + 'cache/delete/', {
      method: "GET",
      credentials: "include"
  })
  .then(response => response.json())
  .then(data => {
      if(data.success) {
          alert('Cache deleted successfully!');
          //location.reload();
      } else {
          alert('Failed to delete cache!');
      }
  })
  .catch(err => {
      alert('Error deleting cache!');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  var deleteCacheBtn = document.getElementById('delete-cache');
  if (deleteCacheBtn) {
      deleteCacheBtn.addEventListener('click', deleteCache);
  }
});
var lucide = lucide ?? null;
document.addEventListener('DOMContentLoaded', function () {
  let timeInterval = setInterval((lucide) => {
    if (lucide){
      lucide.createIcons();
      clearInterval(timeInterval);
    }
  }, 200, lucide);
});