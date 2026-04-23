<?php
if (!defined('PATH_ROOT')) {
    exit('No direct access allowed.');
}

// Function cũ, hạn chế dùng, nên dùng function mới _imglazy
// render img tag (lazy load).... title, alt, src, class, style
if (!function_exists('_img')) {
    function _img($src, $title = '', $lazy = true, $class = '', $style = '', $width = '', $height = '', $id = '')
    {
        // If no image source then return empty string
        if (empty($src)) {
            return '';
        }

        // Create attributes for alt and title, escape special characters
        $attr_alt   = !empty($title) ? ' alt="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_title = !empty($title) ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_id    = !empty($id)  ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '';

        // Process class, style, width, height attributes (escape if needed)
        $attr_class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_style = !empty($style) ? ' style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_width = !empty($width) ? ' width="' . htmlspecialchars($width, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_height = !empty($height) ? ' height="' . htmlspecialchars($height, ENT_QUOTES, 'UTF-8') . '"' : '';

        if ($lazy) {
            // If lazy load is enabled, add "lazyload" class
            if (!empty($class)) {
                $class .= ' lazyload';
            } else {
                $class = 'lazyload';
            }
            $attr_class = ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';

            // Create img tag with data-src instead of src for lazy load
            $img_tag = '<img data-src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
                . $attr_alt . $attr_title . $attr_class . $attr_style . $attr_width . $attr_height . $attr_id . '>';
            // Create fallback with <noscript> tag so browsers without JS support can still display images
            $noscript_tag = '<noscript><img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
                . $attr_alt . $attr_title . $attr_class . $attr_style . $attr_width . $attr_height . $attr_id . '></noscript>';
            return $img_tag . $noscript_tag;
        } else {
            // If not using lazy load, create normal img tag with src
            return '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '"'
                . $attr_alt . $attr_title . $attr_class . $attr_style . $attr_width . $attr_height . $attr_id . '>';
        }
    }
}

if (!function_exists('_imglazy')) {
    /**
     * Generate responsive <picture> element with WebP support (v2)
     * 
     * @param array|object|string $imageData Image data with structure:
     *   [
     *     'id' => int,
     *     'name' => string,
     *     'path' => string,  // e.g., "2025/11/25/owxzv-gk.jpg"
     *     'sizes' => [
     *       ['name' => 'thumbnail', 'width' => 100, 'height' => 100],
     *       ['name' => 'medium', 'width' => 600, 'height' => 337],
     *       ...
     *     ],
     *     'webp' => int|bool  // 1 or true if WebP version exists
     *   ]
     * @param array $attributes Optional attributes:
     *   - 'title' => string: Image title
     *   - 'alt' => string: Alt text (defaults to title if not provided)
     *   - 'style' => string: Inline CSS styles
     *   - 'class' => string: CSS classes
     *   - 'loading' => string: Loading attribute (default: 'lazy')
     *   - 'decoding' => string: Decoding attribute (default: 'async')
     *   - 'sizes' => string|array: Sizes attribute for srcset
     *     * String format: '(max-width: 600px) 300px, (max-width: 1024px) 600px, ...'
     *     * Array format: ['mobile' => 'thumbnail', 'tablet' => 'medium', 'desktop' => 'large', 'large' => 'xlarge']
     *     * Breakpoints: mobile (600px), tablet (1024px), desktop (1440px), large (1920px)
     *   - 'id' => string: HTML id attribute
     *   - 'data-*' => string: Any data attributes
     *   - Other HTML attributes
     * @param bool $compress Compress HTML by removing extra newlines & spaces (default: false)
     * @return string HTML <picture> element
     * 
     * EXAMPLE:
     * $imageData = [
     *   'id' => 709,
     *   'name' => 'owxzv-gk.jpg',
     *   'path' => '2025/11/25/owxzv-gk.jpg',
     *   'sizes' => [
     *     ['name' => 'thumbnail', 'width' => 100, 'height' => 100],
     *     ['name' => 'medium', 'width' => 600, 'height' => 337],
     *     ['name' => 'large', 'width' => 900, 'height' => 506],
     *     ['name' => 'xlarge', 'width' => 1200, 'height' => 675]
     *   ],
     *   'webp' => 1
     * ];
     * 
     * // Using array format (auto-convert to sizes attribute)
     * echo _imglazy($imageData, [
     *   'title' => 'Cover PWM',
     *   'alt' => 'Cover PWM',
     *   'class' => 'responsive-image',
     *   'sizes' => ['mobile' => 'thumbnail', 'tablet' => 'medium', 'desktop' => 'large', 'large' => 'xlarge']
     * ]);
     * 
     * // Using string format (direct sizes attribute)
     * echo _imglazy($imageData, [
     *   'sizes' => '(max-width: 600px) 300px, (max-width: 1024px) 600px, 1200px'
     * ]);
     * 
     * // Compress HTML for production
     * echo _imglazy($imageData, $attributes, 'local', true);
     */
    function _imglazy($imageData, $attributes = [], $compress = true)
    {
        // Convert to array if needed
        if (is_string($imageData)) {
            // Check if it's a URL (external image)
            if (strpos($imageData, '://') !== false || strpos($imageData, '//') === 0) {
                // External URL - return simple img tag
                $attr_alt = !empty($attributes['alt']) ? ' alt="' . htmlspecialchars($attributes['alt'], ENT_QUOTES, 'UTF-8') . '"' : ' alt=""';
                $attr_title = !empty($attributes['title']) ? ' title="' . htmlspecialchars($attributes['title'], ENT_QUOTES, 'UTF-8') . '"' : '';
                $attr_class = !empty($attributes['class']) ? ' class="' . htmlspecialchars($attributes['class'], ENT_QUOTES, 'UTF-8') . '"' : '';
                $attr_loading = ' loading="' . htmlspecialchars($attributes['loading'] ?? 'lazy', ENT_QUOTES, 'UTF-8') . '"';
                
                return '<img src="' . htmlspecialchars($imageData, ENT_QUOTES, 'UTF-8') . '"' 
                    . $attr_alt . $attr_title . $attr_class . $attr_loading . '>';
            }
            
            // JSON string
            $imageData = json_decode($imageData, true);
        }
        if (is_object($imageData)) {
            $imageData = (array)$imageData;
        }

        // Validate required fields
        if (empty($imageData) || empty($imageData['path'])) {
            return '';
        }

        $path = $imageData['path'];
        $sizes = $imageData['sizes'] ?? [];
        $hasWebp = !empty($imageData['webp']);

        // Get file extension
        $dotIndex = strrpos($path, '.');
        if ($dotIndex === false) {
            return '';
        }

        $filePathWithoutExt = substr($path, 0, $dotIndex);
        $fileExt = substr($path, $dotIndex);

        // Detect MIME type from extension
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml'
        ];
        $extLower = strtolower(ltrim($fileExt, '.'));
        $mimeType = $mimeTypes[$extLower] ?? 'image/jpeg';

        /**
         * SVG SPECIAL HANDLING
         * SVG không cần sizes/srcset/webp
         * Return simple img tag
         */
        if ($extLower === 'svg') {
            $svgUrl = files_url( $path );
            
            $attr_title = !empty($attributes['title']) ? ' title="' . htmlspecialchars($attributes['title'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $attr_alt = !empty($attributes['alt']) ? ' alt="' . htmlspecialchars($attributes['alt'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $attr_class = !empty($attributes['class']) ? ' class="' . htmlspecialchars($attributes['class'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $attr_style = !empty($attributes['style']) ? ' style="' . htmlspecialchars($attributes['style'], ENT_QUOTES, 'UTF-8') . '"' : '';
            $attr_id = !empty($attributes['id']) ? ' id="' . htmlspecialchars($attributes['id'], ENT_QUOTES, 'UTF-8') . '"' : '';
            
            return '<img src="' . htmlspecialchars($svgUrl, ENT_QUOTES, 'UTF-8') . '"' 
                . $attr_alt . $attr_title . $attr_class . $attr_style . $attr_id . '>';
        }

        // Extract attributes
        $title = $attributes['title'] ?? '';
        $alt = $attributes['alt'] ?? $title;
        $style = $attributes['style'] ?? '';
        $class = $attributes['class'] ?? '';
        $loading = $attributes['loading'] ?? 'lazy';
        $decoding = $attributes['decoding'] ?? 'async';
        $sizesInput = $attributes['sizes'] ?? '100vw';
        $id = $attributes['id'] ?? '';

        // Define breakpoints with media queries
        // Each breakpoint will load only 1 file (the size specified by dev)
        $breakpoints = [
            'mobile' => [
                'media' => '(max-width: 600px)',
                'sizes' => '100vw'
            ],
            'tablet' => [
                'media' => '(min-width: 601px) and (max-width: 1024px)',
                'sizes' => '100vw'
            ],
            'desktop' => [
                'media' => '(min-width: 1025px) and (max-width: 1440px)',
                'sizes' => '100vw'
            ],
            'large' => [
                'media' => '(min-width: 1441px)',
                'sizes' => '100vw'
            ]
        ];

        // Parse sizes input - can be array or string
        // Array format: ['mobile' => 'thumbnail', 'tablet' => 'medium', 'desktop' => 'medium', 'large' => 'xlarge']
        $breakpointSizes = [];
        if (is_array($sizesInput)) {
            $breakpointSizes = $sizesInput;
        }

        // Build other attributes (excluding already processed ones)
        $excludedAttrs = ['title', 'alt', 'style', 'class', 'loading', 'decoding', 'sizes', 'id'];
        $otherAttrs = [];
        foreach ($attributes as $key => $value) {
            if (!in_array($key, $excludedAttrs) && !empty($value)) {
                $otherAttrs[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }

        // Escape HTML attributes
        $attr_title = !empty($title) ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_alt = !empty($alt) ? ' alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_style = !empty($style) ? ' style="' . htmlspecialchars($style, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_class = !empty($class) ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_loading = !empty($loading) ? ' loading="' . htmlspecialchars($loading, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_decoding = !empty($decoding) ? ' decoding="' . htmlspecialchars($decoding, ENT_QUOTES, 'UTF-8') . '"' : '';
        $attr_id = !empty($id) ? ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' : '';

        // Build other attributes string
        $otherAttrsStr = '';
        foreach ($otherAttrs as $key => $value) {
            $otherAttrsStr .= ' ' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '="' . $value . '"';
        }

        // Create a map of size names to size data (for getting width)
        $sizeMap = [];
        foreach ($sizes as $size) {
            $sizeName = $size['name'] ?? '';
            if (!empty($sizeName)) {
                $sizeMap[$sizeName] = $size;
            }
        }

        // Build srcset for a single size (only 1 file per breakpoint)
        $buildSingleSrcset = function ($sizeName, $basePath, $baseExt, $isWebp = false) use ($sizeMap) {
            if (!isset($sizeMap[$sizeName])) {
                return ''; // Size not available
            }

            $size = $sizeMap[$sizeName];
            $width = $size['width'] ?? 0;

            if ($width <= 0) {
                return '';
            }

            if ($isWebp) {
                // WebP format: filename_size.jpg.webp
                $sizePath = $basePath . '_' . $sizeName . $baseExt . '.webp';
            } else {
                // Original format: filename_size.jpg
                $sizePath = $basePath . '_' . $sizeName . $baseExt;
            }

            // Ensure proper path separator
            $url = files_url( $sizePath );
            return $url . ' ' . $width . 'w';
        };

        // Start building HTML
        $html = '<picture>';

        // Build sources for each breakpoint
        // Each breakpoint loads only 1 file (the size specified by dev)
        $breakpointOrder = ['mobile', 'tablet', 'desktop', 'large'];

        // WebP sources first (if available)
        if ($hasWebp) {
            foreach ($breakpointOrder as $bpName) {
                if (!isset($breakpoints[$bpName]) || !isset($breakpointSizes[$bpName])) {
                    continue;
                }

                $sizeName = $breakpointSizes[$bpName];
                $webpSrcset = $buildSingleSrcset($sizeName, $filePathWithoutExt, $fileExt, true);

                if (!empty($webpSrcset)) {
                    $bp = $breakpoints[$bpName];
                    $html .= "\n    <!-- WebP cho " . $bpName . " -->\n";
                    $html .= '    <source type="image/webp"' . "\n";
                    $html .= '            media="' . htmlspecialchars($bp['media'], ENT_QUOTES, 'UTF-8') . '"' . "\n";
                    $html .= '            srcset="' . htmlspecialchars($webpSrcset, ENT_QUOTES, 'UTF-8') . '"' . "\n";
                    $html .= '            sizes="' . htmlspecialchars($bp['sizes'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
                }
            }
        }

        // Original format fallback sources
        foreach ($breakpointOrder as $bpName) {
            if (!isset($breakpoints[$bpName]) || !isset($breakpointSizes[$bpName])) {
                continue;
            }

            $sizeName = $breakpointSizes[$bpName];
            $originalSrcset = $buildSingleSrcset($sizeName, $filePathWithoutExt, $fileExt, false);

            if (!empty($originalSrcset)) {
                $bp = $breakpoints[$bpName];
                $html .= "\n    <!-- " . strtoupper($extLower) . " fallback cho " . $bpName . " -->\n";
                $html .= '    <source type="' . htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8') . '"' . "\n";
                $html .= '            media="' . htmlspecialchars($bp['media'], ENT_QUOTES, 'UTF-8') . '"' . "\n";
                $html .= '            srcset="' . htmlspecialchars($originalSrcset, ENT_QUOTES, 'UTF-8') . '"' . "\n";
                $html .= '            sizes="' . htmlspecialchars($bp['sizes'], ENT_QUOTES, 'UTF-8') . '">' . "\n";
            }
        }

        /**
         * Fallback img tag
         * Priority: thumbnail → medium → first size → original
         */
        $fallbackPath = $path;
        $thumbnailFound = false;
        
        if (!empty($sizes)) {
            // Try to find thumbnail size first (best for mobile)
            foreach ($sizes as $size) {
                if (($size['name'] ?? '') === 'thumbnail') {
                    $fallbackPath = $filePathWithoutExt . '_thumbnail' . $fileExt;
                    $thumbnailFound = true;
                    break;
                }
            }
            
            // If no thumbnail, try medium
            if (!$thumbnailFound) {
                foreach ($sizes as $size) {
                    if (($size['name'] ?? '') === 'medium') {
                        $fallbackPath = $filePathWithoutExt . '_medium' . $fileExt;
                        break;
                    }
                }
            }
            
            // If no medium either, use first size
            if ($fallbackPath === $path && !empty($sizes[0]['name'])) {
                $fallbackPath = $filePathWithoutExt . '_' . $sizes[0]['name'] . $fileExt;
            }
        }
        // else: Use original path (no sizes available)

        $fallbackUrl = files_url( $fallbackPath );

        // Force lazy loading for fallback img (always lazy, even if dev set eager)
        $fallbackLoading = 'lazy';

        // Get width and height from largest size (for SEO and layout stability)
        $imgWidth = null;
        $imgHeight = null;

        // Find largest size from breakpointSizes (sizes passed by dev)
        if (!empty($breakpointSizes) && !empty($sizes)) {
            $largestSizeName = null;
            $maxWidth = 0;

            // Find the largest size name from breakpointSizes
            foreach ($breakpointSizes as $sizeName) {
                foreach ($sizes as $size) {
                    if (($size['name'] ?? '') === $sizeName) {
                        $width = $size['width'] ?? 0;
                        if ($width > $maxWidth) {
                            $maxWidth = $width;
                            $largestSizeName = $sizeName;
                        }
                    }
                }
            }

            // Get width and height from largest size
            if ($largestSizeName) {
                foreach ($sizes as $size) {
                    if (($size['name'] ?? '') === $largestSizeName) {
                        $imgWidth = $size['width'] ?? null;
                        $imgHeight = $size['height'] ?? null;
                        break;
                    }
                }
            }
        }

        // Fallback: if no sizes from breakpointSizes, use largest size from imageData
        if (!$imgWidth && !empty($sizes)) {
            $maxWidth = 0;
            foreach ($sizes as $size) {
                $width = $size['width'] ?? 0;
                if ($width > $maxWidth) {
                    $maxWidth = $width;
                    $imgWidth = $size['width'] ?? null;
                    $imgHeight = $size['height'] ?? null;
                }
            }
        }

        // Build width and height attributes
        $attr_width = '';
        $attr_height = '';
        if ($imgWidth && $imgHeight) {
            $attr_width = ' width="' . htmlspecialchars($imgWidth, ENT_QUOTES, 'UTF-8') . '"';
            $attr_height = ' height="' . htmlspecialchars($imgHeight, ENT_QUOTES, 'UTF-8') . '"';
        }

        // Default style if not provided
        if (empty($style)) {
            $attr_style = ' style=""';
        }

        /**
         * Fallback img tag với error handling và accessibility
         */
        $html .= "\n    <!-- Fallback img -->\n";
        $html .= '    <img src="' . htmlspecialchars($fallbackUrl, ENT_QUOTES, 'UTF-8') . '"' . "\n";
        
        // Width & Height (for layout stability - prevent CLS)
        if (!empty($attr_width)) {
            $html .= '         ' . trim($attr_width) . "\n";
        }
        if (!empty($attr_height)) {
            $html .= '         ' . trim($attr_height) . "\n";
        }
        
        // Alt (required for accessibility)
        if (!empty($attr_alt)) {
            $html .= '         ' . trim($attr_alt) . "\n";
        }
        
        // Title (optional, for tooltip)
        if (!empty($attr_title)) {
            $html .= '         ' . trim($attr_title) . "\n";
        }
        
        // Loading & Decoding (performance optimization)
        $html .= '         loading="' . htmlspecialchars($fallbackLoading, ENT_QUOTES, 'UTF-8') . '"' . "\n";
        $html .= '         decoding="' . htmlspecialchars($decoding, ENT_QUOTES, 'UTF-8') . '"' . "\n";
        if (!empty($attr_style)) {
            $html .= '         ' . trim($attr_style) . "\n";
        }
        if (!empty($attr_class)) {
            $html .= '         ' . trim($attr_class) . "\n";
        }
        if (!empty($attr_id)) {
            $html .= '         ' . trim($attr_id) . "\n";
        }
        
        // Other custom attributes
        if (!empty($otherAttrsStr)) {
            $html .= '         ' . trim($otherAttrsStr) . "\n";
        }
        
        $html .= '>' . "\n";

        $html .= '</picture>';

        // Compress HTML if requested (remove extra newlines & spaces)
        if ($compress) {
            // Remove HTML comments (optional - uncomment if needed)
            // $html = preg_replace('/<!--.*?-->/s', '', $html);

            // Remove extra whitespace between tags (keep one space if needed for readability)
            $html = preg_replace('/>\s+</', '><', $html);

            // Normalize whitespace: replace multiple spaces/tabs/newlines with single space
            $html = preg_replace('/\s+/', ' ', $html);

            // Trim final result
            $html = trim($html);
        }

        return $html;
    }
}

if (!function_exists('_images')) {
    /**
     * Simple image helper - Auto-detect and render
     * 
     * Smart wrapper cho _imglazy() với sensible defaults
     * 
     * @param mixed $image Image data (array/object/string/URL)
     * @param string|array $alt Alt text or attributes array
     * @param array $attributes Additional attributes (if $alt is string)
     * @return string HTML output
     * 
     * @example
     * echo _images($post['feature'], 'Product image');
     * echo _images($post['feature'], ['alt' => 'Product', 'class' => 'thumbnail']);
     * echo _images('https://cdn.example.com/image.jpg', 'External image');
     */
    function _images($image, $alt = '', $attributes = [])
    {
        // Handle parameter overload
        if (is_array($alt)) {
            $attributes = $alt;
            $alt = $attributes['alt'] ?? '';
        } elseif (is_string($alt) && !empty($alt)) {
            $attributes['alt'] = $alt;
        }
        
        // Set sensible defaults
        if (!isset($attributes['loading'])) {
            $attributes['loading'] = 'lazy';
        }
        if (!isset($attributes['decoding'])) {
            $attributes['decoding'] = 'async';
        }
        
        // Default responsive sizes
        if (!isset($attributes['sizes']) && is_array($image) && !empty($image['sizes'])) {
            $attributes['sizes'] = [
                'mobile' => 'thumbnail',
                'tablet' => 'medium',
                'desktop' => 'large',
                'large' => 'xlarge'
            ];
        }
        
        return _imglazy($image, $attributes, true);
    }
}

if (!function_exists('_img_url')) {
    /**
     * Get image URL (without HTML tag)
     * 
     * @param mixed $imageData Image data
     * @param string $size Size name (thumbnail, medium, large, xlarge, full)
     * @param bool $webp Get WebP version if available
     * @return string Image URL
     * 
     * @example
     * $url = _img_url($post['feature'], 'medium');
     * $webpUrl = _img_url($post['feature'], 'large', true);
     * $originalUrl = _img_url($post['feature'], 'full');  // Original image
     */
    function _img_url($imageData, $size = 'medium', $webp = false)
    {
        // Convert to array
        if (is_string($imageData)) {
            // External URL
            if (strpos($imageData, '://') !== false || strpos($imageData, '//') === 0) {
                return $imageData;
            }
            $imageData = json_decode($imageData, true);
        }
        if (is_object($imageData)) {
            $imageData = (array)$imageData;
        }
        
        if (empty($imageData['path'])) {
            return null;
        }
        
        $path = $imageData['path'];
        
        // Get file parts
        $dotIndex = strrpos($path, '.');
        if ($dotIndex === false) {
            return files_url( $path );
        }
        
        $filePathWithoutExt = substr($path, 0, $dotIndex);
        $fileExt = substr($path, $dotIndex);
        
        // Special case: size='full' or 'original' → return original image (always exists)
        if ($size === 'full' || $size === 'original') {
            $sizePath = $path;
            
            // Add WebP extension if requested
            if ($webp && !empty($imageData['webp'])) {
                $sizePath .= '.webp';
            }
            
            return files_url( $sizePath );
        }
        
        // ✅ Check if size exists in sizes array
        $sizes = $imageData['sizes'] ?? [];
        $sizeExists = false;
        if (!empty($sizes) && is_array($sizes)) {
            foreach ($sizes as $sizeDef) {
                $sizeName = $sizeDef['name'] ?? '';
                if ($sizeName === $size) {
                    $sizeExists = true;
                    break;
                }
            }
        }
        
        // If size not in sizes array, return null (size doesn't exist)
        if (!$sizeExists && !empty($sizes)) {
            return null;
        }
        
        // Build sized path: filename_size.ext
        $sizePath = $filePathWithoutExt . '_' . $size . $fileExt;
        
        // Add WebP extension if requested
        if ($webp && !empty($imageData['webp'])) {
            $sizePath .= '.webp';
        }
        
        // ✅ Check if file actually exists on disk
        $fullPath = PATH_UPLOADS . ltrim($sizePath, '/');
        if (!file_exists($fullPath)) {
            return null;
        }
        
        return files_url( $sizePath );
    }
}

