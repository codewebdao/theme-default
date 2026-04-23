<?php

use App\Libraries\Fastlang as Flang;

$relatedProducts = $relatedProducts ?? [];
?>

<?php if (!empty($relatedProducts)): ?>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="space-y-8">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900">
                <?= Flang::__('related_products', 'Ecommerce') ?>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <?php
                    $relatedId = $relatedProduct['id'] ?? 0;
                    $relatedTitle = $relatedProduct['title'] ?? '';
                    $relatedSlug = $relatedProduct['slug'] ?? '';
                    $relatedImage = $relatedProduct['feature'] ?? null;
                    $relatedPriceRegular = (float)($relatedProduct['price_regular'] ?? 0);
                    $relatedPriceSale = (float)($relatedProduct['price_sale'] ?? 0);
                    $relatedDisplayPrice = $relatedPriceSale > 0 ? $relatedPriceSale : $relatedPriceRegular;
                    $relatedCurrency = $relatedProduct['currency'] ?? 'USD';
                    $relatedStockStatus = $relatedProduct['stock_status'] ?? 'in_stock';
                    
                    $relatedImageUrl = null;
                    if (!empty($relatedImage)) {
                        load_helpers(['images']);
                        $relatedImageUrl = _img_url($relatedImage, 'medium');
                    }
                    ?>
                    <a href="<?= link_posts($relatedSlug, 'products', APP_LANG) ?>" 
                       class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                        <div class="relative aspect-square overflow-hidden bg-gray-100">
                            <?php if ($relatedImageUrl): ?>
                                <img 
                                    src="<?= htmlspecialchars($relatedImageUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                    alt="<?= htmlspecialchars($relatedTitle, ENT_QUOTES, 'UTF-8') ?>"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                                        <circle cx="9" cy="9" r="2"></circle>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($relatedStockStatus !== 'in_stock'): ?>
                                <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                    <?= Flang::__('out_of_stock', 'Ecommerce') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="p-4 space-y-2">
                            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                                <?= htmlspecialchars($relatedTitle) ?>
                            </h3>
                            <div class="flex items-baseline gap-2">
                                <?php if ($relatedPriceSale > 0 && $relatedPriceSale < $relatedPriceRegular): ?>
                                    <span class="text-lg font-bold text-gray-900">
                                        <?= ec_format_price($relatedPriceSale, ['currency' => $relatedCurrency]) ?>
                                    </span>
                                    <span class="text-sm text-gray-500 line-through">
                                        <?= ec_format_price($relatedPriceRegular, ['currency' => $relatedCurrency]) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-gray-900">
                                        <?= ec_format_price($relatedDisplayPrice, ['currency' => $relatedCurrency]) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

