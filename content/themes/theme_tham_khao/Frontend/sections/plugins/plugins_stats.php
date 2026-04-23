<?php

/**
 * Stats Section for Plugins Library
 */

use App\Libraries\Fastlang;
?>
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4"><?= Fastlang::__('plugins.stats.title') ?></h2>
            <p class="text-xl text-slate-600 max-w-2xl mx-auto"><?= Fastlang::__('plugins.stats.description') ?></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center group hover:scale-105 transition-transform duration-300">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download text-purple-600">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" x2="12" y1="15" y2="3"></line>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-2"><?= Fastlang::__('plugins.stats.total') ?></div>
                <div class="text-lg font-semibold text-slate-700 mb-1"><?= Fastlang::__('plugins.stats.available.title') ?></div>
                <div class="text-sm text-slate-500"><?= Fastlang::__('plugins.stats.available.description') ?></div>
            </div>
            <div class="text-center group hover:scale-105 transition-transform duration-300">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-pink-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users text-pink-600">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-2">50K+</div>
                <div class="text-lg font-semibold text-slate-700 mb-1"><?= Fastlang::__('plugins.stats.developers.title') ?></div>
                <div class="text-sm text-slate-500"><?= Fastlang::__('plugins.stats.developers.description') ?></div>
            </div>
            <div class="text-center group hover:scale-105 transition-transform duration-300">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up text-indigo-600">
                        <polyline points="22 12 18 8 13 12 9 8 2 12"></polyline>
                        <polyline points="22 6 22 12 16 12"></polyline>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-2">2M+</div>
                <div class="text-lg font-semibold text-slate-700 mb-1"><?= Fastlang::__('plugins.stats.downloads.title') ?></div>
                <div class="text-sm text-slate-500"><?= Fastlang::__('plugins.stats.downloads.description') ?></div>
            </div>
            <div class="text-center group hover:scale-105 transition-transform duration-300">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-50 rounded-2xl mb-4 group-hover:scale-110 transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-yellow-600">
                        <path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-slate-800 mb-2">4.8</div>
                <div class="text-lg font-semibold text-slate-700 mb-1"><?= Fastlang::__('plugins.stats.rating.title') ?></div>
                <div class="text-sm text-slate-500"><?= Fastlang::__('plugins.stats.rating.description') ?></div>
            </div>
        </div>
    </div>
</section>
