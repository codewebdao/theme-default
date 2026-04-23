<?php

namespace System\Libraries;

use System\Libraries\Storages\LocalStorage;
use System\Libraries\Storages\S3Storage;
use System\Libraries\Storages\GCSStorage;

/**
 * Storages - Storage Factory (Singleton Pattern)
 * 
 * Factory class để tạo và quản lý storage driver instances
 * 
 * HỖ TRỢ DRIVERS:
 * - 'local' / 'filesystem': Lưu trữ local server
 * - 's3' / 'aws': Amazon S3
 * - 'gcs' / 'google': Google Cloud Storage
 * 
 * TÍNH NĂNG:
 * - Auto caching instances (singleton per driver)
 * - Lazy loading
 * - Configurable default driver
 * 
 * SỬ DỤNG:
 * 
 * // 1. Dùng default driver (local)
 * $storage = Storages::make();
 * $storage->save($tmpFile, 'uploads/file.jpg');
 * 
 * // 2. Dùng S3
 * $storage = Storages::make('s3');
 * $storage->save($tmpFile, 'uploads/file.jpg');
 * 
 * // 3. Dùng disk() helper (giống Laravel)
 * $storage = Storages::disk();
 * 
 * // 4. Set default driver
 * Storages::setDefaultDriver('s3');
 * 
 * @package System\Libraries
 * @version 2.0.0
 */
class Storages
{
    /**
     * Storage instances cache
     */
    private static $instances = [];
    
    /**
     * Default driver
     */
    private static $defaultDriver = 'local';
    
    /**
     * Create storage driver instance
     * 
     * @param string|null $driver Driver name (null = default)
     * @param array $config Custom configuration
     * @return \System\Libraries\Storage\StorageInterface
     * @throws \Exception
     */
    public static function make($driver = null, $config = [])
    {
        $driver = $driver ?? self::$defaultDriver;
        
        // Return cached instance if exists
        $cacheKey = $driver . '_' . md5(json_encode($config));
        if (isset(self::$instances[$cacheKey])) {
            return self::$instances[$cacheKey];
        }
        
        // Create new instance
        $instance = self::createDriver($driver, $config);
        
        // Cache instance
        self::$instances[$cacheKey] = $instance;
        
        return $instance;
    }
    
    /**
     * Create storage driver
     * 
     * @param string $driver Driver name
     * @param array $config Configuration
     * @return \System\Libraries\Storage\StorageInterface
     * @throws \Exception
     */
    private static function createDriver($driver, $config = [])
    {
        switch (strtolower($driver)) {
            case 'local':
            case 'filesystem':
                return new LocalStorage($config);
                
            case 's3':
            case 'aws':
                if (!class_exists(S3Storage::class)) {
                    throw new \Exception('S3Storage driver not available');
                }
                return new S3Storage($config);
                
            case 'gcs':
            case 'google':
                if (!class_exists(GCSStorage::class)) {
                    throw new \Exception('GCSStorage driver not available');
                }
                return new GCSStorage($config);
                
            default:
                throw new \Exception("Unknown storage driver: {$driver}");
        }
    }
    
    /**
     * Get default storage driver
     * 
     * @return \System\Libraries\Storage\StorageInterface
     */
    public static function disk()
    {
        return self::make();
    }
    
    /**
     * Set default driver
     * 
     * @param string $driver Driver name
     */
    public static function setDefaultDriver($driver)
    {
        self::$defaultDriver = $driver;
    }
    
    /**
     * Get default driver name
     * 
     * @return string
     */
    public static function getDefaultDriver()
    {
        return self::$defaultDriver;
    }
    
    /**
     * Clear instances cache
     * 
     * @param string|null $driver Clear specific driver (null = all)
     */
    public static function clearCache($driver = null)
    {
        if ($driver === null) {
            self::$instances = [];
        } else {
            foreach (self::$instances as $key => $instance) {
                if (strpos($key, $driver . '_') === 0) {
                    unset(self::$instances[$key]);
                }
            }
        }
    }
    
    /**
     * Get all cached instances
     * 
     * @return array
     */
    public static function getInstances()
    {
        return self::$instances;
    }
}
