<?php
namespace System\Core;
use App\Libraries\Fastlang as Flang;

// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}
class BaseController {

    /**
     * Data to be passed to view
     * @var array
     */
    protected $data = [];

    public function __construct() {
        // Common initialization for all controllers
        // Example: load helpers, libraries, check session, etc.
    }

    /**
     * Check permission for controller and action
     * @param string $action Action name
     * @return bool
     */
    protected function checkPermission($action = null, $controller = null) {
        if (empty($controller)) {
            $controller = get_class($this);
        }
        if (empty($action) || empty($controller)) {
            return false;
        }
        return has_permission($controller, $action) ?? false;
    }

    /**
     * Check permission and throw exception if not allowed
     * 
     * @param string $action Action name
     * @param string|null $controller Controller name (optional)
     * @return void
     * @throws \System\Core\AppException
     */
    protected function requirePermission($action, $controller = null)
    {
        if (!$this->checkPermission($action, $controller)) {
            throw new \System\Core\AppException(__('You do not have permission to perform this action'), 403, null, 403);
        }
    }

    /**
     * Data method: set or get data
     * - If 2 parameters passed: set data
     * - If 1 parameter passed: get data
     * 
     * @param string $key Data name
     * @param mixed|null $value Data value (if any)
     * @return mixed|null Returns data if only 1 parameter passed
     */
    public function data($key, $value = null) {
        if ($value !== null) {
            // Set data if 2 parameters
            $this->data[$key] = $value;
        } else {
            // Get data if only 1 parameter
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }
    }


}