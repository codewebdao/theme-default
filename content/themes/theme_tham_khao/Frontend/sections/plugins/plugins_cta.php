<?php

/**
 * CTA Section for Plugins Library
 */

use App\Libraries\Fastlang;
?>
<section class="py-20 bg-gradient-to-br from-purple-600 via-pink-600 to-indigo-700 text-white relative overflow-hidden">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="absolute top-0 left-0 w-full h-full">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-300/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
    </div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6"><?= Fastlang::__('plugins.cta.title') ?></h2>
            <p class="text-xl mb-12 text-purple-100 leading-relaxed"><?= Fastlang::__('plugins.cta.description') ?></p>

            <!-- CTA Features Grid -->
            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-code text-white">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3"><?= Fastlang::__('plugins.cta.develop.title') ?></h3>
                    <p class="text-purple-100 text-sm leading-relaxed"><?= Fastlang::__('plugins.cta.develop.description') ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-share text-white">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                            <polyline points="16 6 12 2 8 6"></polyline>
                            <line x1="12" x2="12" y1="2" y2="15"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3"><?= Fastlang::__('plugins.cta.share.title') ?></h3>
                    <p class="text-purple-100 text-sm leading-relaxed"><?= Fastlang::__('plugins.cta.share.description') ?></p>
                </div>
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                    <div class="flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-dollar-sign text-white">
                            <line x1="12" x2="12" y1="2" y2="22"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3"><?= Fastlang::__('plugins.cta.monetize.title') ?></h3>
                    <p class="text-purple-100 text-sm leading-relaxed"><?= Fastlang::__('plugins.cta.monetize.description') ?></p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo base_url('community', APP_LANG); ?>" class="bg-white text-purple-600 hover:bg-purple-50 px-8 py-4 text-lg font-semibold rounded-xl flex items-center justify-center gap-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rocket">
                        <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                        <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                        <path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path>
                        <path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path>
                    </svg>
                    <?= Fastlang::__('plugins.cta.start_developing') ?>
                </a>
                <a href="<?php echo base_url('docs/plugin-development', APP_LANG); ?>" class="border border-white/30 text-white hover:bg-white/10 px-8 py-4 text-lg font-semibold rounded-xl backdrop-blur-sm bg-transparent transition-colors">
                    <?= Fastlang::__('plugins.cta.view_guide') ?>
                </a>
            </div>
        </div>
    </div>
</section>
