<?php

/**
 * Asset Build Config
 *
 * Định nghĩa các bundle (signature) để build CSS/JS khi admin Save Performance settings.
 * Mỗi area có thể có head + footer; mỗi location là danh sách file (relative to theme assets).
 *
 * File output: writable/build/assets/{area}/{location}.{hash}.min.css|js
 * Manifest: writable/build/assets/manifest.json
 *
 * @see application/Services/Asset/AssetBuilderService.php
 */

if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

return [
    'theme' => null, // null = use APP_THEME_NAME at build time

    'Frontend' => [
        'head' => [
            // CSS: path relative to content/themes/{theme}/Frontend/assets/
            'css' => [],
            // JS (head): rarely used
            'js' => [],
        ],
        'footer' => [
            'css' => [],
            'js' => [],
        ],
    ],

    'Backend' => [
        'head' => [
            'css' => [],
            'js' => [],
        ],
        'footer' => [
            'css' => [],
            'js' => [],
        ],
    ],
];
