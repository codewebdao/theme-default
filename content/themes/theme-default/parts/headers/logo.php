<?php
$lang = defined('APP_LANG') ? APP_LANG : '';
$logo = option('site_logo');
$brandName = trim((string) (option('site_brand', $lang) ?: ''));
$siteTitle = trim((string) (option('site_title', $lang) ?: ''));
$displayName = $brandName !== '' ? $brandName : ($siteTitle !== '' ? $siteTitle : 'Blog');
$home = base_url();
$fallbackSrc = function_exists('theme_assets') ? theme_assets('images/logo/blog-logo.svg') : '';

$logoUrl = '';
$useCmsLogo = false;
if ($logo) {
    $logoUrl = _img_url($logo, 'original');
    $useCmsLogo = is_string($logoUrl) && $logoUrl !== '';
}

if ($useCmsLogo) {
    $alt = $brandName !== '' ? $brandName : ($siteTitle !== '' ? $siteTitle : 'Logo');
    echo '<a href="' . htmlspecialchars($home, ENT_QUOTES, 'UTF-8') . '" class="flex items-center gap-2 min-w-0">';

    $logoPath = '';
    if (is_string($logo)) {
        $dec = json_decode($logo, true);
        $logoPath = is_array($dec) ? (string) ($dec['path'] ?? '') : '';
    } elseif (is_array($logo)) {
        $logoPath = (string) ($logo['path'] ?? '');
    } elseif (is_object($logo)) {
        $logoPath = (string) ($logo->path ?? '');
    }
    $isSvg = $logoPath !== '' && preg_match('/\.svg$/i', $logoPath);

    if ($isSvg) {
        echo _imglazy($logo, [
            'title'  => $alt,
            'alt'    => $alt,
            'class'  => 'h-10 w-auto object-contain shrink-0',
            'width'  => 160,
            'height' => 40,
        ]);
    } else {
        $pick = static function ($data, bool $webp): string {
            if (!function_exists('_img_url')) {
                return '';
            }
            foreach (['medium', 'large'] as $sz) {
                $u = _img_url($data, $sz, $webp) ?: _img_url($data, $sz, false);
                if ($u) {
                    return $u;
                }
            }
            $u = _img_url($data, 'full', $webp) ?: _img_url($data, 'full', false);

            return is_string($u) ? $u : '';
        };

        $src = $pick($logo, true);
        if ($src === '') {
            $src = $pick($logo, false);
        }
        if ($src === '') {
            $src = $logoUrl;
        }

        $iw = null;
        $ih = null;
        $meta = is_string($logo) ? json_decode($logo, true) : (is_array($logo) ? $logo : (array) $logo);
        if (is_array($meta)) {
            foreach ((array) ($meta['sizes'] ?? []) as $row) {
                if (!is_array($row) || ($row['name'] ?? '') !== 'medium') {
                    continue;
                }
                $iw = isset($row['width']) ? (int) $row['width'] : null;
                $ih = isset($row['height']) ? (int) $row['height'] : null;
                break;
            }
        }

        echo '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"'
            . ' title="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"'
            . ' class="h-10 w-auto max-h-10 object-contain shrink-0"'
            . ' decoding="async" fetchpriority="high"'
            . ($iw && $ih ? ' width="' . (int) $iw . '" height="' . (int) $ih . '"' : '')
            . ' />';
    }

    if ($brandName !== '') {
        echo '<span class="font-bold truncate font-plus text-[color:var(--home-heading)]">' . htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') . '</span>';
    }
    echo '</a>';
} elseif ($fallbackSrc !== '') {
    $alt = htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8');
    echo '<a href="' . htmlspecialchars($home, ENT_QUOTES, 'UTF-8') . '" class="flex items-center gap-2 min-w-0 no-underline">';
    echo '<img src="' . htmlspecialchars($fallbackSrc, ENT_QUOTES, 'UTF-8') . '" alt="' . $alt . '" title="' . $alt . '" width="40" height="40" class="h-10 w-10 shrink-0 object-contain" decoding="async" fetchpriority="high" />';
    echo '<span class="font-bold truncate font-plus text-[color:var(--home-heading)]">' . $alt . '</span>';
    echo '</a>';
}
?>
