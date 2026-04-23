<?php

use App\Libraries\Fastlang as Flang;

$product = $product ?? [];
$title = $title ?? '';
$description = $description ?? '';
$sku = $sku ?? '';
$priceRegular = $priceRegular ?? 0;
$priceSale = $priceSale ?? 0;
$displayPrice = $displayPrice ?? 0;
$hasSale = $hasSale ?? false;
$discountPercent = $discountPercent ?? 0;
$currency = $currency ?? 'USD';
$stockQuantity = $stockQuantity ?? 0;
$stockStatus = $stockStatus ?? 'in_stock';
$stockEnable = $stockEnable ?? false;
$productType = $productType ?? 'simple';
$isVirtual = $isVirtual ?? false;
$isDownloadable = $isDownloadable ?? false;
$hasVariations = $hasVariations ?? false;
$variations = $variations ?? [];
$vendor = $vendor ?? null;
$ratingAvg = $ratingAvg ?? 0;
$ratingCount = $ratingCount ?? 0;

$isInStock = $stockStatus === 'in_stock' && (!$stockEnable || $stockQuantity > 0);
$canPurchase = $isInStock && $displayPrice > 0;
?>

<div class="space-y-6" x-data="productDetail()">
    <!-- Price -->
    <div class="space-y-2">
        <div class="flex items-baseline gap-3">
            <?php if ($hasSale): ?>
                <span class="text-3xl lg:text-4xl font-bold text-gray-900">
                    <?= ec_format_price($priceSale, ['currency' => $currency]) ?>
                </span>
                <span class="text-xl text-gray-500 line-through">
                    <?= ec_format_price($priceRegular, ['currency' => $currency]) ?>
                </span>
                <?php if ($discountPercent > 0): ?>
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-sm font-semibold">
                        -<?= $discountPercent ?>%
                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-3xl lg:text-4xl font-bold text-gray-900">
                    <?= ec_format_price($displayPrice, ['currency' => $currency]) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stock Status -->
    <div class="space-y-2">
        <?php if ($stockEnable): ?>
            <?php if ($isInStock): ?>
                <div class="flex items-center gap-2 text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22,4 12,14.01 9,11.01"></polyline>
                    </svg>
                    <span class="font-semibold"><?= Flang::__('in_stock', 'Ecommerce') ?></span>
                    <?php if ($stockQuantity > 0): ?>
                        <span class="text-sm text-gray-600">(<?= number_format($stockQuantity) ?> <?= Flang::__('available', 'Ecommerce') ?>)</span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="font-semibold"><?= Flang::__('out_of_stock', 'Ecommerce') ?></span>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="flex items-center gap-2 text-gray-700">
                <span class="font-semibold"><?= Flang::__('in_stock', 'Ecommerce') ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Short Description -->
    <?php if (!empty($description)): ?>
        <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($description)) ?></p>
        </div>
    <?php endif; ?>

    <!-- Variations (for Variable Products) -->
    <?php if ($hasVariations && !empty($variations)): ?>
        <div class="space-y-4 border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-900"><?= Flang::__('select_options', 'Ecommerce') ?></h3>
            
            <?php
            // Group variations by attributes
            $attributes = [];
            foreach ($variations as $variation) {
                $variationAttrs = $variation['attributes'] ?? [];
                foreach ($variationAttrs as $attr) {
                    $attrName = $attr['name'] ?? '';
                    $attrValue = $attr['value'] ?? '';
                    if (!empty($attrName) && !empty($attrValue)) {
                        if (!isset($attributes[$attrName])) {
                            $attributes[$attrName] = [];
                        }
                        if (!in_array($attrValue, $attributes[$attrName])) {
                            $attributes[$attrName][] = $attrValue;
                        }
                    }
                }
            }
            ?>

            <?php foreach ($attributes as $attrName => $attrValues): ?>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        <?= htmlspecialchars($attrName) ?>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($attrValues as $attrValue): ?>
                            <button 
                                type="button"
                                @click="selectAttribute('<?= htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8') ?>')"
                                class="px-4 py-2 border-2 rounded-lg text-sm font-medium transition-all"
                                :class="selectedAttributes['<?= htmlspecialchars($attrName, ENT_QUOTES, 'UTF-8') ?>'] === '<?= htmlspecialchars($attrValue, ENT_QUOTES, 'UTF-8') ?>' 
                                    ? 'border-blue-500 bg-blue-50 text-blue-700' 
                                    : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'"
                            >
                                <?= htmlspecialchars($attrValue) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Selected Variation Info -->
            <div x-show="selectedVariation" x-cloak class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <template x-if="selectedVariation">
                    <div class="space-y-2">
                        <div class="text-lg font-semibold text-gray-900" x-text="selectedVariation.title"></div>
                        <div class="text-2xl font-bold text-blue-700" x-text="formatPrice(selectedVariation.price)"></div>
                        <div class="text-sm text-gray-600" x-text="'SKU: ' + selectedVariation.sku"></div>
                        <div class="text-sm" x-show="selectedVariation.stock > 0" x-text="'Stock: ' + selectedVariation.stock"></div>
                        <div class="text-sm text-red-600" x-show="selectedVariation.stock <= 0"><?= Flang::__('out_of_stock', 'Ecommerce') ?></div>
                    </div>
                </template>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quantity & Add to Cart -->
    <div class="space-y-4 border-t pt-6">
        <?php if ($canPurchase): ?>
            <!-- Quantity Selector -->
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700"><?= Flang::__('quantity', 'Ecommerce') ?>:</label>
                <div class="flex items-center border rounded-lg">
                    <button 
                        type="button"
                        @click="decreaseQuantity()"
                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors"
                        :disabled="quantity <= 1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <input 
                        type="number" 
                        x-model="quantity"
                        min="1"
                        :max="maxQuantity"
                        class="w-16 text-center border-0 focus:ring-0 focus:outline-none"
                    />
                    <button 
                        type="button"
                        @click="increaseQuantity()"
                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 transition-colors"
                        :disabled="quantity >= maxQuantity"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="button"
                    @click="addToCart()"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2"
                    :disabled="!canAddToCart"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?= Flang::__('add_to_cart', 'Ecommerce') ?>
                </button>
                <button 
                    type="button"
                    @click="buyNow()"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2"
                    :disabled="!canAddToCart"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                    <?= Flang::__('buy_now', 'Ecommerce') ?>
                </button>
            </div>
        <?php else: ?>
            <button 
                type="button"
                disabled
                class="w-full bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg cursor-not-allowed"
            >
                <?= Flang::__('out_of_stock', 'Ecommerce') ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Product Meta -->
    <div class="space-y-3 border-t pt-6 text-sm text-gray-600">
        <?php if (!empty($sku)): ?>
            <div class="flex items-center gap-2">
                <span class="font-medium"><?= Flang::__('sku', 'Ecommerce') ?>:</span>
                <span><?= htmlspecialchars($sku) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($vendor): ?>
            <div class="flex items-center gap-2">
                <span class="font-medium"><?= Flang::__('vendor', 'Ecommerce') ?>:</span>
                <a href="<?= link_posts($vendor['slug'] ?? '', 'ec_vendors', APP_LANG) ?>" class="text-blue-600 hover:underline">
                    <?= htmlspecialchars($vendor['title'] ?? '') ?>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($isVirtual): ?>
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                    <path d="M12 8v8"></path>
                    <path d="m8 12 4-4 4 4"></path>
                </svg>
                <span><?= Flang::__('virtual_product', 'Ecommerce') ?></span>
            </div>
        <?php endif; ?>

        <?php if ($isDownloadable): ?>
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7,10 12,15 17,10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span><?= Flang::__('downloadable', 'Ecommerce') ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function productDetail() {
    return {
        quantity: 1,
        maxQuantity: <?= $stockEnable ? $stockQuantity : 999 ?>,
        selectedAttributes: {},
        selectedVariation: null,
        variations: <?= json_encode($variations) ?>,
        productId: <?= $product['id'] ?? 0 ?>,
        productType: '<?= htmlspecialchars($productType, ENT_QUOTES, 'UTF-8') ?>',
        canAddToCart: true,

        selectAttribute(attrName, attrValue) {
            this.selectedAttributes[attrName] = attrValue;
            this.findMatchingVariation();
        },

        findMatchingVariation() {
            if (this.productType !== 'variable' || !this.variations || this.variations.length === 0) {
                return;
            }

            // Find variation that matches all selected attributes
            const selected = this.selectedAttributes;
            const matching = this.variations.find(v => {
                const attrs = v.attributes || [];
                if (attrs.length !== Object.keys(selected).length) return false;
                
                return attrs.every(attr => {
                    return selected[attr.name] === attr.value;
                });
            });

            if (matching && matching.value) {
                const variationData = matching.value;
                this.selectedVariation = {
                    id: matching.id || variationData.id,
                    title: variationData.title || matching.name,
                    price: parseFloat(variationData.price_sale || variationData.price_regular || 0),
                    sku: variationData.sku || '',
                    stock: parseInt(variationData.stock_quantity || 0)
                };
                this.maxQuantity = this.selectedVariation.stock > 0 ? this.selectedVariation.stock : 0;
                this.canAddToCart = this.selectedVariation.stock > 0;
            } else {
                this.selectedVariation = null;
                this.canAddToCart = false;
            }
        },

        increaseQuantity() {
            if (this.quantity < this.maxQuantity) {
                this.quantity++;
            }
        },

        decreaseQuantity() {
            if (this.quantity > 1) {
                this.quantity--;
            }
        },

        formatPrice(price) {
            return new Intl.NumberFormat('<?= APP_LANG ?>', {
                style: 'currency',
                currency: '<?= htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') ?>'
            }).format(price);
        },

        async addToCart() {
            if (!this.canAddToCart) return;

            const variationId = this.selectedVariation ? this.selectedVariation.id : null;
            
            try {
                const response = await fetch('<?= base_url('cart/add', APP_LANG) ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_id: this.productId,
                        quantity: this.quantity,
                        variation_id: variationId
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    alert('<?= Flang::__('added_to_cart', 'Ecommerce') ?>');
                    // Update cart count if exists
                    if (window.updateCartCount) {
                        window.updateCartCount();
                    }
                } else {
                    alert(data.message || '<?= Flang::__('failed_to_add', 'Ecommerce') ?>');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('<?= Flang::__('error_occurred', 'Ecommerce') ?>');
            }
        },

        async buyNow() {
            if (!this.canAddToCart) return;

            // Add to cart first
            await this.addToCart();
            
            // Redirect to checkout
            window.location.href = '<?= base_url('checkout', APP_LANG) ?>';
        }
    }
}
</script>

