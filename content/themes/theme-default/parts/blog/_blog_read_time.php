<?php

/**
 * Parse created_at (unix | "Y-m-d H:i:s" | …) → unix timestamp.
 */
if (!function_exists('blog_created_at_to_unix')) {
    function blog_created_at_to_unix($created): int
    {
        if ($created === null || $created === '') {
            return 0;
        }
        if (is_numeric($created)) {
            return (int) $created;
        }
        $ts = strtotime((string) $created);

        return $ts !== false ? $ts : 0;
    }
}

/**
 * Làm tròn lên tới đầu phút kế nếu có giây (16:03:51 → 16:04:00).
 */
if (!function_exists('blog_unix_ceil_to_full_minute')) {
    function blog_unix_ceil_to_full_minute(int $unixTs): int
    {
        if ($unixTs < 1) {
            return 0;
        }
        $sec = (int) date('s', $unixTs);
        if ($sec > 0) {
            $unixTs += 60 - $sec;
        }

        return $unixTs;
    }
}

/**
 * Chỉ số phút (0–59) sau khi làm tròn — vd 16:03:51 → 4.
 */
if (!function_exists('blog_posted_minute_value_ceil')) {
    function blog_posted_minute_value_ceil(int $unixTs): int
    {
        $t = blog_unix_ceil_to_full_minute($unixTs);

        return $t > 0 ? (int) date('i', $t) : -1;
    }
}

/**
 * Nhãn "4 mins" từ created_at.
 */
if (!function_exists('blog_mins_only_label')) {
    function blog_mins_only_label(int $unixTs): string
    {
        $m = blog_posted_minute_value_ceil($unixTs);
        if ($m < 0) {
            return '';
        }

        return $m . ' mins';
    }
}
