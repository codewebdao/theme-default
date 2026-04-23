<?php
namespace App\Models;

use System\Database\BaseModel;

/**
 * UserSessionsModel - User Login Sessions Management
 * 
 * This model manages user login sessions with device fingerprinting.
 * Each login creates a new session record that can be tracked and revoked.
 * 
 * Features:
 * - Device fingerprinting (32-char MD5 for optimal indexing)
 * - IP address and User-Agent tracking
 * - Remember token storage
 * - Session expiration
 * - Multi-device support per user
 * 
 * @package App\Models
 */
class UserSessionsModel extends BaseModel
{
    // =========================================================================
    // CONFIGURATION PROPERTIES
    // =========================================================================

    /** @var string Unprefixed base table name */
    protected $table = 'user_sessions';
    
    /** @var string|null Connection name */
    protected $connection = null;
    
    /** @var string Primary key column name */
    protected $primaryKey = 'id';
    
    /** @var string[] Fillable fields for mass assignment */
    protected $fillable = [
        'user_id',
        'fingerprint',
        'ip_address',
        'user_agent',
        'token_type',
        'token_hash',
        'token_expires',
        'token_last_used',
        'refresh_hash',
        'refresh_expires',
        'refresh_last_used',
        'expires_at',
        'last_activity',
        'status'
    ];

    /** @var string[] Guarded fields (blacklist) */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    /** @var bool Enable automatic timestamps */
    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const CREATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';
    public const UPDATED_AT_DEFAULT = 'CURRENT_TIMESTAMP';

    /** @var array<string, mixed> Default attribute values */
    protected $attributes = [
        'status' => 'active',
        'token_type' => 'remember',
    ];

    // =========================================================================
    // SCHEMA DEFINITION
    // =========================================================================

    /**
     * Define the user_sessions table structure.
     * 
     * Optimized schema with consolidated token tracking:
     * 
     * Fields:
     * - fingerprint: 32-char device ID
     * - ip_address: Client IP (IPv4/IPv6)
     * - user_agent: Browser UA string
     * - token_type: 'access' (API) or 'remember' (Web)
     * 
     * Token tracking:
     * - token_hash: Primary token (access_token OR remember_token)
     * - token_expires: Primary token expiration
     * - token_last_used: Primary token last use
     * - refresh_hash: Refresh token (only for type='access')
     * - refresh_expires: Refresh token expiration
     * - refresh_last_used: Refresh token last use
     * 
     * Session expiration:
     * - expires_at: MAX(token_expires, refresh_expires)
     *   Purpose: Efficient cleanup queries (single indexed column)
     *   Auto-calculated from token expirations
     * 
     * Composite unique: (user_id, fingerprint, token_type)
     * → Same device can have BOTH 'access' (API) AND 'remember' (Web) sessions
     * 
     * @return array Schema definition
     */
    protected function _schema()
    {
        return [
            // Primary key
            ['type' => 'increments', 'name' => 'id', 'options' => ['unsigned' => true]],
            
            // User reference
            ['type' => 'integer', 'name' => 'user_id', 'options' => ['unsigned' => true, 'index' => true]],
            
            // Device fingerprint (32 chars)
            ['type' => 'varchar', 'name' => 'fingerprint', 'options' => ['length' => 32], 'index' => ['type' => 'index', 'name' => 'idx_user_fingerprint_type', 'columns' => ['user_id', 'fingerprint', 'token_type']]],
            
            // Network information
            ['type' => 'varchar', 'name' => 'ip_address', 'options' => ['length' => 45, 'null' => true]],
            ['type' => 'text', 'name' => 'user_agent', 'options' => ['null' => true]],
            
            // Session type ('access' for API, 'remember' for Web)
            ['type' => 'enum', 'name' => 'token_type', 'options' => ['values' => ['access', 'remember'], 'default' => 'remember']],
            
            // Primary token (access token for API, remember token for Web)
            ['type' => 'varchar', 'name' => 'token_hash', 'options' => ['length' => 64, 'null' => true, 'index' => true]],
            ['type' => 'datetime', 'name' => 'token_expires', 'options' => ['null' => true]],
            ['type' => 'datetime', 'name' => 'token_last_used', 'options' => ['null' => true]],
            
            // Refresh token (only for API type='access')
            ['type' => 'varchar', 'name' => 'refresh_hash', 'options' => ['length' => 64, 'null' => true, 'index' => true]],
            ['type' => 'datetime', 'name' => 'refresh_expires', 'options' => ['null' => true]],
            ['type' => 'datetime', 'name' => 'refresh_last_used', 'options' => ['null' => true]],
            
            // Session lifecycle
            ['type' => 'datetime', 'name' => 'expires_at', 'options' => ['null' => true, 'index' => ['type' => 'index', 'name' => 'idx_expires', 'columns' => ['expires_at', 'status']]]],
            ['type' => 'datetime', 'name' => 'last_activity', 'options' => ['null' => true]],
            
            // Status
            ['type' => 'enum', 'name' => 'status', 'options' => ['values' => ['active', 'expired', 'revoked'], 'default' => 'active', 'index' => ['type' => 'index', 'name' => 'idx_user_status', 'columns' => ['user_id', 'status']] ]],
            
            // Timestamps
            ['type' => 'datetime', 'name' => 'created_at', 'options' => ['null' => true], 'default' => self::CREATED_AT_DEFAULT],
            ['type' => 'datetime', 'name' => 'updated_at', 'options' => ['null' => true], 'default' => self::UPDATED_AT_DEFAULT],
            
        ];
    }

    // =========================================================================
    // SESSION MANAGEMENT METHODS
    // =========================================================================

    /**
     * Create or update session - TRUE UPSERT (no duplicates)
     * 
     * One active session per (user_id + fingerprint + token_type)
     * - Web remember: token_type = 'remember'
     * - API access: token_type = 'access'
     * - Same device can have both types simultaneously
     * 
     * Algorithm:
     * 1. Find existing session (user_id + fingerprint + token_type)
     * 2. If exists: UPDATE
     * 3. If not exists: INSERT
     * 4. This ensures EXACTLY 1 session (no duplicates)
     * 
     * @param int $user_id User ID
     * @param string $fingerprint Device fingerprint (32 chars)
     * @param array|null $tokens ['access_token' => string, 'refresh_token' => string] (optional)
     * @param array $metadata Additional metadata
     * @return int Session ID
     */
    public function createSession($user_id, $fingerprint, $tokens = null, $metadata = [])
    {
        $tokenType = $metadata['token_type'] ?? 'remember';
        $now = _DateTime();
        
        // Find existing session with all 3 conditions (ensures uniqueness)
        $existingSession = $this->newQuery()
                                ->where('user_id', $user_id)
                                ->where('fingerprint', $fingerprint)
                                ->where('token_type', $tokenType)
                                ->first(); // Get any status (active/revoked/expired)
        // Prepare update data
        $data = [
            'ip_address' => $metadata['ip_address'] ?? get_client_ip(),
            'user_agent' => $metadata['user_agent'] ?? get_user_agent(),
            'status' => 'active', // Always set to active on create/update
            'last_activity' => $now,
            'updated_at' => $now,
        ];

        // Add token hashes if provided
        if ($tokens && is_array($tokens)) {
            if ($tokenType === 'access') {
                // API session: access_token (primary) + refresh_token
                if (isset($tokens['access_token'])) {
                    $data['token_hash'] = hash('sha256', $tokens['access_token']);
                    $data['token_expires'] = date('Y-m-d H:i:s', time() + \App\Libraries\Fasttoken::ACCESS_TOKEN_TTL);
                    $data['token_last_used'] = $now;
                }
                
                if (isset($tokens['refresh_token'])) {
                    $data['refresh_hash'] = hash('sha256', $tokens['refresh_token']);
                    $data['refresh_expires'] = date('Y-m-d H:i:s', time() + \App\Libraries\Fasttoken::REFRESH_TOKEN_TTL);
                    $data['refresh_last_used'] = $now;
                }
            } else {
                // Web remember session: remember_token (primary only)
                if (isset($tokens['remember_token'])) {
                    $data['token_hash'] = hash('sha256', $tokens['remember_token']);
                    $data['token_expires'] = date('Y-m-d H:i:s', time() + \App\Libraries\Fasttoken::REMEMBER_TOKEN_TTL);
                    $data['token_last_used'] = $now;
                }
            }
        }

        // Set session expiration = MAX(token_expires, refresh_expires)
        // This is used for efficient cleanup queries
        if (isset($metadata['expires_at'])) {
            $data['expires_at'] = $metadata['expires_at'];
        } else {
            // Auto-calculate from tokens
            $maxExpires = 0;
            
            if (isset($data['token_expires'])) {
                $maxExpires = max($maxExpires, strtotime($data['token_expires']));
            }
            
            if (isset($data['refresh_expires'])) {
                $maxExpires = max($maxExpires, strtotime($data['refresh_expires']));
            }
            
            // Fallback if no token expires set
            if ($maxExpires === 0) {
                $expiresSeconds = $tokenType === 'access' ? 
                                  \App\Libraries\Fasttoken::REFRESH_TOKEN_TTL : 
                                  \App\Libraries\Fasttoken::REMEMBER_TOKEN_TTL;
                $maxExpires = time() + $expiresSeconds;
            }
            
            $data['expires_at'] = date('Y-m-d H:i:s', $maxExpires);
        }
        
        if ($existingSession) {
            // UPDATE existing session (reactivate if was revoked/expired)
            $updated = $this->newQuery()
                            ->where('id', $existingSession['id'])
                            ->update($data);
            
            return $updated ? $existingSession['id'] : false;
        } else {
            // INSERT new session
            $data['user_id'] = $user_id;
            $data['fingerprint'] = $fingerprint;
            $data['status'] = 'active';
            $data['token_type'] = $tokenType;
            $data['created_at'] = $now;
            
            return $this->insert($data);
        }
    }

    /**
     * Validate session - lightweight query (single query only)
     * 
     * Note: createSession() ensures no duplicates via UPSERT
     * So we don't need to check/cleanup duplicates here
     * 
     * @param int $user_id User ID
     * @param string $fingerprint Device fingerprint  
     * @param string|null $tokenType Token type ('access' or 'remember')
     * @param string|null $tokenHash Token hash for exact validation
     * @return array|null Session data or null if invalid
     */
    public function validateSession($user_id, $fingerprint, $tokenType = null, $tokenHash = null)
    {
        // Single query - efficient
        $query = $this->newQuery()
                      ->where('user_id', $user_id)
                      ->where('fingerprint', $fingerprint)
                      ->where('status', 'active')
                      ->where('expires_at', _DateTime(), '>');

        // Add token_type filter if provided
        if ($tokenType !== null) {
            $query->where('token_type', $tokenType);
        }

        $session = $query->first();
        
        if (!$session) {
            return null;
        }

        // Optional: Validate token hash for exact match
        if ($tokenHash !== null) {
            // Check primary token (token_hash)
            $primaryMatch = !empty($session['token_hash']) && 
                           hash_equals($session['token_hash'], $tokenHash);
            
            // For API sessions, also check refresh_hash
            $refreshMatch = false;
            if ($session['token_type'] === 'access' && !empty($session['refresh_hash'])) {
                $refreshMatch = hash_equals($session['refresh_hash'], $tokenHash);
            }
            
            if (!$primaryMatch && !$refreshMatch) {
                return null;
            }
        }
        
        return $session;
    }


    /**
     * Update session last activity
     * 
     * @param int $session_id Session ID
     * @return bool Success status
     */
    public function touchSession($session_id)
    {
        return $this->newQuery()
                    ->where('id', $session_id)
                    ->update(['last_activity' => _DateTime()]);
    }

    /**
     * Update token last used timestamp
     * Auto-updates last_activity as well
     *
     * @param int $session_id Session ID
     * @param string $tokenType 'primary' (access/remember) or 'refresh'
     * @return bool
     */
    public function touchToken($session_id, $tokenType = 'primary')
    {
        $now = _DateTime();
        
        $updateData = ['last_activity' => $now];
        
        if ($tokenType === 'refresh') {
            $updateData['refresh_last_used'] = $now;
        } else {
            $updateData['token_last_used'] = $now;
        }
        
        return $this->newQuery()
                    ->where('id', $session_id)
                    ->update($updateData);
    }

    /**
     * Validate session by token hash (primary or refresh)
     * 
     * @param string $tokenHash SHA-256 hash of token
     * @return array|null Session data
     */
    public function validateByTokenHash($tokenHash)
    {
        $now = _DateTime();
        
        // Check token_hash or refresh_hash
        return $this->newQuery()
                    ->where(function($query) use ($tokenHash) {
                        $query->where('token_hash', $tokenHash)
                              ->orWhere('refresh_hash', $tokenHash);
                    })
                    ->where('status', 'active')
                    ->where('expires_at', $now, '>')
                    ->first();
    }

    /**
     * Revoke session by token hash (primary or refresh)
     *
     * @param string $tokenHash SHA-256 hash of token
     * @return int Number of sessions revoked
     */
    public function revokeByTokenHash($tokenHash)
    {
        return $this->newQuery()
                    ->where(function($query) use ($tokenHash) {
                        $query->where('token_hash', $tokenHash)
                              ->orWhere('refresh_hash', $tokenHash);
                    })
                    ->update(['status' => 'revoked', 'updated_at' => _DateTime()]);
    }

    /**
     * Revoke all sessions of specific type
     *
     * @param int $user_id User ID
     * @param string $tokenType 'access' or 'remember'
     * @return int Number of revoked sessions
     */
    public function revokeByType($user_id, $tokenType)
    {
        return $this->newQuery()
                    ->where('user_id', $user_id)
                    ->where('token_type', $tokenType)
                    ->update(['status' => 'revoked', 'updated_at' => _DateTime()]);
    }

    /**
     * Get all active sessions for a user
     * 
     * @param int $user_id User ID
     * @return array Array of sessions
     */
    public function getUserSessions($user_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('status', 'active')
                    ->orderBy('last_activity', 'DESC')
                    ->get();
    }

    /**
     * Revoke a specific session
     * 
     * @param int $session_id Session ID
     * @return bool Success status
     */
    public function revokeSession($session_id)
    {
        return $this->where('id', $session_id)
                    ->update(['status' => 'revoked']);
    }

    /**
     * Revoke all sessions for a user except current
     * 
     * @param int $user_id User ID
     * @param int $except_session_id Current session ID to keep
     * @return int Number of revoked sessions
     */
    public function revokeOtherSessions($user_id, $except_session_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('id', $except_session_id, '!=')
                    ->update(['status' => 'revoked']);
    }

    /**
     * Revoke all sessions for a user
     * Useful when password changes
     * 
     * @param int $user_id User ID
     * @return int Number of revoked sessions
     */
    public function revokeAllSessions($user_id)
    {
        return $this->where('user_id', $user_id)
                    ->update(['status' => 'revoked']);
    }

    /**
     * Clean up expired sessions
     * Should be run periodically (cron job)
     * 
     * @param int $days_old Delete sessions older than X days (default: 90)
     * @return int Number of deleted sessions
     */
    public function cleanupExpiredSessions($days_old = 90)
    {
        $cutoff_date = date('Y-m-d H:i:s', time() - ($days_old * 86400));
        
        return $this->where('expires_at', $cutoff_date, '<')
                    ->orWhere(function($query) use ($cutoff_date) {
                        $query->where('status', 'active', '!=')
                              ->where('updated_at', $cutoff_date, '<');
                    })
                    ->delete();
    }

    /**
     * Get session statistics for a user
     * 
     * @param int $user_id User ID
     * @return array Statistics
     */
    public function getSessionStats($user_id)
    {
        $total = $this->where('user_id', $user_id)->count();
        $active = $this->where('user_id', $user_id)->where('status', 'active')->count();
        $revoked = $this->where('user_id', $user_id)->where('status', 'revoked')->count();
        $expired = $this->where('user_id', $user_id)->where('status', 'expired')->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'revoked' => $revoked,
            'expired' => $expired
        ];
    }
    
    /**
     * Cleanup duplicate active sessions for a user+fingerprint pair
     * Keeps only the newest session, revokes others
     * 
     * @param int $user_id User ID
     * @param string $fingerprint Device fingerprint
     * @return int Number of sessions revoked
     */
    public function cleanupDuplicateSessions($user_id, $fingerprint)
    {
        // Get newest session
        $newestSession = $this->where('user_id', $user_id)
                              ->where('fingerprint', $fingerprint)
                              ->where('status', 'active')
                              ->orderBy('created_at', 'DESC')
                              ->first();
        
        if (!$newestSession) {
            return 0;
        }
        
        // Revoke all other sessions with same user_id + fingerprint
        return $this->where('user_id', $user_id)
                    ->where('fingerprint', $fingerprint)
                    ->where('status', 'active')
                    ->where('id', $newestSession['id'], '!=')
                    ->update(['status' => 'revoked', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Mark expired sessions
     * Run this periodically to update status
     * 
     * @return int Number of updated sessions
     */
    public function markExpiredSessions()
        {
            return $this->where('expires_at', _DateTime(), '<')
                    ->where('status', 'active')
                    ->update(['status' => 'expired']);
    }
}
