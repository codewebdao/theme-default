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
$iconWrapperClass = $isBlogShare
    ? 'w-9 h-9 shrink-0 rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-100 transition'
    : 'text-sm text-gray-600 hover:text-home-primary transition-colors font-plus';

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

    $net = $row['network'];
    if ($net === 'twitter') {
        $net = 'x';
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
    <?php if ($net === 'facebook'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M17.1919 12.1546V14.1284H19.4994L19.134 16.7872H17.1919V22.913C16.8025 22.9702 16.4041 23 15.9997 23C15.5329 23 15.0745 22.9606 14.6281 22.8845V16.7872H12.5V14.1284H14.6281V11.7134C14.6281 10.2151 15.7759 9 17.1925 9V9.00127C17.1967 9.00127 17.2003 9 17.2045 9H19.5V11.2995H18C17.5543 11.2995 17.1925 11.6823 17.1925 12.154L17.1919 12.1546Z" fill="var(--home-body)" />
    </svg>
    <?php elseif ($net === 'instagram'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M19.6509 9H12.3495C10.3324 9 8.69141 10.6415 8.69141 12.6592V19.3408C8.69141 21.3585 10.3324 23 12.3495 23H19.6509C21.668 23 23.309 21.3585 23.309 19.3408V12.6592C23.309 10.6415 21.668 9 19.6509 9ZM9.98187 12.6592C9.98187 11.3535 11.0442 10.2908 12.3495 10.2908H19.6509C20.9562 10.2908 22.0185 11.3535 22.0185 12.6592V19.3408C22.0185 20.6465 20.9562 21.7092 19.6509 21.7092H12.3495C11.0442 21.7092 9.98187 20.6465 9.98187 19.3408V12.6592Z" fill="var(--home-body)" />
      <path d="M16.0003 19.402C17.8761 19.402 19.403 17.8755 19.403 15.9984C19.403 14.1212 17.8769 12.5947 16.0003 12.5947C14.1237 12.5947 12.5977 14.1212 12.5977 15.9984C12.5977 17.8755 14.1237 19.402 16.0003 19.402ZM16.0003 13.8864C17.1651 13.8864 18.1125 14.8341 18.1125 15.9992C18.1125 17.1644 17.1651 18.112 16.0003 18.112C14.8355 18.112 13.8881 17.1644 13.8881 15.9992C13.8881 14.8341 14.8355 13.8864 16.0003 13.8864Z" fill="var(--home-body)" />
      <path d="M19.7176 13.1437C20.2227 13.1437 20.6344 12.7327 20.6344 12.2266C20.6344 11.7206 20.2235 11.3096 19.7176 11.3096C19.2116 11.3096 18.8008 11.7206 18.8008 12.2266C18.8008 12.7327 19.2116 13.1437 19.7176 13.1437Z" fill="var(--home-body)" />
    </svg>
    <?php elseif ($net === 'x'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M8.55941 9L14.3332 16.7217L8.52344 23H9.83136L14.9183 17.5036L19.028 23H23.4781L17.3797 14.8439L22.7877 9H21.4798L16.7955 14.0621L13.0104 9H8.56027H8.55941ZM10.4822 9.96348H12.5261L21.5535 22.0365H19.5096L10.4822 9.96348Z" fill="var(--home-body)" />
    </svg>
    <?php elseif ($net === 'youtube'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <!-- TV bo góc + tam giác trắng (lỗ play), cùng phong cách đơn sắc như Facebook/IG -->
      <rect x="10.75" y="11.25" width="10.5" height="9.5" rx="2.35" ry="2.35" fill="var(--home-body)" />
      <path fill="white" d="M13.45 13.55L18.95 16l-5.5 2.45v-4.9z" />
    </svg>
    <?php elseif ($net === 'linkedin'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M12.2 22.5h-2.9V13.4h2.9v9.1zM10.7 12.1c-.9 0-1.7-.7-1.7-1.7 0-.9.8-1.7 1.7-1.7s1.7.8 1.7 1.7c0 1-.8 1.7-1.7 1.7zM23.5 22.5h-2.9v-4.4c0-1.1 0-2.5-1.5-2.5-1.5 0-1.7 1.2-1.7 2.4v4.5h-2.9V13.4h2.8v1.2h.1c.4-.8 1.4-1.6 2.9-1.6 3.1 0 3.7 2 3.7 4.7v4.8z" fill="var(--home-body)" />
    </svg>
    <?php elseif ($net === 'github'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path fill-rule="evenodd" clip-rule="evenodd" d="M16 9.2c-3.7 0-6.8 3-6.8 6.8 0 3 1.9 5.5 4.6 6.4.3.1.5-.1.5-.3v-1.2c-1.9.4-2.3-.9-2.3-.9-.3-.8-.8-1-.8-1-.7-.4 0-.4 0-.4.7.1 1.1.8 1.1.8.7 1.2 1.8.9 2.2.7.1-.5.3-.9.5-1.1-1.5-.2-3.1-.8-3.1-3.5 0-.8.3-1.4.7-1.9-.1-.2-.3-.9.1-1.8 0 0 .6-.2 1.9.7.5-.2 1.1-.3 1.6-.3.5 0 1.1.1 1.6.3 1.3-.9 1.9-.7 1.9-.7.4.9.2 1.6.1 1.8.5.5.7 1.1.7 1.9 0 2.7-1.6 3.3-3.1 3.5.3.2.5.7.5 1.4v2.1c0 .2.2.4.5.3 2.7-.9 4.5-3.4 4.5-6.4 0-3.8-3-6.8-6.8-6.8z" fill="var(--home-body)" />
    </svg>
    <?php elseif ($net === 'telegram'): ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M22.35 9.65c.35.09.59.4.55.76l-1.65 15.52c-.03.28-.22.52-.49.6a.68.68 0 0 1-.76-.28l-4.42-5.98-2.82 2.72c-.13.12-.3.19-.48.19a.75.75 0 0 1-.75-.75v-3.86l8.38-7.75-10.32 6.32-3.82-1.19c-.33-.1-.55-.41-.52-.75.03-.35.3-.62.65-.68l16.5-3.05z" fill="var(--home-body)" />
    </svg>
    <?php else: ?>
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <rect x="0.421053" y="0.421053" width="31.1579" height="31.1579" rx="15.5789" stroke="#F3F4F6" stroke-width="0.842105" />
      <circle cx="16" cy="16" r="15.5789" fill="white" stroke="#F3F4F6" stroke-width="0.842105" />
      <path d="M12 14h-2v8h8v-2h-6v-6zm4-6v2h6v6h2V8h-8z" fill="var(--home-body)" />
    </svg>
    <?php endif; ?>
  </a>
    <?php
}