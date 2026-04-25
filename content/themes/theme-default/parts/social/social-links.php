<?php
/**
 * Icon link mạng xã hội từ option('social') — cùng format JSON [{ "network": "facebook", "url": "https://..." }, ...]
 */
$lang = defined('APP_LANG') ? APP_LANG : '';
$raw = function_exists('option') ? option('social', $lang) : null;
$items = [];

if (is_string($raw) && $raw !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $net = strtolower(trim((string) ($row['network'] ?? $row['Network'] ?? $row['name'] ?? $row['Name'] ?? $row['platform'] ?? $row['Platform'] ?? '')));
            $url = trim((string) ($row['url'] ?? $row['URL'] ?? $row['link'] ?? $row['Link'] ?? ''));
            // Cho phép url "#" hoặc rỗng (placeholder admin) — vẫn hiện icon như footer cũ
            if ($net !== '') {
                $items[] = ['network' => $net, 'url' => $url !== '' ? $url : '#'];
            }
        }
    }
} elseif (is_array($raw)) {
    $isList = isset($raw[0]) && is_array($raw[0]);
    if ($isList) {
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }
            $net = strtolower(trim((string) ($row['network'] ?? $row['Network'] ?? $row['name'] ?? $row['Name'] ?? '')));
            $url = trim((string) ($row['url'] ?? $row['URL'] ?? $row['link'] ?? $row['Link'] ?? ''));
            if ($net !== '') {
                $items[] = ['network' => $net, 'url' => $url !== '' ? $url : '#'];
            }
        }
    } else {
        foreach (['facebook', 'instagram', 'twitter', 'x', 'youtube', 'linkedin', 'tiktok', 'github', 'threads', 'pinterest', 'telegram'] as $k) {
            if (empty($raw[$k])) {
                continue;
            }
            $url = trim((string) $raw[$k]);
            if ($url !== '') {
                $items[] = ['network' => $k, 'url' => $url];
            }
        }
    }
}

$social_links_variant = $social_links_variant ?? 'footer';
$social_share_url = isset($social_share_url) ? trim((string) $social_share_url) : '';
$social_share_title = isset($social_share_title) ? trim((string) $social_share_title) : '';

$isBlogShare = ($social_links_variant === 'blog_share');
$isDrawer = ($social_links_variant === 'drawer');
$isFooter = !$isBlogShare && !$isDrawer;
/** Cùng footer: nền gradient + icon trắng (blog cột share dùng chung) */
$iconClassFooterOrBlog = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-0 bg-gradient-to-br from-cyan-400 via-teal-500 to-teal-800 text-white shadow-[0_0_14px_rgba(45,212,191,0.45),inset_0_1px_0_rgba(255,255,255,0.2)] ring-1 ring-cyan-200/40 transition hover:from-cyan-300 hover:via-teal-400 hover:to-teal-700 hover:shadow-[0_0_18px_rgba(45,212,191,0.55)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-400/80 dark:from-cyan-500 dark:via-teal-600 dark:to-teal-950 dark:shadow-[0_0_16px_rgba(34,211,238,0.35)] dark:ring-cyan-400/20 dark:hover:from-cyan-400 dark:hover:via-teal-500 [&_svg]:block [&_svg]:h-6 [&_svg]:w-6 [&_svg]:max-w-[1.5rem] [&_svg]:max-h-[1.5rem] [&_svg]:text-white';
$iconWrapperClass = $isDrawer
    ? 'flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-0 bg-gradient-to-br from-cyan-400 via-teal-500 to-teal-800 text-white shadow-[0_0_16px_rgba(45,212,191,0.5),inset_0_1px_0_rgba(255,255,255,0.2)] ring-1 ring-cyan-200/40 transition hover:from-cyan-300 hover:via-teal-400 hover:to-teal-700 hover:shadow-[0_0_20px_rgba(45,212,191,0.6),inset_0_1px_0_rgba(255,255,255,0.25)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-400/80 dark:from-cyan-500 dark:via-teal-600 dark:to-teal-950 dark:shadow-[0_0_18px_rgba(34,211,238,0.4),inset_0_1px_0_rgba(255,255,255,0.1)] dark:ring-cyan-400/25 dark:hover:from-cyan-400 dark:hover:via-teal-500 dark:hover:to-teal-900 [&_svg]:block [&_svg]:h-6 [&_svg]:w-6 [&_svg]:max-w-[1.5rem] [&_svg]:max-h-[1.5rem] [&_svg]:text-white'
    : $iconClassFooterOrBlog;

foreach ($items as $row) {
    $url = $row['url'];
    $href = '#';
    $external = false;
    if ($url !== '' && $url !== '#') {
        if (!preg_match('#^https?://#i', $url) && strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        if (preg_match('#^https?://#i', $url)) {
            $href = $url;
            $external = true;
        }
    }

    $net = strtolower(trim((string) $row['network']));
    if ($net === 'twitter') {
        $net = 'x';
    }
    if ($net === 'pin') {
        $net = 'pinterest';
    }
    $aliasNet = [
        'ig' => 'instagram', 'insta' => 'instagram', 'fb' => 'facebook', 'yt' => 'youtube',
        'tt' => 'tiktok', 'twit' => 'x', 'tw' => 'x', 'in' => 'linkedin', 'gh' => 'github',
        'thread' => 'threads', 'tele' => 'telegram', 'pint' => 'pinterest', 'x_twitter' => 'x',
    ];
    if (isset($aliasNet[$net])) {
        $net = $aliasNet[$net];
    }
    if ($href !== '' && $href !== '#') {
        $host = strtolower((string) (parse_url($href, PHP_URL_HOST) ?? ''));
        if ($host !== '') {
            $hostToNet = [
                // Tên miền có chứa ".me" phải khai trước "m.me" (zalo.me / wa.me chứa chuỗi m.me)
                ['zalo.me', 'zalo'], ['oa.zalo.me', 'zalo'],
                ['liff.line.me', 'line'], ['line.me', 'line'],
                ['wa.me', 'whatsapp'],
                ['instagr', 'instagram'],
                ['facebook.com', 'facebook'], ['fb.com', 'facebook'], ['l.facebook', 'facebook'],
                ['m.me', 'messenger'], ['l.messenger', 'messenger'],
                ['youtube.com', 'youtube'], ['youtu.be', 'youtube'], ['m.youtube', 'youtube'],
                ['linkedin.com', 'linkedin'], ['lnkd', 'linkedin'],
                ['tiktok.com', 'tiktok'],
                ['pinterest', 'pinterest'], ['pin.it', 'pinterest'],
                ['t.me', 'telegram'], ['telegram.org', 'telegram'], ['web.telegram', 'telegram'],
                ['github.com', 'github'],
                ['x.com', 'x'], ['twitter.com', 'x'], ['t.co', 'x'],
                ['threads.net', 'threads'], ['www.threads', 'threads'],
                ['whatsapp.com', 'whatsapp'], ['web.whatsapp', 'whatsapp'], ['api.whatsapp', 'whatsapp'], ['chat.whatsapp', 'whatsapp'],
                ['discord.com', 'discord'], ['discord.gg', 'discord'],
                ['zalo', 'zalo'],
            ];
            foreach ($hostToNet as [$sub, $nkey]) {
                if (str_contains($host, (string) $sub)) {
                    $net = $nkey;
                    break;
                }
            }
        }
    }

    switch ($net) {
        case 'facebook':
            $labelKey = 'Facebook';
            break;
        case 'instagram':
            $labelKey = 'Instagram';
            break;
        case 'x':
            $labelKey = 'X';
            break;
        case 'youtube':
            $labelKey = 'YouTube';
            break;
        case 'linkedin':
            $labelKey = 'LinkedIn';
            break;
        case 'tiktok':
            $labelKey = 'TikTok';
            break;
        case 'github':
            $labelKey = 'GitHub';
            break;
        case 'threads':
            $labelKey = 'Threads';
            break;
        case 'pinterest':
            $labelKey = 'Pinterest';
            break;
        case 'telegram':
            $labelKey = 'Telegram';
            break;
        case 'zalo':
            $labelKey = 'Zalo';
            break;
        case 'whatsapp':
            $labelKey = 'WhatsApp';
            break;
        case 'discord':
            $labelKey = 'Discord';
            break;
        case 'messenger':
            $labelKey = 'Messenger';
            break;
        case 'line':
            $labelKey = 'LINE';
            break;
        default:
            $labelKey = ucfirst($net);
    }

    if ($isBlogShare && $social_share_url !== '') {
        switch ($net) {
            case 'facebook':
                $href = 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode($social_share_url);
                $external = true;
                break;
            case 'x':
                $href = 'https://twitter.com/intent/tweet?url=' . rawurlencode($social_share_url) . '&text=' . rawurlencode($social_share_title);
                $external = true;
                break;
            case 'linkedin':
                $href = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($social_share_url);
                $external = true;
                break;
            case 'pinterest':
                $href = 'https://www.pinterest.com/pin/create/button/?url=' . rawurlencode($social_share_url) . '&description=' . rawurlencode($social_share_title);
                $external = true;
                break;
            case 'telegram':
                $href = 'https://t.me/share/url?url=' . rawurlencode($social_share_url) . '&text=' . rawurlencode($social_share_title);
                $external = true;
                break;
            default:
                break;
        }
    }

    if ($isBlogShare && $social_share_url !== '') {
        switch ($net) {
            case 'facebook':
                $aria = function_exists('__') ? __('share_facebook') : 'Share on Facebook';
                break;
            case 'instagram':
                $aria = function_exists('__') ? __('share_instagram') : 'Open Instagram';
                break;
            case 'x':
                $aria = function_exists('__') ? __('share_x') : 'Share on X';
                break;
            case 'linkedin':
                $aria = function_exists('__') ? __('share_on_linkedin') : 'Share on LinkedIn';
                break;
            case 'pinterest':
                $aria = function_exists('__') ? __('share_on_pinterest') : 'Share on Pinterest';
                break;
            case 'telegram':
                $aria = function_exists('__') ? __('share_telegram') : 'Share on Telegram';
                break;
            case 'youtube':
            case 'tiktok':
            case 'github':
            case 'threads':
                $aria = function_exists('__')
                    ? __('theme_footer.social_visit', $labelKey)
                    : ('Visit us on ' . $labelKey);
                break;
            default:
                $aria = function_exists('__')
                    ? __('theme_footer.social_visit', $labelKey)
                    : ('Visit us on ' . $labelKey);
        }
    } else {
        $aria = function_exists('__')
            ? __('theme_footer.social_visit', $labelKey)
            : ('Visit us on ' . $labelKey);
    }
    ?>
  <a href="<?php echo e($href); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="<?php echo e($iconWrapperClass); ?>"
    aria-label="<?php echo e($aria); ?>">
    <?php
    require __DIR__ . '/social-icon-drawer.php';
    ?>
  </a>
    <?php
}

if (($isDrawer || $isFooter || $isBlogShare) && $items === []) {
    ?>
  <span class="sr-only"><?php echo e(__('theme.nav.drawer_social_empty')); ?></span>
    <?php
}