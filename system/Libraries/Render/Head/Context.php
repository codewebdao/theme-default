<?php
/**
 * Head Context – Thông tin layout + payload cho Head (title, meta, OG, canonical)
 *
 * Tương tự Schema Context: Controller set context một lần, Head::render() dùng để build mặc định.
 * View không cần set title/meta/OG khi dùng mặc định; chỉ gọi Head khi override hoặc dùng filter.
 *
 * @package System\Libraries\Render\Head
 */

namespace System\Libraries\Render\Head;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Context
{
    /** @var array|null ['layout' => string, 'payload' => mixed] */
    private static $current = null;

    /**
     * Set current head context. Gọi từ FrontendController cùng lúc Schema::setCurrentContext().
     *
     * @param string $layout Layout name: front-page, 404, search, page, single-blogs, archive, …
     *                         Mọi tên không khớp case trong Builder::build() dùng fallback mặc định
     *                         (meta, canonical từ base_url / payload['canonical'], …).
     * @param mixed  $payload Page/post/query data (object hoặc array)
     */
    public static function setCurrent($layout, $payload = null)
    {
        self::$current = [
            'layout'  => $layout,
            'payload' => $payload,
        ];
    }

    /**
     * Get current head context.
     *
     * @return array|null ['layout' => string, 'payload' => mixed] or null
     */
    public static function getCurrent()
    {
        return self::$current;
    }

    /**
     * Clear current context (tests / khi chuyển context).
     */
    public static function clear()
    {
        self::$current = null;
    }
}
