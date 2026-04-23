<?php
namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Fasttoken - Complete JWT Token Management
 * 
 * Production-ready token system with:
 * - Dual token pattern (access + refresh)
 * - Flexible token creation
 * - Token validation and verification
 * - Device fingerprint binding
 * - Session management integration
 * - Security best practices
 * 
 * @package App\Libraries
 * @version 3.0.0
 */
class Fasttoken
{
    /**
     * JWT algorithm
     * @var string
     */
    private static $algorithm = 'HS256';

    /**
     * Default token expiration (fallback)
     * @var int
     */
    private static $tokenExpiration = 157680000; // 5 years (not used normally)

    /**
     * App ID (issuer)
     * @var string|null
     */
    private static $appId = null;

    /**
     * App secret (signing key)
     * @var string|null
     */
    private static $appSecret = null;

    /**
     * Token TTL constants
     */
    const ACCESS_TOKEN_TTL = 900;       // 15 minutes
    const REFRESH_TOKEN_TTL = 2592000;  // 30 days (2592000 = 30 * 86400)
    const REMEMBER_TOKEN_TTL = 31536000; // 365 days (31536000 = 365 * 86400)
    const API_TOKEN_TTL = 86400;        // 1 day (for backward compatibility)

    /**
     * Initialize configuration
     * Lazy-loaded on first use
     */
    public static function init()
    {
        if (is_null(self::$appId) || is_null(self::$appSecret)) {
            self::$appId = config('app_id', 'Security') ?? 'cmsff';
            self::$appSecret = config('app_secret', 'Security') ?? '';
            
            if (empty(self::$appSecret)) {
                throw new \RuntimeException('JWT secret key not configured in config/security.php');
            }
        }
    }

    /**
     * Retrieve token from request headers
     * 
     * @return string|null The Bearer token if present, null otherwise
     */
    public static function headerToken()
    {
        $headers = getallheaders();
        if ($headers === false) {
            $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        } else {
            $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }
        if ($authorization && preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Decode JWT token without validation
     * 
     * Use checkToken() for validated decoding
     * This method is for debugging/inspection only
     * 
     * @param string $token The JWT token to decode
     * @return array Decoded token data with success status
     */
    public static function decodeToken($token)
    {
        try {
            self::init();
            
            if (empty($token)) {
                return [
                    'success' => false,
                    'message' => 'Empty token',
                    'error_code' => 'EMPTY_TOKEN'
                ];
            }

            if (empty(self::$appSecret)) {
                return [
                    'success' => false,
                    'message' => 'JWT secret not configured',
                    'error_code' => 'NO_SECRET'
                ];
            }

            // Decode with signature verification
            $decoded = JWT::decode($token, new Key(self::$appSecret, self::$algorithm));
            
            return [
                'success' => true,
                'data' => (array)$decoded
            ];

        } catch (\Firebase\JWT\ExpiredException $e) {
            return [
                'success' => false,
                'message' => 'Token expired',
                'error_code' => 'TOKEN_EXPIRED'
            ];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return [
                'success' => false,
                'message' => 'Invalid token signature',
                'error_code' => 'INVALID_SIGNATURE'
            ];
        } catch (\Firebase\JWT\BeforeValidException $e) {
            return [
                'success' => false,
                'message' => 'Token not yet valid',
                'error_code' => 'TOKEN_NOT_YET_VALID'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'DECODE_ERROR'
            ];
        }
    }

    /**
     * Check and validate JWT token
     * 
     * Validates:
     * - Token structure and signature
     * - Expiration time
     * - Required fields (user_id)
     * 
     * @param string $token The JWT token to decode
     * @return array|null Decoded token data or null if invalid/expired
     */
    public static function checkToken($token)
    {
        if (empty($token)) {
            return null;
        }

        $tokenDecode = self::decodeToken($token);
        
        if (!$tokenDecode['success']) {
            return null;
        }

        $userData = $tokenDecode['data'] ?? null;
        
        // Validate required fields
        if (empty($userData) || !isset($userData['user_id'])) {
            return null;
        }

        // Check expiration
        if (!isset($userData['exp']) || $userData['exp'] <= time()) {
            return null;
        }

        // Check not-before time
        if (isset($userData['nbf']) && $userData['nbf'] > time()) {
            return null;
        }

        return $userData;
    }
    
    /**
     * Validate token type
     * Check if token matches expected type (api, remember, etc.)
     * 
     * @param array $tokenData Decoded token data from checkToken()
     * @param string $expectedType Expected token type ('api', 'remember', etc.)
     * @return bool True if token type matches, false otherwise
     */
    public static function validateTokenType($tokenData, $expectedType)
    {
        if (empty($tokenData) || !isset($tokenData['token_type'])) {
            return false;
        }
        return $tokenData['token_type'] === $expectedType;
    }

    /**
     * Create JWT token with flexible fields
     * 
     * Production-ready token creation with:
     * - Automatic field filtering (only add if present)
     * - Custom expiration support
     * - Security validations
     * - Error handling
     * 
     * @param array $userData User data to encode
     * @param int|null $customExpiration TTL in seconds (null = use default)
     * @return string|null JWT token or null on failure
     * 
     * @example
     * // Access Token (15 min)
     * $token = Fasttoken::createToken([
     *     'user_id' => 1,
     *     'role' => 'member',
     *     'token_type' => 'access'
     * ], Fasttoken::ACCESS_TOKEN_TTL);
     * 
     * // Refresh Token (30 days)
     * $token = Fasttoken::createToken([
     *     'user_id' => 1,
     *     'token_type' => 'refresh',
     *     'fingerprint' => 'abc123...',
     *     'password_at' => '2024-12-16 10:00:00'
     * ], Fasttoken::REFRESH_TOKEN_TTL);
     */
    public static function createToken($userData, $customExpiration = null)
    {
        try {
            self::init();
            
            // Validate inputs (return null instead of throwing)
            if (empty($userData)) {
                error_log('createToken: User data is empty');
                return null;
            }

            if (empty(self::$appSecret)) {
                error_log('createToken: JWT secret not configured');
                return null;
            }
            
            // Calculate timestamps
            $issuedAt = time();
            $expiration = $customExpiration ?? self::$tokenExpiration;
            
            // Validate expiration
            if ($expiration <= 0) {
                error_log('createToken: Invalid expiration value');
                return null;
            }

            $expiresAt = $issuedAt + $expiration;

            // Build base payload with standard JWT claims
            $payload = [
                'iss' => self::$appId,           // Issuer
                'iat' => $issuedAt,              // Issued at
                'exp' => $expiresAt,             // Expiration
                'nbf' => $issuedAt,              // Not before
            ];
            
            // Add user_id (mandatory for our system)
            if (isset($userData['user_id'])) {
                $payload['user_id'] = $userData['user_id'];
            } elseif (isset($userData['id'])) {
                $payload['user_id'] = $userData['id'];
            } else {
                error_log('createToken: user_id is required but not provided');
                return null;
            }
            
            // Optional fields (only add if present in userData)
            $optionalFields = [
                'role',
                'username', 
                'email',
                'permissions',
                'password_at',
                'fingerprint',
                'token_type',
                'token_id',
                'session_id',
                'jti'  // JWT ID
            ];
            
            foreach ($optionalFields as $field) {
                if (isset($userData[$field]) && $userData[$field] !== null) {
                    $payload[$field] = $userData[$field];
                }
            }
            
            // Support any additional custom fields
            foreach ($userData as $key => $value) {
                if (!isset($payload[$key]) && $key !== 'id' && $value !== null) {
                    $payload[$key] = $value;
                }
            }

            // Encode to JWT
            return JWT::encode($payload, self::$appSecret, self::$algorithm);

        } catch (\Exception $e) {
            error_log("Fasttoken::createToken error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Set custom token expiration time
     * 
     * @param int $seconds Expiration time in seconds
     */
    public static function setTokenExpiration($seconds)
    {
        self::$tokenExpiration = $seconds;
    }

    /**
     * Set custom JWT algorithm
     * 
     * @param string $algorithm JWT algorithm to use (HS256, HS384, HS512, RS256, etc.)
     */
    public static function setAlgorithm($algorithm)
    {
        self::$algorithm = $algorithm;
    }

    // =========================================================================
    // DUAL TOKEN SYSTEM (Access + Refresh Tokens)
    // =========================================================================

    /**
     * Generate access token and refresh token pair
     * 
     * Implements dual-token pattern for API security:
     * - Access token: Short-lived (15min), for API requests
     * - Refresh token: Long-lived (30 days), for getting new access tokens
     *
     * @param array $user User data (id, role, permissions, password_at)
     * @param string $fingerprint Device fingerprint (32 chars)
     * @param int|null $sessionId Session ID from database
     * @return array|null Token pair with metadata or null on error
     */
    public static function generateTokenPair($user, $fingerprint, $sessionId = null)
    {
        try {
            // Validate inputs
            if (empty($user['id']) || empty($fingerprint)) {
                error_log('generateTokenPair: Missing user ID or fingerprint');
                return null;
            }

            if (!validate_fingerprint($fingerprint)) {
                error_log('generateTokenPair: Invalid fingerprint format');
                return null;
            }
        } catch (\Exception $e) {
            error_log('generateTokenPair validation error: ' . $e->getMessage());
            return null;
        }

        try {
            // Generate access token (short-lived)
            $accessTokenData = [
                'user_id' => $user['id'],
                'role' => $user['role'] ?? 'member',
                'permissions' => $user['permissions'] ?? null,
                'token_type' => 'access',
                'fingerprint' => $fingerprint,
                'token_id' => generate_token_id(),
                'jti' => bin2hex(random_bytes(16))
            ];

            $accessToken = self::createToken($accessTokenData, self::ACCESS_TOKEN_TTL);

            if (!$accessToken) {
                error_log('generateTokenPair: Failed to create access token');
                return null;
            }

            // Generate refresh token (long-lived)
            $refreshTokenData = [
                'user_id' => $user['id'],
                'token_type' => 'refresh',
                'fingerprint' => $fingerprint,
                'session_id' => $sessionId,
                'password_at' => $user['password_at'] ?? 0,
                'token_id' => generate_token_id(),
                'jti' => bin2hex(random_bytes(16))
            ];

            $refreshToken = self::createToken($refreshTokenData, self::REFRESH_TOKEN_TTL);

            if (!$refreshToken) {
                error_log('generateTokenPair: Failed to create refresh token');
                return null;
            }

            return [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => self::ACCESS_TOKEN_TTL,
                'refresh_expires_in' => self::REFRESH_TOKEN_TTL
            ];

        } catch (\Exception $e) {
            error_log('generateTokenPair error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresh access token using refresh token
     * 
     * Validates refresh token and generates new access token
     * Refresh token remains valid (not rotated by default)
     *
     * @param string $refreshToken Refresh token
     * @param string $fingerprint Device fingerprint
     * @param bool $rotateRefresh Whether to rotate refresh token (optional, extra security)
     * @return array|null New tokens or null if invalid
     */
    public static function refreshAccessToken($refreshToken, $fingerprint, $rotateRefresh = false)
    {
        // Validate refresh token structure
        $tokenData = self::checkToken($refreshToken);
        
        if (!$tokenData || !isset($tokenData['user_id'], $tokenData['fingerprint'])) {
            return null;
        }

        // Verify token type
        if (!isset($tokenData['token_type']) || $tokenData['token_type'] !== 'refresh') {
            return null;
        }

        // Verify fingerprint match
        if (!hash_equals($tokenData['fingerprint'], $fingerprint)) {
            return null;
        }

        // Validate session exists in database (type: access for API)
        $sessionModel = new \App\Models\UserSessionsModel();
        
        // Calculate token hash for exact validation
        $tokenHash = hash('sha256', $refreshToken);
        
        $session = $sessionModel->validateSession(
            $tokenData['user_id'], 
            $fingerprint,
            'access', // API sessions are type 'access'
            $tokenHash // Validate exact refresh token
        );

        if (!$session) {
            return null;
        }

        // Update refresh token last used (refresh token)
        $sessionModel->touchToken($session['id'], 'refresh');

        // Get user data
        $userModel = new \App\Models\UsersModel();
        $user = $userModel->getUserById($tokenData['user_id']);

        if (!$user || $user['status'] !== 'active') {
            return null;
        }

        // Verify password hasn't changed
        if (isset($tokenData['password_at'])) {
            if (empty($user['password_at']) || $user['password_at'] != $tokenData['password_at']) {
                // Password changed - revoke all sessions
                $sessionModel->revokeAllSessions($user['id']);
                return null;
            }
        }

        // Update session activity
        $sessionModel->touchSession($session['id']);

        // Generate new access token
        $accessTokenData = [
            'user_id' => $user['id'],
            'role' => $user['role'],
            'permissions' => $user['permissions'] ?? null,
            'token_type' => 'access',
            'fingerprint' => $fingerprint,
            'token_id' => generate_token_id(),
            'jti' => bin2hex(random_bytes(16))
        ];

        $newAccessToken = self::createToken($accessTokenData, self::ACCESS_TOKEN_TTL);

        $result = [
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL
        ];

        // Optional: Rotate refresh token (extra security)
        if ($rotateRefresh) {
            $refreshTokenData = [
                'user_id' => $user['id'],
                'token_type' => 'refresh',
                'fingerprint' => $fingerprint,
                'session_id' => $session['id'],
                'password_at' => $user['password_at'] ?? 0,
                'token_id' => generate_token_id(),
                'jti' => bin2hex(random_bytes(16))
            ];

            $result['refresh_token'] = self::createToken($refreshTokenData, self::REFRESH_TOKEN_TTL);
            $result['refresh_expires_in'] = self::REFRESH_TOKEN_TTL;
        }

        return $result;
    }

    /**
     * Validate access token
     * 
     * Checks token structure, type, expiration, and optionally session
     *
     * @param string $token Access token
     * @param bool $checkSession Whether to validate session in database
     * @return array|null Token data or null if invalid
     */
    public static function validateAccessToken($token, $checkSession = true)
    {
        $tokenData = self::checkToken($token);

        if (!$tokenData || !isset($tokenData['user_id'])) {
            return null;
        }

        // Verify token type
        if (!isset($tokenData['token_type']) || $tokenData['token_type'] !== 'access') {
            return null;
        }

        // Optional: Validate session in database
        if ($checkSession && isset($tokenData['fingerprint'])) {
            $sessionModel = new \App\Models\UserSessionsModel();
            $tokenHash = hash('sha256', $token);
            
            $session = $sessionModel->validateSession(
                $tokenData['user_id'],
                $tokenData['fingerprint'],
                'access', // Access tokens are type 'access'
                $tokenHash
            );

            if (!$session) {
                return null;
            }

            // Update primary token (access) last used
            $sessionModel->touchToken($session['id'], 'primary');
        }

        return $tokenData;
    }

    /**
     * Validate refresh token
     *
     * @param string $token Refresh token
     * @param bool $checkSession Whether to validate session
     * @return array|null Token data or null if invalid
     */
    public static function validateRefreshToken($token, $checkSession = true)
    {
        $tokenData = self::checkToken($token);

        if (!$tokenData || !isset($tokenData['user_id'])) {
            return null;
        }

        // Verify token type
        if (!isset($tokenData['token_type']) || $tokenData['token_type'] !== 'refresh') {
            return null;
        }

        // Optional: Validate session
        if ($checkSession && isset($tokenData['fingerprint'])) {
            $sessionModel = new \App\Models\UserSessionsModel();
            $tokenHash = hash('sha256', $token);
            
            $session = $sessionModel->validateSession(
                $tokenData['user_id'],
                $tokenData['fingerprint'],
                'access', // Refresh tokens stored in 'access' type sessions
                $tokenHash
            );

            if (!$session) {
                return null;
            }

            // Update refresh token last used
            $sessionModel->touchToken($session['id'], 'refresh');
        }

        return $tokenData;
    }

    /**
     * Get token expiration timestamp
     *
     * @param string $token JWT token
     * @return int|null Unix timestamp or null if invalid
     */
    public static function getTokenExpiration($token)
    {
        $tokenData = self::checkToken($token);
        return $tokenData['exp'] ?? null;
    }

    /**
     * Check if token is expired
     *
     * @param string $token JWT token
     * @return bool True if expired or invalid
     */
    public static function isTokenExpired($token)
    {
        $exp = self::getTokenExpiration($token);
        return $exp === null || $exp < time();
    }

    /**
     * Get token time-to-live (seconds remaining)
     *
     * @param string $token JWT token
     * @return int Seconds remaining (0 if expired)
     */
    public static function getTokenTTL($token)
    {
        $exp = self::getTokenExpiration($token);
        
        if ($exp === null) {
            return 0;
        }

        return max(0, $exp - time());
    }

    /**
     * Get token metadata (for debugging/monitoring)
     *
     * @param string $token JWT token
     * @return array|null Token information
     */
    public static function getTokenInfo($token)
    {
        $tokenData = self::checkToken($token);

        if (!$tokenData) {
            return null;
        }

        return [
            'user_id' => $tokenData['user_id'] ?? null,
            'role' => $tokenData['role'] ?? null,
            'token_type' => $tokenData['token_type'] ?? null,
            'token_id' => $tokenData['token_id'] ?? null,
            'fingerprint' => $tokenData['fingerprint'] ?? null,
            'issued_at' => $tokenData['iat'] ?? null,
            'expires_at' => $tokenData['exp'] ?? null,
            'ttl' => self::getTokenTTL($token),
            'is_expired' => self::isTokenExpired($token)
        ];
    }

    /**
     * Revoke all tokens for a user (by revoking sessions)
     * 
     * Call this when:
     * - Password changed
     * - Security breach detected
     * - Account disabled
     *
     * @param int $userId User ID
     * @return int Number of sessions revoked
     */
    public static function revokeAllUserTokens($userId)
    {
        $sessionModel = new \App\Models\UserSessionsModel();
        return $sessionModel->revokeAllSessions($userId);
    }

    /**
     * Revoke specific device tokens
     *
     * @param int $userId User ID
     * @param string $fingerprint Device fingerprint
     * @return bool Success status
     */
    public static function revokeDeviceTokens($userId, $fingerprint)
    {
        $sessionModel = new \App\Models\UserSessionsModel();
        
        // Find and revoke session
        $session = $sessionModel->newQuery()
                                ->where('user_id', $userId)
                                ->where('fingerprint', $fingerprint)
                                ->where('status', 'active')
                                ->first();

        if ($session) {
            return $sessionModel->revokeSession($session['id']);
        }

        return false;
    }
}