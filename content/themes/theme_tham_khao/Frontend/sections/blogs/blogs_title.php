<?php // Helper functions are auto-loaded 
?>
<section class="min-h-screen bg-gradient-to-br from-emerald-600 via-teal-700 to-cyan-800 text-white relative overflow-hidden flex items-center">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-20 h-20 bg-yellow-400/20 rounded-full animate-pulse"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-pink-400/20 rounded-full animate-pulse"
            style="animation-delay: 2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-green-400/20 rounded-full animate-pulse"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Main Content Container - Centered -->
    <div class="relative z-10 w-full py-20">
        <div class="container mx-auto px-4 text-center">
            <div class="max-w-4xl mx-auto space-y-8">

                <!-- Icon -->
                <div class="flex justify-center">
                    <div class="p-4 bg-white/20 rounded-full backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="lucide lucide-pen-tool text-yellow-300">
                            <path d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z"></path>
                            <path d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18"></path>
                            <path d="m2.3 2.3 7.286 7.286"></path>
                            <circle cx="11" cy="11" r="2"></circle>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-4xl lg:text-6xl font-bold leading-tight">
                    <?php _e('hero_blog_prefix') ?>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-orange-400">
                        <?php _e('hero_blog_highlight') ?>
                    </span><br>
                    <?php _e('hero_blog_suffix') ?>
                </h1>

                <!-- Description -->
                <p class="text-xl lg:text-2xl text-emerald-100 leading-relaxed max-w-3xl mx-auto">
                    <?php _e('hero_blog_description') ?>
                </p>

                <!-- Buttons -->
                <div class="flex flex-col md:flex-row justify-center items-center gap-4 sm:gap-6">
                    <a href="#hot" class="w-full sm:w-auto">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-12 px-8 bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                <polyline points="16 7 22 7 22 13"></polyline>
                            </svg>
                            <?php _e('button_hot_posts') ?>
                        </button>
                    </a>

                    <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 h-12 px-8 bg-transparent border-2 border-white text-white hover:bg-white hover:text-slate-900 font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M15.707 21.293a1 1 0 0 1-1.414 0l-1.586-1.586a1 1 0 0 1 0-1.414l5.586-5.586a1 1 0 0 1 1.414 0l1.586 1.586a1 1 0 0 1 0 1.414z"></path>
                            <path d="m18 13-1.375-6.874a1 1 0 0 0-.746-.776L3.235 2.028a1 1 0 0 0-1.207 1.207L5.35 15.879a1 1 0 0 0 .776.746L13 18"></path>
                            <path d="m2.3 2.3 7.286 7.286"></path>
                            <circle cx="11" cy="11" r="2"></circle>
                        </svg>
                        <?php _e('button_guide') ?>
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto pt-4">
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">
                            <?php _e('stats_posts_value') ?>
                        </div>
                        <div class="text-sm text-emerald-200 mt-1">
                            <?php _e('stats_posts_label') ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">
                            <?php _e('stats_reads_value') ?>
                        </div>
                        <div class="text-sm text-emerald-200 mt-1">
                            <?php _e('stats_reads_label') ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">
                            <?php _e('stats_comments_value') ?>
                        </div>
                        <div class="text-sm text-emerald-200 mt-1">
                            <?php _e('stats_comments_label') ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl md:text-3xl font-bold text-yellow-300">
                            <?php _e('stats_authors_value') ?>
                        </div>
                        <div class="text-sm text-emerald-200 mt-1">
                            <?php _e('stats_authors_label') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
