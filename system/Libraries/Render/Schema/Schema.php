<?php
/**
 * Schema Library – Entry point (public API)
 *
 * Lifecycle: Schema::get() / Schema::render()
 *   → Context::make() (hoặc getCurrentContext từ FrontendController)
 *   → Builder::build()
 *   → apply_filters('schema.render', $schemas, $context)
 *   → Graph::render()
 *
 * Schema chính được gọi trong Head::render() qua Schema::get(). View chỉ cần view_head().
 * Mọi hook chạy trước khi render JSON.
 *
 * @package System\Libraries\Render\Schema
 * @since 1.0.0
 */

namespace System\Libraries\Render\Schema;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Schema
{
    /** @var array|null Current context [type => string, payload => mixed] when no args passed to render() */
    private static $currentContext = null;

    /**
     * Set current page context (type + payload). Gọi từ FrontendController trước khi render view.
     * Head::render() gọi Schema::get() không tham số → context này được dùng.
     *
     * @param string $type    Schema type: front, page, article, product, archive, search, person, …
     * @param object|array|null $payload Data (post, page, query, …)
     */
    public static function setCurrentContext($type, $payload = null)
    {
        self::$currentContext = [
            'type'    => $type ?: 'front',
            'payload' => $payload,
        ];
    }

    /**
     * Get current context (set by FrontendController or manually).
     *
     * @return array|null ['type' => string, 'payload' => mixed] or null
     */
    public static function getCurrentContext()
    {
        return self::$currentContext;
    }

    /**
     * Clear current context (useful for tests or when switching context).
     */
    public static function clearCurrentContext()
    {
        self::$currentContext = null;
    }

    /**
     * Render JSON-LD schema (output <script type="application/ld+json">).
     *
     * Được gọi trong view. Không echo JSON trực tiếp trong view.
     * Khi gọi không tham số: dùng context đã set bởi FrontendController (từ layout hiện tại).
     *
     * @param string|null $type   Schema type: front, product, article, …
     * @param object|array|null $payload Data từ view (product, post, …)
     * @return void Echoes HTML script tag
     *
     * @example
     * Schema::render();              // context từ controller (page type tự động)
     * Schema::render('product', $product);
     * Schema::render('article', $post);
     */
    public static function render($type = null, $payload = null)
    {
        if ($type === null && $payload === null) {
            $current = self::getCurrentContext();
            if ($current !== null) {
                $type    = $current['type'];
                $payload = $current['payload'];
            }
        }
        $context = Context::make($type ?: 'front', $payload);
        $schemas = Builder::build($context);

        if (function_exists('apply_filters')) {
            $schemas = apply_filters('schema.render', $schemas, $context);
        }

        if (!is_array($schemas)) {
            $schemas = [];
        }

        echo Graph::render($schemas);
    }

    /**
     * Return JSON-LD string (không echo) – dùng khi cần gắn vào Head hoặc buffer.
     *
     * @param string|null $type
     * @param object|array|null $payload
     * @return string HTML <script type="application/ld+json">...</script>
     */
    public static function get($type = null, $payload = null)
    {
        if ($type === null && $payload === null) {
            $current = self::getCurrentContext();
            if ($current !== null) {
                $type    = $current['type'];
                $payload = $current['payload'];
            }
        }
        $context = Context::make($type ?: 'front', $payload);
        $schemas = Builder::build($context);

        if (function_exists('apply_filters')) {
            $schemas = apply_filters('schema.render', $schemas, $context);
        }

        if (!is_array($schemas)) {
            $schemas = [];
        }

        return Graph::render($schemas);
    }
}
