<!-- Comprehensive CMS Solution for Website -->
<section id="features" class="py-16 md:py-24 bg-slate-50">
    <div class="container mx-auto px-4">

        <div class="text-center mb-16">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?php _e('feature.cms_solution_part1') ?> 
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                    <?php _e('feature.cms_solution_highlight') ?>
                </span> 
                <?php _e('feature.cms_solution_part2') ?>
            </h1>
            <p class="mt-4 text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?php _e('feature.cms_solution_desc') ?>
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-layers text-white">
                                <path
                                    d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z">
                                </path>
                                <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"></path>
                                <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"></path>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_posttype_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_posttype_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>


            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-sparkles text-white">
                                <path
                                    d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z">
                                </path>
                                <path d="M20 3v4"></path>
                                <path d="M22 5h-4"></path>
                                <path d="M4 17v2"></path>
                                <path d="M5 18H3"></path>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_ai_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_ai_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>


            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shield-check text-white">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                </path>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_security_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_security_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>

            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-zap text-white">
                                <path
                                    d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z">
                                </path>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_cache_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_cache_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>


            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-settings2 text-white">
                                <path d="M20 7h-9"></path>
                                <path d="M14 17H5"></path>
                                <circle cx="17" cy="17" r="3"></circle>
                                <circle cx="7" cy="7" r="3"></circle>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_dashboard_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_dashboard_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>

            <div
                class="bg-card text-card-foreground bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 rounded-xl overflow-hidden group transform hover:-translate-y-2 border border-blue-200">
                <div class="flex flex-col space-y-1.5 p-6">
                    <div class="flex items-center space-x-4">
                        <div
                            class="p-3 rounded-lg bg-blue-600 shadow-md group-hover:shadow-lg transition-shadow duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-layout-grid text-white">
                                <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                                <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                                <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                                <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                            </svg>
                        </div>
                        <div
                            class="tracking-tight text-xl font-semibold text-slate-800 group-hover:text-blue-700 transition-colors duration-300">
                            <?php _e('feature_marketplace_title') ?>
                        </div>
                    </div>
                </div>
                <div class="p-6 pt-0">
                    <p class="text-slate-600 mb-6 text-base"><?php _e('feature_marketplace_desc') ?></p>
                    <a href="<?= base_url('download') ?>">
                        <button
                            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 underline-offset-4 hover:underline h-10 text-blue-600 p-0 hover:text-indigo-700 font-semibold group-hover:translate-x-1 transition-all duration-300">
                            <?php _e('Experience Now') ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-right ml-1">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </button>
                    </a>
                </div>
            </div>


        </div>
    </div>
</section>