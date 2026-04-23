<?php

namespace App\Controllers;

use System\Core\BaseController;
use App\Libraries\Fastlang as Flang;
use System\Libraries\Session;
use System\Libraries\Response;
use System\Libraries\Render\View;
use System\Libraries\Render\Theme\ThemeContext;
use App\Libraries\Admin\AdminMenuBuilder;

/**
 * AppController
 * 
 * Base controller for other "backend" controllers.
 * Automatically loads helpers, initializes sidebar, header, footer, etc.
 */
class BackendController extends BaseController
{
    protected $post_lang;

    /**
     * Constructor
     * - Load helpers
     * - Initialize assets with default CSS/JS
     * - Pre-render layout parts (header, footer, sidebar)
     */
    public function __construct()
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('BackendController::__construct');
            \System\Libraries\Monitor::mark('BackendController::parentConstructor');
        }
        // Call parent BaseController constructor (to maintain common functionality)
        parent::__construct();
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('BackendController::parentConstructor');
        }
        ThemeContext::setScope('admin');
        ThemeContext::setTheme(defined('APP_THEME_ADMIN_NAME') ? APP_THEME_ADMIN_NAME : (defined('APP_THEME_NAME') ? APP_THEME_NAME : 'default'), 'admin');
        View::scope('admin');

        // User ID Global variable
        global $me_info;
        if (empty($me_info)) {
            $me_id = Session::get('user_id');
            $usersModel = new \App\Models\UsersModel();
            $me_info = $usersModel->getUserById($me_id);
            if (empty($me_info)) {
                redirect(auth_url('logout'));
            }
        }
        // Load helper (View: view_header / view_footer + assets_head / assets_footer)
        load_helpers(['uri', 'string', 'languages', 'themes', 'links', 'images', 'query', 'posts', 'terms', 'forms', 'View']);
        Flang::load('Backend/Global');

        $adminFunctions = rtrim((string) APP_THEME_PATH, '/\\') . DIRECTORY_SEPARATOR . 'functions.php';
        if (is_file($adminFunctions)) {
            require_once $adminFunctions;
        }

        $this->post_lang = S_GET('post_lang') ?? APP_LANG;

        $this->prepareAdminLayoutDefaults();

        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('BackendController::__construct');
        }
    }

    /**
     * Gán user_info + menuData mặc định cho mọi view admin (sidebar/topbar cần có).
     * Menu lấy qua {@see AdminMenuBuilder} (không instantiate Block).
     * Các action có thể ghi đè qua $this->data('user_info', ...) / menuData.
     */
    protected function prepareAdminLayoutDefaults(): void
    {
        global $me_info;

        if (!array_key_exists('user_info', $this->data)) {
            $user_info = is_array($me_info) ? $me_info : [];
            $user_info['avatar'] = $user_info['avatar'] ?? '';
            $user_info['fullname'] = $user_info['fullname'] ?? '';
            $user_info['role'] = $user_info['role'] ?? '';
            $user_info['email'] = $user_info['email'] ?? '';
            $this->data('user_info', $user_info);
        }

        if (!array_key_exists('menuData', $this->data)) {
            $this->data('menuData', AdminMenuBuilder::getMenuData());
        }
    }

    /**
     * Send success JSON response using Response library
     * Used by backend controllers for AJAX/API-like responses.
     *
     * @param array $data Response data
     * @param string $message Success message
     * @param int $statusCode HTTP status code (default: 200)
     * @return void
     */
    protected function success($data = [], $message = 'Success', $statusCode = 200)
    {
        Response::sendSuccess($data, $message, $statusCode);
    }

    /**
     * Send error JSON response using Response library
     *
     * @param string $message Error message
     * @param mixed $errors Error details (can include error_code)
     * @param int $statusCode HTTP status code (default: 400)
     * @return void
     */
    protected function error($message = 'An error occurred', $errors = [], $statusCode = 400)
    {
        $errorCode = null;
        if (is_array($errors) && isset($errors['error_code'])) {
            $errorCode = $errors['error_code'];
            unset($errors['error_code']);
        }
        Response::sendError($message, $errors, $errorCode, $statusCode);
    }
}
