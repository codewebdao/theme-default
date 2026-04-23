<?php

use App\Libraries\Fastlang as Flang;

$product = $product ?? [];
$title = $title ?? '';
$featureImage = $featureImage ?? null;
$ratingAvg = $ratingAvg ?? 0;
$ratingCount = $ratingCount ?? 0;
$productCategories = $productCategories ?? [];
?>

<div class="relative bg-gradient-to-r from-blue-900 via-purple-900 to-indigo-900 text-white overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/30 to-purple-600/30"></div>
        <svg class="absolute bottom-0 left-0 w-full h-32" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"
                fill="rgba(255,255,255,0.1)"></path>
        </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="space-y-4">
            <!-- Breadcrumb -->
            <nav class="flex items-center flex-wrap gap-x-2 gap-y-1 text-xs sm:text-sm text-blue-200">
                <a href="<?= base_url('', APP_LANG) ?>" class="hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9,22 9,12 15,12 15,22"></polyline>
                    </svg> <?= Flang::__('home', 'CMS') ?>
                </a>
                <span>/</span>
                <a href="<?= base_url('products', APP_LANG) ?>" class="hover:text-white transition-colors">
                    <?= Flang::__('products', 'Ecommerce') ?>
                </a>
                <?php if (!empty($productCategories)): ?>
                    <?php foreach ($productCategories as $index => $category): ?>
                        <span>/</span>
                        <a href="<?= link_category($category['slug'] ?? '', 'products', APP_LANG) ?>" class="hover:text-white transition-colors">
                            <?= htmlspecialchars($category['name'] ?? '') ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <span>/</span>
                <span class="text-white truncate max-w-[200px] sm:max-w-xs block" title="<?= htmlspecialchars($title) ?>">
                    <?= htmlspecialchars($title) ?>
                </span>
            </nav>

            <!-- Product Title -->
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold leading-tight">
                <?= htmlspecialchars($title) ?>
            </h1>

            <!-- Product Meta Information -->
            <div class="flex flex-wrap items-center gap-4 lg:gap-6 text-blue-200 text-sm">
                <!-- Rating -->
                <?php if ($ratingAvg > 0): ?>
                    <div class="flex items-center gap-2">
                        <div class="flex text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $ratingAvg): ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                                    </svg>
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                                        <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                                    </svg>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span>(<?= number_format($ratingCount) ?>)</span>
                    </div>
                <?php endif; ?>

                <!-- SKU -->
                <?php if (!empty($product['sku'])): ?>
                    <div class="flex items-center gap-2">
                        <span class="text-blue-300"><?= Flang::__('sku', 'Ecommerce') ?>:</span>
                        <span class="font-semibold"><?= htmlspecialchars($product['sku']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

