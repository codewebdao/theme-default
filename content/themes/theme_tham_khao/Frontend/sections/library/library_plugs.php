<section id="plugins" class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <!-- Header Section -->
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('plugins_title') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-purple-600 to-pink-600">
                    <?php _e('plugins_powerful') ?>
                </span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('plugins_description') ?>
            </p>
        </div>

        <!-- Plugins Grid -->
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php if (!empty($plug)): ?>
                <?php foreach ($plug as $item): ?>
                    <!-- Plugin Card -->
                    <div class="rounded-lg group relative flex flex-col justify-between bg-card text-card-foreground <?= $item['background_class'] ?? '' ?> shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">

                        <!-- Price Badge -->
                        <span class="absolute px-2 py-1 bg-white top-4 right-2 border border-blue-600 rounded-lg text-sm font-bold text-blue-600">
                            <?= (empty($item['price']) || $item['price'] == 0) ? _e('extensions_section.title.tag2') : '$' . number_format($item['price']) ?>
                        </span>

                        <!-- Card Header -->
                        <div class="flex flex-col space-y-1.5 p-6">
                            <div class="flex items-start justify-between">
                                <!-- Icon -->
                                <div class="p-2 rounded-xl <?= $item['icon_bg_class'] ?? 'bg-gradient-to-br from-blue-500 to-purple-600' ?> shadow-lg">
                                    <?php if (!empty($item['icon_url'])): ?>
                                        <?= _img(theme_assets(get_image_full($item['icon_url'])), $item['title'], true, 'w-8 h-8 object-cover rounded') ?>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="M21 15l-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    <?php endif; ?>
                                </div>

                                <!-- Tags (Hidden on smaller screens) -->
                                <div class="hidden 2xl:flex flex-wrap gap-2">
                                    <div class="whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-xs font-semibold bg-green-500 text-white">
                                        <?php _e('extensions_section.title.tag1') ?>
                                    </div>
                                    <div class="whitespace-nowrap inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-xs font-semibold bg-blue-500 text-white">
                                        <?php _e('extensions_section.title.tag2') ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Title and Category -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-xl font-bold text-slate-800 line-clamp-2">
                                        <a href="<?= base_url('libraries/plugins/' . $item['slug'], APP_LANG) ?>" class="group-hover:text-blue-600 inline"><?= $item['title'] ?></a>
                                    </h3>
                                </div>
                                <!-- <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 text-foreground text-xs mb-3">
                                    SEO
                                </div> -->
                            </div>
                            <p class="text-slate-600 text-sm mb-4 line-clamp-2"><?= $item['content'] ?></p>

                        </div>

                        <!-- Card Content -->
                        <div>
                            <!-- Description and Stats -->
                            <div class="p-6 pt-0">
                                <!-- Rating and Download Stats -->
                                <div class="flex items-center justify-between text-sm text-slate-500">
                                    <!-- Rating -->
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-yellow-500 fill-current">
                                            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                                        </svg>
                                        <span><?= $item['rating_avg'] ?></span>
                                    </div>

                                    <!-- Download Count -->
                                    <?php
                                    if (!function_exists('formatCompactNumber')) {
                                        function formatCompactNumber($number)
                                        {
                                            if ($number >= 1000000) {
                                                return round($number / 1000000, 1) . 'M';
                                            } elseif ($number >= 1000) {
                                                return round($number / 1000, 1) . 'K';
                                            } else {
                                                return $number;
                                            }
                                        }
                                    }
                                    ?>

                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                        <span><?= formatCompactNumber($item['download'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="items-center p-6 pt-0 flex space-x-3">
                                <!-- Download Button -->
                                <a href="<?= base_url('libraries/plugins/' . $item['slug'], APP_LANG) ?>" class="flex-1">
                                    <button class="inline-flex w-full items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 h-10 px-4 py-2 flex-1 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download mr-2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                        <?= _e('theme.download') ?>
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No Plugins Available -->
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">No plugins available</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- View All Plugins Button -->
        <div class="text-center mt-12">
            <a href="<?= base_url('libraries/plugins', APP_LANG) ?>">
                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-11 rounded-md px-8 border-purple-600 text-purple-600 hover:bg-purple-50">
                    <?= _e('theme.plugins') ?>
                </button>
            </a>
        </div>
    </div>
</section>
