<?php
require_once __DIR__ . '/_blog_read_time.php';
$item = $item ?? [];
$blog_category_styles = $blog_category_styles ?? [];
$blog_category_default_style = $blog_category_default_style ?? 'bg-gray-100 text-gray-700';
$__catClass = $item['category_style'] ?? $blog_category_default_style;
$__catName = $item['category_name'] ?? '';
$__title = html_entity_decode((string) ($item['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
$__url = $item['url'] ?? '#';
$__description_title = $item['description_title'] ?? '';
$__plain = strip_tags((string) $__description_title);
$__excerpt = mb_strlen($__plain) > 120 ? mb_substr($__plain, 0, 120) . '...' : $__plain;
$__username = $item['username'] ?? '';
$__search_blob = trim(preg_replace('/\s+/u', ' ', strip_tags($__title . ' ' . $__plain . ' ' . $__catName . ' ' . $__username)));
$__search_blob = mb_strtolower($__search_blob, 'UTF-8');
$__created = $item['created_at'] ?? '';
$__ts = blog_created_at_to_unix($__created);
$__dateStr = $__ts > 0 ? date('M j, Y', $__ts) : '';
$__minsLabel = $__ts > 0 ? blog_mins_only_label($__ts) : '';
?>
<article
    class="group blog-search-card bg-white rounded-home-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow p-1"
    data-blog-search="<?php echo htmlspecialchars($__search_blob, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="relative overflow-hidden rounded-home-lg">
        <?php if (!empty($item['feature'])): ?>
            <?php echo _imglazy($item['feature'], [
                'alt' => $__title,
                'class' => 'blog-article-card__thumb w-full object-cover transition-transform duration-500 ease-out hover:scale-105',
                /* Grid tối đa 4 cột: thumb ~280–400px — medium đủ 2x DPR, tránh large/xlarge ~1000px */
                'sizes' => [
                    'mobile' => 'medium',
                    'tablet' => 'medium',
                    'desktop' => 'medium',
                    'large' => 'medium',
                ],
            ]); ?>
        <?php endif; ?>
        <?php if ($__catName !== ''): ?>
            <div class="absolute top-3 left-3">
                <span class="<?php echo e($__catClass); ?> text-xs font-semibold px-3 py-2 rounded-home-md"><?php echo e($__catName); ?></span>
            </div>
        <?php endif; ?>
    </div>
    <div class="blog-article-card__body">
        <div class="flex items-center gap-6 text-sm text-gray-500 mb-4">
            <?php if ($__dateStr !== ''): ?>
                <div class="flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.66667 1.66663V4.99996M13.3333 1.66663V4.99996M2.5 8.33329H17.5M4.16667 3.33329H15.8333C16.7538 3.33329 17.5 4.07948 17.5 4.99996V16.6666C17.5 17.5871 16.7538 18.3333 15.8333 18.3333H4.16667C3.24619 18.3333 2.5 17.5871 2.5 16.6666V4.99996C2.5 4.07948 3.24619 3.33329 4.16667 3.33329Z"
                            stroke="#97A4B2" stroke-width="1.66667" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                    <span><?php echo e($__dateStr); ?></span>
                </div>
            <?php endif; ?>
            <div class="flex items-center gap-[6px]">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M9.99935 4.99996V9.99996L13.3327 11.6666M18.3327 9.99996C18.3327 14.6023 14.6017 18.3333 9.99935 18.3333C5.39698 18.3333 1.66602 14.6023 1.66602 9.99996C1.66602 5.39759 5.39698 1.66663 9.99935 1.66663C14.6017 1.66663 18.3327 5.39759 18.3327 9.99996Z"
                        stroke="#97A4B2" stroke-width="1.66667" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span><?php echo e($__minsLabel !== '' ? $__minsLabel : '—'); ?></span>
            </div>
        </div>
        <a href="<?php echo e($__url); ?>">
        <h3 class="blog-article-card__title text-[24px] text-gray-900 mb-4 group-hover:text-home-primary transition-colors">
            <?php echo e($__title); ?>
        </h3>
        </a>
        <p class="text-md text-gray-600 mb-6 line-clamp-2">
            <?php echo e($__excerpt); ?>
        </p>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <?php if (!empty($item['avatar'])): ?>
                    <div class="blog-author-avatar">
                        <?php echo _imglazy($item['avatar'], [
                            'alt' => $__username,
                            'class' => 'w-full h-full object-cover',
                            'sizes' => [
                                'mobile' => 'thumbnail',
                                'tablet' => 'medium',
                                'desktop' => 'medium',
                                'large' => 'medium',
                            ],
                        ]); ?>
                    </div>
                <?php endif; ?>
                <?php if ($__username !== ''): ?>
                    <span class="text-sm font-medium text-gray-700"><?php echo e($__username); ?></span>
                <?php endif; ?>
            </div>
            <a href="<?php echo e($__url); ?>"
                class="group/link text-home-primary text-md font-semibold flex items-center gap-1 transition-all">
                <span
                    class="group-hover/link:-translate-x-1 transition-transform duration-200 font-bold"><?php echo e(__('listing_read_short')); ?></span>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.4 12L9.4 18L8 16.6L12.6 12L8 7.4L9.4 6L15.4 12Z" fill="var(--home-primary)" />
                </svg>

            </a>
        </div>
    </div>
</article>