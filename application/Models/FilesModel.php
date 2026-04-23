<?php
namespace App\Models;
use System\Database\BaseModel;

class FilesModel extends BaseModel {

    protected $table = APP_PREFIX.'files';
    protected $primaryKey = 'id';
    public $timestamps = true;

    // Columns that are fillable (can be added or modified)
    protected $fillable = ['id','name', 'path', 'type', 'size', 'md5', 'storage', 'resize', 'webp', 'autoclean', 'user_id', 'post_used',];
    // Columns that are guarded (cannot be modified)
    protected $guarded = [];

    /**
     * Define the table schema (indexed array format)
     * 
     * @return array Table schema
     */
    public function _schema() {
        return [
            ['type' => 'increments', 'name' => 'id', 'options' => ['primary' => true, 'unsigned' => true]],
            ['type' => 'varchar', 'name' => 'name', 'options' => ['length' => 150, 'null' => false, 'default' => '']],
            ['type' => 'varchar', 'name' => 'path', 'options' => ['length' => 255, 'null' => false, 'default' => '', 'index' => true]],
            ['type' => 'varchar', 'name' => 'type', 'options' => ['length' => 50, 'null' => false, 'default' => '', 'comment' => 'File extension/MIME type']],
            ['type' => 'bigint', 'name' => 'size', 'options' => ['unsigned' => true, 'null' => false, 'default' => 0, 'comment' => 'File size in bytes']],
            ['type' => 'varchar', 'name' => 'md5', 'options' => ['length' => 32, 'null' => true, 'comment' => 'MD5 hash for duplicate detection', 'index' => true]],
            ['type' => 'enum', 'name' => 'storage', 'options' => ['values' => ['local', 's3', 'gcs'], 'default' => 'local', 'comment' => 'Storage driver used']],
            ['type' => 'text', 'name' => 'resize', 'options' => ['null' => true, 'comment' => 'Image resize sizes 200x200;400x400;800x800']],
            ['type' => 'boolean', 'name' => 'webp', 'options' => ['default' => 0, 'comment' => 'Has WebP variant (0=no, 1=yes)']],
            ['type' => 'boolean', 'name' => 'autoclean', 'options' => ['default' => 0, 'comment' => 'Auto delete flag']],
            ['type' => 'int', 'name' => 'user_id', 'options' => ['unsigned' => true, 'null' => true, 'default' => null, 'index' => true]],
            ['type' => 'varchar', 'name' => 'post_used', 'options' => ['length' => 255, 'null' => true, 'default' => '']]
        ];
    }

    /**
     * Fetches paginated files from the database with optional conditions and ordering
     *
     * @param string $where
     * @param array $params
     * @param string $orderBy
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getFiles($where = '', $params = [], $orderBy = 'id desc', $page = 1, $limit = null) {
        if ($limit) {
            // Use paginateWith for pagination
            return static::query()->paginateWith('*', $where, $params, $orderBy, $page, $limit);
        }
        
        // For non-paginated results
        $query = static::query();
        
        if (!empty($where)) {
            $query->whereRaw($where, $params);
        }
        
        // Parse orderBy string
        $orderParts = preg_split('/\s+/', trim($orderBy), 2);
        $orderColumn = $orderParts[0] ?? 'id';
        $orderDirection = strtolower($orderParts[1] ?? 'desc');
        
        $query->orderBy($orderColumn, $orderDirection);
        
        return [
            'data' => $query->get(),
            'total' => static::count(),
            'is_next' => 0
        ];
    }

    /**
     * Get file by ID
     *
     * @param int $id
     * @return array|null
     */
    public function getFileById($id) {
        $result = static::find($id);
        return $result ? $result : null;
    }

    /**
     * Get file by path
     *
     * @param string $path
     * @return array|null
     */
    public function getFileByPath($path) {
        $result = static::where('path', $path)->first();
        return $result ? $result : null;
    }
    
    /**
     * Get file by MD5 hash
     *
     * @param string $md5
     * @return array|null
     */
    public function getFileByMd5($md5) {
        $result = static::where('md5', $md5)->first();
        return $result ? $result : null;
    }
    
    /**
     * Check if file exists by path or MD5
     *
     * @param string $path
     * @param string|null $md5
     * @return array|null
     */
    public function fileExists($path, $md5 = null) {
        if ($md5) {
            $result = static::where('md5', $md5)
                ->orWhere('path', $path)
                ->first();
            return $result ? $result : null;
        }
        return $this->getFileByPath($path);
    }
    
    /**
     * Get URL for file (helper method)
     *
     * @param string $path
     * @return string
     */
    public function upload_url($path) {
        // Gọi helper function
        return file_url($path);
    }

    /**
     * Add a new file
     *
     * @param array $data
     * @return mixed
     */
    public function addFile($data) {
        $file = static::create($data);
        return isset($file['id']) ? $file['id'] : $file;
    }

    /**
     * Update file information
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateFile($id, $data) {
        return static::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete a file
     *
     * @param int $id
     * @return bool
     */
    public function deleteFile($id) {
        return static::destroy($id) > 0;
    }

    /**
     * Replace path prefix for multiple files (batch update)
     *
     * @param string $oldPath
     * @param string $newPath
     * @return array
     */
    public function replacePath($oldPath, $newPath) {
        // Use raw SQL for complex CONCAT operation
        $tableName = $this->getTable();
        
        // SQL statement structure to update path
        $sql = "UPDATE {$tableName} 
                SET path = CONCAT(?, SUBSTRING(path, LENGTH(?) + 1)) 
                WHERE path LIKE ?";

        // Parameters for SQL statement
        $params = [
            $newPath,          // New value to concatenate to path
            $oldPath,          // oldPath value to calculate length
            $oldPath . '%'     // Condition to find paths starting with oldPath
        ];

        // Execute SQL statement using raw query
        try {
            $affectedRows = $this->raw($sql, $params);

            // Check if any records were updated
            if ($affectedRows === 0) {
                return ['error', 'No files found with the specified old path prefix.'];
            }

            // Success message
            return ['success', "Successfully replaced paths from '{$oldPath}' to '{$newPath}'. Total records updated: {$affectedRows}."];
        } catch (\Exception $e) {
            return ['error', 'Failed to replace paths: ' . $e->getMessage()];
        }
    }
}