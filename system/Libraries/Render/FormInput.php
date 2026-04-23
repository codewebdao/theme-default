<?php

namespace System\Libraries\Render;

/**
 * Render field partials từ theme `common/Input/{type}.php` (admin / form builder).
 * Không phụ thuộc file legacy `system/Libraries/Render.php`.
 */
class FormInput
{
    /**
     * @param array $field Field to render
     * @param mixed $field_value Field value (can be from database or request)
     * @param string|null $error_message Error message if any
     * @param string|null $prefix Prefix for nested fields (e.g., "parent[0]")
     * @param int|null $index Index for array fields
     * @return string Input HTML string
     */
    public static function render($field, $field_value = null, $error_message = null, $prefix = null, $index = null)
    {
        $html = '';
        $field_type = strtolower($field['type'] ?? 'text');
        $field_type = strtolower(preg_replace('/[^a-z0-9]+/', '_', $field_type));
        $inputPath = APP_THEME_PATH . 'common/Input/' . $field_type . '.php';
        if (!file_exists($inputPath)) {
            throw new \Exception("Input type '{$field_type}' does not exist at path '{$inputPath}'.");
        }
        if (!isset($field['field_name']) && isset($field['name'])) {
            $field['field_name'] = $field['name'];
        }

        $field_name = $field['field_name'] ?? '';
        if ($prefix && $index !== null) {
            $field_name = $prefix . '[' . $index . '][' . $field_name . ']';
        } elseif ($prefix) {
            $field_name = $prefix . '[' . $field_name . ']';
        }

        $inputData = [
            'id' => 'field_' . (isset($field['id']) ? xss_clean($field['id']) : uniqid()),
            'type' => $field_type,
            'label' => isset($field['label']) ? xss_clean($field['label']) : '',
            'name' => $field_name,
            'field_name' => $field['field_name'] ?? '',
            'default_value' => $field['default_value'] ?? '',
            'value' => isset($field_value) ? $field_value : ($field['default_value'] ?? ''),
            'description' => isset($field['description']) ? xss_clean($field['description']) : '',
            'autofill'  => isset($field['autofill']) ? xss_clean($field['autofill']) : null,
            'autofill_type' =>  isset($field['autofill_type']) ? xss_clean($field['autofill_type']) : 'match',
            'required' => isset($field['required']) && $field['required'],
            'visibility' => isset($field['visibility']) && !$field['visibility'] ? false : true,
            'css_class' => isset($field['css_class']) ? xss_clean($field['css_class']) : '',
            'placeholder' => isset($field['placeholder']) ? xss_clean($field['placeholder']) : '',
            'order' => isset($field['order']) ? (int) $field['order'] : 0,
            'min' => isset($field['min']) ? (int) $field['min'] : null,
            'max' => isset($field['max']) ? (int) $field['max'] : null,
            'width_value' => isset($field['width_value']) ? (int) $field['width_value'] : 100,
            'width_unit' => isset($field['width_unit']) ? $field['width_unit'] : '%',
            'position' => isset($field['position']) ? $field['position'] : 'left',
            'options' => $field['options'] ?? [],
            'rows' => isset($field['rows']) ? (int) $field['rows'] : 3,
            'allow_types' => $field['allow_types'] ?? [],
            'max_file_size' => isset($field['max_file_size']) ? (float) $field['max_file_size'] : null,
            'multiple' => isset($field['multiple']) && $field['multiple'],
            'multiple_server' => isset($field['multiple_server']) && $field['multiple_server'],
            'servers' => isset($field['servers']) ? $field['servers'] : array(),
            'reference' => isset($field['reference']) ? $field['reference'] : null,
            'error_message' => isset($error_message) ? $error_message : '',
            'data' => !empty($field['data']) ? $field['data'] : [],
            'prefix' => $prefix,
            'index' => $index,
        ];
        if (isset($field['step']) && $field['step'] > 0) {
            $inputData['step'] = $field['step'];
        }
        if (isset($field['layouts']) && count($field['layouts']) > 0) {
            $inputData['layouts'] = $field['layouts'];
            if (isset($field['button_label'])) {
                $inputData['button_label'] = $field['button_label'];
            }
            if (isset($field['min_layouts'])) {
                $inputData['min_layouts'] = $field['min_layouts'];
            }
            if (isset($field['max_layouts'])) {
                $inputData['max_layouts'] = $field['max_layouts'];
            }
        }

        $uploadMaxFilesize = _bytes(ini_get('upload_max_filesize'));
        $postMaxSize = _bytes(ini_get('post_max_size'));
        $maxUploadSize = min($uploadMaxFilesize, $postMaxSize);
        if ($inputData['max_file_size'] === null || $inputData['max_file_size'] * 1024 * 1024 > $maxUploadSize) {
            $inputData['max_file_size'] = $maxUploadSize;
            $inputData['max_file_size'] = ceil($inputData['max_file_size'] / (1024 * 1024));
        }

        if (strtolower($field['type']) == 'image') {
            $inputData['autocrop'] = $field['autocrop'] ?? 0;
            $inputData['watermark'] = $field['watermark'] ?? 0;
            $inputData['watermark_img'] = $field['watermark_img'] ?? '';
            $inputData['resizes'] = $field['resizes'] ?? [];
        }

        if ($field_type == 'repeater') {
            $inputData['fields'] = $field['fields'] ?? [];
            $inputData['level'] = empty($field['level']) ? 1 : $field['level'];
        }

        if ($field_type == 'flexible') {
            $inputData['layouts'] = $field['layouts'] ?? [];
            $inputData['button_label'] = $field['button_label'] ?? 'Add Layout';
            $inputData['min_layouts'] = $field['min_layouts'] ?? null;
            $inputData['max_layouts'] = $field['max_layouts'] ?? null;
        }

        $inputData['is_repeater'] = $field['is_repeater'] ?? false;

        ob_start();
        extract($inputData);
        require $inputPath;
        $html .= ob_get_clean();

        return $html;
    }
}
