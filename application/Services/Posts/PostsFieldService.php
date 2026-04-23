<?php

namespace App\Services\Posts;

use System\Database\DB;

/**
 * PostsFieldService - Field Processing Logic
 * 
 * Handles all field-related operations including:
 * - Dynamic field processing (id always exists, others are dynamic)
 * - Special fields handling (title, slug, search_string, user_id, created_at, updated_at)
 * - Field type conversions and validations
 * - Reference and Point fields
 * 
 * @package App\Services\Posts
 */
class PostsFieldService
{
    /**
     * Process all fields for saving
     * 
     * @param array $fields Field definitions
     * @param array $data Input data
     * @param array $existingData Existing post data (for updates)
     * @param array $context Context data ['post_id' => int, 'posttype_slug' => string, 'lang' => string]
     * @return array Processed data ready for database
     */
    public function processFields($fields, $data, $existingData = [], $context = [])
    {
        $processed = [];

        // Process each field definition
        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';
            if (empty($fieldName)) {
                continue;
            }
            // Check if field exists in input data
            if (!array_key_exists($fieldName, $data)) {
                continue;
            }
            $value = $data[$fieldName];
            if ($value === '' && $field['unique']) {
                $processed[$fieldName] = null;
                continue;
            }
            // Process based on field type (pass context for complex fields)
            $processed[$fieldName] = $this->processFieldValue($field, $value, $context);
        }

        return $processed;
    }

    /**
     * Process single field value based on type
     * 
     * @param array $field Field definition
     * @param mixed $value Field value
     * @param array $context Context ['post_id', 'posttype_slug', 'lang']
     * @return mixed Processed value
     */
    public function processFieldValue($field, $value, $context = [])
    {
        $fieldType = $field['type'] ?? 'Text';

        switch ($fieldType) {
            case 'Number':
            case 'Integer':
                return $this->processNumberField($field, $value);

            case 'Float':
            case 'Decimal':
                return $this->processFloatField($field, $value);

            case 'Boolean':
            case 'Checkbox':
                return $this->processBooleanField($value);

            case 'Point':
                return $this->processPointField($value);

            case 'Date':
            case 'DateTime':
                return $this->processDateField($value);

            case 'User':
                return $this->processUserField($value);

            case 'Password':
                return $this->processPasswordField($value);

            case 'ColorPicker':
                return $this->processColorField($value);

            case 'Reference':
                return $this->processReferenceField($field, $value);

            case 'Variations':
                return $this->processVariationsField($field, $value, $context);

            case 'JSON':
            case 'Array':
                return $this->processJsonField($value);

            case 'Text':
            case 'Textarea':
            case 'Richtext':
            case 'WYSIWYG':
            case 'Email':
            case 'URL':
            case 'OEmbed':
            case 'Select':
            case 'Radio':
            case 'File':
            case 'Image':
            case 'Gallery':
            case 'Repeater':
            case 'Flexible':
            default:
                return $this->processTextField($value);
        }
    }

    /**
     * Process Number/Integer field
     * 
     * @param array $field Field definition
     * @param mixed $value Field value
     * @return int|null
     */
    protected function processNumberField($field, $value)
    {
        // Empty string or null → null
        if ($value === '' || $value === null) {
            return null;
        }

        // Check step to determine int or float
        $step = isset($field['step']) ? $field['step'] : 1;
        // Compare float value with int value to avoid implicit conversion warning
        $stepFloat = (float)$step;
        $stepInt = (int)$stepFloat;
        if (isset($step) && $step > 0 && abs($stepFloat - $stepInt) < 0.0001) {
            // Step is integer → cast to int
            return (int)$value;
        }

        // Default to int
        return (int)$value;
    }

    /**
     * Process Float/Decimal field
     * 
     * @param array $field Field definition
     * @param mixed $value Field value
     * @return float|null
     */
    protected function processFloatField($field, $value)
    {
        // Empty string or null → null
        if ($value === '' || $value === null) {
            return null;
        }

        return (float)$value;
    }

    /**
     * Process Boolean/Checkbox field
     * 
     * @param mixed $value Field value
     * @return int 0 or 1
     */
    protected function processBooleanField($value)
    {
        // Convert to boolean: 1, '1', true, 'true', 'on', 'yes' → 1
        // Everything else → 0
        return in_array($value, [1, '1', true, 'true', 'on', 'yes'], true) ? 1 : 0;
    }

    /**
     * Process Point field (Geometry)
     * 
     * @param mixed $value Field value
     * @return \System\Database\DB\raw|null Raw SQL expression or null
     */
    protected function processPointField($value)
    {
        // Decode if JSON string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        // Validate structure
        if (!is_array($value) || !isset($value['lat']) || !isset($value['lng'])) {
            return null;
        }

        $latitude = (float)$value['lat'];
        $longitude = (float)$value['lng'];

        // Validate coordinate ranges
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        // Check for NaN or Infinity
        if (is_nan($latitude) || is_nan($longitude) || is_infinite($latitude) || is_infinite($longitude)) {
            return null;
        }

        // Return raw SQL expression for ST_GeomFromText
        return DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})')");
    }

    /**
     * Process Date/DateTime field
     * 
     * @param mixed $value Field value
     * @return string|null Formatted datetime string
     */
    protected function processDateField($value)
    {
        if (empty($value)) {
            return null;
        }

        // If numeric (unix timestamp)
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }

        // If string (try to parse)
        if (is_string($value)) {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }

        return null;
    }

    /**
     * Process User field
     * 
     * @param mixed $value User ID
     * @return int|null User ID
     */
    protected function processUserField($value)
    {
        // Empty → try to get current user
        if (empty($value)) {
            if (function_exists('get_author_id')) {
                $userId = get_author_id();
                return $userId ?: 1;
            }
            return 1; // Default user ID
        }

        // Validate numeric
        if (!is_numeric($value)) {
            return 1; // Fallback to default
        }

        return (int)$value;
    }

    /**
     * Process Password field
     * 
     * @param mixed $value Password value
     * @return string|null Hashed password or null
     */
    protected function processPasswordField($value)
    {
        // Empty → null (don't update password on edit)
        if (empty($value)) {
            return null;
        }

        // Hash password if not already hashed
        // bcrypt produces 60 character strings
        if (strlen($value) < 60) {
            return \System\Libraries\Security::hashPassword($value);
        }

        // Already hashed, return as-is
        return $value;
    }

    /**
     * Process ColorPicker field
     * 
     * @param mixed $value Color value (hex format)
     * @return string|null Normalized hex color (#RRGGBB)
     */
    protected function processColorField($value)
    {
        if (empty($value)) {
            return null;
        }

        $color = trim($value);
        
        // Add # if missing
        if (substr($color, 0, 1) !== '#') {
            $color = '#' . $color;
        }

        // Validate hex format (#RRGGBB)
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return strtoupper($color);
        }

        // Invalid format
        return null;
    }

    /**
     * Process JSON/Array field
     * 
     * @param mixed $value Field value
     * @return string|null JSON string
     */
    protected function processJsonField($value)
    {
        if (is_array($value)) {
            $encoded = json_encode($value);
            if ($encoded === false) {
                return null;
            }
            return $encoded;
        }

        if (is_string($value)) {
            // Validate if it's already JSON
            if (substr($value, 0, 1) === '{' || substr($value, 0, 1) === '[') {
                json_decode($value);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Process Text field (default handler)
     * 
     * @param mixed $value Field value
     * @return string|null
     */
    protected function processTextField($value)
    {
        // If array, encode to JSON
        if (is_array($value)) {
            return json_encode($value);
        }

        // If scalar, return as string
        if (is_scalar($value)) {
            return (string)$value;
        }

        return null;
    }


    /**
     * Process Reference field value
     * 
     * NEW STRUCTURE (v2): Field config có nested object "reference"
     * {
     *   "type": "Reference",
     *   "reference": {
     *     "postTypeRef": "pages",
     *     "selectionMode": "single",     // ← CONTROLS BEHAVIOR (single | multiple)
     *     "bidirectional": true,          // Sync reverse relation
     *     "reverseField": "venue_id",     // Column name in referenced table
     *     "filter": [...],
     *     "sort": [...]
     *   }
     * }
     * 
     * LOGIC LƯU TRỮ MỚI (UPDATED):
     * =============================
     * 
     * CẢ 2 MODE ĐỀU TẠO CỘT VÀ LƯU VÀO CỘT:
     * 
     * 1. selectionMode = "single":
     *    → Người dùng chọn 1 item
     *    → LƯU ID VÀO CỘT: UPDATE posts SET relfield = 2
     *    → LƯU VÀO RELATION TABLE: INSERT post_relations (...)
     *    → Return (int) ID để lưu vào cột
     * 
     * 2. selectionMode = "multiple":
     *    → Người dùng chọn nhiều items
     *    → LƯU FIRST ID VÀO CỘT: UPDATE posts SET relfield = 2 (để tránh null)
     *    → LƯU TẤT CẢ IDs VÀO RELATION TABLE: INSERT post_relations (...)
     *    → Return null để lưu vào cột
     * 
     * 3. bidirectional = true:
     *    → Lưu ID bài viết hiện tại vào cột reverseField của bài được reference
     *    → Example: UPDATE pages SET venue_id = 5 WHERE id = 2
     *    → Xử lý trong RelationshipService::syncBidirectional()
     * 
     * LÝ DO THAY ĐỔI:
     * - Tránh SQL error khi UPDATE với field = NULL mà cột không tồn tại
     * - Consistency: Tất cả Reference fields đều có cột
     * - Simplicity: Không cần logic phức tạp để skip null fields
     * 
     * @param array $field Field configuration
     * @param mixed $value Field value
     * @return int|null ID to save in column, null if no selection
     */
    protected function processReferenceField($field, $value)
    {
        // Empty value
        if (empty($value)) {
            return null;
        }

        // Get reference config
        $refConfig = $this->getReferenceConfig($field);
        
        // Extract IDs from value (object, array, string...)
        $ids = $this->extractReferenceIds($value);
        
        if (empty($ids)) {
            return null;
        }

        // Check selectionMode
        if ($refConfig['selection_mode'] === 'single') {
            /**
             * SINGLE SELECT:
             * - Return ID để lưu vào CỘT database
             * - Example: UPDATE posts SET relfield = 2
             */
            return (int)$ids[0];
        } else {
            /**
             * MULTIPLE SELECT:
             * - Return null (không lưu vào cột)
             * - IDs sẽ được xử lý bởi:
             *   PostsService::handleRelationships()
             *   → RelationshipService::syncReferences()
             *   → Save vào relation table
             */
            return null;
        }
    }

    /**
     * Get normalized reference config from field (NEW structure only)
     * 
     * @param array $field Field configuration
     * @return array Normalized config
     */
    public function getReferenceConfig($field)
    {
        // NEW structure: nested "reference" object (REQUIRED)
        if (empty($field['reference']) || !is_array($field['reference'])) {
            return [
                'post_type_reference' => '',
                'selection_mode' => 'single',
                'bidirectional' => false,
                'reverse_field' => '',
                'filter' => [],
                'sort' => [],
            ];
        }
        
        $ref = $field['reference'];
        
        return [
            'post_type_reference' => $ref['postTypeRef'] ?? '',
            'selection_mode' => $ref['selectionMode'] ?? 'single',
            'bidirectional' => !empty($ref['bidirectional']),
            'reverse_field' => $ref['reverseField'] ?? '',
            'filter' => $ref['filter'] ?? [],
            'sort' => $ref['sort'] ?? [],
            'search_columns' => $ref['search_columns'] ?? [],
            'display_columns' => $ref['display_columns'] ?? [],
        ];
    }

    /**
     * Extract reference IDs from various value formats
     * 
     * @param mixed $value Input value
     * @return array Array of IDs
     */
    protected function extractReferenceIds($value)
    {
        $ids = [];

        if (is_numeric($value)) {
            // Single ID: 123
            $ids[] = (int)$value;
        } elseif (is_string($value)) {
            // JSON string or comma-separated IDs
            if (strpos($value, '{') === 0 || strpos($value, '[') === 0) {
                // JSON format
                $decoded = _json_decode($value);
                $ids = $this->extractReferenceIds($decoded);
            } elseif (strpos($value, ',') !== false) {
                // Comma-separated: "1,2,3"
                $ids = array_map('intval', explode(',', $value));
            } else {
                // Single ID as string
                $ids[] = (int)$value;
            }
        } elseif (is_array($value)) {
            if (isset($value['id'])) {
                // Single object: {"id": 123, "title": "..."}
                $ids[] = (int)$value['id'];
            } else {
                // Array of IDs or objects
                foreach ($value as $item) {
                    if (is_numeric($item)) {
                        // Array of IDs: [1, 2, 3]
                        $ids[] = (int)$item;
                    } elseif (is_array($item) && isset($item['id'])) {
                        // Array of objects: [{"id": 1}, {"id": 2}]
                        $ids[] = (int)$item['id'];
                    }
                }
            }
        }

        // Remove duplicates and filter out invalid IDs
        $ids = array_unique(array_filter($ids, function($id) {
            return is_numeric($id) && $id > 0;
        }));

        return array_values($ids);
    }

    /**
     * Process Variations field
     * 
     * Variations được lưu trong bảng con: {posttype_slug}_{field_name}
     * Method này query tất cả variations từ bảng con
     * 
     * NOTE: Khi SAVE, Variations được xử lý qua API riêng (CRUD variations)
     *       Method này CHỈ dùng khi LOAD để hiển thị
     * 
     * @param array $field Variations field definition
     * @param mixed $value Variations value
     * @param array $context Context ['post_id', 'posttype_slug']
     * @return string|null JSON
     */
    protected function processVariationsField($field, $value, $context = [])
    {
        $postId = $context['post_id'] ?? null;
        $posttypeSlug = $context['posttype_slug'] ?? '';

        // Nếu chưa có post ID (đang create) → return null
        if (empty($postId) || empty($posttypeSlug)) {
            return null;
        }

        try {
            // Table name: {posttype_slug}_{field_name}
            $fieldName = $field['field_name'] ?? '';
            $variationsTable = $posttypeSlug . '_' . $fieldName;

            // Query all variations for this post
            $variations = \System\Database\DB::table($variationsTable)
                ->where('post_id', $postId)
                ->orderBy('id', 'ASC')
                ->get();

            return json_encode($variations);
        } catch (\Exception $e) {
            error_log("processVariationsField error: " . $e->getMessage());
            // Return value as-is if query fails
            return is_string($value) ? $value : json_encode($value);
        }
    }

    /**
     * Process special fields ONLY if they exist in posttype
     * 
     * Special fields get custom processing:
     * - title → Generate slug if slug empty
     * - slug → Auto-unique, lowercase
     * - search_string → Auto-generate from title
     * - user_id/author → Auto-assign current user
     * - created_at/updated_at → Format timestamp
     * - status → Validate & auto-adjust
     * 
     * @param array $data Input data
     * @param array $existingData Existing post data (for updates)
     * @param array $fields Field definitions from posttype
     * @return array Processed special fields
     */
    public function processSpecialFields($data, $existingData = [], $fields = [])
    {
        $processed = [];

        // Build map of available fields
        $availableFields = [];
        foreach ($fields as $field) {
            $availableFields[$field['field_name'] ?? ''] = $field;
        }

        // TITLE - xử lý nếu có
        if (isset($data['title']) && isset($availableFields['title'])) {
            $processed['title'] = trim($data['title']);
        }

        // SLUG - xử lý nếu có
        if (isset($availableFields['slug'])) {
            if (isset($data['slug'])) {
                $slug = trim($data['slug']);
                // Auto-generate from title if empty and title exists
                if (empty($slug) && !empty($processed['title'])) {
                    $slug = url_slug($processed['title']);
                }
                $processed['slug'] = !empty($slug) ? url_slug($slug) : null;
            } elseif (empty($existingData['slug']) && !empty($processed['title'])) {
                // Auto-generate slug from title for new post
                $processed['slug'] = url_slug($processed['title']);
            }
        }

        // SEARCH_STRING - xử lý nếu có
        if (isset($availableFields['search_string'])) {
            if (isset($data['search_string'])) {
                $searchString = trim($data['search_string']);
                // Auto-generate from title if empty
                if (empty($searchString) && !empty($processed['title'])) {
                    $searchString = keyword_slug($processed['title']);
                }
                $processed['search_string'] = !empty($searchString) ? keyword_slug($searchString) : null;
            } elseif (empty($existingData['search_string']) && !empty($processed['title'])) {
                // Auto-generate from title
                $processed['search_string'] = keyword_slug($processed['title']);
            }
        }

        // USER_ID / AUTHOR - xử lý nếu có
        $userFields = ['user_id', 'author', 'author_id', 'creator_id', 'vendor_id', 'created_by'];
        foreach ($userFields as $userField) {
            if (isset($availableFields[$userField]) && isset($data[$userField])) {
                $userId = $data[$userField];
                // Auto-assign current user if empty
                if (empty($userId) && function_exists('get_author_id')) {
                    $userId = get_author_id();
                }
                $processed[$userField] = $userId ? (int)$userId : null;
            }
        }

        // CREATED_AT - xử lý nếu có
        if (isset($availableFields['created_at']) && isset($data['created_at'])) {
            $createdAt = $data['created_at'];
            
            if (empty($createdAt)) {
                $processed['created_at'] = date('Y-m-d H:i:s');
            } elseif (is_numeric($createdAt)) {
                $processed['created_at'] = date('Y-m-d H:i:s', (int)$createdAt);
            } elseif (is_string($createdAt)) {
                $timestamp = strtotime($createdAt);
                $processed['created_at'] = $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : date('Y-m-d H:i:s');
            } else {
                $processed['created_at'] = date('Y-m-d H:i:s');
            }
        }

        // UPDATED_AT - xử lý nếu có
        if (isset($availableFields['updated_at']) && isset($data['updated_at'])) {
            $processed['updated_at'] = date('Y-m-d H:i:s');
        }

        // STATUS - xử lý nếu có
        if (isset($availableFields['status']) && isset($data['status'])) {
            $status = $data['status'];
            $allowedStatuses = ['active', 'pending', 'inactive', 'schedule', 'draft', 'suspended', 'deleted'];
            
            if (!in_array($status, $allowedStatuses)) {
                $status = 'active';
            }

            // Auto-schedule logic (nếu có created_at)
            if (isset($processed['created_at'])) {
                $now = date('Y-m-d H:i:s');
                if ($status === 'active' && $processed['created_at'] > $now) {
                    $status = 'schedule';
                } elseif ($status === 'schedule' && $processed['created_at'] < $now) {
                    $status = 'active';
                }
            }

            $processed['status'] = $status;
        }

        return $processed;
    }

    /**
     * Parse Point field for display (convert WKT to lat/lng object)
     * 
     * @param string $wkt WKT format: "POINT(lng lat)"
     * @return array|null ['lat' => float, 'lng' => float]
     */
    public function parsePointForDisplay($wkt)
    {
        if (empty($wkt) || !is_string($wkt)) {
            return null;
        }

        // Parse WKT format: POINT(lng lat)
        if (preg_match('/POINT\(\s*([-0-9\.]+)\s+([-0-9\.]+)\s*\)/i', $wkt, $matches)) {
            return [
                'lng' => (float)$matches[1],
                'lat' => (float)$matches[2]
            ];
        }

        return null;
    }

    /**
     * Parse fields for display (convert database values to display format)
     * 
     * @param array $fields Field definitions
     * @param array $data Post data from database
     * @return array Parsed data ready for display
     */
    public function parseFieldsForDisplay($fields, $data)
    {
        $parsed = $data;

        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';
            $fieldType = $field['type'] ?? 'Text';

            if (empty($fieldName) || !isset($data[$fieldName])) {
                continue;
            }

            $value = $data[$fieldName];

            switch ($fieldType) {
                case 'Point':
                    $parsed[$fieldName] = $this->parsePointForDisplay($value);
                    break;

                case 'JSON':
                case 'Array':
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $parsed[$fieldName] = $decoded;
                        }
                    }
                    break;

                case 'Boolean':
                case 'Checkbox':
                    $parsed[$fieldName] = (int)$value;
                    break;
            }
        }

        return $parsed;
    }

    /**
     * Get default value for field
     * 
     * @param array $field Field definition
     * @return mixed Default value
     */
    public function getFieldDefault($field)
    {
        $fieldType = $field['type'] ?? 'Text';
        $defaultValue = $field['default_value'] ?? null;

        if ($defaultValue !== null) {
            return $defaultValue;
        }

        // Type-specific defaults
        switch ($fieldType) {
            case 'Number':
            case 'Integer':
            case 'Float':
            case 'Decimal':
                return 0;

            case 'Boolean':
            case 'Checkbox':
                return 0;

            case 'Array':
            case 'JSON':
                return '[]';

            case 'Date':
            case 'DateTime':
                return date('Y-m-d H:i:s');

            default:
                return '';
        }
    }

    /**
     * Normalize all data (special fields + custom fields)
     * 
     * @param array $fields Field definitions
     * @param array $data Input data
     * @param array $existingData Existing post data (for updates)
     * @return array Normalized data
     */
    public function normalizeAllData($fields, $data, $existingData = [])
    {
        // Process special fields first
        $normalized = $this->processSpecialFields($data, $existingData);

        // Process custom fields
        $customFieldsData = $this->processFields($fields, $data, $existingData);

        // Merge together
        $normalized = array_merge($normalized, $customFieldsData);

        return $normalized;
    }

    /**
     * Load reference field options (available posts to select from)
     * NEW structure only
     * 
     * @param array $field Reference field definition
     * @param string $lang Language code
     * @param string $modelClass PostsModel class name
     * @return array Available posts
     */
    public function loadReferenceFieldOptions($field, $lang, $modelClass = '\App\Models\PostsModel')
    {
        // Get reference config (NEW structure)
        $ref = $field['reference'] ?? [];
        $referencedPosttype = $ref['postTypeRef'] ?? '';
        
        if (empty($referencedPosttype)) {
            return [];
        }

        try {
            // Create model instance
            $model = new $modelClass($referencedPosttype, $lang);
            $query = $model->newQuery();

            // Apply filters from NEW structure
            $filters = $ref['filter'] ?? [];
            if (!empty($filters)) {
                foreach ($filters as $filterGroup) {
                    $logic = $filterGroup['logic'] ?? 'AND';
                    $conditions = $filterGroup['conditions'] ?? [];
                    
                    if (empty($conditions)) continue;
                    
                    $method = ($logic === 'OR') ? 'orWhereGroup' : 'whereGroup';
                    
                    $query->$method(function($q) use ($conditions) {
                        foreach ($conditions as $condition) {
                            $column = $condition['column'] ?? '';
                            $op = $condition['op'] ?? '=';
                            $value = $condition['value'] ?? '';
                
                            if (!empty($column) && preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                                $q->where($column, $op, $value);
                            }
                        }
                    });
                }
            }

            // Apply sort from NEW structure
            $sorts = $ref['sort'] ?? [];
            if (!empty($sorts)) {
                foreach ($sorts as $sort) {
                    $column = $sort['column'] ?? 'id';
                    $direction = strtoupper($sort['direction'] ?? 'ASC');
                    
                    if (preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                        $query->orderBy($column, $direction);
                }
            }
            } else {
                $query->orderBy('id', 'ASC');
            }

            // Limit to 100 for performance
            return $query->limit(100)->get();
        } catch (\Exception $e) {
            error_log("loadReferenceFieldOptions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sanitize custom filter - Remove dangerous SQL keywords
     * 
     * @param string $filter Custom filter string
     * @return string Sanitized filter
     */
    protected function sanitizeCustomFilter($filter)
    {
        // Remove dangerous keywords (case-insensitive)
        $dangerous = [
            'DROP', 'DELETE', 'TRUNCATE', 'UPDATE', 'INSERT',
            'ALTER', 'CREATE', 'GRANT', 'REVOKE', 'EXEC',
            'EXECUTE', 'SCRIPT', 'JAVASCRIPT', 'ONERROR',
            '--', '/*', '*/', 'UNION', 'DECLARE'
        ];

        $filter = str_ireplace($dangerous, '', $filter);

        // Remove multiple spaces
        $filter = preg_replace('/\s+/', ' ', $filter);

        return trim($filter);
    }

    /**
     * Parse custom filter to Query Builder conditions
     * 
     * Chuyển đổi custom filter thành Query Builder calls (an toàn hơn)
     * 
     * @param object $query Query builder instance
     * @param string $filter Sanitized filter
     * @return void
     */
    protected function parseCustomFilterToQuery($query, $filter)
    {
        // Parse simple conditions: field = value, field > value, etc.
        // Example: "price > 100 AND stock > 0 OR (title LIKE '%apple%')"
        // Example: "price > 100 AND stock > 0 OR (title LIKE '%apple%')"
        // Split by AND
        $conditions = preg_split('/\s+AND\s+/i', $filter);
        
        foreach ($conditions as $condition) {
            // Parse: field operator value
            if (preg_match('/^([a-zA-Z0-9_]+)\s*(=|!=|>|>=|<|<=|LIKE)\s*(.+)$/i', trim($condition), $matches)) {
                $field = $matches[1];
                $operator = $matches[2];
                $value = trim($matches[3], '\'" ');
                
                // ✅ SAFE: Use Query Builder with parameters
                $query->where($field, $operator, $value);
            }
        }
    }

    /**
     * Finalize Image field configuration
     * 
     * Load watermark settings từ global options vào field definition
     * Được gọi khi load form để hiển thị
     * 
     * @param array $field Image field definition
     * @return array Field with watermark settings
     */
    public function finalizeImageFieldConfig($field)
    {
        // Only process Image fields
        if (($field['type'] ?? '') !== 'Image') {
            return $field;
        }

        // Check if watermark is enabled
        if (empty($field['watermark']) || $field['watermark'] !== true) {
            return $field;
        }

        // ✅ Load watermark settings từ global options (nếu chưa set)
        
        // Watermark image path
        if (empty($field['watermark_img'])) {
            $field['watermark_img'] = option('watermark_img') ?? '';
        }

        // Watermark opacity (0-100)
        if (empty($field['watermark_opacity'])) {
            $field['watermark_opacity'] = option('watermark_opacity') ?? 100;
        }

        // Watermark position (top-left, top-right, center, bottom-left, bottom-right)
        if (empty($field['watermark_position'])) {
            $field['watermark_position'] = option('watermark_position') ?? 'bottom-right';
        }

        // Watermark padding (pixels)
        if (empty($field['watermark_padding'])) {
            $field['watermark_padding'] = option('watermark_padding') ?? 10;
        }

        // Watermark scale (percentage)
        if (empty($field['watermark_scale'])) {
            $field['watermark_scale'] = option('watermark_scale') ?? 100;
        }

        return $field;
    }

}


