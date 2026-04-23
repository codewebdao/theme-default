<section class="py-16 md:py-24 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-slate-800 mb-4">
                <?= _e('stats_section.title.before') ?> <span
                    class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-purple-600"><?= _e('stats_section.title.highlight') ?></span>
            </h2>
            <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                <?= _e('stats_section.description') ?>
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-blue-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download text-white">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">2.5M+</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.total_downloads.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.total_downloads.description') ?></p>
                </div>
            </div>

            <div class="rounded-lg bg-gradient-to-br from-green-50 to-emerald-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-green-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users text-white">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">15,000+</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.active_developers.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.active_developers.description') ?></p>
                </div>
            </div>

            <div class="rounded-lg bg-gradient-to-br from-yellow-50 to-amber-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-yellow-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-yellow-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-white">
                            <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">4.8/5</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.average_rating.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.average_rating.description') ?></p>
                </div>
            </div>

            <div class="rounded-lg bg-gradient-to-br from-purple-50 to-violet-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-purple-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award text-white">
                            <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"></path>
                            <circle cx="12" cy="8" r="6"></circle>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">2,500+</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.quality_products.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.quality_products.description') ?></p>
                </div>
            </div>

            <div class="rounded-lg bg-gradient-to-br from-orange-50 to-amber-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-orange-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-orange-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up text-white">
                            <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                            <polyline points="16 7 22 7 22 13"></polyline>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">+25%</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.monthly_growth.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.monthly_growth.description') ?></p>
                </div>
            </div>

            <div class="rounded-lg bg-gradient-to-br from-teal-50 to-cyan-100 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-teal-200">
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-teal-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe text-white">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                            <path d="M2 12h20"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">180+</div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2"><?= _e('stats_section.global_reach.title') ?></h3>
                    <p class="text-slate-600 text-sm"><?= _e('stats_section.global_reach.description') ?></p>
                </div>
            </div>
        </div>

        <div class="mt-16 bg-gradient-to-br from-slate-50 to-blue-50 rounded-2xl p-8 border border-slate-200">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-slate-800 mb-4"><?= _e('stats_section.community.heading') ?></h3>
                <p class="text-slate-600 mb-6 max-w-2xl mx-auto"><?= _e('stats_section.community.description') ?></p>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 gap-2">
                    <div class="bg-white rounded-lg p-4 shadow-md">
                        <div class="text-2xl font-bold text-blue-600">500+</div>
                        <div class="text-sm text-slate-600"><?= _e('stats_section.community.contributors.label') ?></div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-md">
                        <div class="text-2xl font-bold text-green-600">1,200+</div>
                        <div class="text-sm text-slate-600"><?= _e('stats_section.community.commits.label') ?></div>
                    </div>
                    <div class="bg-white rounded-lg p-4 shadow-md">
                        <div class="text-2xl font-bold text-purple-600">50+</div>
                        <div class="text-sm text-slate-600"><?= _e('stats_section.community.releases.label') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
