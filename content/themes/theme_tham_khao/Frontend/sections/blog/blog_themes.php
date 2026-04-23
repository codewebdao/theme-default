<section class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12"><svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="lucide lucide-palette mx-auto text-pink-600 mb-4">
                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                <path
                    d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z">
                </path>
            </svg>
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-3">
                <?php _e('themes_title') ?>
            </h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                <?php _e('themes_description') ?>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

            <?php foreach ($themes as $theme) : ?>
                <div
                    class="rounded-lg bg-card text-card-foreground shadow-xl hover:shadow-2xl transition-shadow duration-300 border-0 overflow-hidden group">
                    <div class="relative overflow-hidden"><img src="<?= theme_assets('images/themes/' . $theme['thumbnail_url']) ?>"
                            alt="<?= $theme['title'] ?>"
                            class="w-full h-60 object-cover group-hover:scale-105 transition-transform duration-300">
                        <div
                            class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors duration-300">
                        </div>
                        <div
                            class="absolute top-4 right-4 bg-pink-600 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <?= (empty($theme['price']) || $theme['price'] == 0) ? 'Free' : '$' . number_format($theme['price']) ?></div>
                    </div>
                    <div class="flex flex-col space-y-1.5 p-6">
                        <div class="tracking-tight text-xl font-semibold text-slate-800 mb-1"><?= $theme['title'] ?></div>

                    </div>
                    <div class="p-6 pt-0">
                        <p class="text-sm text-slate-600 mb-4 min-h-[60px]"><?= $theme['description'] ?></p>
                        <div class="flex flex-wrap gap-2 mb-6">
                            <?php foreach (explode(',', $theme['tags']) as $tag): ?>
                                <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded-full">
                                    <?= htmlspecialchars(mb_convert_case(trim($tag), MB_CASE_TITLE, "UTF-8")) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>

                        <a href="<?= content_url('theme', $theme['slug']) ?>"><button
                                class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 border bg-background hover:text-accent-foreground h-10 px-4 py-2 w-full border-pink-600 text-pink-600 hover:bg-pink-50"><?php _e('details') ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
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
                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0  h-11 rounded-md px-8 bg-gradient-to-r from-pink-600 to-rose-700 text-white hover:opacity-90 transition-opacity"><?php _e('more_themes') ?><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-2">
                        <path d="M5 12h14"></path>
                        <path d="m12 5 7 7-7 7"></path>
                    </svg></button></a></div>
    </div>
</section>
