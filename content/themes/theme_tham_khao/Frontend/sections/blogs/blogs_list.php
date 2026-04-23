<section class="py-16 md:py-24 bg-slate-50 lg:hidden">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('categories_title') ?>
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-teal-600 to-cyan-600">
                    <?php _e('categories_highlight') ?>
                </span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('categories_description') ?>
            </p>
        </div>

        <!-- Mobile horizontal scroll container -->
        <div class="overflow-x-auto md:overflow-visible pb-4 md:pb-0">
            <div class="flex gap-6 md:grid md:grid-cols-2 lg:grid-cols-4 min-w-max md:min-w-0">
                <!-- Performance -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 p-6 shadow-xl hover:shadow-2xl border border-blue-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-zap text-white">
                                <path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_performance_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_performance_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_performance_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Tutorials -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-green-50 to-emerald-100 p-6 shadow-xl hover:shadow-2xl border border-green-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-book-open text-white">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_tutorials_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_tutorials_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_tutorials_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-red-50 to-pink-100 p-6 shadow-xl hover:shadow-2xl border border-red-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-red-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shield text-white">
                                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_security_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_security_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_security_count') ?>
                        </div>
                    </div>
                </div>

                <!-- AI & Tech -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-purple-50 to-violet-100 p-6 shadow-xl hover:shadow-2xl border border-purple-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-cpu text-white">
                                <rect width="16" height="16" x="4" y="4" rx="2"></rect>
                                <rect width="6" height="6" x="9" y="9" rx="1"></rect>
                                <path d="M15 2v2"></path>
                                <path d="M15 20v2"></path>
                                <path d="M2 15h2"></path>
                                <path d="M2 9h2"></path>
                                <path d="M20 15h2"></path>
                                <path d="M20 9h2"></path>
                                <path d="M9 2v2"></path>
                                <path d="M9 20v2"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_ai_tech_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_ai_tech_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_ai_tech_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Design -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-pink-50 to-rose-100 p-6 shadow-xl hover:shadow-2xl border border-pink-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-pink-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-palette text-white">
                                <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"></circle>
                                <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"></circle>
                                <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"></circle>
                                <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"></circle>
                                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_design_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_design_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_design_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Community -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-orange-50 to-amber-100 p-6 shadow-xl hover:shadow-2xl border border-orange-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-orange-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-users text-white">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_community_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_community_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_community_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Business -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-teal-50 to-cyan-100 p-6 shadow-xl hover:shadow-2xl border border-teal-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-teal-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-trending-up text-white">
                                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                <polyline points="16 7 22 7 22 13"></polyline>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_business_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_business_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_business_count') ?>
                        </div>
                    </div>
                </div>

                <!-- Development -->
                <div class="rounded-lg bg-card text-card-foreground bg-gradient-to-br from-slate-50 to-gray-100 p-6 shadow-xl hover:shadow-2xl border border-slate-200 transition-all duration-300 hover:-translate-y-2 cursor-pointer group flex-shrink-0 w-72 md:w-auto">
                    <div class="p-0 text-center">
                        <div class="w-16 h-16 bg-slate-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <!-- SVG icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-code text-white">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-teal-700 transition-colors duration-300">
                            <?php _e('cat_development_name') ?>
                        </h3>
                        <p class="text-slate-600 text-sm mb-3">
                            <?php _e('cat_development_desc') ?>
                        </p>
                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold transition-colors text-foreground text-xs">
                            <?php _e('cat_development_count') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <div class="mt-12 text-center">
            <div class="bg-white rounded-xl p-8 shadow-lg max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold text-slate-800 mb-4">
                    <?php _e('categories_not_found_title') ?>
                </h3>
                <p class="text-slate-600 mb-6">
                    <?php _e('categories_not_found_desc') ?>
                </p>
                <div class="flex flex-col md:flex-row justify-center gap-2">
                    <input
                        placeholder="<?php _e('categories_request_input_placeholder') ?>"
                        class="px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                        type="text">
                    <button class="bg-gradient-to-r from-teal-600 to-cyan-700 text-white px-6 py-2 rounded-lg hover:from-teal-700 hover:to-cyan-800 transition-all">
                        <?php _e('categories_request_submit') ?>
                    </button>
                </div>
            </div>
        </div> -->
    </div>
</section>
