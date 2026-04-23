<?php
namespace System\Libraries;
// Check if PATH_ROOT is not defined, prevent direct access
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

class Security {

    // Static variables to store app_id and app_secret
    protected static $app_id;
    protected static $app_secret;

    /**
     * Initialize app_id and app_secret from config
     */
    public static function init() {
        // Get app_id and app_secret from config
        $security = config(null, 'Security');
        if (empty($security['app_id']) || empty($security['app_secret'])) {
            throw new \System\Core\AppException("App ID & Secret is not set in config.");
        }
        self::$app_id = $security['app_id'];
        self::$app_secret = $security['app_secret'];
    }

    /**
     * Encrypt data using AES-256-CBC with random IV
     * 
     * @param string $data Data to encrypt
     * @return string Encrypted data (IV + Data)
     */
    public static function encrypt($data) {
        if (is_null(self::$app_secret)) self::init(); // Ensure app_secret is set
        
        $key = self::deriveKey(self::$app_secret); // Generate key from app_secret
        $iv = random_bytes(16); // Random IV, 16 bytes for AES-256-CBC

        // Encrypt data
        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        // Return IV + encrypted data, store IV together with encrypted data
        return base64_encode($iv . $encryptedData);
    }

    /**
     * Decrypt data encrypted using AES-256-CBC
     * 
     * @param string $encryptedData Encrypted data (IV + Data)
     * @return string|false Decrypted data, or false if failed
     */
    public static function decrypt($encryptedData) {
        if (is_null(self::$app_secret)) self::init(); // Ensure app_secret is set
        
        $key = self::deriveKey(self::$app_secret);
        $data = base64_decode($encryptedData);

        // Extract IV from data
        $iv = substr($data, 0, 16);
        $encryptedData = substr($data, 16);

        // Decrypt data
        return openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Hash password using Bcrypt
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify hashed password
     * 
     * @param string $password Plain password
     * @param string $hashedPassword Hashed password
     * @return bool True if password matches, False otherwise
     */
    public static function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Generate a random token for CSRF or other purposes
     * 
     * @param int $length Token length (default 32)
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        return random_string($length);
    }

    /**
     * Generate encryption key from app_secret using HMAC-SHA256
     * 
     * @param string $secret Secret value
     * @return string Encryption key
     */
    private static function deriveKey($secret) {
        // Use HMAC-SHA256 to generate strong key from app_secret
        return hash_hmac('sha256', $secret, 'framework_secret_key', true); // true to get binary format
    }

    /**
     * Create a signature to protect data, used to ensure data integrity
     * 
     * @param string $data Data to sign
     * @return string HMAC-SHA256 signature
     */
    public static function createSignature($data) {
        if (is_null(self::$app_secret)) self::init(); // Ensure app_secret is set

        return hash_hmac('sha256', $data, self::$app_secret);
    }

    /**
     * Verify if the data signature matches the created signature
     * 
     * @param string $data Original data
     * @param string $signature Created signature
     * @return bool True if signature is valid, False otherwise
     */
    public static function verifySignature($data, $signature) {
        $calculatedSignature = self::createSignature($data);
        return hash_equals($calculatedSignature, $signature); // Use hash_equals to prevent timing attack
    }
}