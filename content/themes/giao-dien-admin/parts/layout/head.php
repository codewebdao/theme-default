<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $title ?? 'Dashboard' ?> - <?= option('site_brand') ?></title>

    <script type="text/javascript">
    window.BASE_URL = '<?= base_url() ?>';
    window.API_URL = '<?= api_url() ?>';
    window.ADMIN_URL = '<?= admin_url() ?>';
    </script>

    <?php
    // View::addJs / View::addCss (theme functions.php + BackendController)
    view_head();
    ?>

    <style type="text/tailwindcss">
        body {
        font-family: 'Inter', sans-serif;
        font-size: var(--font-size);
        font-weight: var(--font-weight);
        line-height: var(--line-height);
      }
      .card-content {
        padding: var(--card-padding);
        border-width: var(--card-border-width);
        border-radius: var(--radius);
      }
      .custom-btn {
        border-radius: var(--button-border-radius);
        font-size: var(--button-font-size);
        padding-top: var(--button-padding-y);
        padding-bottom: var(--button-padding-y);
        padding-left: var(--button-padding-x);
        padding-right: var(--button-padding-x);
      }
      @layer utilities {
        .scrollbar-none {
          scrollbar-width: none;
        }
        .scrollbar-none::-webkit-scrollbar {
          display: none;
        }
      }
      [x-cloak] { display: none !important; }
    </style>

    <script type="module">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))"
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "hsl(var(--secondary-foreground))"
                        },
                        destructive: {
                            DEFAULT: "hsl(var(--destructive))",
                            foreground: "hsl(var(--destructive-foreground))"
                        },
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))"
                        },
                        accent: {
                            DEFAULT: "hsl(var(--accent))",
                            foreground: "hsl(var(--accent-foreground))"
                        },
                        popover: {
                            DEFAULT: "hsl(var(--popover))",
                            foreground: "hsl(var(--popover-foreground))"
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))"
                        },
                        // Menu specific colors
                        menu: {
                            background: 'hsl(var(--menu-background))',
                            border: 'hsl(var(--menu-border))',
                            'section-label': 'hsl(var(--menu-section-label))',
                            icon: 'hsl(var(--menu-icon))',
                            text: 'hsl(var(--menu-text))',
                            'background-hover': 'hsl(var(--menu-background-hover))',
                            'icon-hover': 'hsl(var(--menu-icon-hover))',
                            'text-hover': 'hsl(var(--menu-text-hover))',
                        }
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    },
                },
            },
        };
    </script>

</head>

<body x-data="appState()" x-init="init()" :class="{ 'dark': theme === 'dark' }" :dir="config.layout" class="bg-background text-foreground" x-cloak>
    <div x-data="themeCustomizer(config, theme)" x-init="initCustomizer($watch)">
        <button @click="isOpen = true" :class="`fixed top-1/2 -translate-y-1/2 z-[80] p-3 bg-card border rounded-full shadow-lg ${config.layout === 'rtl' ? 'left-4' : 'right-4'}`" title="Theme Customizer"><i data-lucide="palette" class="w-5 h-5"></i></button>
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click="isOpen = false" class="fixed inset-0 bg-black/50 z-[90]"></div>
            <div :class="`fixed top-0 h-full w-80 bg-card border-${config.layout === 'rtl' ? 'r' : 'l'} border-border z-[100] overflow-y-auto ${config.layout === 'rtl' ? 'left-0' : 'right-0'}`" x-show="isOpen" x-transition:enter="transition ease-out duration-300" :x-transition:enter-start="config.layout === 'rtl' ? '-translate-x-full' : 'translate-x-full'" x-transition:enter-end="'translate-x-0'" x-transition:leave="transition ease-in duration-200" :x-transition:leave-start="'translate-x-0'" :x-transition:leave-end="config.layout === 'rtl' ? '-translate-x-full' : 'translate-x-full'">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold">Theme Customizer</h2><button @click="isOpen = false" class="p-1 rounded-md hover:bg-accent"><i data-lucide="x" class="w-4 h-4"></i></button>
                    </div>
                    <div class="space-y-4">
                        <div x-data="customSelect({ options: [{value: 'full', text: 'Full'}, {value: 'collapsed', text: 'Collapsed'}, {value: 'hidden', text: 'Hidden'}], initialValue: config.menuState, onChange: (val) => config.menuState = val })" class="space-y-3">
                            <label class="text-sm font-medium">Menu</label>
                            <div class="relative"><button @click="toggle()" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm"><span x-text="selectedOption.text"></span><i data-lucide="chevron-down" class="h-4 w-4 opacity-50"></i></button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute z-10 mt-1 w-full bg-background border rounded-md shadow-lg"><template x-for="option in options" :key="option.value">
                                        <div @click="select(option.value)" class="px-3 py-2 text-sm hover:bg-accent cursor-pointer" x-text="option.text"></div>
                                    </template></div>
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div x-data="customSelect({ options: [{value: 'ltr', text: 'LTR'}, {value: 'rtl', text: 'RTL'}], initialValue: config.layout, onChange: (val) => config.layout = val })" class="space-y-3">
                            <label class="text-sm font-medium">Layout</label>
                            <div class="relative"><button @click="toggle()" class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm"><span x-text="selectedOption.text"></span><i data-lucide="chevron-down" class="h-4 w-4 opacity-50"></i></button>
                                <div x-show="open" @click.away="open = false" x-transition class="absolute z-10 mt-1 w-full bg-background border rounded-md shadow-lg">
                                    <template x-for="option in options" :key="option.value">
                                        <div @click="select(option.value)" class="px-3 py-2 text-sm hover:bg-accent cursor-pointer" x-text="option.text"></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium">Dark Mode</label>
                            <button @click="theme = (theme === 'light' ? 'dark' : 'light');" :class="theme === 'dark' ? 'bg-primary' : 'bg-input'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors">
                                <span :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition"></span>
                            </button>
                        </div>
                        <hr class="border-border" />
                        <div class="space-y-3">
                            <label class="text-sm font-medium">Border & Padding</label>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs text-muted-foreground">Border Radius</label>
                                    <span class="text-xs" x-text="`${config.card.borderRadius}px`"></span>
                                </div>
                                <input type="range" x-model.number="config.card.borderRadius" min="0" max="32" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Border Width</label><span class="text-xs" x-text="`${config.card.borderWidth}px`"></span></div><input type="range" x-model.number="config.card.borderWidth" min="0" max="5" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Padding</label><span class="text-xs" x-text="`${config.card.padding}px`"></span></div><input type="range" x-model.number="config.card.padding" min="0" max="48" step="4" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div class="space-y-3"><label class="text-sm font-medium">Typography</label>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Font Size</label><span class="text-xs" x-text="`${config.typography.fontSize}px`"></span></div><input type="range" x-model.number="config.typography.fontSize" min="12" max="18" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Line Height</label><span class="text-xs" x-text="config.typography.lineHeight"></span></div><input type="range" x-model.number="config.typography.lineHeight" min="1" max="2" step="0.1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Font Weight</label><span class="text-xs" x-text="config.typography.fontWeight"></span></div><input type="range" x-model.number="config.typography.fontWeight" min="300" max="800" step="100" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div class="space-y-3"><label class="text-sm font-medium">Forms Button</label>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Border Radius</label><span class="text-xs" x-text="`${config.button.borderRadius}px`"></span></div><input type="range" x-model.number="config.button.borderRadius" min="0" max="32" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Font Size</label><span class="text-xs" x-text="`${config.button.fontSize}px`"></span></div><input type="range" x-model.number="config.button.fontSize" min="12" max="18" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Padding (Y)</label><span class="text-xs" x-text="`${config.button.paddingY}px`"></span></div><input type="range" x-model.number="config.button.paddingY" min="4" max="20" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between"><label class="text-xs text-muted-foreground">Padding (X)</label><span class="text-xs" x-text="`${config.button.paddingX}px`"></span></div><input type="range" x-model.number="config.button.paddingX" min="8" max="32" step="1" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div class="flex items-center justify-between"><label class="font-medium text-sm">Edit Dark Colors</label><button @click="editingDarkMode = !editingDarkMode" :class="editingDarkMode ? 'bg-primary' : 'bg-input'" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"><span :class="theme === 'dark' ? 'translate-x-6' : 'translate-x-1'" class="inline-block h-4 w-4 transform rounded-full bg-white transition"></span></button></div>
                        <div class="space-y-3"><label class="text-sm font-medium" x-text="`Colors (${editingDarkMode ? 'Dark' : 'Light'})`"></label>
                            <div class="grid gap-3 p-1">
                                <template x-for="(value, key) in (editingDarkMode ? config.colors.dark : config.colors.light)" :key="key">
                                    <div class="space-y-2"><label class="text-xs font-medium capitalize" x-text="key.replace(/([A-Z])/g, ' $1')"></label>
                                        <div class="flex gap-2"><input type="text" x-model="(editingDarkMode ? config.colors.dark : config.colors.light)[key]" class="flex-1 h-8 text-xs w-full rounded-md border border-input bg-background px-3 py-2" /><input type="color" :value="hslToHex((editingDarkMode ? config.colors.dark : config.colors.light)[key])" @input="(editingDarkMode ? config.colors.dark : config.colors.light)[key] = hexToHsl($event.target.value)" class="w-8 h-8 rounded border cursor-pointer bg-transparent" /></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <hr class="border-border" />
                        <div class="flex gap-2 pt-4"><button @click="saveConfig()" class="flex-1 bg-primary text-primary-foreground h-10 px-4 py-2 inline-flex items-center justify-center rounded-md text-sm font-medium"><i data-lucide="save" class="w-4 h-4 mr-2"></i> Save</button><button @click="resetConfig()" class="flex-1 border border-input bg-transparent h-10 px-4 py-2 inline-flex items-center justify-center rounded-md text-sm font-medium"><i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Reset</button></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
