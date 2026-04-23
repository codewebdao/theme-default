<?php

namespace App\Services\Posts;

use System\Libraries\Validate;

load_helpers(['languages']);
/**
 * PostsValidationService - Validation Logic for Posts
 * 
 * Handles all validation operations for posts and fields
 * 
 * @package App\Services\Posts
 */
class PostsValidationService
{


    /**
     * VALIDATE - Method chính, tổng hợp TẤT CẢ validations
     * 
     * Thực hiện 4 LAYERS:
     * ====================
     * 1. Library validation - Dùng thư viện Validate theo type rules (với optional wrap)
     * 2. Required validation - Custom check (if empty → error) - CHỈ KHI KHÔNG PHẢI DRAFT
     * 3. Unique validation - Database check (WHERE field=value AND id!=excludeId) - CHỈ KHI CÓ GIÁ TRỊ
     * 4. Complex fields validation - Recursive (Repeater/Flexible/Variations)
     * 
     * DRAFT MODE (skipRequired = true, excludeId = null):
     * - KHÔNG validate required
     * - CHỈ validate NẾU field có giá trị (dùng optional wrap)
     * - Cho phép tạo draft với data trống
     * 
     * @param array $fields Field definitions từ posttype
     * @param array $data Post data từ form
     * @param object $model Model instance (for unique check)
     * @param int|null $excludeId Exclude ID (null for create, postId for update)
     * @param bool $skipRequired Skip required validation (true for draft create, false for edit/active)
     * @return array Errors
     */
    public function validate($fields, $data, $model, $excludeId = null, $skipRequired = false)
    {
        $errors = [];

        // ===== LAYER 1: LIBRARY VALIDATION (Dùng thư viện Validate) =====
        // Build rules với optional wrap cho draft create
        $rules = $this->buildRules($fields, $skipRequired);
        $libraryErrors = $this->validateRules($data, $rules);

        if (!empty($libraryErrors)) {
            $errors = array_merge($errors, $libraryErrors);
        }

        // ===== LAYER 2-4: VALIDATE TỪNG FIELD (Required, Unique, Complex) =====
        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';
            $fieldType = $field['type'] ?? 'Text';
            $isRequired = !empty($field['required']);
            $isUnique = !empty($field['unique']);

            if (empty($fieldName)) {
                continue;
            }

            $value = $data[$fieldName] ?? null;
            $label = $field['label'] ?? ucfirst($fieldName);

            // ✅ SKIP VALIDATION NẾU:
            // - Draft mode (skipRequired = true)
            // - Field không có giá trị (null/empty)
            // → Cho phép tạo draft với field trống
            $isEmpty = ($value === null) ||
                (is_string($value) && trim($value) === '') ||
                (is_array($value) && empty($value));

            // ✅ DRAFT CREATE: Skip validation for empty fields
            if ($skipRequired && $excludeId === null && $isEmpty) {
                continue; // Không validate field trống khi tạo draft
            }

            // ===== LAYER 2: REQUIRED VALIDATION =====
            // CHỈ validate required khi KHÔNG phải draft
            if ($isRequired && !$skipRequired) {
                if ($isEmpty) {
                    $errors[$fieldName] = [__('%1% is required', $label)];
                    continue; // Skip other validations if required failed
                }
            }

            // ===== LAYER 3: UNIQUE VALIDATION =====
            // CHỈ validate unique NẾU có giá trị
            if ($isUnique && !$isEmpty) {                // Trim value if string
                $checkValue = is_string($value) ? trim($value) : $value;

                if ($checkValue !== '') {
                    // Query database
                    if (!$model->isFieldUnique($fieldName, $checkValue, $excludeId)) {
                        $errors[$fieldName] = [__('Field %1% must be unique. Value "%2%" already exists', $label, $checkValue)];
                        continue;
                    }
                }
            }

            // ===== LAYER 4: COMPLEX FIELDS & TYPE-SPECIFIC VALIDATION =====
            // CHỈ validate NẾU có giá trị (không validate field trống)
            if (!$isEmpty) {
                switch ($fieldType) {
                    case 'Repeater':
                        $repeaterErrors = $this->validateRepeater($field, $value, $skipRequired);
                        if (!empty($repeaterErrors)) {
                            $errors[$fieldName] = $repeaterErrors;
                        }
                        break;

                    case 'Flexible':
                        $flexibleErrors = $this->validateFlexible($field, $value, $skipRequired);
                        if (!empty($flexibleErrors)) {
                            $errors[$fieldName] = $flexibleErrors;
                        }
                        break;

                    case 'Variations':
                        $variationsErrors = $this->validateVariations($field, $value, $skipRequired);
                        if (!empty($variationsErrors)) {
                            $errors[$fieldName] = array();
                            foreach ($variationsErrors as $errorKey => $errorValue){
                                $errors[$fieldName][] = $errorKey . ': ' . ( is_array($errorValue) ? json_encode($errorValue) : $errorValue );
                            }
                        }
                        break;

                    case 'ColorPicker':
                        if (!$this->validateHexColor($value)) {
                            $errors[$fieldName] = [__('Invalid color format. Use hex format (#RRGGBB)')];
                        }
                        break;

                    case 'Point':
                        if (!$this->validatePoint($value)) {
                            $errors[$fieldName] = [__('%1% has invalid coordinates (lat: -90 to 90, lng: -180 to 180)', $label)];
                        }
                        break;

                    case 'Email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$fieldName] = [__('%1% must be a valid email address', $label)];
                        }
                        break;

                    case 'URL':
                    case 'OEmbed':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            $errors[$fieldName] = [__('%1% must be a valid URL', $label)];
                        }
                        break;

                    case 'Number':
                    case 'Integer':
                    case 'Float':
                    case 'Decimal':
                        if (!is_numeric($value)) {
                            $errors[$fieldName] = [__('%1% must be a number', $label)];
                        }
                        break;
                }
            }
        }

        return $errors;
    }


    /**
     * Build validation rules from field definitions
     * 
     * LOGIC MỚI:
     * - skipRequired = true (draft create): Wrap TẤT CẢ rules bằng optional() → Chỉ validate NẾU có nhập
     * - skipRequired = false (edit/active): Validate bình thường
     * 
     * @param array $fields Field definitions
     * @param bool $skipRequired Skip required validation (true = draft create, false = edit)
     * @return array Validation rules
     */
    public function buildRules($fields, $skipRequired = false)
    {
        $rules = [];

        if (empty($fields)) {
            return $rules;
        }

        foreach ($fields as $field) {
            $fieldName = $field['field_name'] ?? '';
            $fieldType = $field['type'] ?? 'Text';
            $isRequired = !empty($field['required']);

            if (empty($fieldName)) {
                continue;
            }

            $rules[$fieldName] = [
                'rules'    => [],
                'messages' => []
            ];

            // RULE 1: Required validation
            // CHỈ add khi KHÔNG skip required (edit mode or active post)
            if ($isRequired && !$skipRequired) {
                $rules[$fieldName]['rules'][]    = Validate::notEmpty();
                $rules[$fieldName]['messages'][] = __('Field is required');
            }

            // RULE 2: Length validation (for text fields)
            if (!empty($field['min']) || !empty($field['max'])) {
                $min = $field['min'] ?? 1;
                $max = $field['max'] ?? 255;

                $lengthRule = Validate::length($min, $max);

                // ✅ Wrap với optional NẾU:
                // - Field không required, HOẶC
                // - Draft mode (skipRequired = true)
                if (!$isRequired || $skipRequired) {
                    $lengthRule = Validate::optional($lengthRule);
                }

                $rules[$fieldName]['rules'][]    = $lengthRule;
                $rules[$fieldName]['messages'][] = __('Length must be between %1% and %2%', $min, $max);
            }

            // RULE 3: Type-specific validation
            switch ($fieldType) {
                case 'Number':
                case 'Integer':
                case 'Float':
                case 'Decimal':
                    $numericRule = Validate::numericVal();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $numericRule = Validate::optional($numericRule);
                    }

                    $rules[$fieldName]['rules'][]    = $numericRule;
                    $rules[$fieldName]['messages'][] = __('Must be a number');

                    // Min/Max value validation for numbers
                    if (isset($field['min']) || isset($field['max'])) {
                        $minVal = isset($field['min']) ? (float)$field['min'] : null;
                        $maxVal = isset($field['max']) ? (float)$field['max'] : null;

                        if ($minVal !== null) {
                            $minRule = Validate::min($minVal, true);
                            // ✅ Always wrap optional cho min/max (chỉ validate khi có giá trị)
                            $rules[$fieldName]['rules'][] = Validate::optional($minRule);
                            $rules[$fieldName]['messages'][] = __('Value must be greater than %1%', $minVal);
                        }
                        if ($maxVal !== null) {
                            $maxRule = Validate::max($maxVal, true);
                            $rules[$fieldName]['rules'][] = Validate::optional($maxRule);
                            $rules[$fieldName]['messages'][] = __('Value must be less than %1%', $maxVal);
                        }
                    }
                    break;

                case 'Email':
                    $emailRule = Validate::email();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $emailRule = Validate::optional($emailRule);
                    }

                    $rules[$fieldName]['rules'][]    = $emailRule;
                    $rules[$fieldName]['messages'][] = __('Must be a valid email');
                    break;

                case 'Password':
                    // Password: min length (security), no max
                    $minLength = $field['min'] ?? 6;

                    if ($isRequired && !$skipRequired) {
                        $rules[$fieldName]['rules'][] = Validate::length($minLength, 255);
                        $rules[$fieldName]['messages'][] = __('Password must be at least %1% characters', $minLength);
                    } elseif ($skipRequired) {
                        // ✅ Draft mode: Optional password validation
                        $rules[$fieldName]['rules'][] = Validate::optional(Validate::length($minLength, 255));
                        $rules[$fieldName]['messages'][] = __('Password must be at least %1% characters', $minLength);
                    }
                    break;

                case 'URL':
                case 'OEmbed':
                    $urlRule = Validate::url();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $urlRule = Validate::optional($urlRule);
                    }

                    $rules[$fieldName]['rules'][]    = $urlRule;
                    $rules[$fieldName]['messages'][] = __('Must be a valid URL');
                    break;

                case 'Date':
                    $dateRule = Validate::date();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $dateRule = Validate::optional($dateRule);
                    }

                    $rules[$fieldName]['rules'][]    = $dateRule;
                    $rules[$fieldName]['messages'][] = __('Must be a valid date');
                    break;

                case 'DateTime':
                    $dateRule = Validate::datetime();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $dateRule = Validate::optional($dateRule);
                    }

                    $rules[$fieldName]['rules'][]    = $dateRule;
                    $rules[$fieldName]['messages'][] = __('Must be a valid datetime');
                    break;

                case 'ColorPicker':
                    // ColorPicker: hex format validated in Layer 4 (validate() method)
                    // No library validation needed
                    break;

                case 'Textarea':
                case 'WYSIWYG':
                case 'Richtext':
                    // Text fields - length already handled above in RULE 2
                    // No additional type validation needed
                    break;

                case 'Boolean':
                case 'Checkbox':
                    // Boolean: always 0 or 1, no validation needed
                    // Processing handles conversion
                    break;

                case 'Radio':
                case 'Select':
                    // Radio/Select: validate against options if provided
                    if (!empty($field['options']) && is_array($field['options'])) {
                        $allowedValues = [];
                        foreach ($field['options'] as $option) {
                            if (is_array($option) && isset($option['value'])) {
                                $allowedValues[] = $option['value'];
                            } elseif (is_scalar($option)) {
                                $allowedValues[] = $option;
                            }
                        }

                        if (!empty($allowedValues)) {
                            $inArrayRule = Validate::inArray($allowedValues);

                            // ✅ Wrap optional NẾU: not required HOẶC draft mode
                            if (!$isRequired || $skipRequired) {
                                $inArrayRule = Validate::optional($inArrayRule);
                            }

                            $rules[$fieldName]['rules'][] = $inArrayRule;
                            $rules[$fieldName]['messages'][] = __('Invalid option selected');
                        }
                    }
                    break;

                case 'File':
                case 'Image':
                case 'Gallery':
                    // File validation: handled by upload helper
                    // Skip validation here (file upload validated separately)
                    break;

                case 'User':
                    // User: validate numeric ID
                    $numericRule = Validate::numericVal();

                    // ✅ Wrap optional NẾU: not required HOẶC draft mode
                    if (!$isRequired || $skipRequired) {
                        $numericRule = Validate::optional($numericRule);
                    }

                    $rules[$fieldName]['rules'][] = $numericRule;
                    $rules[$fieldName]['messages'][] = __('User ID must be a number');
                    break;

                case 'Point':
                    // Point: coordinates validated in Layer 4 (validate() method)
                    // No library validation needed
                    break;

                case 'Repeater':
                    // Repeater: min/max items validated in Layer 4 (validateRepeater)
                    // No library validation needed
                    break;

                case 'Flexible':
                    // Flexible: min/max layouts validated in Layer 4 (validateFlexible)
                    // No library validation needed
                    break;

                case 'Variations':
                    // Variations: min/max items validated in Layer 4 (validateVariations)
                    // No library validation needed
                    break;

                case 'Reference':
                    // Reference: relationship validated in RelationshipService
                    // No validation needed here
                    break;

                case 'Text':
                default:
                    // Text fields already handled by length validation above
                    break;
            }
            // RULE 4: Slug must be lowercase
            if ($fieldName === 'slug') {
                $slugRule = Validate::lowercase();

                // ✅ Wrap optional NẾU: not required HOẶC draft mode
                if (!$isRequired || $skipRequired) {
                    $slugRule = Validate::optional($slugRule);
                }

                $rules[$fieldName]['rules'][]    = $slugRule;
                $rules[$fieldName]['messages'][] = __('Must be lowercase');
            }
        }
        return $rules;
    }

    /**
     * Validate theo rules (dùng thư viện Validate)
     * 
     * @param array $data Post data
     * @param array $rules Validation rules từ buildRules()
     * @return array Errors (empty array if valid)
     */
    protected function validateRules($data, $rules)
    {
        if (empty($rules)) {
            return [];
        }

        $validator = new Validate();

        if (!$validator->check($data, $rules)) {
            return $validator->getErrors();
        }

        return [];
    }

    /**
     * Validate status value
     * 
     * @param string $status Status to validate
     * @param array $allowedStatuses Allowed status values
     * @return string Valid status (returns default if invalid)
     */
    public function validateStatus($status, $allowedStatuses = ['active', 'pending', 'inactive', 'schedule', 'draft', 'suspended', 'deleted'])
    {
        if (in_array($status, $allowedStatuses)) {
            return $status;
        }

        return 'active'; // Default fallback
    }

    /**
     * Auto-adjust status based on created_at date
     * 
     * @param string $status Current status
     * @param string $createdAt Created at timestamp
     * @return string Adjusted status
     */
    public function autoAdjustStatus($status, $createdAt)
    {
        $now = date('Y-m-d H:i:s');

        // If status is 'active' but created_at is in future → change to 'schedule'
        if ($status === 'active' && $createdAt > $now) {
            return 'schedule';
        }

        // If status is 'schedule' but created_at is in past → change to 'active'
        if ($status === 'schedule' && $createdAt < $now) {
            return 'active';
        }

        return $status;
    }

    /**
     * Validate and normalize created_at timestamp
     * 
     * @param mixed $createdAt Created at value
     * @return string Normalized datetime string
     */
    public function normalizeCreatedAt($createdAt)
    {
        if (empty($createdAt)) {
            return date('Y-m-d H:i:s');
        }

        // If numeric (unix timestamp)
        if (is_numeric($createdAt)) {
            return date('Y-m-d H:i:s', (int)$createdAt);
        }

        // If string (try to parse)
        if (is_string($createdAt)) {
            $timestamp = strtotime($createdAt);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }

        // Fallback to now
        return date('Y-m-d H:i:s');
    }

    /**
     * Validate Point field coordinates
     * 
     * @param mixed $value Point value
     * @return bool True if valid, false otherwise
     */
    public function validatePoint($value)
    {
        if (is_string($value)) {
            $value = _json_decode($value, true);
        }
        if (!is_array($value) || empty($value)) {
            return false;
        }

        if (!isset($value['lat']) || !isset($value['lng'])) {
            return false;
        }

        $lat = (float)$value['lat'];
        $lng = (float)$value['lng'];

        // Validate ranges: Latitude -90 to 90, Longitude -180 to 180
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return false;
        }

        // Check for NaN or Infinity
        if (is_nan($lat) || is_nan($lng) || is_infinite($lat) || is_infinite($lng)) {
            return false;
        }

        return true;
    }

    /**
     * Validate JSON field
     * 
     * @param mixed $value JSON value
     * @return bool True if valid, false otherwise
     */
    public function validateJson($value)
    {
        if (is_array($value)) {
            return true; // Already decoded
        }

        if (!is_string($value)) {
            return false;
        }

        // Check if it looks like JSON
        if (substr($value, 0, 1) !== '{' && substr($value, 0, 1) !== '[') {
            return false;
        }

        // Try to decode
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate Repeater field (has child fields)
     * 
     * @param array $field Repeater field definition
     * @param mixed $value Repeater data (array of items)
     * @param bool $skipRequired Skip required validation
     * @return array Errors
     */
    public function validateRepeater($field, $value, $skipRequired = false)
    {
        $errors = [];

        // Value must be array or JSON string
        if (is_string($value)) {
            $value = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['Invalid JSON structure for Repeater field'];
            }
        }

        if (!is_array($value)) {
            return ['Repeater field must be an array'];
        }

        // Check min/max items
        $minItems = $field['min_items'] ?? 0;
        $maxItems = $field['max_items'] ?? 999;
        $count = count($value);

        if ($count < $minItems) {
            return [__('Must have at least %1% items', $minItems)];
        }

        if ($count > $maxItems) {
            return [__('Cannot have more than %1% items', $maxItems)];
        }

        // Validate child fields if defined
        $childFields = $field['fields'] ?? [];
        if (!empty($childFields)) {
            foreach ($value as $index => $item) {
                // ✅ RECURSIVE: Build rules and validate
                $childRules = $this->buildRules($childFields, $skipRequired);
                $childErrors = $this->validateRules($item, $childRules);

                if (!empty($childErrors)) {
                    foreach ($childErrors as $childField => $childError) {
                        $errors["Item #{$index} - {$childField}"] = $childError;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate Flexible field (has layouts, each layout has child fields)
     * 
     * @param array $field Flexible field definition
     * @param mixed $value Flexible data (array of layouts)
     * @param bool $skipRequired Skip required validation
     * @return array Errors
     */
    public function validateFlexible($field, $value, $skipRequired = false)
    {
        $errors = [];

        // Value must be array or JSON string
        if (is_string($value)) {
            $value = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['Invalid JSON structure for Flexible field'];
            }
        }

        if (!is_array($value)) {
            return ['Flexible field must be an array'];
        }

        // Check min/max layouts
        $minLayouts = $field['min_layouts'] ?? 0;
        $maxLayouts = $field['max_layouts'] ?? 999;
        $count = count($value);

        if ($count < $minLayouts) {
            return [__('Must have at least %1% layouts', $minLayouts)];
        }

        if ($count > $maxLayouts) {
            return [__('Cannot have more than %1% layouts', $maxLayouts)];
        }

        // Validate each layout
        $layouts = $field['layouts'] ?? [];
        foreach ($value as $index => $item) {
            $layoutName = $item['acf_fc_layout'] ?? '';
            if (empty($layoutName)) {
                $errors["Layout #{$index}"] = __('Layout %1%: name is required', $index);
                continue;
            }

            // Find layout definition
            $layoutDef = null;
            foreach ($layouts as $layout) {
                if ($layout['name'] === $layoutName) {
                    $layoutDef = $layout;
                    break;
                }
            }

            if (empty($layoutDef)) {
                $errors["Layout #{$index}"] = ["Unknown layout: %1%", $layoutName];
                continue;
            }

            // Validate layout fields
            $layoutFields = $layoutDef['fields'] ?? [];
            if (!empty($layoutFields)) {
                $layoutData = $item['data'] ?? [];
                // ✅ RECURSIVE: Build rules and validate
                $layoutRules = $this->buildRules($layoutFields, $skipRequired);
                $layoutErrors = $this->validateRules($layoutData, $layoutRules);

                if (!empty($layoutErrors)) {
                    foreach ($layoutErrors as $layoutField => $layoutError) {
                        $errors["Layout #{$index} ({$layoutName}) - {$layoutField}"] = $layoutError;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate Variations field (has items with child fields)
     * 
     * @param array $field Variations field definition
     * @param mixed $value Variations data (array of items)
     * @param bool $skipRequired Skip required validation
     * @return array Errors
     */
    public function validateVariations($field, $value, $skipRequired = false)
    {
        $errors = [];

        // Value must be array or JSON string
        if (is_string($value)) {
            $value = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['Invalid JSON structure for Variations field'];
            }
        }

        if (!is_array($value)) {
            return ['Variations field must be an array'];
        }

        // ✅ CHỈ validate 'variations' array, bỏ qua 'attributes'
        $variations = $value['variations'] ?? [];
        
        if (!is_array($variations)) {
            return ['Variations field must contain a "variations" array'];
        }

        // Check min/max items (chỉ count variations)
        $minItems = $field['min_items'] ?? 0;
        $maxItems = $field['max_items'] ?? 999;
        $count = count($variations);

        if ($count < $minItems) {
            return [__('Must have at least %1% variation items', $minItems)];
        }

        if ($count > $maxItems) {
            return [__('Cannot have more than %1% variation items', $maxItems)];
        }

        // ✅ Validate child fields if defined
        $childFields = $field['fields'] ?? [];
        if (!empty($childFields)) {
            foreach ($variations as $index => $item) {
                // ✅ Validate 'value' object của mỗi variation item
                $itemValue = $item['value'] ?? [];
                
                if (!is_array($itemValue)) {
                    $errors["Variation #{$index}"] = ['Invalid variation value structure'];
                    continue;
                }

                // ✅ RECURSIVE: Build rules and validate
                $childRules = $this->buildRules($childFields, $skipRequired);
                $childErrors = $this->validateRules($itemValue, $childRules);

                if (!empty($childErrors)) {
                    foreach ($childErrors as $childField => $childError) {
                        $errors["Variation #{$index} - {$childField}"] = $childError;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate ColorPicker hex format
     * 
     * @param string $value Color value
     * @return bool True if valid hex
     */
    public function validateHexColor($value)
    {
        if (empty($value)) {
            return true; // Empty is OK if not required
        }

        // Validate hex format: #RRGGBB or RRGGBB
        $color = trim($value);

        // Remove # if present
        if (substr($color, 0, 1) === '#') {
            $color = substr($color, 1);
        }

        // Check if valid hex (6 characters)
        return preg_match('/^[0-9A-Fa-f]{6}$/', $color) === 1;
    }
}
