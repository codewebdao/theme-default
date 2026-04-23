<?php

namespace App\Services\Settings\Traits;

/**
 * Media settings: Image Sizes, Optimization, Watermark, Media Display.
 * Lưu trữ: TOÀN TRANG (all).
 *
 * @package App\Services\Settings\Traits
 */
trait MediaSettingsTrait
{
    public function getMediaSettingsGroup(): array
    {
        return [
            'id' => 'media',
            'icon' => 'image',
            'title' => __('Media & Resize'),
            'description' => __('Image processing, sizes, WebP conversion and watermark'),
            'detail' => __('Image sizes, optimization, watermark and media display options'),
            'url' => admin_url('settings/media'),
            'tabs' => [
                ['id' => 'image_sizes', 'label' => __('Image Sizes')],
                ['id' => 'optimization', 'label' => __('Image Optimization')],
                ['id' => 'watermark', 'label' => __('Watermark')],
                ['id' => 'media_display', 'label' => __('Media Display')],
            ],
            'form_options' => ['app_lang' => ['all']],
        ];
    }

    /**
     * @return array{tabs: array, fields: array}
     */
    public function getMediaSettings(): array
    {
        $tabs = [
            forms_tab('image_sizes', __('Image Sizes'), ['icon' => 'maximize']),
            forms_tab('optimization', __('Image Optimization'), ['icon' => 'package']),
            forms_tab('watermark', __('Watermark'), ['icon' => 'droplet']),
            forms_tab('media_display', __('Media Display'), ['icon' => 'image']),
        ];

        $sizes = $this->getDefaultImageSizes();
        $sizeOptions = [];
        foreach ($sizes as $s) {
            $name = $s['name'] ?? $s['size_key'] ?? 'thumb';
            $sizeOptions[] = ['value' => $name, 'label' => $name];
        }
        if (empty($sizeOptions)) {
            $sizeOptions = [['value' => 'thumbnail', 'label' => 'thumbnail'], ['value' => 'medium', 'label' => 'medium'], ['value' => 'large', 'label' => 'large']];
        }

        $fields = [
            // TAB 1: Image Sizes (JSON repeater representation)
            forms_field('textarea', 'image_sizes', __('Image Sizes (JSON)'), [
                'description' => __('REPEATER: size_key, width, height, crop, quality, webp, enabled. One JSON array.'),
                'tab' => 'image_sizes', 'rows' => 12,
                'default_value' => json_encode($sizes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'placeholder' => '[{"size_key":"thumbnail","width":150,"height":150,"crop":true}]', 'width_value' => 100,
            ]),
            // TAB 2: Image Optimization – 2–3 col
            forms_field('select', 'media_compression_level', __('Compression level'), [
                'tab' => 'optimization',
                'options' => [['value' => 'low', 'label' => __('Low')], ['value' => 'medium', 'label' => __('Medium')], ['value' => 'high', 'label' => __('High')]],
                'default_value' => 'medium', 'width_value' => 33,
            ]),
            forms_field('number', 'media_jpeg_quality', __('JPEG quality (%)'), [
                'tab' => 'optimization', 'min' => 0, 'max' => 100, 'default_value' => 82, 'width_value' => 33,
            ]),
            forms_field('number', 'media_webp_quality', __('WebP quality (%)'), [
                'tab' => 'optimization', 'min' => 0, 'max' => 100, 'default_value' => 80, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_png_optimize', __('PNG optimization'), [
                'tab' => 'optimization', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_strip_exif', __('Strip EXIF'), [
                'tab' => 'optimization', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_webp_enabled', __('Auto convert WebP'), [
                'tab' => 'optimization', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('text', 'media_allowed_types', __('Allowed mime types'), [
                'tab' => 'optimization', 'default_value' => 'jpg,jpeg,png,webp,gif', 'placeholder' => 'jpg, png, webp', 'width_value' => 50,
            ]),
            forms_field('number', 'media_max_upload', __('Max upload size (MB)'), [
                'tab' => 'optimization', 'min' => 1, 'max' => 100, 'default_value' => 10, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_keep_original', __('Keep original'), [
                'tab' => 'optimization', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_webp_on_upload', __('Generate WebP on upload'), [
                'tab' => 'optimization', 'default_value' => true, 'width_value' => 33,
            ]),
            // TAB 3: Watermark
            forms_field('boolean', 'media_watermark_enabled', __('Enable watermark'), [
                'tab' => 'watermark', 'default_value' => false, 'width_value' => 50,
            ]),
            forms_field('image', 'media_watermark_image', __('Watermark image'), [
                'tab' => 'watermark', 'width_value' => 50,
            ]),
            forms_field('select', 'media_watermark_position', __('Position'), [
                'tab' => 'watermark',
                'options' => [
                    ['value' => 'top-left', 'label' => __('Top Left')], ['value' => 'top-center', 'label' => __('Top Center')], ['value' => 'top-right', 'label' => __('Top Right')],
                    ['value' => 'center-left', 'label' => __('Center Left')], ['value' => 'center', 'label' => __('Center')], ['value' => 'center-right', 'label' => __('Center Right')],
                    ['value' => 'bottom-left', 'label' => __('Bottom Left')], ['value' => 'bottom-center', 'label' => __('Bottom Center')], ['value' => 'bottom-right', 'label' => __('Bottom Right')],
                ],
                'default_value' => 'bottom-right', 'width_value' => 33,
            ]),
            forms_field('number', 'media_watermark_opacity', __('Opacity (%)'), [
                'tab' => 'watermark', 'min' => 10, 'max' => 100, 'default_value' => 70, 'width_value' => 33,
            ]),
            forms_field('number', 'media_watermark_scale', __('Scale (%)'), [
                'tab' => 'watermark', 'min' => 1, 'max' => 100, 'default_value' => 20, 'width_value' => 33,
            ]),
            forms_field('text', 'media_watermark_sizes', __('Apply to sizes'), [
                'tab' => 'watermark', 'placeholder' => 'thumbnail, medium, large', 'width_value' => 50,
            ]),
            forms_field('number', 'media_watermark_margin', __('Margin (px)'), [
                'tab' => 'watermark', 'min' => 0, 'default_value' => 10, 'width_value' => 33,
            ]),
            // TAB 4: Media Display
            forms_field('boolean', 'media_lazy_load', __('Lazy load images'), [
                'tab' => 'media_display', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('select', 'media_placeholder', __('Placeholder loading'), [
                'tab' => 'media_display',
                'options' => [['value' => 'none', 'label' => 'None'], ['value' => 'blur', 'label' => 'Blur'], ['value' => 'color', 'label' => 'Color']],
                'default_value' => 'blur', 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_auto_dimension', __('Auto set width & height (CLS fix)'), [
                'tab' => 'media_display', 'default_value' => true, 'width_value' => 33,
            ]),
            forms_field('text', 'media_lazy_include', __('Lazy load include'), [
                'tab' => 'media_display', 'placeholder' => 'content, gallery, featured', 'width_value' => 50,
            ]),
            forms_field('textarea', 'media_lazy_exclude', __('Lazy load exclude'), [
                'tab' => 'media_display', 'rows' => 2, 'width_value' => 50,
            ]),
            forms_field('boolean', 'media_require_alt', __('Require alt text'), [
                'tab' => 'media_display', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_auto_alt', __('Auto apply alt'), [
                'tab' => 'media_display', 'default_value' => false, 'width_value' => 33,
            ]),
            forms_field('boolean', 'media_auto_title', __('Auto apply title'), [
                'tab' => 'media_display', 'default_value' => false, 'width_value' => 33,
            ]),
        ];

        return ['tabs' => $tabs, 'fields' => $fields];
    }

    /**
     * @return array<int, array{name?: string, size_key?: string, width: int, height: int, crop?: bool, quality?: int, webp?: bool, enabled?: bool}>
     */
    protected function getDefaultImageSizes(): array
    {
        return [
            ['name' => 'thumbnail', 'size_key' => 'thumbnail', 'width' => 150, 'height' => 150, 'crop' => true, 'quality' => 82, 'webp' => true, 'enabled' => true],
            ['name' => 'medium', 'size_key' => 'medium', 'width' => 300, 'height' => 300, 'crop' => false, 'quality' => 82, 'webp' => true, 'enabled' => true],
            ['name' => 'large', 'size_key' => 'large', 'width' => 1024, 'height' => 768, 'crop' => false, 'quality' => 82, 'webp' => true, 'enabled' => true],
        ];
    }
}
