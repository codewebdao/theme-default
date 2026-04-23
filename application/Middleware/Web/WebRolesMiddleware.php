<?php
namespace App\Middleware\Web;

use System\Libraries\Session;

/**
 * Web Roles/Permissions Middleware
 * 
 * Validates user roles and permissions for web requests
 * Redirects to 403 page or dashboard if forbidden
 * 
 * Usage in routes:
 * $router->get('admin/users', 'AdminController::users', [
 *     WebAuthMiddleware::class,
 *     WebRolesMiddleware::class => ['roles' => ['admin']]
 * ]);
 */
class WebRolesMiddleware
{
    /**
     * Handle CORS headers
     *
     * @param mixed $request Request information
     * @param callable $next Next middleware
     * @return mixed
     */
    public function handle($request, $next)
    {
        // Check if authenticated
        if (!Session::has('user_id')) {
            Session::flash('error', \App\Libraries\Fastlang::__('Please login to continue'));
            redirect(auth_url('login'));
            exit;
        }

        $userRole = Session::get('role') ?? 'guest';
        $userPermissions = Session::get('permissions') ?? [];

        // Check roles
        if (isset($params['roles']) && !empty($params['roles'])) {
            $requiredRoles = (array) $params['roles'];
            
            if (!in_array($userRole, $requiredRoles)) {
                Session::flash('error', \App\Libraries\Fastlang::__('Access denied. Insufficient privileges.'));
                redirect(base_url());
                exit;
            }
        }

        // Check permissions
        if (isset($params['permissions']) && !empty($params['permissions'])) {
            $requiredPermissions = (array) $params['permissions'];
            
            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $userPermissions)) {
                    Session::flash('error', \App\Libraries\Fastlang::__('Access denied. Missing permission: %1%', $permission));
                    redirect(base_url());
                    exit;
                }
            }
        }
        // All checks passed
        return $next($request);
    }
}
