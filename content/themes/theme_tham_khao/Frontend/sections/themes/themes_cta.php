<?php

/**
 * Themes CTA Section
 * Displays call-to-action section
 */

use App\Libraries\Fastlang;

$ctaTitle = option('site_cta_title', APP_LANG) ?: Fastlang::__('themes.cta.title');
$ctaDescription = option('site_cta_description', APP_LANG) ?: Fastlang::__('themes.cta.description');
?>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold mb-4"><?= $ctaTitle ?></h2>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto"><?= $ctaDescription ?></p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-3"><?= Fastlang::__('themes.cta.design.title') ?></h3>
                <p class="text-blue-100"><?= Fastlang::__('themes.cta.design.description') ?></p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-3"><?= Fastlang::__('themes.cta.share.title') ?></h3>
                <p class="text-blue-100"><?= Fastlang::__('themes.cta.share.description') ?></p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-3"><?= Fastlang::__('themes.cta.monetize.title') ?></h3>
                <p class="text-blue-100"><?= Fastlang::__('themes.cta.monetize.description') ?></p>
            </div>
        </div>

        <div class="text-center mt-12 flex items-center justify-center gap-4">
            <a href="<?= base_url('libraries/themes', APP_LANG) ?>" class="inline-flex items-center px-4 md:px-8 py-2 md:py-4 bg-white text-blue-600 font-semibold rounded-xl hover:bg-gray-50 transition-colors mr-4">
                <?= Fastlang::__('themes.cta.start_designing') ?>
            </a>
            <a href="<?= base_url('guide', APP_LANG) ?>" class="inline-flex items-center px-4 md:px-8 py-2 md:py-4 border-2 border-white text-white font-semibold rounded-xl hover:bg-white hover:text-blue-600 transition-colors">
                <?= Fastlang::__('themes.cta.view_guide') ?>
            </a>
        </div>
    </div>
</section>
