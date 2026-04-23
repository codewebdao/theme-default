<?php

namespace System\Libraries\Storages;

use System\Libraries\Responses\StorageResponse;

/**
 * S3Storage - AWS S3 Storage Driver
 * 
 * Storage driver cho AWS S3
 * Requires: composer require aws/aws-sdk-php
 * 
 * @package System\Libraries\Storages
 * @version 2.0.0
 */
class S3Storage extends BaseStorage
{
    /**
     * S3 Client
     */
    protected $client;
    
    /**
     * S3 Bucket
     */
    protected $bucket;
    
    /**
     * S3 Region
     */
    protected $region;
    
    /**
     * Base URL
     */
    protected $baseUrl;
    
    /**
     * Driver name
     */
    protected $driver = 's3';
    
    /**
     * Constructor
     * 
     * @param array $config Configuration
     * @throws \Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        
        // Check if AWS SDK is available
        if (!class_exists('\Aws\S3\S3Client')) {
            throw new \Exception('AWS SDK required: composer require aws/aws-sdk-php');
        }
        
        // Validate config
        if (empty($config['key']) || empty($config['secret']) || empty($config['bucket'])) {
            throw new \Exception('S3 requires: key, secret, bucket');
        }
        
        $this->bucket = $config['bucket'];
        $this->region = $config['region'] ?? 'us-east-1';
        
        // Initialize S3 Client
        $this->client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ]
        ]);
        
        // Set base URL
        $this->baseUrl = $config['url'] ?? "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/";
    }
    
    public function exists($path)
    {
        try {
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function get($path)
    {
        try {
            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return (string) $result['Body'];
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function put($path, $contents, $options = [])
    {
        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path),
                'Body' => $contents,
                'ACL' => $options['visibility'] ?? 'public-read',
            ]);
            
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
        return $this->put($destinationPath, $contents, $options);
    }
    
    public function delete($path)
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return StorageResponse::deleted($path);
        } catch (\Exception $e) {
            return StorageResponse::deleteFailed($path, $e->getMessage());
        }
    }
    
    public function copy($from, $to)
    {
        try {
            $this->client->copyObject([
                'Bucket' => $this->bucket,
                'CopySource' => $this->bucket . '/' . $this->normalizePath($from),
                'Key' => $this->normalizePath($to)
            ]);
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
            $result = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return $result['ContentLength'] ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function lastModified($path)
    {
        try {
            $result = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return $result['LastModified'] ? $result['LastModified']->getTimestamp() : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function mimeType($path)
    {
        try {
            $result = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            return $result['ContentType'] ?? false;
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
            $cmd = $this->client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($path)
            ]);
            $request = $this->client->createPresignedRequest($cmd, "+{$expiration} seconds");
            return (string) $request->getUri();
        } catch (\Exception $e) {
            return $this->url($path);
        }
    }
    
    public function makeDirectory($path)
    {
        // S3 doesn't have real directories
        return ['success' => true, 'error' => null];
    }
    
    public function deleteDirectory($path)
    {
        // Delete all objects with prefix
        try {
            $objects = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Prefix' => $this->normalizePath($path) . '/'
            ]);
            
            if (!empty($objects['Contents'])) {
                $keys = [];
                foreach ($objects['Contents'] as $obj) {
                    $keys[] = ['Key' => $obj['Key']];
                }
                $this->client->deleteObjects([
                    'Bucket' => $this->bucket,
                    'Delete' => ['Objects' => $keys]
                ]);
            }
            
            return ['success' => true, 'error' => null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function files($directory = '', $recursive = false)
    {
        try {
            $prefix = $this->normalizePath($directory);
            if ($prefix) $prefix .= '/';
            
            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'Delimiter' => $recursive ? '' : '/'
            ]);
            
            $files = [];
            if (!empty($result['Contents'])) {
                foreach ($result['Contents'] as $obj) {
                    $files[] = $obj['Key'];
                }
            }
            return $files;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function directories($directory = '', $recursive = false)
    {
        try {
            $prefix = $this->normalizePath($directory);
            if ($prefix) $prefix .= '/';
            
            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'Delimiter' => '/'
            ]);
            
            $directories = [];
            if (!empty($result['CommonPrefixes'])) {
                foreach ($result['CommonPrefixes'] as $p) {
                    $directories[] = rtrim($p['Prefix'], '/');
                }
            }
            return $directories;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function isDirectory($path)
    {
        return false; // S3 doesn't have real directories
    }
    
    public function isFile($path)
    {
        return $this->exists($path);
    }
    
    // ========================================
    // CHUNK UPLOAD IMPLEMENTATION - S3 Multipart Upload
    // ========================================
    
    /**
     * Check if supports chunk upload
     * 
     * @return bool
     */
    public function supportsChunkUpload()
    {
        return true;
    }
    
    /**
     * Initialize multipart upload
     * 
     * @param string $uploadId Upload ID (will be generated)
     * @param array $metadata Upload metadata
     * @return array
     */
    public function initChunkUpload($uploadId, $metadata = [])
    {
        try {
            $key = $metadata['destination_path'] ?? $metadata['file_name'];
            
            $params = [
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($key),
            ];
            
            if (!empty($metadata['content_type'])) {
                $params['ContentType'] = $metadata['content_type'];
            }
            
            // Create multipart upload
            $result = $this->client->createMultipartUpload($params);
            
            // Store S3 upload ID in metadata
            $s3UploadId = $result['UploadId'];
            
            return [
                'success' => true,
                'error' => null,
                'data' => [
                    'upload_id' => $uploadId,
                    's3_upload_id' => $s3UploadId,
                    'key' => $key
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'S3 multipart init failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Upload a part (chunk)
     * 
     * @param string $uploadId Upload ID
     * @param int $chunkNumber Chunk number (part number in S3)
     * @param string $chunkData Chunk file path or data
     * @param array $options Options (must contain s3_upload_id and key)
     * @return array
     */
    public function uploadChunk($uploadId, $chunkNumber, $chunkData, $options = [])
    {
        try {
            if (empty($options['s3_upload_id']) || empty($options['key'])) {
                return [
                    'success' => false,
                    'error' => 'Missing S3 upload ID or key'
                ];
            }
            
            // S3 part numbers start from 1
            $partNumber = $chunkNumber + 1;
            
            // Read chunk data
            if (is_file($chunkData)) {
                $body = fopen($chunkData, 'rb');
            } else {
                $body = $chunkData;
            }
            
            // Upload part
            $result = $this->client->uploadPart([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($options['key']),
                'UploadId' => $options['s3_upload_id'],
                'PartNumber' => $partNumber,
                'Body' => $body
            ]);
            
            if (is_resource($body)) {
                fclose($body);
            }
            
            return [
                'success' => true,
                'error' => null,
                'data' => [
                    'part_number' => $partNumber,
                    'etag' => $result['ETag']
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'S3 part upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Complete multipart upload
     * 
     * @param string $uploadId Upload ID
     * @param string $destinationPath Destination path
     * @param array $options Options (must contain s3_upload_id, key, parts)
     * @return array
     */
    public function completeChunkUpload($uploadId, $destinationPath, $options = [])
    {
        try {
            if (empty($options['s3_upload_id']) || empty($options['key'])) {
                return [
                    'success' => false,
                    'error' => 'Missing S3 upload ID or key'
                ];
            }
            
            // List all uploaded parts
            $parts = $this->client->listParts([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($options['key']),
                'UploadId' => $options['s3_upload_id']
            ]);
            
            $multipartParts = [];
            foreach ($parts['Parts'] as $part) {
                $multipartParts[] = [
                    'PartNumber' => $part['PartNumber'],
                    'ETag' => $part['ETag']
                ];
            }
            
            // Complete multipart upload
            $result = $this->client->completeMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($options['key']),
                'UploadId' => $options['s3_upload_id'],
                'MultipartUpload' => [
                    'Parts' => $multipartParts
                ]
            ]);
            
            return [
                'success' => true,
                'error' => null,
                'data' => [
                    'path' => $destinationPath,
                    'url' => $this->url($destinationPath),
                    'etag' => $result['ETag'] ?? null
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'S3 complete multipart failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Abort multipart upload
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options (must contain s3_upload_id and key)
     * @return array
     */
    public function abortChunkUpload($uploadId, $options = [])
    {
        try {
            if (empty($options['s3_upload_id']) || empty($options['key'])) {
                return [
                    'success' => false,
                    'error' => 'Missing S3 upload ID or key'
                ];
            }
            
            $this->client->abortMultipartUpload([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($options['key']),
                'UploadId' => $options['s3_upload_id']
            ]);
            
            return [
                'success' => true,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'S3 abort multipart failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get chunk upload progress
     * 
     * @param string $uploadId Upload ID
     * @param array $options Options (must contain s3_upload_id and key)
     * @return array
     */
    public function getChunkUploadProgress($uploadId, $options = [])
    {
        try {
            if (empty($options['s3_upload_id']) || empty($options['key'])) {
                return [
                    'success' => false,
                    'error' => 'Missing S3 upload ID or key'
                ];
            }
            
            $parts = $this->client->listParts([
                'Bucket' => $this->bucket,
                'Key' => $this->normalizePath($options['key']),
                'UploadId' => $options['s3_upload_id']
            ]);
            
            $uploadedChunks = [];
            foreach ($parts['Parts'] as $part) {
                // Convert part number back to chunk number (0-based)
                $uploadedChunks[] = $part['PartNumber'] - 1;
            }
            
            sort($uploadedChunks);
            
            return [
                'success' => true,
                'uploaded_chunks' => $uploadedChunks,
                'total_chunks' => $options['total_chunks'] ?? 0,
                'metadata' => $options
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'S3 list parts failed: ' . $e->getMessage()
            ];
        }
    }
}
