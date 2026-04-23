<section id="hot" class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('hot_title') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">
                    <?php _e('hot_highlight') ?>
                </span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('hot_description') ?>
            </p>
        </div>

        <!-- Mobile horizontal scroll container -->
        <div class="overflow-x-auto md:overflow-visible pb-4 md:pb-0">
            <div class="flex gap-4 md:grid md:grid-cols-2 lg:grid-cols-3 min-w-max md:min-w-0">
                <?php
                // Use the top blogs by views data directly (already sorted by views DESC)
                $top_blogs = array_slice($blogs, 0, 3);

                if (empty($top_blogs)): ?>
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-500 text-lg mb-4">
                            <i class="fas fa-blog text-4xl text-gray-400 mb-4"></i>
                            <p><?php _e('no_blogs_found', 'No blogs found') ?></p>
                        </div>
                    </div>
                    <?php else:
                    foreach ($top_blogs as $blog): ?>
                        <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-200 overflow-hidden group flex-shrink-0 w-80 md:w-auto">
                            <div class="flex flex-col space-y-1.5 p-0 relative">
                                <?php if (!empty($blog['thumb_url'])): ?>
                                    <a href="<?php echo content_url('blogs', $blog['slug']) ?>">
                                        <?= _img(
                                            theme_assets(get_image_full($blog['thumb_url'])),
                                            $blog['title'],
                                            true,
                                            'w-full h-52 object-cover'
                                        ) ?>
                                    </a>
                                <?php else: ?>
                                    <div class="w-full h-52 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                        <i class="fas fa-blog text-6xl text-white opacity-50"></i>
                                    </div>
                                <?php endif; ?>

                                <div class="absolute top-3 left-3 flex items-center h-7">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 bg-red-500 text-white text-xs font-semibold h-full">
                                        <i class="fas fa-fire mr-1"></i>
                                        <?php _e('hot_badge', 'HOT') ?>
                                    </div>
                                </div>
                                <div class="absolute top-3 right-3 flex items-center h-7">
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 bg-yellow-500 text-slate-900 text-xs font-semibold h-full">
                                        <?php _e('badge_featured') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4">
                                <h3 class="text-xl font-bold text-slate-800 mb-3 group-hover:text-blue-700 transition-colors duration-300 line-clamp-2">
                                    <a href="<?php echo content_url('blogs', $blog['slug']) ?>">
                                        <?php echo htmlspecialchars($blog['title']) ?>
                                    </a>
                                </h3>
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span><?php echo htmlspecialchars($blog['author_name'] ?? ($blog['author'] ?? 'Admin')) ?></span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-calendar"></i>
                                        <?php
                                        $created_at = $blog['created_at'] ?? date('Y-m-d H:i:s');
                                        $day = date('j', strtotime($created_at));
                                        $month = (int)date('n', strtotime($created_at));
                                        $year = date('Y', strtotime($created_at));

                                        // Get month names from translation
                                        $month_names = __('month_names');
                                        $month_name = $month_names[$month] ?? 'month ' . $month;

                                        $date = $day . ' ' . $month_name . ', ' . $year;
                                        ?>
                                        <span><?php echo $date ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-500 mt-2">
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M2 12l10 10 10-10V2H2v10z" />
                                            <circle cx="7" cy="7" r="2" fill="currentColor" />
                                        </svg>
                                        <span><?php echo htmlspecialchars($blog['type_display'] ?? ucfirst($blog['type'] ?? 'General')) ?></span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-red-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye">
                                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span><?php echo $blog['formatted_views'] ?? format_views($blog['views'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center p-4 pt-0">
                                <a
                                    class="inline-flex items-center gap-2 text-sm hover:underline font-semibold text-blue-600 hover:text-blue-700 transition-all duration-300"
                                    href="<?php echo content_url('blogs', $blog['slug']) ?>">
                                    <?php _e('button_read_more') ?>
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</section>
