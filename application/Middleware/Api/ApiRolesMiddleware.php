<?php
namespace App\Middleware\Api;

use System\Libraries\Response;

/**
 * API Roles/Permissions Middleware
 * 
 * Validates user roles and permissions for API requests
 * Returns 403 JSON if forbidden (NO redirects)
 * 
 * Usage in routes:
 * $router->post('api/v2/admin/users', 'AdminController::users', [
 *     ApiAuthMiddleware::class,
 *     ApiRolesMiddleware::class => ['roles' => ['admin', 'moderator']]
 * ]);
 */
class ApiRolesMiddleware
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
        global $me_info;

        // Check if user is authenticated
        if (empty($me_info) || !isset($me_info['user_id'])) {
            return $this->forbidden(\App\Libraries\Fastlang::__('Authentication required'), 'NOT_AUTHENTICATED');
        }

        $userRole = $me_info['role'] ?? 'guest';
        $userPermissions = $me_info['permissions'] ?? [];

        // Check roles (if specified)
        if (isset($params['roles']) && !empty($params['roles'])) {
            $requiredRoles = (array) $params['roles'];
            
            if (!in_array($userRole, $requiredRoles)) {
                return $this->forbidden(
                    \App\Libraries\Fastlang::__('Insufficient role. Required: %1%', implode(', ', $requiredRoles)),
                    'INSUFFICIENT_ROLE'
                );
            }
        }

        // Check permissions (if specified)
        if (isset($params['permissions']) && !empty($params['permissions'])) {
            $requiredPermissions = (array) $params['permissions'];
            
            foreach ($requiredPermissions as $permission) {
                if (!in_array($permission, $userPermissions)) {
                    return $this->forbidden(
                        \App\Libraries\Fastlang::__('Missing permission: %1%', $permission),
                        'MISSING_PERMISSION'
                    );
                }
            }
        }
        // All checks passed
        return $next($request);
    }

    /**
     * Send 403 forbidden response and exit
     *
     * @param string $message Error message
     * @param string $errorCode Error code
     * @return void
     */
    private function forbidden($message, $errorCode)
    {
        Response::sendForbidden($message, $errorCode);
    }
}

