<?php

namespace App\Controllers\Api\V2;

use App\Controllers\BaseAuthController;
use System\Libraries\Session;
use System\Libraries\Security;
use System\Libraries\Validate;
use System\Libraries\Events;
use System\Libraries\Response;

/**
 * API v2 Authentication Controller
 * 
 * This controller handles authentication for the API v2 interface.
 * It extends BaseAuthController to inherit common authentication logic
 * and implements API-specific JSON response handling.
 * 
 * @package App\Controllers\Api\V2
 * @author Your Name
 * @version 1.0.0
 */
class AuthController extends BaseAuthController
{
    /**
     * Constructor - Initialize API-specific components
     */
    public function __construct()
    {
        // CORS handling
        _cors();

        // Parent initialization
        parent::__construct();

        // Set content type
        header('Content-Type: application/json; charset=utf-8');

        // Security headers
        $this->setSecurityHeaders();
    }

    /**
     * Set comprehensive security headers for API
     */
    protected function setSecurityHeaders()
    {
        // Prevent XSS
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');

        // Strict CSP for API (no resources allowed)
        header("Content-Security-Policy: default-src 'none'");

        // Referrer policy
        header('Referrer-Policy: no-referrer');

        // Remove server identification
        header_remove('X-Powered-By');
        header('X-Powered-By: CMSFullForm');

        // HSTS (if HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Rate limit headers (informational)
        $ip = get_client_ip();
        $loginKey = 'login:' . $ip;

        if (\System\Libraries\RateLimiter::attempts($loginKey) > 0) {
            $remaining = \System\Libraries\RateLimiter::remaining($loginKey, 5);
            $resetAt = time() + \System\Libraries\RateLimiter::availableIn($loginKey);

            header('X-RateLimit-Limit: 5');
            header('X-RateLimit-Remaining: ' . $remaining);
            header('X-RateLimit-Reset: ' . $resetAt);
        }
    }

    /**
     * CSRF not required for API
     * Bearer token provides sufficient authentication
     */
    protected function isCsrfRequired()
    {
        return false; // API doesn't use CSRF
    }

    /**
     * CSRF verification always succeeds for API
     */
    protected function verifyCsrfToken($token)
    {
        return true; // Always valid for API
    }

    /**
     * No CSRF token generation for API
     */
    protected function generateCsrfToken($time = 600)
    {
        return ''; // Not needed
    }

    /**
     * Login endpoint with rate limiting and security
     * 
     * POST /api/v2/auth/login
     * Body: {username, password, device_fingerprint}
     * 
     * Rate Limit: 5 attempts per 5 minutes per IP
     * 
     * @return void
     */
    public function login()
    {
        // Get client IP (handles proxies)
        $ip = get_client_ip();
        $key = 'login:' . $ip;

        // Rate limit check
        if (\System\Libraries\RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = \System\Libraries\RateLimiter::availableIn($key);

            return $this->error(
                __('Too many login attempts. Try again in %1% seconds.', $seconds),
                [
                    'retry_after' => $seconds,
                    'retry_at' => date('Y-m-d H:i:s', time() + $seconds),
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ],
                429
            );
        }

        // Hit rate limiter (count this attempt)
        \System\Libraries\RateLimiter::hit($key, 300);

        // Call parent login method
        // Note: On success, handleSuccessfulLogin() will clear rate limit
        return parent::login();
    }

    /**
     * Register endpoint with rate limiting and flood protection
     * 
     * POST /api/v2/auth/register
     * Body: {username, email, password, password_repeat, fullname, phone?, terms, device_fingerprint}
     * 
     * Note: Fingerprint REQUIRED for registration (creates session)
     * Rate Limit: 3 attempts per hour per IP
     * 
     * @return void
     */
    public function register()
    {
        // Validate device fingerprint (REQUIRED for registration)
        $fingerprint = S_POST('device_fingerprint');

        if (empty($fingerprint) || !validate_fingerprint($fingerprint)) {
            return $this->error(
                __('Valid device fingerprint required for registration'),
                ['error_code' => 'FINGERPRINT_REQUIRED'],
                400
            );
        }

        // Rate limiting by IP (prevent registration flooding)
        $ip = get_client_ip();
        $key = 'register:' . $ip;

        if (\System\Libraries\RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = \System\Libraries\RateLimiter::availableIn($key);

            return $this->error(
                __('Too many registration attempts. Try again in %1% minutes.', ceil($seconds / 60)),
                [
                    'retry_after' => $seconds,
                    'retry_at' => date('Y-m-d H:i:s', time() + $seconds),
                    'error_code' => 'REGISTRATION_RATE_LIMIT'
                ],
                429
            );
        }

        // Additional check: Bot detection
        if (is_bot_user_agent()) {
            return $this->error(
                __('Automated registration not allowed'),
                ['error_code' => 'BOT_DETECTED'],
                403
            );
        }

        // Hit rate limiter
        \System\Libraries\RateLimiter::hit($key, 3600);

        return parent::register();
    }

    /**
     * Forgot password endpoint with rate limiting
     * 
     * POST /api/v2/auth/forgot
     * Body: {email}
     * 
     * Rate Limit: 3 attempts per 15 minutes per IP
     * 
     * @return void
     */
    public function forgot()
    {
        // Rate limiting by IP (prevent email bombing)
        $ip = get_client_ip();
        $key = 'forgot:' . $ip;

        if (\System\Libraries\RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = \System\Libraries\RateLimiter::availableIn($key);

            return $this->error(
                __('Too many password reset requests. Try again in %1% minutes.', ceil($seconds / 60)),
                [
                    'retry_after' => $seconds,
                    'retry_at' => date('Y-m-d H:i:s', time() + $seconds),
                    'error_code' => 'FORGOT_PASSWORD_RATE_LIMIT'
                ],
                429
            );
        }

        // Hit rate limiter
        \System\Libraries\RateLimiter::hit($key, 900);

        return parent::forgot();
    }

    // logout() is inherited from BaseAuthController

    /**
     * Get user profile (override to use Bearer token)
     * 
     * GET /api/v2/auth/profile
     * Header: Authorization: Bearer {access_token}
     * 
     * @return void
     */
    public function profile()
    {
        // Use _auth() to support Bearer token (not just session)
        $user = $this->_auth();

        if (!$user) {
            return $this->error(
                __('Unauthorized'),
                ['error_code' => 'UNAUTHORIZED'],
                401
            );
        }

        // Get full user data from database
        $userData = $this->usersModel->getUserById($user['id']);

        if (!$userData) {
            return $this->error(
                __('User not found'),
                ['error_code' => 'USER_NOT_FOUND'],
                404
            );
        }

        // Prepare profile data
        $me_info = $this->_prepareProfileData($userData);

        return $this->displayProfilePage($me_info);
    }

    /**
     * Update user profile (override to use Bearer token)
     * 
     * POST /api/v2/auth/set-profile
     * Header: Authorization: Bearer {access_token}
     * Body: {fullname?, phone?, country?, ...}
     * 
     * @return void
     */
    public function set_profile()
    {
        // Use _auth() to support Bearer token
        $user = $this->_auth();

        if (!$user) {
            return $this->error(
                __('Unauthorized'),
                ['error_code' => 'UNAUTHORIZED'],
                401
            );
        }

        // Get full user data
        $userData = $this->usersModel->getUserById($user['id']);

        if (!$userData) {
            return $this->error(
                __('User not found'),
                ['error_code' => 'USER_NOT_FOUND'],
                404
            );
        }

        // Check if POST request
        if (!HAS_POST('fullname') && !HAS_POST('phone') && !HAS_POST('country')) {
            // No fields to update - just return profile
            return $this->displayProfilePage($this->_prepareProfileData($userData));
        }

        // Validate and update profile
        $errors = $this->_setProfile($user['id'], $userData);

        if (empty($errors)) {
            // Success - get updated data
            $updatedUser = $this->usersModel->getUserById($user['id']);
            $me_info = $this->_prepareProfileData($updatedUser);

            return $this->success([
                'user' => $me_info
            ], __('Profile updated successfully'));
        }

        return $this->error(
            __('Profile update failed'),
            $errors,
            400
        );
    }

    /**
     * Change password (override to use Bearer token)
     * 
     * POST /api/v2/auth/change-password
     * Header: Authorization: Bearer {access_token}
     * Body: {current_password, new_password, confirm_password}
     * 
     * @return void
     */
    public function change_password()
    {
        // Use _auth() to support Bearer token
        $user = $this->_auth();

        if (!$user) {
            return $this->error(
                __('Unauthorized'),
                ['error_code' => 'UNAUTHORIZED'],
                401
            );
        }

        // Call parent method
        return parent::change_password();
    }

    // Abstract method implementations for API
    protected function handleInactiveAccount($user)
    {
        return $this->error(__('Account not active'), [
            'user_id' => $user['id'],
            'status' => $user['status']
        ], 403);
    }

    protected function handleSuccessfulLogin($user)
    {
        // Clear rate limiter on successful login
        $ip = get_client_ip();
        \System\Libraries\RateLimiter::clear('login:' . $ip);

        try {
            // Get device fingerprint
            $fingerprint = $this->_getClientFingerprint();

            if (!$fingerprint) {
                // No fingerprint - return error
                return $this->error(
                    __('Device fingerprint required'),
                    ['error_code' => 'FINGERPRINT_REQUIRED'],
                    400
                );
            }

            // Generate dual tokens (access + refresh)
            $sessionId = \System\Libraries\Session::get('session_id');
            $tokens = \App\Libraries\Fasttoken::generateTokenPair($user, $fingerprint, $sessionId);

            if (!$tokens) {
                return $this->error(
                    __('Failed to generate authentication tokens'),
                    ['error_code' => 'TOKEN_GENERATION_FAILED'],
                    500
                );
            }

            // Save API session with tokens (separate from web remember session)
            try {
                $apiSessionId = $this->userSessionsModel->createSession(
                    $user['id'],
                    $fingerprint,
                    $tokens, // access_token and refresh_token
                    [
                        'ip_address' => get_client_ip(),
                        'user_agent' => get_user_agent(),
                        'token_type' => 'access',
                        'expires_at' => date('Y-m-d H:i:s', time() + \App\Libraries\Fasttoken::REFRESH_TOKEN_TTL)
                    ]
                );

                // Update session_id to API session
                if ($apiSessionId) {
                    \System\Libraries\Session::set('api_session_id', $apiSessionId);
                }
            } catch (\Exception $e) {
                error_log('Failed to save API session: ' . $e->getMessage());
            }

            // Prepare user data (remove sensitive info)
            $me_info = $this->_prepareProfileData($user);

            // Return tokens in JSON only (NO cookies)
            return $this->success([
                'user' => $me_info,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'refresh_expires_in' => $tokens['refresh_expires_in']
            ], __('Login successful'));
        } catch (\Exception $e) {
            error_log('handleSuccessfulLogin error: ' . $e->getMessage());

            return $this->error(
                __('Login error'),
                ['error_code' => 'LOGIN_ERROR'],
                500
            );
        }
    }

    protected function handleSuccessfulRegistration($user_id, $userData)
    {
        return $this->success([
            'user_id' => $user_id,
            'message' => __('Registration successful')
        ], __('Registration successful'));
    }

    protected function handleForgotPasswordSent($user)
    {
        return $this->success([
            'email' => $user['email'],
            'message' => __('Password reset code sent successfully')
        ], __('Password reset code sent successfully'));
    }

    // Additional abstract method implementations
    protected function handleAlreadyLoggedIn()
    {
        try {
            $user = $this->usersModel->getUserById(\System\Libraries\Session::get('user_id'));

            if (!$user) {
                return $this->error(__('User not found'), ['error_code' => 'USER_NOT_FOUND'], 404);
            }

            // For API, generate dual tokens (no cookies)
            $fingerprint = $this->_getClientFingerprint();
            $sessionId = \System\Libraries\Session::get('session_id');

            if ($fingerprint) {
                $tokens = \App\Libraries\Fasttoken::generateTokenPair($user, $fingerprint, $sessionId);

                if ($tokens) {
                    return $this->success([
                        'user' => $this->_prepareProfileData($user),
                        'access_token' => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'],
                        'token_type' => $tokens['token_type'],
                        'expires_in' => $tokens['expires_in']
                    ], __('Already logged in'));
                }
            }

            return $this->success([
                'user' => $this->_prepareProfileData($user)
            ], __('Already logged in'));
        } catch (\Exception $e) {
            error_log('handleAlreadyLoggedIn error: ' . $e->getMessage());
            return $this->error(__('Session error'), ['error_code' => 'SESSION_ERROR'], 500);
        }
    }

    protected function handleSessionExpired()
    {
        return $this->error(__('Session expired'), [], 401);
    }

    protected function handleAccountNotFound()
    {
        return $this->error(__('Account not found'), [], 404);
    }

    protected function handleAccountAlreadyActive()
    {
        return $this->success([], __('Account is already active'));
    }

    protected function handleAccountDisabled()
    {
        return $this->error(__('Account is disabled'), [], 403);
    }

    protected function handleInvalidAccountStatus()
    {
        return $this->error(__('Invalid account status'), [], 400);
    }

    protected function handleActivationExpired($activationType, $userOptional)
    {
        return $this->error(__('Activation code has expired'), [], 400);
    }

    protected function handleCsrfFailed()
    {
        return $this->error(__('CSRF verification failed'), [], 400);
    }
    protected function handleConfirmCsrfFailed()
    {
        return $this->error(__('CSRF verification failed'), [], 400);
    }

    protected function handleMaxAttemptsReached($activationType, $userOptional)
    {
        return $this->error(__('Too many failed attempts. Please wait 30 minutes before trying again.'), [], 429);
    }

    protected function handleCodeVerified($user_id, $activationString)
    {
        return $this->success([
            'user_id' => $user_id,
            'activation_string' => $activationString,
            'message' => __('Code verified successfully')
        ], __('Code verified successfully'));
    }

    protected function handleInvalidCode($activationType = 'registration', $remainingAttempts = 0, $user = null)
    {
        return $this->error(__('Invalid code. %1% attempts remaining.', $remainingAttempts), [], 400);
    }

    protected function displayConfirmForm($activationType, $user)
    {
        $confirmData = [
            'csrf_token' => $this->generateCsrfToken(),
            'activation_type' => $activationType,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'cooldown_until' => $user['cooldown_until'] ?? 0
            ],
            'message' => __('Confirmation form displayed')
        ];
        return $this->success($confirmData, __('Confirmation form displayed'));
    }

    protected function handleInvalidActivationLink()
    {
        return $this->error(__('Invalid activation link'), [], 400);
    }

    protected function handleActivationLinkExpired()
    {
        return $this->error(__('Activation link has expired'), [], 400);
    }

    protected function handleForgotPasswordConfirmation($user_id)
    {
        return $this->success([
            'user_id' => $user_id,
            'message' => __('Password reset confirmation required')
        ], __('Password reset confirmation required'));
    }

    protected function handleSuccessfulActivation($user)
    {
        try {
            // For API, generate dual tokens (no cookies)
            $fingerprint = $this->_getClientFingerprint();

            if (!$fingerprint) {
                // Fallback: activation successful but no tokens
                $me_info = $this->_prepareProfileData($user);
                return $this->success([
                    'user' => $me_info,
                    'message' => __('Account activated. Please login to get tokens.')
                ], __('Account activated'));
            }

            $sessionId = \System\Libraries\Session::get('session_id');

            // Generate tokens
            $tokens = \App\Libraries\Fasttoken::generateTokenPair($user, $fingerprint, $sessionId);

            $me_info = $this->_prepareProfileData($user);

            return $this->success([
                'user' => $me_info,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => $tokens['token_type'],
                'expires_in' => $tokens['expires_in'],
                'refresh_expires_in' => $tokens['refresh_expires_in']
            ], __('Account activated successfully'));
        } catch (\Exception $e) {
            error_log('Activation token error: ' . $e->getMessage());

            // Activation succeeded but token generation failed
            return $this->success([
                'user' => $this->_prepareProfileData($user),
                'message' => __('Account activated. Please login to get tokens.')
            ], __('Account activated'));
        }
    }

    protected function handleCooldownPeriod($remainingMinutes)
    {
        return $this->error(__('Please wait %1% minutes before requesting a new code.', $remainingMinutes), [], 429);
    }

    protected function handleCodeResent()
    {
        return $this->success([], __('New code sent successfully'));
    }

    protected function handleInvalidResetRequest()
    {
        return $this->error(__('Invalid reset request'), [], 400);
    }

    protected function handlePasswordResetValidationErrors($errors)
    {
        return $this->error(__('Password reset validation failed'), $errors, 400);
    }

    protected function handlePasswordResetSuccess()
    {
        return $this->success([], __('Password reset successfully'));
    }


    protected function handleUserNotFound()
    {
        return $this->error(__('User not found'), [], 404);
    }

    protected function handlePasswordChangeSuccess()
    {
        return $this->success([], __('Password changed successfully'));
    }

    protected function handlePasswordChangeErrors($errors, $user)
    {
        return $this->error(__('Password change failed'), $errors, 400);
    }

    protected function displayPasswordChangeForm($user)
    {
        $user = $this->_prepareProfileData($user);

        return $this->success([
            'user' => $user
        ], __('Password change form displayed'));
    }

    protected function displayProfilePage($me_info)
    {
        return $this->success([
            'user' => $me_info
        ], __('Profile page displayed'));
    }

    protected function handleProfileUpdateSuccess($page_type)
    {
        $messages = [
            'personal_info' => __('Personal information updated successfully'),
            'social_media' => __('Social media updated successfully'),
            'detailed_info' => __('Detailed information updated successfully')
        ];

        return $this->success([
            'page_type' => $page_type,
            'message' => $messages[$page_type] ?? __('Profile updated successfully')
        ], $messages[$page_type] ?? __('Profile updated successfully'));
    }

    protected function handleProfileUpdateErrors($errors, $user_id, $page_type)
    {
        return $this->error(__('Profile update failed'), $errors, 400);
    }

    protected function handleGoogleAuthRedirect($auth_url)
    {
        return $this->success([
            'auth_url' => $auth_url,
            'message' => __('Google authentication URL generated')
        ], __('Google authentication URL generated'));
    }

    protected function handleGoogleLoginSuccess($user, $state = '')
    {
        $me_info = $this->_prepareProfileData($user);
        return $this->success([
            'user' => $me_info,
            'message' => __('Login with Google successful'),
            'redirect_url' => !empty($state) ? urldecode($state) : null
        ], __('Login with Google successful'));
    }

    protected function handleGoogleUserNotFound($fullname, $email_user, $state = '')
    {
        return $this->success([
            'fullname' => $fullname,
            'email' => $email_user,
            'message' => __('Please complete your registration'),
            'redirect_url' => !empty($state) ? urldecode($state) : null
        ], __('Please complete your registration'));
    }

    protected function handleGoogleAuthError()
    {
        return $this->error([
            'message' => __('Google authentication failed. Please try again.')
        ], __('Google authentication failed. Please try again.'), 400);
    }

    // Called by BaseAuthController::logout()
    protected function handleLogoutSuccess()
    {
        return $this->success([], __('Logout successful'));
    }

    // Hooks for shared login()
    protected function displayLoginForm()
    {
        return $this->error(
            __('Missing credentials'),
            ['error_code' => 'MISSING_CREDENTIALS'],
            400
        );
    }

    protected function handleLoginErrors($errors)
    {
        // Add rate limit info to error response
        $ip = get_client_ip();
        $key = 'login:' . $ip;

        $remaining = \System\Libraries\RateLimiter::remaining($key, 5);
        $availableIn = \System\Libraries\RateLimiter::availableIn($key);

        $errorData = [
            'errors' => $errors,
            'remaining_attempts' => $remaining,
            'error_code' => 'LOGIN_FAILED'
        ];

        if ($availableIn > 0) {
            $errorData['locked_for_seconds'] = $availableIn;
        }

        return $this->error(__('Login failed'), $errorData, 401);
    }

    // Abstract method implementations for register
    protected function handleRegistrationErrors($errors)
    {
        return $this->error(__('Registration failed'), $errors, 400);
    }

    protected function handleMissingRegistrationFields()
    {
        return $this->error(__('Missing required fields'), [], 400);
    }

    protected function displayRegistrationForm()
    {
        return $this->csrf_token();
        //return $this->error(__('Missing required fields'), [], 400);
    }

    // Abstract method implementations for forgot password
    protected function handleForgotPasswordErrors($errors)
    {
        return $this->error(__('Forgot password failed'), $errors, 400);
    }

    protected function handleMissingEmailField()
    {
        return $this->error(__('Missing email field'), [], 400);
    }

    protected function displayForgotPasswordForm()
    {
        return $this->error(
            __('Missing email field'),
            ['error_code' => 'MISSING_EMAIL'],
            400
        );
    }

    protected function displayPasswordResetForm()
    {
        return $this->error(
            __('Invalid reset request'),
            ['error_code' => 'INVALID_RESET'],
            400
        );
    }

    /**
     * Send success JSON response
     * 
     * @param array $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return void
     */
    protected function success($data = [], $message = 'Success', $code = 200)
    {
        Response::sendSuccess($data, $message, $code);
    }

    /**
     * Send error JSON response
     * 
     * @param string $message Error message
     * @param mixed $errors Error details (can include error_code)
     * @param int $code HTTP status code
     * @return void
     */
    protected function error($message = 'Error', $errors = [], $code = 400)
    {
        // Extract error_code if present
        $errorCode = null;
        if (is_array($errors) && isset($errors['error_code'])) {
            $errorCode = $errors['error_code'];
            unset($errors['error_code']); // Remove from errors array
        }

        Response::sendError($message, $errors, $errorCode, $code);
    }

    /**
     * Refresh access token using refresh token
     * 
     * POST /api/v2/auth/refresh
     * Body: {refresh_token, device_fingerprint}
     * 
     * Note: Fingerprint REQUIRED here for security (validates device)
     * Returns new access token (refresh token remains valid)
     * 
     * @return void
     */
    public function refresh()
    {
        try {
            // Get refresh token from request
            $refreshToken = S_POST('refresh_token');
            $fingerprint = S_POST('device_fingerprint');

            if (empty($refreshToken)) {
                return $this->error(
                    __('Refresh token required'),
                    ['error_code' => 'MISSING_REFRESH_TOKEN'],
                    400
                );
            }

            // Fingerprint REQUIRED for refresh (security check)
            if (empty($fingerprint) || !validate_fingerprint($fingerprint)) {
                return $this->error(
                    __('Valid device fingerprint required for token refresh'),
                    ['error_code' => 'INVALID_FINGERPRINT'],
                    400
                );
            }

            // Refresh the token (validates fingerprint + session DB)
            $result = \App\Libraries\Fasttoken::refreshAccessToken($refreshToken, $fingerprint);

            if (!$result) {
                return $this->error(
                    __('Invalid or expired refresh token'),
                    ['error_code' => 'INVALID_REFRESH_TOKEN'],
                    401
                );
            }

            return $this->success($result, __('Token refreshed successfully'));
        } catch (\Exception $e) {
            error_log('Token refresh error: ' . $e->getMessage());

            return $this->error(
                __('Token refresh failed'),
                ['error_code' => 'REFRESH_ERROR'],
                500
            );
        }
    }

    /**
     * Get all active sessions for current user
     * 
     * GET /api/v2/auth/sessions
     * Header: Authorization: Bearer {access_token}
     * 
     * Returns list of active devices/sessions
     * 
     * @return void
     */
    public function sessions()
    {
        try {
            $user = $this->_auth();

            if (!$user) {
                return $this->error(__('Unauthorized'), ['error_code' => 'UNAUTHORIZED'], 401);
            }

            // Get all active sessions
            $sessions = $this->userSessionsModel->getUserSessions($user['id']);

            // Format sessions for response
            $formattedSessions = [];
            $currentSessionId = \System\Libraries\Session::get('session_id');

            foreach ($sessions as $session) {
                $formattedSessions[] = [
                    'id' => $session['id'],
                    'fingerprint' => substr($session['fingerprint'], 0, 8) . '...', // Truncate for privacy
                    'ip_address' => $session['ip_address'],
                    'user_agent' => substr($session['user_agent'], 0, 100), // Truncate
                    'token_type' => $session['token_type'],
                    'last_activity' => $session['last_activity'],
                    'expires_at' => $session['expires_at'],
                    'created_at' => $session['created_at'],
                    'is_current' => $session['id'] == $currentSessionId
                ];
            }

            return $this->success([
                'sessions' => $formattedSessions,
                'total' => count($formattedSessions)
            ], __('Sessions retrieved'));
        } catch (\Exception $e) {
            error_log('Get sessions error: ' . $e->getMessage());

            return $this->error(
                __('Failed to retrieve sessions'),
                ['error_code' => 'SESSIONS_ERROR'],
                500
            );
        }
    }

    /**
     * Revoke a specific session
     * 
     * POST /api/v2/auth/revoke-session
     * Header: Authorization: Bearer {access_token}
     * Body: {session_id}
     * 
     * @return void
     */
    public function revoke_session()
    {
        try {
            $user = $this->_auth();

            if (!$user) {
                return $this->error(__('Unauthorized'), ['error_code' => 'UNAUTHORIZED'], 401);
            }

            $sessionId = S_POST('session_id');

            if (empty($sessionId)) {
                return $this->error(__('Session ID required'), ['error_code' => 'MISSING_SESSION_ID'], 400);
            }

            // Verify session belongs to user
            $session = \App\Models\UserSessionsModel::find($sessionId);

            if (!$session || $session['user_id'] != $user['id']) {
                return $this->error(__('Session not found'), ['error_code' => 'SESSION_NOT_FOUND'], 404);
            }

            // Revoke session
            $this->userSessionsModel->revokeSession($sessionId);

            return $this->success([
                'message' => __('Session revoked successfully')
            ], __('Session revoked'));
        } catch (\Exception $e) {
            error_log('Revoke session error: ' . $e->getMessage());

            return $this->error(
                __('Failed to revoke session'),
                ['error_code' => 'REVOKE_ERROR'],
                500
            );
        }
    }

    /**
     * Revoke all other sessions (logout other devices)
     * 
     * POST /api/v2/auth/revoke-others
     * Header: Authorization: Bearer {access_token}
     * 
     * Keeps current session active
     * 
     * @return void
     */
    public function revoke_others()
    {
        try {
            $user = $this->_auth();

            if (!$user) {
                return $this->error(__('Unauthorized'), ['error_code' => 'UNAUTHORIZED'], 401);
            }

            $currentSessionId = \System\Libraries\Session::get('session_id');

            if (!$currentSessionId) {
                return $this->error(
                    __('Current session not found'),
                    ['error_code' => 'NO_CURRENT_SESSION'],
                    400
                );
            }

            // Revoke all except current
            $count = $this->userSessionsModel->revokeOtherSessions($user['id'], $currentSessionId);

            return $this->success([
                'revoked_count' => $count,
                'message' => __('Logged out %1% other devices', $count)
            ], __('Other sessions revoked'));
        } catch (\Exception $e) {
            error_log('Revoke others error: ' . $e->getMessage());

            return $this->error(
                __('Failed to revoke sessions'),
                ['error_code' => 'REVOKE_OTHERS_ERROR'],
                500
            );
        }
    }

    /**
     * Get current token information
     * 
     * GET /api/v2/auth/token-info
     * Header: Authorization: Bearer {access_token}
     * 
     * Returns information about current access token
     * 
     * @return void
     */
    public function token_info()
    {
        try {
            $token = \App\Libraries\Fasttoken::headerToken();

            if (!$token) {
                return $this->error(__('No token provided'), ['error_code' => 'NO_TOKEN'], 401);
            }

            // Get comprehensive token info
            $info = \App\Libraries\Fasttoken::getTokenInfo($token);

            if (!$info) {
                return $this->error(
                    __('Invalid token'),
                    ['error_code' => 'INVALID_TOKEN'],
                    401
                );
            }

            // Format timestamps
            if (isset($info['issued_at']) && is_numeric($info['issued_at'])) {
                $info['issued_at'] = date('Y-m-d H:i:s', $info['issued_at']);
            }

            if (isset($info['expires_at']) && is_numeric($info['expires_at'])) {
                $info['expires_at'] = date('Y-m-d H:i:s', $info['expires_at']);
            }

            return $this->success($info, __('Token information'));
        } catch (\Exception $e) {
            error_log('Token info error: ' . $e->getMessage());

            return $this->error(
                __('Failed to get token info'),
                ['error_code' => 'TOKEN_INFO_ERROR'],
                500
            );
        }
    }

    /**
     * Health check endpoint
     * 
     * GET /api/v2/auth/health
     * 
     * Check if API and dependencies are working
     * 
     * @return void
     */
    public function health()
    {
        $checks = [
            'api' => 'ok',
            'timestamp' => time(),
            'date' => date('Y-m-d H:i:s')
        ];

        // Check database
        try {
            $this->usersModel->newQuery()->limit(1)->first();
            $checks['database'] = 'ok';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
            $checks['database_error'] = $e->getMessage();
        }

        // Check cache
        try {
            \System\Libraries\Cache::put('health_check', 'ok', 10);
            $value = \System\Libraries\Cache::get('health_check');
            $checks['cache'] = $value === 'ok' ? 'ok' : 'error';
        } catch (\Exception $e) {
            $checks['cache'] = 'error';
            $checks['cache_error'] = $e->getMessage();
        }

        $allOk = $checks['database'] === 'ok' && $checks['cache'] === 'ok';

        return $this->success($checks, $allOk ? 'Healthy' : 'Degraded', $allOk ? 200 : 503);
    }
}
