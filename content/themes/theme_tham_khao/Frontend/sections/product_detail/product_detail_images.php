<?php

$product = $product ?? [];
$featureImage = $featureImage ?? null;
$galleries = $galleries ?? [];

// Get main image URL
$mainImageUrl = null;
if (!empty($featureImage)) {
    $mainImageUrl = _img_url($featureImage, 'large');
}

// Get gallery images
$galleryImages = [];
if (!empty($galleries)) {
    if (is_string($galleries)) {
        $galleries = json_decode($galleries, true) ?? [];
    }
    if (is_array($galleries)) {
        foreach ($galleries as $galleryItem) {
            if (is_array($galleryItem) && !empty($galleryItem['path'])) {
                $galleryImages[] = $galleryItem;
            }
        }
    }
}

// Combine main image with galleries
$allImages = [];
if ($mainImageUrl) {
    $allImages[] = [
        'url' => $mainImageUrl,
        'thumbnail' => _img_url($featureImage, 'thumbnail'),
        'large' => $mainImageUrl,
        'alt' => $product['title'] ?? ''
    ];
}
foreach ($galleryImages as $galleryItem) {
    $imgUrl = _img_url($galleryItem, 'large');
    if ($imgUrl) {
        $allImages[] = [
            'url' => $imgUrl,
            'thumbnail' => _img_url($galleryItem, 'thumbnail'),
            'large' => $imgUrl,
            'alt' => $product['title'] ?? ''
        ];
    }
}

// If no images, use placeholder
if (empty($allImages)) {
    $allImages[] = [
        'url' => theme_assets('/images/placeholder-product.jpg'),
        'thumbnail' => theme_assets('/images/placeholder-product.jpg'),
        'large' => theme_assets('/images/placeholder-product.jpg'),
        'alt' => $product['title'] ?? 'Product Image'
    ];
}
?>

<div class="space-y-4" x-data="{ 
    selectedImage: 0,
    images: <?= json_encode($allImages) ?>
}">
    <!-- Main Image -->
    <div class="relative rounded-xl overflow-hidden bg-white shadow-lg aspect-square">
        <?php if (!empty($allImages[0])): ?>
            <?= _images($allImages[0]['large'], $allImages[0]['alt'], ['class' => 'w-full h-full object-contain', 'id' => 'main-product-image']) ?>
        <?php else: ?>
            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                    <circle cx="9" cy="9" r="2"></circle>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path>
                </svg>
            </div>
        <?php endif; ?>
    </div>

    <!-- Thumbnail Gallery -->
    <?php if (count($allImages) > 1): ?>
        <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
            <?php foreach ($allImages as $index => $image): ?>
                <button 
                    type="button"
                    @click="selectedImage = <?= $index ?>"
                    x-bind:class="{ 'ring-2 ring-blue-500': selectedImage === <?= $index ?> }"
                    class="relative rounded-lg overflow-hidden bg-white shadow-sm hover:shadow-md transition-all aspect-square border-2 border-transparent hover:border-blue-300"
                >
                    <img 
                        src="<?= htmlspecialchars($image['thumbnail'], ENT_QUOTES, 'UTF-8') ?>" 
                        alt="<?= htmlspecialchars($image['alt'], ENT_QUOTES, 'UTF-8') ?>"
                        class="w-full h-full object-cover"
                    />
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

