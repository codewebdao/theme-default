<?php
namespace App\Middleware\Web;

use System\Libraries\Session;

/**
 * Web Authentication Middleware
 * 
 * Validates session for web requests
 * Redirects to login if unauthorized
 * Supports auto-login from remember cookie
 */
class WebAuthMiddleware
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
        // Check if user is logged in
        if (!Session::has('user_id')) {
            // Try auto-login from remember cookie
            $this->tryAutoLogin();
            
            // Check again after auto-login attempt
            if (!Session::has('user_id')) {
                // Not authenticated - redirect to login
                Session::flash('error', \App\Libraries\Fastlang::__('Please login to continue'));
                Session::set('redirect_after_login', $_SERVER['REQUEST_URI'] ?? '/');
                redirect(auth_url('login'));
                exit;
            }
        }

        // User is authenticated - continue
        return $next($request);
    }

    /**
     * Try to auto-login from remember cookie
     */
    private function tryAutoLogin()
    {
        // Check if remember cookie exists
        if (!isset($_COOKIE['cmsff_remember'])) {
            return;
        }

        $rememberToken = $_COOKIE['cmsff_remember'];

        // Validate token
        $tokenData = \App\Libraries\Fasttoken::checkToken($rememberToken);
        
        if (!$tokenData || !isset($tokenData['user_id'], $tokenData['fingerprint'])) {
            // Invalid token - clear cookie
            setcookie('cmsff_remember', '', time() - 3600, '/');
            return;
        }

        // Validate session in database
        $sessionModel = new \App\Models\UserSessionsModel();
        $tokenHash = hash('sha256', $rememberToken);
        
        $session = $sessionModel->validateSession(
            $tokenData['user_id'],
            $tokenData['fingerprint'],
            'remember',
            $tokenHash
        );

        if (!$session) {
            // Session revoked or expired
            setcookie('cmsff_remember', '', time() - 3600, '/');
            return;
        }

        // Get user
        $userModel = new \App\Models\UsersModel();
        $user = $userModel->getUserById($tokenData['user_id']);

        if (!$user || $user['status'] !== 'active') {
            return;
        }

        // Verify password hasn't changed
        if (isset($tokenData['password_at'])) {
            if (empty($user['password_at']) || $user['password_at'] != $tokenData['password_at']) {
                // Password changed - revoke all
                $sessionModel->revokeAllSessions($user['id']);
                setcookie('cmsff_remember', '', time() - 3600, '/');
                return;
            }
        }
        global $me_info;
        $me_info = $user;
        // Auto-login successful - set session
        Session::set('user_id', $user['id']);
        Session::set('role', $user['role']);
        Session::set('permissions', user_permissions($user['role'], $user['permissions']));
        Session::set('session_id', $session['id']);
        Session::regenerate();

        // Update session activity
        $sessionModel->touchToken($session['id'], 'primary');
    }
}
