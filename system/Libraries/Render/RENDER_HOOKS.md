# Render Library – Hooks Reference

Danh sách đầy đủ **filter** (`apply_filters`) và **action** (`do_action`) do thư viện Render (và Schema) gọi. Plugin/theme dùng `add_filter` / `add_action` để can thiệp.

**Hàm helper tương ứng (assets, head, template):** xem [RENDER_API_GUIDE.md](RENDER_API_GUIDE.md).

---

## 1. Template (PhpTemplate)

| Hook | Loại | Tham số | Mô tả |
|------|------|---------|--------|
| `render.template_name` | Filter | `$template`, `$namespace`, `$data` | Sửa tên template trước khi locate. |
| `render.template_fallback` | Filter | `$path`, `$template`, `$namespace` | Khi template không tìm thấy: trả về đường dẫn file thay thế (string) hoặc null. Path phải nằm trong allowed bases (PathValidator). |
| `render.template_found` | Action | `$path`, `$template`, `$namespace` | Khi đã tìm thấy file template. |
| `render.data` | Filter | `$data`, `$template`, `$path` | Sửa data trước khi đưa vào template. |
| `render.output` | Filter | `$html`, `$template`, `$path`, `$data` | Sửa HTML sau khi render. |

**Thứ tự:** `template_name` → locate → (nếu không thấy) `template_fallback` → `template_found` → `data` → render → `output`.

---

## 2. Head

| Hook | Loại | Tham số | Mô tả |
|------|------|---------|--------|
| `render.head.set_title` | Filter | `$titleParts`, `$append` | Sửa phần title sau setTitle. |
| `render.head.title_parts` | Filter | `$parts` | Sửa mảng title parts trước khi format (getTitle). |
| `render.head.add_meta` | Filter | `$meta`, `$name`, `$content`, `$type` | Sửa meta tag trước khi thêm (addMeta). |
| `render.head.set_description` | Filter | `$description` | Sửa description trước khi set. |
| `render.head.set_canonical` | Filter | `$url` | Sửa canonical URL. |
| `render.head.schema.add` | Filter | `$schema`, `$key` | Sửa schema bổ sung trước khi addSchema. |
| `render.head.defaults` | Filter | `$result`, `$layout`, `$payload` | Sửa mảng defaults từ Head\Builder::build. |
| `render.head.meta.before` | Filter | `$metaTags` | Sửa toàn bộ meta tags trước khi render. |
| `render.head.schema.item` | Filter | `$schema`, `$key` | Sửa từng item schema khi render. |
| `render.head.schema.before` | Filter | `$schemas` | Sửa mảng schemas trước khi render JSON-LD. |
| `render.head.canonical.before` | Filter | `$canonical` | Sửa canonical trước khi in thẻ link. |
| `render.head.after` | Filter | `$html` | Sửa toàn bộ HTML head sau khi build. |

**Thứ tự render head:** profile → title → meta (sau meta.before) → canonical (sau canonical.before) → OG → Twitter → product meta → other meta → JSON-LD (sau schema.before) → icon → after.

---

## 3. Schema (JSON-LD)

| Hook | Loại | Tham số | Mô tả |
|------|------|---------|--------|
| `schema.context` | Filter | `$context`, `$layout`, `$data` | Sửa context trước khi set (FrontendController gọi). |
| `schema.build` | Filter | `$schemas`, `$context` | Sửa toàn bộ mảng schema sau khi Builder load Types. |
| `schema.{type}` | Filter | `$schema`, `$context` | Sửa từng type (vd. `schema.product`, `schema.article`, `schema.website`). |
| `schema.render` | Filter | `$schemas`, `$context` | Sửa mảng schema cuối trước khi Graph::render (array → JSON-LD). |

**Lifecycle:** setCurrentContext → Builder::build (schema.build, schema.{type}) → schema.render → Graph::render.

---

## 4. Asset (AssetManager)

| Hook | Loại | Tham số | Mô tả |
|------|------|---------|--------|
| `render.css.before` | Filter | `$sorted`, `$area`, `$location` | Sửa danh sách CSS đã sort trước khi output. |
| `render.css.inline.before` | Filter | `$inlineStyles`, `$area`, `$location` | Sửa inline CSS trước khi in. |
| `render.css.after` | Filter | `$html`, `null`, `$location` | Sửa HTML output CSS (sau khi đã build chuỗi). |
| `render.js.before` | Filter | `$sorted`, `$area`, `$location` | Sửa danh sách JS đã sort trước khi output. |
| `render.js.inline.before` | Filter | `$inlineScripts`, `$area`, `$location` | Sửa inline JS trước khi in. |
| `render.js.after` | Filter | `$html`, `null`, `$location` | Sửa HTML output JS. |

**Thứ tự (CSS/JS):** before → (built hoặc từng file) + inline (sau inline.before) → after.

---

## 5. Minify

| Hook | Loại | Tham số | Mô tả |
|------|------|---------|--------|
| `minify.html.before` | Filter | `$html` | Sửa HTML trước khi minify (vd. giữ nguyên một đoạn). |
| `minify.html.after` | Filter | `$html` | Sửa HTML sau khi minify. |

---

## Ví dụ (dùng helper khi có thể)

```php
// Sửa title parts
add_filter('render.head.title_parts', function ($parts) {
    $parts[] = 'Custom';
    return $parts;
});

// Đăng ký script và inject biến JS (dùng helper: add_js hoặc enqueue_script)
enqueue_script('my-script', 'js/app.js');
localize_script('my-script', 'myConfig', [
    'ajax_url' => base_url('api'),
    'nonce'    => csrf_token(),
]);

// Fallback template khi không tìm thấy
add_filter('render.template_fallback', function ($path, $template, $namespace) {
    if ($template === 'Frontend/special' && $namespace === 'Frontend') {
        return PATH_THEMES . '/cmsfullform/Frontend/fallback-special.php';
    }
    return null;
}, 10, 3);

// Sửa schema product
add_filter('schema.product', function ($schema, $context) {
    $schema['brand'] = ['name' => 'My Brand'];
    return $schema;
}, 10, 2);
```
