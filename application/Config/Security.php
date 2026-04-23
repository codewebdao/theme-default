<?php

/**
 * Security Configuration
 * 
 * Configure all security-related settings for the application
 * including JWT tokens, CORS, rate limiting, and authentication
 * 
 * @package Config
 * @version 3.0.0
 */

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    |
    | JWT (JSON Web Token) settings for API authentication
    | 
    | app_id: Issuer identifier (used in JWT 'iss' claim)
    | app_secret: Secret key for signing tokens (KEEP SECRET!)
    |              Recommended: 64+ random characters
    |              Generate: openssl rand -base64 64
    |
    */

    'app_id' => 'app_123456', //APP_ID for JWT create
    
    'app_secret' => 'Cms_Secret_Key@ForSecurity*2005',


    /*
    |--------------------------------------------------------------------------
    | CORS (Cross-Origin Resource Sharing)
    |--------------------------------------------------------------------------
    |
    | Configure which origins can access your API
    | 
    | allowed_origins:
    |   - Use specific URLs in production: ['https://app.example.com']
    |   - Supports wildcards: ['*.example.com']
    |   - Use ['*'] only in development (not secure for production)
    |
    | allowed_methods: HTTP methods allowed for CORS
    |
    | allowed_headers: Headers that can be sent in requests
    |
    | exposed_headers: Headers that client can read from response
    |
    | allow_credentials:
    |   - false: For Bearer token auth (recommended, more secure)
    |   - true: For cookie-based auth (less secure, allows AJAX attacks)
    |
    | max_age: How long browser caches preflight response (seconds)
    |
    */

    'cors' => [
        'allowed_origins' => [
            '*', // Allow all (DEVELOPMENT ONLY - change for production!)
            // Production examples:
            // 'https://app.cmsfullform.com',
            // 'https://mobile.cmsfullform.com',
            // '*.cmsfullform.com', // Wildcard subdomain
        ],

        'allowed_methods' => [
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'OPTIONS'
        ],

        'allowed_headers' => [
            'Authorization',      // Bearer token
            'Content-Type',       // JSON requests
            'Accept',            // Response format
            'X-Device-Fingerprint', // Device identification (optional)
            'X-Requested-With',  // AJAX detection
            'Origin',            // CORS
            'User-Agent',        // Client info
            'Referer'            // Request source
        ],

        'exposed_headers' => [
            'X-RateLimit-Limit',      // Rate limit max
            'X-RateLimit-Remaining',  // Attempts left
            'X-RateLimit-Reset',      // Reset timestamp
            'X-Total-Count',          // Pagination total
            'X-Page-Count'            // Pagination pages
        ],

        'allow_credentials' => false, // false = Bearer auth (secure), true = cookies (less secure)

        'max_age' => 3600, // 1 hour preflight cache
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | IP addresses or CIDR ranges of trusted reverse proxies
    | These proxies' X-Forwarded-For headers will be trusted
    |
    | Common use cases:
    | - Behind Cloudflare
    | - Behind load balancer
    | - Behind Nginx reverse proxy
    | - Docker containers
    |
    | Security warning: Only add proxies you control!
    | Attacker can spoof X-Forwarded-For if proxy not trusted
    |
    */

    'trusted_proxies' => [
        // Private networks (RFC 1918)
        '10.0.0.0/8',       // Class A private
        '172.16.0.0/12',    // Class B private
        '192.168.0.0/16',   // Class C private
        '127.0.0.1',        // Localhost
        
        // Docker networks
        // '172.17.0.0/16',
        
        // Cloudflare IPs (uncomment if using Cloudflare)
        // See: https://www.cloudflare.com/ips/
        // '103.21.244.0/22',
        // '103.22.200.0/22',
        // ...
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for various operations
    | Format: [max_attempts, decay_seconds]
    |
    | Example: ['attempts' => 5, 'decay' => 300]
    |   = 5 attempts per 300 seconds (5 minutes)
    |   = After 5 attempts, locked for 5 minutes
    |
    | Recommendations:
    | - Login: Strict (5/5min) - Prevent brute force
    | - Register: Very strict (3/hour) - Prevent spam
    | - Forgot: Strict (3/15min) - Prevent email bombing
    | - API calls: Lenient (100/min) - Allow normal usage
    |
    */

    'rate_limits' => [
        'login' => [
            'attempts' => 5,
            'decay' => 300,  // 5 minutes
        ],
        
        'register' => [
            'attempts' => 3,
            'decay' => 3600, // 1 hour
        ],
        
        'forgot_password' => [
            'attempts' => 3,
            'decay' => 900, // 15 minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Configure password requirements
    |
    | min_length: Minimum password length
    | max_length: Maximum password length
    | require_uppercase: Require at least one uppercase letter
    | require_lowercase: Require at least one lowercase letter
    | require_numbers: Require at least one number
    | require_special: Require at least one special character
    | special_chars: Allowed special characters
    |
    */

    'password_policy' => [
        'min_length' => 6,
        'max_length' => 60,
        'require_uppercase' => false, // Set true for stronger passwords
        'require_lowercase' => false,
        'require_numbers' => false,
        'require_special' => false,
        'special_chars' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | session_lifetime: Session cookie lifetime (seconds)
    | session_name: Session cookie name
    | session_regenerate: Regenerate session ID on login (prevent fixation)
    |
    */

    'session' => [
        'lifetime' => 7200,     // 2 hours
        'name' => 'PHPSESSID',
        'regenerate' => true,
        'secure' => false,      // Set true for HTTPS only
        'httponly' => true,     // Prevent JavaScript access
        'samesite' => 'Lax',    // CSRF protection
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    |
    | Configure security headers sent with responses
    | 
    | These headers protect against common attacks:
    | - XSS (Cross-Site Scripting)
    | - Clickjacking
    | - MIME sniffing
    | - Information disclosure
    |
    */

    'security_headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'no-referrer',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        
        // Content Security Policy (CSP)
        'csp' => [
            'enabled' => true,
            
            // API endpoints (strict - no resources allowed)
            'api' => "default-src 'none'",
            
            // Web pages (allow necessary resources)
            'web' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: https:",
                "font-src 'self' data:",
                "connect-src 'self'",
                "frame-ancestors 'none'"
            ])
        ],
        
        // Remove server identification
        'hide_server_info' => true, // Removes X-Powered-By header
    ],

    /*
    |--------------------------------------------------------------------------
    | Brute Force Protection
    |--------------------------------------------------------------------------
    |
    | Account lockout after failed login attempts
    |
    | max_attempts: Failed attempts before lockout
    | lockout_time: Lockout duration (seconds)
    | reset_time: Reset counter after successful login
    |
    */

    'brute_force' => [
        'enabled' => true,
        'max_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
        'reset_on_success' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Blacklist/Whitelist
    |--------------------------------------------------------------------------
    |
    | Control access by IP address
    |
    | blacklist: IPs that are always blocked
    | whitelist: IPs that bypass rate limiting (admin IPs, monitoring)
    |
    */

    'ip_blacklist' => [
        // '123.45.67.89', // Block specific IP
        // '10.0.0.0/8',   // Block range
    ],

    'ip_whitelist' => [
        // '192.168.1.100', // Office IP
        // '10.0.0.0/8',    // Internal network
    ],

    /*
    |--------------------------------------------------------------------------
    | API Request Limits
    |--------------------------------------------------------------------------
    |
    | Prevent API abuse
    |
    | max_request_size: Maximum request body size (bytes)
    | max_json_depth: Maximum JSON nesting depth
    | timeout: Request timeout (seconds)
    |
    */

    'api_limits' => [
        'max_request_size' => 5242880,  // 5MB
        'max_json_depth' => 10,         // Prevent deeply nested JSON attacks
        'timeout' => 30,                // 30 seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Device Fingerprinting
    |--------------------------------------------------------------------------
    |
    | Configure device fingerprint requirements
    |
    | required_for: Operations that require fingerprint
    | validation: Enable strict validation
    | length: Expected fingerprint length (chars)
    |
    */

    'fingerprint' => [
        'required_for' => [
            'login',
            'register',
            'token_refresh'
        ],
        'validation' => true,
        'length' => 32, // MD5 length
        'algorithm' => 'sha256-truncated', // sha256 first 32 chars
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption
    |--------------------------------------------------------------------------
    |
    | Encryption settings for sensitive data
    |
    | cipher: Encryption algorithm (aes-256-cbc recommended)
    | key: Encryption key (auto-generated on first run)
    |
    */

    'encryption' => [
        'cipher' => 'aes-256-cbc',
        'key' => 'ENCRYPTION_KEY',
    ],

    /*
    |--------------------------------------------------------------------------
    | 2FA (Two-Factor Authentication)
    |--------------------------------------------------------------------------
    |
    | Two-factor authentication settings (future feature)
    |
    | enabled: Enable 2FA system-wide
    | methods: Available 2FA methods
    | mandatory_for: Roles that must use 2FA
    |
    */

    '2fa' => [
        'enabled' => false, // Set true when implemented
        'methods' => ['totp', 'sms', 'email'],
        'mandatory_for' => ['admin', 'superadmin'],
        'backup_codes' => 10, // Number of backup codes to generate
    ],

    /*
    |--------------------------------------------------------------------------
    | Account Security
    |--------------------------------------------------------------------------
    |
    | Account-level security settings
    |
    | password_expiry: Force password change after X days (0 = never)
    | session_limit: Max concurrent sessions per user (0 = unlimited)
    | suspicious_activity: Enable suspicious activity detection
    |
    */

    'account' => [
        'password_expiry' => 0,      // Days (0 = never expire)
        'session_limit' => 10,       // Max devices logged in simultaneously
        'suspicious_activity' => true,
        'notify_new_device' => true, // Email on new device login
        'notify_password_change' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    |
    | Security event logging configuration
    |
    | log_failed_logins: Log all failed login attempts
    | log_api_errors: Log API authentication errors
    | log_rate_limits: Log rate limit violations
    | retention_days: How long to keep security logs
    |
    */

    'logging' => [
        'log_failed_logins' => true,
        'log_api_errors' => true,
        'log_rate_limits' => true,
        'log_token_refresh' => false, // Can be noisy
        'retention_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Settings
    |--------------------------------------------------------------------------
    |
    | Environment-specific security settings
    |
    | debug_mode: Show detailed error messages (DISABLE in production!)
    | maintenance_mode: Enable maintenance mode
    | maintenance_ips: IPs that can access during maintenance
    |
    */

    'environment' => [
        'debug_mode' => env('APP_DEBUG', false),
        'maintenance_mode' => false,
        'maintenance_ips' => [
            '127.0.0.1',
            '::1'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot Detection
    |--------------------------------------------------------------------------
    |
    | Settings for detecting and handling bots/crawlers
    |
    | block_bots: Block known bots from registration/posting
    | allowed_bots: Bots that are allowed (search engines)
    |
    */

    'bot_detection' => [
        'block_registration' => true,  // Block bots from registering
        'block_posting' => false,      // Allow bots to view content
        
        'allowed_bots' => [
            'googlebot',
            'bingbot',
            'slurp', // Yahoo
            'duckduckbot'
        ],
        
        'blocked_patterns' => [
            'curl',
            'wget',
            'scrapy',
            'python-requests'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Hardening
    |--------------------------------------------------------------------------
    |
    | Additional security measures
    |
    | disable_directory_listing: Prevent directory browsing
    | disable_version_disclosure: Hide application version
    | force_https: Redirect HTTP to HTTPS
    |
    */

    'hardening' => [
        'disable_directory_listing' => true,
        'disable_version_disclosure' => true,
        'force_https' => false, // Set true in production
        'remove_default_pages' => true, // Remove phpinfo, test pages
    ],

];
