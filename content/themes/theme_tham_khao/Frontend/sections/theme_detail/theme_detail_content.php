<?php

/**
 * Theme Detail Content Section
 * Displays theme description, features, and specifications
 */

use App\Libraries\Fastlang;

$theme = $data['theme'] ?? [];
?>

<!-- Content Section -->
<section class="bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Description -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.description'); ?></h2>
            <div class="prose prose-lg max-w-none">
                <?= $theme['content'] ?? $theme['description'] ?? '' ?>
            </div>
        </div>
        <?php if (!empty($theme['feature_detail'])): ?>
            <?php
            $featureDetail = json_decode($theme['feature_detail'], true);
            ?>
            <!-- Features -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.features'); ?></h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <?php foreach ($featureDetail as $feature): ?>
                        <div class="bg-gray-50 rounded-xl p-6 text-center">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mb-4 mx-auto" style="background-color: <?= $feature['bg_color'] ?? '#000' ?>; color: <?= $feature['color'] ?? '#000' ?>;">
                                <i data-feather="<?= $feature['icon'] ?? '' ?>" class="w-6 h-6"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900 mb-1"><?= $feature['title'] ?? '' ?></h3>
                            <p class="text-sm text-gray-600"><?= $feature['desc'] ?? '' ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <!-- Specifications -->
        <?php if (!empty($theme['specifications'])): ?>
            <?php
            $specifications = json_decode($theme['specifications'], true);
            ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.specifications'); ?></h2>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php foreach ($specifications as $specification): ?>
                            <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                <span class="font-medium text-gray-700"><?= $specification['title'] ?? '' ?></span>
                                <span class="text-gray-900"><?= $specification['desc'] ?? '' ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <!-- Screenshots -->
        <?php if (!empty($theme['screenshots'])): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.screenshots'); ?></h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($theme['screenshots'] as $index => $screenshot): ?>
                        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer"
                            onclick="openImageModal('<?= htmlspecialchars($screenshot) ?>', '<?= htmlspecialchars($theme['title']) ?> - Screenshot <?= $index + 1 ?>')">
                            <?= _img($screenshot, $theme['title'], true, 'w-full h-full object-cover') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Changelog -->
        <?php if (!empty($theme['changelog'])): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.changelog'); ?></h2>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="prose prose-sm max-w-none">
                        <?= $theme['changelog'] ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Requirements -->
        <?php if (!empty($theme['requirements'])): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.requirements'); ?></h2>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="prose prose-sm max-w-none">
                        <?= $theme['requirements'] ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Installation -->
        <?php if (!empty($theme['installation'])): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.installation'); ?></h2>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="prose prose-sm max-w-none">
                        <?= $theme['installation'] ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Compatibility -->
        <?php if (!empty($theme['compatibility'])): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6"><?= Fastlang::__('theme_detail.content.compatibility'); ?></h2>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="prose prose-sm max-w-none">
                        <?= $theme['compatibility'] ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>
