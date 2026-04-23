<?php

namespace App\Controllers\Backend;

use App\Controllers\BackendController;
use System\Libraries\Render\View;
use App\Libraries\Fastlang as Flang;
use App\Libraries\Admin\AdminMenuBuilder;

class HomeController extends BackendController
{
    public function __construct()
    {
        parent::__construct();
        Flang::load('general', APP_LANG);
    }

    public function index()
    {
        global $me_info;

        $component = [];
        $globPath = APP_THEME_PATH . 'parts/homecomponent/*.php';
        $files = glob($globPath);
        if (is_array($files)) {
            foreach ($files as $file) {
                $component[] = basename($file, '.php');
            }
        }

        $menuData = AdminMenuBuilder::getMenuData();

        $user_info = is_array($me_info) ? $me_info : [];
        if (!isset($user_info['role'])) {
            $user_info['role'] = '';
        }

        $this->data('component', $component);
        $this->data('title', Flang::__('Dashboard'));
        $this->data('menuData', $menuData);
        $this->data('user_info', $user_info);
        $this->data('breadcrumb', [
            [
                'name' => __('Dashboard'),
                'url' => admin_url('home'),
                'active' => true,
            ],
        ]);

        echo View::make('home_index', $this->data)->render();
    }
}
