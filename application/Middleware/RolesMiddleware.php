<?php
namespace App\Middleware;

/**
 * Unified Roles/Permissions Middleware
 * 
 * Auto-detects request type and delegates to appropriate middleware
 * - API requests: JSON 403 response
 * - Web requests: Redirect to 403 page
 * 
 * Handles controller-action based permissions
 */
class RolesMiddleware
{
    /**
     * Handle role/permission authorization
     *
     * @param mixed $request Request information
     * @param callable $next Next middleware
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::mark('RolesMiddleware::handle');
        }
        // Get controller and action
        $controller = $request['controller'] ?? '';
        $action = $request['action'] ?? '';

        // Check authentication first
        global $me_info;
        
        if (empty($me_info) || empty($me_info['id'])) {
            // Not authenticated - get user from session
            if (\System\Libraries\Session::has('user_id')) {
                $user_id = \System\Libraries\Session::get('user_id');
                $usersModel = new \App\Models\UsersModel();
                $me_info = $usersModel->getUserById($user_id);
                
                if ($me_info) {
                    \System\Libraries\Session::set('role', $me_info['role']);
                    \System\Libraries\Session::set('permissions', $me_info['permissions']);
                }
            }
        }

        // Check if user has permission
        if (!empty($me_info) && !empty($me_info['id'])) {
            if (has_permission($controller, $action)) {
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop('RolesMiddleware::handle');
                }
                // Permission granted
                return $next($request);
            }
        }

        // Permission denied - detect response type
        $isApiRequest = $this->isApiRequest();

        if ($isApiRequest) {
            if (APP_DEBUGBAR) {
                \System\Libraries\Monitor::stop('RolesMiddleware::handle');
            }
            // API response - use Response library
            \System\Libraries\Response::sendError(
                \App\Libraries\Fastlang::__('Access denied. Insufficient permissions.'),
                ['required_permission' => $controller . ':' . $action],
                'INSUFFICIENT_PERMISSIONS',
                403
            );
        } else {
            // Web response - check if AJAX request
            if (is_ajax()) {
                if (APP_DEBUGBAR) {
                    \System\Libraries\Monitor::stop('RolesMiddleware::handle');
                }
                // AJAX request - use Response library
                \System\Libraries\Response::sendError(
                    \App\Libraries\Fastlang::__('You do not have permission to access this page!'),
                    ['roles_middleware' => ['Permission denied']],
                    'INSUFFICIENT_PERMISSIONS',
                    403
                );
            }
        }
        if (APP_DEBUGBAR) {
            \System\Libraries\Monitor::stop('RolesMiddleware::handle');
        }
        // Regular web request - throw exception for 403 page
        throw new \System\Core\AppException(
            'You do not have permission to access this page!<div>' . 
            $controller . '->' . $action . '()</div>',
            403,
            null,
            403
        );
    }

    /**
     * Detect if request is API request
     *
     * @return bool
     */
    private function isApiRequest()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '';
        
        if (strpos($path, '/api/') !== false) {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (strpos($accept, 'application/json') !== false && 
            strpos($accept, 'text/html') === false) {
            return true;
        }

        return false;
    }
}

