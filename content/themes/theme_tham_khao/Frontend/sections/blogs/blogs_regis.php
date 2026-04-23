<section class="py-16 md:py-24 bg-gradient-to-br from-indigo-600 via-purple-700 to-pink-800 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="flex justify-center mb-6">
                <div class="p-4 bg-white/20 rounded-full backdrop-blur-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-mail text-yellow-300">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6">
                <?php _e('newsletter_title') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-yellow-400 to-orange-400">
                    <?php _e('newsletter_highlight') ?>
                </span>
            </h2>
            <p class="text-xl text-indigo-100 mb-8 leading-relaxed">
                <?php _e('newsletter_description') ?>
            </p>

            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="rounded-lg border text-card-foreground shadow-sm bg-white/10 backdrop-blur-sm border-white/20">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-bell mx-auto text-yellow-300 mb-4">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                        </svg>
                        <h3 class="text-lg font-semibold mb-2">
                            <?php _e('newsletter_card_weekly_title') ?>
                        </h3>
                        <p class="text-sm text-indigo-200">
                            <?php _e('newsletter_card_weekly_desc') ?>
                        </p>
                    </div>
                </div>

                <div class="rounded-lg border text-card-foreground shadow-sm bg-white/10 backdrop-blur-sm border-white/20">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-gift mx-auto text-yellow-300 mb-4">
                            <rect x="3" y="8" width="18" height="4" rx="1"></rect>
                            <path d="M12 8v13"></path>
                            <path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path>
                            <path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"></path>
                        </svg>
                        <h3 class="text-lg font-semibold mb-2">
                            <?php _e('newsletter_card_exclusive_title') ?>
                        </h3>
                        <p class="text-sm text-indigo-200">
                            <?php _e('newsletter_card_exclusive_desc') ?>
                        </p>
                    </div>
                </div>

                <div class="rounded-lg border text-card-foreground shadow-sm bg-white/10 backdrop-blur-sm border-white/20">
                    <div class="p-6 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round"
                            class="lucide lucide-mail mx-auto text-yellow-300 mb-4">
                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                        <h3 class="text-lg font-semibold mb-2">
                            <?php _e('newsletter_card_early_title') ?>
                        </h3>
                        <p class="text-sm text-indigo-200">
                            <?php _e('newsletter_card_early_desc') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- <div class="max-w-2xl mx-auto">
                <div class="flex flex-col md:flex-row justify-center gap-4">
                    <input
                        placeholder="<?php _e('newsletter_input_placeholder') ?>"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        type="email">
                    <button
                        class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&amp;_svg]:pointer-events-none [&amp;_svg]:size-4 [&amp;_svg]:shrink-0 h-11 rounded-md bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 px-8">
                        <?php _e('newsletter_register_button') ?>
                    </button>
                </div>
                <p class="text-sm text-indigo-200 mt-4">
                    <?php _e('newsletter_terms_prefix') ?>
                    <a href="#" class="text-yellow-300 hover:underline"><?php _e('newsletter_terms_tos') ?></a>
                    <?php _e('newsletter_terms_and') ?>
                    <a href="#" class="text-yellow-300 hover:underline"><?php _e('newsletter_terms_privacy') ?></a>
                    <?php _e('newsletter_terms_suffix') ?>
                </p>
            </div> -->

            <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6 max-w-2xl mx-auto">
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-300">
                        <?php _e('stats_subscribers_value') ?>
                    </div>
                    <div class="text-sm text-indigo-200">
                        <?php _e('stats_subscribers_label') ?>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-300">
                        <?php _e('stats_open_rate_value') ?>
                    </div>
                    <div class="text-sm text-indigo-200">
                        <?php _e('stats_open_rate_label') ?>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-300">
                        <?php _e('stats_frequency_value') ?>
                    </div>
                    <div class="text-sm text-indigo-200">
                        <?php _e('stats_frequency_label') ?>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-300">
                        <?php _e('stats_forever_value') ?>
                    </div>
                    <div class="text-sm text-indigo-200">
                        <?php _e('stats_forever_label') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
