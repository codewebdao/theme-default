<?php
/**
 * Schema Context – Thông tin page hiện tại
 *
 * Class đơn giản, chỉ chứa:
 * - $type   (front, product, article, …)
 * - $payload (object truyền từ view)
 *
 * KHÔNG hook, KHÔNG logic. Chỉ data container.
 *
 * @package System\Libraries\Render\Schema
 * @since 1.0.0
 */

namespace System\Libraries\Render\Schema;

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Context
{
    /** @var string Schema type: front, product, article, … */
    public $type;

    /** @var object|null Payload từ view (product, post, …) */
    public $payload;

    /**
     * Create context (internal – gọi từ Schema::render).
     *
     * @param string $type
     * @param object|null $payload
     */
    public function __construct($type = 'front', $payload = null)
    {
        $this->type    = $type;
        $this->payload = $payload;
    }

    /**
     * Factory: build context từ tham số render.
     *
     * @param string|null $type
     * @param object|array|null $payload
     * @return self
     */
    public static function make($type = null, $payload = null)
    {
        $type    = $type ?: 'front';
        $payload = is_array($payload) ? (object) $payload : $payload;
        return new self($type, $payload);
    }
}
