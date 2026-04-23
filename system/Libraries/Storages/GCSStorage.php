<?php

namespace System\Libraries\Storages;

use System\Libraries\Responses\StorageResponse;

/**
 * GCSStorage - Google Cloud Storage Driver
 * 
 * Storage driver cho Google Cloud Storage
 * Requires: composer require google/cloud-storage
 * 
 * @package System\Libraries\Storages
 * @version 2.0.0
 */
class GCSStorage extends BaseStorage
{
    /**
     * GCS Client
     */
    protected $client;
    
    /**
     * GCS Bucket
     */
    protected $bucket;
    
    /**
     * Base URL
     */
    protected $baseUrl;
    
    /**
     * Driver name
     */
    protected $driver = 'gcs';
    
    /**
     * Constructor
     * 
     * @param array $config Configuration
     * @throws \Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        
        // Check if Google Cloud SDK is available
        if (!class_exists('\Google\Cloud\Storage\StorageClient')) {
            throw new \Exception('Google Cloud Storage SDK required: composer require google/cloud-storage');
        }
        
        // Validate config
        if (empty($config['bucket'])) {
            throw new \Exception('GCS requires: bucket');
        }
        
        $this->bucket = $config['bucket'];
        
        // Initialize GCS Client
        $clientConfig = [];
        
        if (!empty($config['keyFilePath'])) {
            $clientConfig['keyFilePath'] = $config['keyFilePath'];
        }
        
        if (!empty($config['projectId'])) {
            $clientConfig['projectId'] = $config['projectId'];
        }
        
        $this->client = new \Google\Cloud\Storage\StorageClient($clientConfig);
        
        // Set base URL
        $this->baseUrl = $config['url'] ?? "https://storage.googleapis.com/{$this->bucket}/";
    }
    
    public function exists($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            return $object->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function get($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            return $object->downloadAsString();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function put($path, $contents, $options = [])
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            
            $uploadOptions = [
                'name' => $this->normalizePath($path)
            ];
            
            if (isset($options['ContentType'])) {
                $uploadOptions['metadata'] = ['contentType' => $options['ContentType']];
            }
            
            $bucket->upload($contents, $uploadOptions);
            
            return StorageResponse::saved(['path' => $path, 'url' => $this->url($path)]);
        } catch (\Exception $e) {
            return StorageResponse::saveFailed($path, $e->getMessage());
        }
    }
    
    public function save($sourcePath, $destinationPath, $options = [])
    {
        if (!file_exists($sourcePath)) {
            return StorageResponse::saveFailed($destinationPath, 'Source not found');
        }
        
        $contents = file_get_contents($sourcePath);
        
        // Detect MIME type
        if (!isset($options['ContentType'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $options['ContentType'] = finfo_file($finfo, $sourcePath);
            finfo_close($finfo);
        }
        
        return $this->put($destinationPath, $contents, $options);
    }
    
    public function delete($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            $object->delete();
            
            return StorageResponse::deleted($path);
        } catch (\Exception $e) {
            return StorageResponse::deleteFailed($path, $e->getMessage());
        }
    }
    
    public function copy($from, $to)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($from));
            $object->copy($bucket, ['name' => $this->normalizePath($to)]);
            
            return StorageResponse::copied($from, $to);
        } catch (\Exception $e) {
            return StorageResponse::saveFailed($to, $e->getMessage());
        }
    }
    
    public function move($from, $to)
    {
        $result = $this->copy($from, $to);
        if ($result['success']) {
            $this->delete($from);
        }
        return $result;
    }
    
    public function size($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            $info = $object->info();
            return $info['size'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function lastModified($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            $info = $object->info();
            
            if (isset($info['updated'])) {
                return strtotime($info['updated']);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function mimeType($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            $info = $object->info();
            return $info['contentType'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function url($path)
    {
        return $this->baseUrl . $this->normalizePath($path);
    }
    
    public function temporaryUrl($path, $expiration = 3600)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $object = $bucket->object($this->normalizePath($path));
            
            $url = $object->signedUrl(new \DateTime("+{$expiration} seconds"));
            return $url;
        } catch (\Exception $e) {
            return $this->url($path);
        }
    }
    
    public function makeDirectory($path)
    {
        // GCS doesn't have real directories
        return ['success' => true, 'error' => null];
    }
    
    public function deleteDirectory($path)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $prefix = $this->normalizePath($path) . '/';
            
            $objects = $bucket->objects(['prefix' => $prefix]);
            
            foreach ($objects as $object) {
                $object->delete();
            }
            
            return ['success' => true, 'error' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function files($directory = '', $recursive = false)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $prefix = $this->normalizePath($directory);
            if ($prefix) $prefix .= '/';
            
            $options = ['prefix' => $prefix];
            if (!$recursive) {
                $options['delimiter'] = '/';
            }
            
            $objects = $bucket->objects($options);
            
            $files = [];
            foreach ($objects as $object) {
                $files[] = $object->name();
            }
            
            return $files;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function directories($directory = '', $recursive = false)
    {
        try {
            $bucket = $this->client->bucket($this->bucket);
            $prefix = $this->normalizePath($directory);
            if ($prefix) $prefix .= '/';
            
            $objects = $bucket->objects([
                'prefix' => $prefix,
                'delimiter' => '/'
            ]);
            
            $directories = [];
            foreach ($objects->prefixes() as $prefix) {
                $directories[] = rtrim($prefix, '/');
            }
            
            return $directories;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function isDirectory($path)
    {
        return false; // GCS doesn't have real directories
    }
    
    public function isFile($path)
    {
        return $this->exists($path);
    }
}
