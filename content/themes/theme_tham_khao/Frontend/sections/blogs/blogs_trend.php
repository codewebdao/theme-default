<?php

// Use trending blogs data passed from blogs.php
// If no data is passed, use empty array as fallback
$trending_blogs = $trending_blogs ?? [];

?>

<section class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-orange-600 to-red-600">
                    <?php _e('trending_title_prefix') ?>
                </span>
                <?php _e('trending_title_suffix') ?>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('trending_description') ?>
            </p>
        </div>

        <!-- Mobile horizontal scroll container -->
        <div class="overflow-x-auto md:overflow-visible pb-4 md:pb-0">
            <div class="flex gap-4 md:grid md:grid-cols-2 lg:grid-cols-3 min-w-max md:min-w-0">
                <?php if (!empty($trending_blogs)): ?>
                    <?php foreach ($trending_blogs as $index => $blog):
                        // Define colors for each card
                        $gradients = [
                            'from-blue-50 to-indigo-100 border-blue-200',
                            'from-purple-50 to-violet-100 border-purple-200',
                            'from-green-50 to-emerald-100 border-green-200',
                            'from-orange-50 to-amber-100 border-orange-200',
                            'from-teal-50 to-cyan-100 border-teal-200'
                        ];
                        $categoryColors = [
                            'bg-blue-500',
                            'bg-purple-500',
                            'bg-blue-500',
                            'bg-green-500',
                            'bg-green-500'
                        ];
                        $gradient = $gradients[$index] ?? $gradients[0];
                        $categoryColor = $categoryColors[$index] ?? $categoryColors[0];
                    ?>
                        <!-- Card <?= $index + 1 ?> -->
                        <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br <?= $gradient ?> shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group cursor-pointer flex-shrink-0 w-80 md:w-auto">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            <?= $blog['trending_rank'] ?? $index + 1 ?>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up text-orange-500">
                                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                            <polyline points="16 7 22 7 22 13"></polyline>
                                        </svg>
                                    </div>
                                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent bg-orange-500 text-white text-xs">
                                        +<?= $blog['views_week_formatted'] ?? format_views($blog['views_week'] ?? 0) ?>
                                    </div>
                                </div>
                                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent text-xs font-semibold mb-3 <?= $categoryColor ?> text-white">
                                    <?= htmlspecialchars($blog['type_display'] ?? ucfirst($blog['type'] ?? 'Trending')) ?>
                                </div>
                                <h3 class="text-lg font-bold mb-4 group-hover:text-blue-700 transition-colors duration-300 line-clamp-2">
                                    <a href="<?= content_url('blogs', $blog['slug'] ?? '') ?>">
                                        <?= htmlspecialchars($blog['title'] ?? 'Untitled') ?>
                                    </a>
                                </h3>
                                <div class="flex items-center justify-between text-sm text-slate-500">
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye">
                                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span><?= $blog['views_week_formatted'] ?? format_views($blog['views_week'] ?? 0) ?></span>
                                    </div>
                                    <div class="flex items-center space-x-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle">
                                            <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                        </svg>
                                        <span><?= $blog['rating_count'] ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback content when no trending blogs are available -->
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-500 text-lg mb-4">
                            <i class="fas fa-fire text-4xl text-gray-400 mb-4"></i>
                            <p><?php _e('no_trending_blogs_found', 'No trending blogs found this week') ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
