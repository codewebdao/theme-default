  <section class="py-16 md:py-24 bg-slate-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
         stroke-linejoin="round" class="lucide lucide-puzzle mx-auto text-blue-600 mb-4">
        <path
            d="M19.439 7.85c-.049.322.059.648.289.878l1.568 1.568c.47.47.706 1.087.706 1.704s-.235 1.233-.706 1.704l-1.611 1.611a.98.98 0 0 1-.837.276c-.47-.07-.802-.48-.968-.925a2.501 2.501 0 1 0-3.214 3.214c.446.166.855.497.925.968a.979.979 0 0 1-.276.837l-1.61 1.61a2.404 2.404 0 0 1-1.705.707 2.402 2.402 0 0 1-1.704-.706l-1.568-1.568a1.026 1.026 0 0 0-.877-.29c-.493.074-.84.504-1.02.968a2.5 2.5 0 1 1-3.237-3.237c.464-.18.894-.527.967-1.02a1.026 1.026 0 0 0-.289-.877l-1.568-1.568A2.402 2.402 0 0 1 1.998 12c0-.617.236-1.234.706-1.704L4.23 8.77c.24-.24.581-.353.917-.303.515.077.877.528 1.073 1.01a2.5 2.5 0 1 0 3.259-3.259c-.482-.196-.933-.558-1.01-1.073-.05-.336.062-.676.303-.917l1.525-1.525A2.402 2.402 0 0 1 12 1.998c.617 0 1.234.236 1.704.706l1.568 1.568c.23.23.556.338.877.29.493-.074.84-.504 1.02-.968a2.5 2.5 0 1 1 3.237 3.237c-.464.18-.894.527-.967 1.02Z">
        </path>
    </svg>
    <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-3">
        <?php _e('featured_plugins_title') ?>
    </h2>
    <p class="text-lg text-slate-600 max-w-2xl mx-auto">
        <?php _e('featured_plugins_description') ?>
    </p>
</div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <?php foreach ($plug as $item): ?>
                        <div
                            class="rounded-lg bg-card shadow-xl hover:shadow-2xl transition-shadow duration-300 border-0 overflow-hidden bg-gradient-to-br from-green-500 to-emerald-600 text-white">
                            <div class="space-y-1.5 flex flex-row items-start space-x-4 p-6"><img
                                    src="image/mcpiPCgnuHVnSKfT0ifHuyH55Tc.png.png" alt="<?= $item['title'] ?>"
                                    class="w-16 h-16 rounded-lg bg-white/20 p-2">
                                <div>
                                    <div class="tracking-tight text-xl font-semibold"><?= $item['title'] ?></div>
                                    <p class="text-sm opacity-80"><?php _e('version') ?>: <!-- -->2.5.1<!-- --> | <!-- --><?= format_views($item['dowload'] ?? 0) ?><!-- -->
                                        <?php _e('downloads') ?></p>
                                </div>
                            </div>
                            <div class="p-6 pt-0">
                                <p class="text-sm mb-6 opacity-90 min-h-[60px]"><?= $item['content'] ?></p><a href="<?= base_url('download') ?>"><button
                                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border hover:text-accent-foreground h-10 px-4 py-2 w-full bg-white/10 border-white/30 hover:bg-white/20 text-white">
                                        <?php _e('details') ?><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-arrow-right ml-2">
                                            <path d="M5 12h14"></path>
                                            <path d="m12 5 7 7-7 7"></path>
                                        </svg></button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-12"><a href="<?= base_url('libraries') ?>"><button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-11 rounded-md px-8 bg-gradient-to-r from-blue-600 to-indigo-700 text-white hover:opacity-90 transition-opacity"><?php _e('more_plugins') ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-2">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg></button></a></div>
            </div>
        </section>