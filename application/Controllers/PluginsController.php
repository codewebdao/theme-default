<?php

namespace App\Controllers;

use System\Libraries\Render\View;

/**
 * Base cho controller plugin trong khu vực admin (đã đăng nhập).
 * Dùng chung init với {@see BackendController} (theme admin, View, menu) + JS backend plugin.
 */
class PluginsController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        View::addJs('plugins-backend', 'js/backend.js', [], null, false, false, false, false);
    }
}
