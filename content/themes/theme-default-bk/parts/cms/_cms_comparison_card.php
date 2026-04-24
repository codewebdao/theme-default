<?php
/**
 * @var array $card Normalized CMS comparison row (cms-comparison.php).
 */
$card = $card ?? [];
$title = (string) ($card['title'] ?? '');
$description = (string) ($card['description'] ?? '');
$label = trim((string) ($card['label'] ?? ''));
$postSlug = trim((string) ($card['slug'] ?? ''));
$reviewDetailHref = ($postSlug !== '')
    ? rtrim((string) link_posts($postSlug, 'reviews', defined('APP_LANG') ? APP_LANG : ''), '/')
    : '';
$feature = $card['feature'] ?? null;
$ratingDisplay = (string) ($card['rating_display'] ?? '0.0');
$perf = (int) ($card['performance'] ?? 0);
$perfLabel = (string) ($card['performance_label'] ?? ($perf . '/100'));
$barClass = (string) ($card['performance_bar_class'] ?? 'bg-gradient-to-r from-home-primary to-home-accent');
$link = (string) ($card['link'] ?? '#');
$linkDownloadName = trim((string) ($card['link_download_name'] ?? ''));
$proItems = is_array($card['pro_items'] ?? null) ? $card['pro_items'] : [];
$consItems = is_array($card['cons_items'] ?? null) ? $card['cons_items'] : [];
$performanceUser = trim((string) ($card['performance_user'] ?? ''));

$cmsT = static function (string $k, string $fb = ''): string {
    return function_exists('__') ? (string) __($k) : ($fb !== '' ? $fb : $k);
};

$isExternal = (bool) preg_match('#^https?://#i', $link);
$linkAttrs = '';
if ($link !== '#' && $link !== '') {
    if ($linkDownloadName !== '') {
        $linkAttrs .= ' download="' . htmlspecialchars($linkDownloadName, ENT_QUOTES, 'UTF-8') . '"';
    } else {
        $linkAttrs .= ' download';
    }
}
if ($isExternal) {
    $linkAttrs .= ' rel="noopener noreferrer"';
}
?>
<div
    class="bg-white rounded-home-xl border border-home-surface shadow-[0_2.667px_8px_rgba(43,140,238,0.05)] flex flex-col items-start relative h-full">
    <?php if ($label !== ''): ?>
    <div
        class="flex justify-center items-center gap-1.5 py-1 px-4 rounded-tr-xl rounded-bl-xl bg-[#FACC15] absolute right-[0.333px] z-[1]">
        <span class="text-sm font-medium text-[#2C2C2C] font-plus"><?php echo e($label); ?></span>
    </div>
    <?php endif; ?>
    <div class="px-6 pt-8 flex-1 w-full">
        <div class="flex items-start justify-between w-full mb-4">
            <div class="cms-comparison-card-logo shrink-0">
                <?php if ($reviewDetailHref !== ''): ?>
                <a href="<?php echo htmlspecialchars($reviewDetailHref, ENT_QUOTES, 'UTF-8'); ?>"
                    class="block rounded-[8px] focus:outline-none focus-visible:ring-2 focus-visible:ring-home-primary focus-visible:ring-offset-2"
                    aria-label="<?php echo e($title !== '' ? $title : $cmsT('cms_comparison_rating', 'Rating')); ?>">
                <?php endif; ?>
                <?php if (!empty($feature) && function_exists('_imglazy')): ?>
                <?php echo _imglazy($feature, [
                    'alt'   => $title,
                    'class' => 'h-[60px] w-[60px] rounded-[8px] object-cover object-center shrink-0',
                    /** Hiển thị 60px — dùng thumbnail mọi breakpoint, tránh tải medium 600px */
                    'sizes' => [
                        'mobile'  => 'thumbnail',
                        'tablet'  => 'thumbnail',
                        'desktop' => 'thumbnail',
                        'large'   => 'thumbnail',
                    ],
                ]); ?>
                <?php else: ?>
                <div class="cms-comparison-card-logo__placeholder" aria-hidden="true"></div>
                <?php endif; ?>
                <?php if ($reviewDetailHref !== ''): ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="flex flex-col items-end text-right">
                <div class="font-plus text-xs font-normal leading-[22px] text-home-body mb-1">
                    <?php echo e($cmsT('cms_comparison_rating', 'Rating')); ?>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-lg font-semibold text-gray-900 font-plus"><?php echo e($ratingDisplay); ?></span>
                    <svg width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path
                            d="M7.37646 0.92129C7.77559 -0.307124 9.51347 -0.307126 9.91261 0.921288L11.0393 4.38904C11.2178 4.9384 11.7298 5.31035 12.3074 5.31035H15.9536C17.2453 5.31035 17.7823 6.96317 16.7373 7.72237L13.7875 9.86556C13.3202 10.2051 13.1246 10.8069 13.3031 11.3563L14.4299 14.824C14.829 16.0524 13.423 17.0739 12.3781 16.3147L9.42825 14.1715C8.96093 13.832 8.32813 13.832 7.86082 14.1715L4.91097 16.3147C3.86602 17.0739 2.46005 16.0524 2.85918 14.824L3.98592 11.3563C4.16442 10.8069 3.96888 10.2051 3.50156 9.86556L0.551718 7.72237C-0.493233 6.96317 0.0438008 5.31035 1.33543 5.31035H4.98164C5.55927 5.31035 6.07122 4.9384 6.24971 4.38904L7.37646 0.92129Z"
                            fill="#FFE029" />
                    </svg>
                </div>
            </div>
        </div>

        <?php if ($reviewDetailHref !== ''): ?>
        <h3 class="text-2xl font-semibold leading-9 font-plus">
            <a href="<?php echo htmlspecialchars($reviewDetailHref, ENT_QUOTES, 'UTF-8'); ?>"
                class="text-home-heading hover:text-home-primary transition-colors"><?php echo e($title); ?></a>
        </h3>
        <?php else: ?>
        <h3 class="text-2xl font-semibold leading-9 text-home-heading font-plus"><?php echo e($title); ?></h3>
        <?php endif; ?>

        <?php if ($description !== ''): ?>
        <p class="text-base font-normal leading-6 text-home-body mb-8 pb-3 mt-2 font-plus"><?php echo e($description); ?></p>
        <?php else: ?>
        <div class="mb-8 pb-3 mt-2"></div>
        <?php endif; ?>

        <div class="mb-8 w-full self-stretch">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-normal leading-[18px] text-home-body font-plus">
                    <?php echo e($cmsT('cms_comparison_performance', 'Performance')); ?>
                </span>
                <span class="text-xs font-semibold text-home-heading font-plus"><?php echo e($perfLabel); ?></span>
            </div>
            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-2 rounded-full <?php echo e($barClass); ?>" style="width: <?php echo (int) min(100, max(0, $perf)); ?>%;">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-2 gap-6 w-full self-stretch sm:mb-12 mb-8 lg:gap-2">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <svg width="52" height="21" viewBox="0 0 52 21" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path
                            d="M18.1669 8.83332C18.5474 10.7011 18.2762 12.6428 17.3984 14.3348C16.5206 16.0268 15.0893 17.3667 13.3431 18.1311C11.597 18.8955 9.64154 19.0381 7.80293 18.5353C5.96433 18.0325 4.35368 16.9145 3.23958 15.3678C2.12548 13.8212 1.57529 11.9394 1.68074 10.0361C1.78619 8.13294 2.54092 6.3234 3.81906 4.9093C5.0972 3.4952 6.8215 2.56202 8.7044 2.26537C10.5873 1.96872 12.515 2.32654 14.166 3.27916M7.49935 9.66666L9.99935 12.1667L18.3327 3.83332"
                            stroke="var(--home-success)" stroke-width="1.66667" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path
                            d="M29.008 16V5.57H32.9C33.6093 5.57 34.2347 5.70067 34.776 5.962C35.3267 6.214 35.756 6.58733 36.064 7.082C36.372 7.56733 36.526 8.16 36.526 8.86C36.526 9.55067 36.3673 10.1433 36.05 10.638C35.742 11.1233 35.3173 11.4967 34.776 11.758C34.2347 12.0193 33.6093 12.15 32.9 12.15H30.912V16H29.008ZM30.912 10.47H32.928C33.2733 10.47 33.572 10.4047 33.824 10.274C34.076 10.134 34.272 9.94267 34.412 9.7C34.552 9.45733 34.622 9.17733 34.622 8.86C34.622 8.53333 34.552 8.25333 34.412 8.02C34.272 7.77733 34.076 7.59067 33.824 7.46C33.572 7.32 33.2733 7.25 32.928 7.25H30.912V10.47ZM37.9321 16V8.384H39.6541V10.078L39.5141 9.826C39.6915 9.25667 39.9668 8.86 40.3401 8.636C40.7228 8.412 41.1801 8.3 41.7121 8.3H42.1601V9.924H41.5021C40.9795 9.924 40.5595 10.0873 40.2421 10.414C39.9248 10.7313 39.7661 11.1793 39.7661 11.758V16H37.9321ZM46.8925 16.168C46.1458 16.168 45.4645 15.9953 44.8485 15.65C44.2418 15.3047 43.7565 14.8333 43.3925 14.236C43.0378 13.6387 42.8605 12.9573 42.8605 12.192C42.8605 11.4267 43.0378 10.7453 43.3925 10.148C43.7565 9.55067 44.2418 9.07933 44.8485 8.734C45.4551 8.38867 46.1365 8.216 46.8925 8.216C47.6391 8.216 48.3158 8.38867 48.9225 8.734C49.5291 9.07933 50.0098 9.55067 50.3645 10.148C50.7285 10.736 50.9105 11.4173 50.9105 12.192C50.9105 12.9573 50.7285 13.6387 50.3645 14.236C50.0005 14.8333 49.5151 15.3047 48.9085 15.65C48.3018 15.9953 47.6298 16.168 46.8925 16.168ZM46.8925 14.488C47.3031 14.488 47.6625 14.39 47.9705 14.194C48.2878 13.998 48.5351 13.7273 48.7125 13.382C48.8991 13.0273 48.9925 12.6307 48.9925 12.192C48.9925 11.744 48.8991 11.352 48.7125 11.016C48.5351 10.6707 48.2878 10.4 47.9705 10.204C47.6625 9.99867 47.3031 9.896 46.8925 9.896C46.4725 9.896 46.1038 9.99867 45.7865 10.204C45.4691 10.4 45.2171 10.6707 45.0305 11.016C44.8531 11.352 44.7645 11.744 44.7645 12.192C44.7645 12.6307 44.8531 13.0273 45.0305 13.382C45.2171 13.7273 45.4691 13.998 45.7865 14.194C46.1038 14.39 46.4725 14.488 46.8925 14.488Z"
                            fill="var(--home-success)" />
                    </svg>
                </div>
                <ul class="space-y-2">
                    <?php foreach ($proItems as $line): ?>
                    <li class="text-xs text-home-body flex items-center gap-2 font-plus">
                        <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <circle cx="3" cy="3" r="3" fill="var(--home-body)" />
                        </svg>
                        <span><?php echo e((string) $line); ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($proItems === []): ?>
                    <li class="text-xs text-gray-400 font-plus">—</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <svg width="64" height="21" viewBox="0 0 64 21" fill="none" xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true">
                        <path
                            d="M12.5003 8.00002L7.50033 13M7.50033 8.00002L12.5003 13M18.3337 10.5C18.3337 15.1024 14.6027 18.8334 10.0003 18.8334C5.39795 18.8334 1.66699 15.1024 1.66699 10.5C1.66699 5.89765 5.39795 2.16669 10.0003 2.16669C14.6027 2.16669 18.3337 5.89765 18.3337 10.5Z"
                            stroke="#ED661D" stroke-width="1.66667" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path
                            d="M33.908 16.168C33.1613 16.168 32.4707 16.0327 31.836 15.762C31.2107 15.4913 30.66 15.1133 30.184 14.628C29.7173 14.1427 29.3533 13.5733 29.092 12.92C28.8307 12.2667 28.7 11.5527 28.7 10.778C28.7 10.0033 28.826 9.28933 29.078 8.636C29.3393 7.97333 29.7033 7.404 30.17 6.928C30.646 6.44267 31.2013 6.06933 31.836 5.808C32.4707 5.53733 33.1613 5.402 33.908 5.402C34.6547 5.402 35.322 5.528 35.91 5.78C36.5073 6.032 37.0113 6.368 37.422 6.788C37.8327 7.19867 38.1267 7.65133 38.304 8.146L36.596 8.944C36.4 8.42133 36.0687 7.992 35.602 7.656C35.1353 7.31067 34.5707 7.138 33.908 7.138C33.2547 7.138 32.676 7.292 32.172 7.6C31.6773 7.908 31.29 8.33267 31.01 8.874C30.7393 9.41533 30.604 10.05 30.604 10.778C30.604 11.506 30.7393 12.1453 31.01 12.696C31.29 13.2373 31.6773 13.662 32.172 13.97C32.676 14.278 33.2547 14.432 33.908 14.432C34.5707 14.432 35.1353 14.264 35.602 13.928C36.0687 13.5827 36.4 13.1487 36.596 12.626L38.304 13.424C38.1267 13.9187 37.8327 14.376 37.422 14.796C37.0113 15.2067 36.5073 15.538 35.91 15.79C35.322 16.042 34.6547 16.168 33.908 16.168ZM43.2831 16.168C42.5364 16.168 41.8551 15.9953 41.2391 15.65C40.6324 15.3047 40.1471 14.8333 39.7831 14.236C39.4284 13.6387 39.2511 12.9573 39.2511 12.192C39.2511 11.4267 39.4284 10.7453 39.7831 10.148C40.1471 9.55067 40.6324 9.07933 41.2391 8.734C41.8457 8.38867 42.5271 8.216 43.2831 8.216C44.0297 8.216 44.7064 8.38867 45.3131 8.734C45.9197 9.07933 46.4004 9.55067 46.7551 10.148C47.1191 10.736 47.3011 11.4173 47.3011 12.192C47.3011 12.9573 47.1191 13.6387 46.7551 14.236C46.3911 14.8333 45.9057 15.3047 45.2991 15.65C44.6924 15.9953 44.0204 16.168 43.2831 16.168ZM43.2831 14.488C43.6937 14.488 44.0531 14.39 44.3611 14.194C44.6784 13.998 44.9257 13.7273 45.1031 13.382C45.2897 13.0273 45.3831 12.6307 45.3831 12.192C45.3831 11.744 45.2897 11.352 45.1031 11.016C44.9257 10.6707 44.6784 10.4 44.3611 10.204C44.0531 9.99867 43.6937 9.896 43.2831 9.896C42.8631 9.896 42.4944 9.99867 42.1771 10.204C41.8597 10.4 41.6077 10.6707 41.4211 11.016C41.2437 11.352 41.1551 11.744 41.1551 12.192C41.1551 12.6307 41.2437 13.0273 41.4211 13.382C41.6077 13.7273 41.8597 13.998 42.1771 14.194C42.4944 14.39 42.8631 14.488 43.2831 14.488ZM48.6919 16V8.384H50.4139V9.882L50.2739 9.616C50.4512 9.15867 50.7406 8.81333 51.1419 8.58C51.5526 8.33733 52.0286 8.216 52.5699 8.216C53.1299 8.216 53.6246 8.33733 54.0539 8.58C54.4926 8.82267 54.8332 9.16333 55.0759 9.602C55.3186 10.0313 55.4399 10.5307 55.4399 11.1V16H53.6059V11.534C53.6059 11.198 53.5406 10.9087 53.4099 10.666C53.2792 10.4233 53.0972 10.2367 52.8639 10.106C52.6399 9.966 52.3739 9.896 52.0659 9.896C51.7672 9.896 51.5012 9.966 51.2679 10.106C51.0346 10.2367 50.8526 10.4233 50.7219 10.666C50.5912 10.9087 50.5259 11.198 50.5259 11.534V16H48.6919ZM59.8887 16.168C59.0767 16.168 58.3674 15.9767 57.7607 15.594C57.1634 15.202 56.7527 14.6747 56.5287 14.012L57.9007 13.354C58.0967 13.7833 58.3674 14.1193 58.7127 14.362C59.0674 14.6047 59.4594 14.726 59.8887 14.726C60.2247 14.726 60.4907 14.6513 60.6867 14.502C60.8827 14.3527 60.9807 14.1567 60.9807 13.914C60.9807 13.7647 60.9387 13.6433 60.8547 13.55C60.7801 13.4473 60.6727 13.3633 60.5327 13.298C60.4021 13.2233 60.2574 13.1627 60.0987 13.116L58.8527 12.766C58.2087 12.5793 57.7187 12.2947 57.3827 11.912C57.0561 11.5293 56.8927 11.0767 56.8927 10.554C56.8927 10.0873 57.0094 9.68133 57.2427 9.336C57.4854 8.98133 57.8167 8.706 58.2367 8.51C58.6661 8.314 59.1561 8.216 59.7067 8.216C60.4254 8.216 61.0601 8.38867 61.6107 8.734C62.1614 9.07933 62.5534 9.56467 62.7867 10.19L61.3867 10.848C61.2561 10.5027 61.0367 10.2273 60.7287 10.022C60.4207 9.81667 60.0754 9.714 59.6927 9.714C59.3847 9.714 59.1421 9.784 58.9647 9.924C58.7874 10.064 58.6987 10.246 58.6987 10.47C58.6987 10.61 58.7361 10.7313 58.8107 10.834C58.8854 10.9367 58.9881 11.0207 59.1187 11.086C59.2587 11.1513 59.4174 11.212 59.5947 11.268L60.8127 11.632C61.4381 11.8187 61.9187 12.0987 62.2547 12.472C62.6001 12.8453 62.7727 13.3027 62.7727 13.844C62.7727 14.3013 62.6514 14.7073 62.4087 15.062C62.1661 15.4073 61.8301 15.678 61.4007 15.874C60.9714 16.07 60.4674 16.168 59.8887 16.168Z"
                            fill="#ED661D" />
                    </svg>
                </div>
                <ul class="space-y-2">
                    <?php foreach ($consItems as $line): ?>
                    <li class="text-xs text-home-body flex items-center gap-2 font-plus">
                        <svg width="6" height="6" viewBox="0 0 6 6" fill="none" xmlns="http://www.w3.org/2000/svg"
                            aria-hidden="true">
                            <circle cx="3" cy="3" r="3" fill="var(--home-body)" />
                        </svg>
                        <span><?php echo e((string) $line); ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($consItems === []): ?>
                    <li class="text-xs text-gray-400 font-plus">—</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div
        class="flex items-center w-full py-2.5 border-gray-200 bg-[#F3F4F6] px-6 rounded-b-2xl mt-auto gap-2 <?php echo $performanceUser !== '' ? 'justify-between' : 'justify-end'; ?>">
        <?php if ($performanceUser !== ''): ?>
        <div class="flex items-center gap-1.5 min-w-0">
            <svg class="shrink-0" width="16" height="16" viewBox="0 0 18 18" fill="none"
                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path
                    d="M12 15.75V14.25C12 13.4544 11.6839 12.6913 11.1213 12.1287C10.5587 11.5661 9.79565 11.25 9 11.25H4.5C3.70435 11.25 2.94129 11.5661 2.37868 12.1287C1.81607 12.6913 1.5 13.4544 1.5 14.25V15.75M12 2.346C12.6433 2.51278 13.213 2.88845 13.6198 3.41405C14.0265 3.93965 14.2471 4.58542 14.2471 5.25C14.2471 5.91458 14.0265 6.56035 13.6198 7.08595C13.213 7.61155 12.6433 7.98722 12 8.154M16.5 15.75V14.25C16.4995 13.5853 16.2783 12.9396 15.871 12.4142C15.4638 11.8889 14.8936 11.5137 14.25 11.3475M9.75 5.25C9.75 6.90685 8.40685 8.25 6.75 8.25C5.09315 8.25 3.75 6.90685 3.75 5.25C3.75 3.59315 5.09315 2.25 6.75 2.25C8.40685 2.25 9.75 3.59315 9.75 5.25Z"
                    stroke="var(--home-body)" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
            <span class="text-xs text-gray-600 font-plus truncate"><?php echo e($performanceUser);  ?> of Web</span>
        </div>
        <?php endif; ?>
        <div class="flex items-center gap-2 shrink-0">
            <a href="<?php echo e($link); ?>"
                class="text-home-primary text-xs font-medium hover:text-home-primary-hover transition-colors font-plus"
                <?php echo $linkAttrs; ?>><?php echo e($cmsT('Try on Laragon', 'Try on Laragon')); ?></a>
            <a href="<?php echo e($link); ?>" class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors"
                aria-label="<?php echo e($cmsT('cms_comparison_open_link', 'Open link')); ?>" <?php echo $linkAttrs; ?>>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path
                        d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15"
                        stroke="var(--home-primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </div>
    </div>
</div>
