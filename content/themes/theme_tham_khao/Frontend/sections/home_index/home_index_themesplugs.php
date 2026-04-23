<?php
// Get data from extracted variables
$title = $title ?? '';
$tabs = $tabs ?? [];
$tabCount = count($tabs);

?>


<!-- Rich Themes & Plugins Repository -->
<section id="integrations" class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('section.themes_plugins_title') ?>
            </h2>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('section.themes_plugins_description') ?>
            </p>
        </div>


        <!-- Tab Navigation -->
        <div class="flex justify-center mb-10">
            <div class="flex justify-center sm:inline-flex bg-slate-100 rounded-full p-1.5 shadow-md gap-2">
                <!-- Themes Tab -->
                <button
                    class="plugin-theme-tab-btn inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-primary/90 h-10 rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 bg-white text-blue-600 shadow-sm"
                    data-plugin-theme-tab="themes">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="hidden sm:flex lucide lucide-palette mr-2">
                        <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                        <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                        <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                        <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                        <path
                            d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z">
                        </path>
                    </svg>
                    Themes
                </button>

                <!-- Plugins Tab -->
                <button
                    class="plugin-theme-tab-btn inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-primary/90 h-10 rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 bg-transparent text-slate-600 hover:text-slate-900"
                    data-plugin-theme-tab="plugins">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="hidden sm:flex lucide lucide-puzzle mr-2">
                        <path
                            d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.61 1.61a2.404 2.404 0 0 1-1.705.707 2.402 2.402 0 0 1-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02a1.026 1.026 0 0 0-.289-.877l-1.568-1.568A2.402 2.402 0 0 1 1.998 12c0-.617.236-1.234.706-1.704L4.23 8.77c.24-.24.581-.353.917-.303.515.077.877.528 1.073 1.01a2.5 2.5 0 1 0 3.259-3.259c-.482-.196-.933-.558-1.01-1.073-.05-.336.062-.676.303-.917l1.525-1.525A2.402 2.402 0 0 1 12 1.998c.617 0 1.234.236 1.704.706l1.568 1.568c.23.23.556.338.877.29.493-.074.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02Z">
                        </path>
                    </svg>
                    Plugins
                </button>

            </div>
        </div>


        <!-- Themes Content -->
        <div class="plugin-theme-content" data-plugin-theme-content="themes">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php foreach (($tabs[0]['themes'] ?? []) as $theme) : ?>
                    <div
                        class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-white to-slate-50 overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group border border-slate-200 hover:border-blue-300">
                        <div class="relative">
                            <a href="<?= $theme['url'] ?? base_url('libraries/themes', $theme['slug']) ?>" target="_blank" class="block">
                                <?= _img(
                                    theme_assets(get_image_full($theme['thumbnail_url'] ?? '')),
                                    $theme['title'],
                                    true,
                                    'w-full h-72 object-cover group-hover:scale-105 transition-transform duration-300'
                                ) ?>
                            </a>
                            <?php if (!empty($theme['tags'])): ?>
                                <div class="absolute top-3 right-3 flex flex-col justify-end items-end gap-2 flex-wrap gap-1">
                                    <?php
                                    // Handle tags as string or array
                                    if (is_array($theme['tags'])) {
                                        $tags = $theme['tags'];
                                    } else {
                                        $tags = array_map('trim', explode(',', $theme['tags']));
                                    }

                                    // Diverse background colors (rotating)
                                    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500'];

                                    foreach ($tags as $i => $tag):
                                        if ($tag === '') continue;

                                        // Capitalize first letter of each word
                                        $label = ucwords(mb_strtolower($tag)); // use mb_strtolower to support Vietnamese
                                        $color = $colors[$i % count($colors)];
                                    ?>
                                        <span class="w-fit text-xs font-semibold px-2.5 py-1 rounded-full text-white <?= $color ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div
                                class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-slate-900/80 to-transparent">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-blue-600 mr-3"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-shopping-cart text-white">
                                            <circle cx="8" cy="21" r="1"></circle>
                                            <circle cx="19" cy="21" r="1"></circle>
                                            <path
                                                d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                            </path>
                                        </svg></div>
                                    <h3 class="text-lg font-semibold text-white line-clamp-1 hover:text-blue-600">
                                        <a href="<?= $theme['url'] ?? base_url('libraries/themes/'. $theme['slug']) ?>" target="_blank" class="inline"><?= $theme['title']; ?></a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <p class="text-slate-600 text-sm"><?= $theme['seo_desc'] ?? $theme['description'] ?? ''; ?>.</p>
                            <div class="mt-4 flex justify-between items-center">
                                <a href="<?= $theme['demo_url'] ?? '' ?>" rel="nofollow" target="_blank">
                                    <button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-9 rounded-md px-3 text-blue-600 border-blue-500 hover:bg-blue-50"><?= _e('button.view_demo') ?>
                                    </button></a>
                                <a href="<?= $theme['url'] ?? base_url('libraries/themes/'. $theme['slug']) ?>" target="_blank">
                                    <button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  hover:bg-primary/90 h-9 rounded-md px-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white"><?= _e('button.details') ?></button></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Plugins Content -->
        <div class="plugin-theme-content hidden" data-plugin-theme-content="plugins">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                <?php foreach (($tabs[1]['plugins'] ?? []) as $plugin) : ?>
                    <div
                        class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-white to-slate-50 overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 group border border-slate-200 hover:border-blue-300">
                        <div class="relative">
                            <a href="<?= $plugin['url'] ?? base_url('libraries/plugins', $plugin['slug']) ?>" target="_blank" class="block">
                                <?= _img(
                                    theme_assets(get_image_full($plugin['icon_url'] ?? '')),
                                    $plugin['title'],
                                    true,
                                    'w-full h-72 object-cover group-hover:scale-105 transition-transform duration-300'
                                ) ?>
                            </a>
                            <?php if (!empty($plugin['tags'])): ?>
                                <div class="absolute top-3 right-3 flex flex-col justify-end items-end gap-2 flex-wrap gap-1">
                                    <?php
                                    // Handle tags as string or array
                                    if (is_array($plugin['tags'])) {
                                        $tags = $plugin['tags'];
                                    } else {
                                        $tags = array_map('trim', explode(',', $plugin['tags']));
                                    }

                                    // Diverse background colors (rotating)
                                    $colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500'];

                                    foreach ($tags as $i => $tag):
                                        if ($tag === '') continue;

                                        // Capitalize first letter of each word
                                        $label = ucwords(mb_strtolower($tag)); // use mb_strtolower to support Vietnamese
                                        $color = $colors[$i % count($colors)];
                                    ?>
                                        <span class="w-fit text-xs font-semibold px-2.5 py-1 rounded-full text-white <?= $color ?>">
                                            <?= htmlspecialchars($label) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div
                                class="absolute bottom-0 left-0 w-full p-4 bg-gradient-to-t from-slate-900/80 to-transparent">
                                <div class="flex items-center">
                                    <div class="p-2 rounded-full bg-blue-600 mr-3"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-shopping-cart text-white">
                                            <circle cx="8" cy="21" r="1"></circle>
                                            <circle cx="19" cy="21" r="1"></circle>
                                            <path
                                                d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-white line-clamp-1 hover:text-blue-600">
                                        <a href="<?= $plugin['url'] ?? base_url('libraries/plugins/'. $plugin['slug']) ?>" target="_blank" class="inline"><?= $plugin['title']; ?></a>
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="p-5">
                            <p class="text-slate-600 text-sm"><?= $plugin['seo_desc'] ?? $plugin['description'] ?? $plugin['content'] ?? ''; ?>.</p>
                            <div class="mt-4 flex justify-between items-center">
                                <a href="<?= $plugin['install_url'] ?? '' ?>" rel="nofollow" target="_blank">
                                    <button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-9 rounded-md px-3 text-blue-600 border-blue-500 hover:bg-blue-50">
                                        <?= _e('button.view_demo') ?></button></a>
                                <a href="<?= $plugin['url'] ?? base_url('libraries/plugins/'. $plugin['slug']) ?>" target="_blank">
                                    <button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  hover:bg-primary/90 h-9 rounded-md px-3 bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white">
                                        <?= _e('button.details') ?></button></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>



        <div class="text-center mt-12">
            <a id="view-all-link" href="<?= base_url('libraries/themes', APP_LANG) ?>">
                <button id="view-all-btn"
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 text-blue-600 border-blue-500 hover:border-blue-600 hover:bg-blue-50 font-medium"><?= _e('button.view_all_themes') ?>
                </button>
            </a>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.plugin-theme-tab-btn');
        const tabContents = document.querySelectorAll('.plugin-theme-content');
        const viewAllLink = document.getElementById('view-all-link');
        const viewAllBtn = document.getElementById('view-all-btn');

        // Define tab-specific content
        const tabConfig = {
            'themes': {
                url: '<?= base_url('libraries/themes', APP_LANG) ?>',
                text: '<?= _e('button.view_all_themes') ?>'
            },
            'plugins': {
                url: '<?= base_url('libraries/plugins', APP_LANG) ?>',
                text: '<?= _e('button.view_all_plugins') ?>'
            }
        };

        // Function to update view all button
        function updateViewAllButton(tabType) {
            if (tabConfig[tabType] && viewAllLink && viewAllBtn) {
                viewAllLink.href = tabConfig[tabType].url;
                viewAllBtn.textContent = tabConfig[tabType].text;
            }
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-plugin-theme-tab');

                // Remove active state from all buttons
                tabButtons.forEach(btn => {
                    btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                    btn.classList.add('bg-transparent', 'text-slate-600');
                });

                // Add active state to clicked button
                this.classList.remove('bg-transparent', 'text-slate-600');
                this.classList.add('bg-white', 'text-blue-600', 'shadow-sm');

                // Hide all content
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show target content
                const targetContent = document.querySelector(`[data-plugin-theme-content="${targetTab}"]`);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                }

                // Update view all button
                updateViewAllButton(targetTab);
            });
        });

        // Set initial state (themes tab is active by default)
        updateViewAllButton('themes');
    });
</script>
