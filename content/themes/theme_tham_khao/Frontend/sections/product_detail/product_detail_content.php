<?php

$product = $product ?? [];
$content = $content ?? '';
$description = $description ?? '';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg p-6 lg:p-8">
        <?php if (!empty($content)): ?>
            <div class="prose max-w-none">
                <?= $content ?>
            </div>
        <?php elseif (!empty($description)): ?>
            <div class="prose max-w-none">
                <p><?= nl2br(htmlspecialchars($description)) ?></p>
            </div>
        <?php else: ?>
            <p class="text-gray-500 italic"><?= __('No description available') ?></p>
        <?php endif; ?>
    </div>
</div>

