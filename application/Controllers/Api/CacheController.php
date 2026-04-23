<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;
use Exception;

class CacheController extends ApiController
{
    private $cacheDir;
    
    public function __construct()
    {
        parent::__construct();
        $this->cacheDir = PATH_WRITE . 'cache';
    }
    
    public function delete()
    {
        try {
            $result = $this->deleteAllCache();
            return $this->success($result);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    
    private function deleteAllCache()
    {
        if (!is_dir($this->cacheDir)) {
            return ['success' => false, 'message' => 'Cache directory not found'];
        }
        
        $deletedCount = $this->rrmdir($this->cacheDir);
        
        // Recreate the cache directory
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
        
        return [
            'success' => true, 
            'message' => "Successfully deleted all cache files ({$deletedCount} items)"
        ];
    }
    
    private function rrmdir($dir)
    {
        if (!is_dir($dir)) return 0;
        
        $count = 0;
        $objects = scandir($dir);
        
        foreach ($objects as $object) {
            if ($object == "." || $object == "..") continue;
            
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($path)) {
                $count += $this->rrmdir($path);
            } else {
                if (@unlink($path)) {
                    $count++;
                }
            }
        }
        
        @rmdir($dir);
        return $count;
    }
    
}