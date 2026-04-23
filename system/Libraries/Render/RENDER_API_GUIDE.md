# Render Library – API & Helper Reference

Tài liệu cho developer theme/plugin: **hàm helper** (ngắn gọn, dễ nhớ) và **Class API** (View, Head, Schema). Load helper: `load_helpers(['view'])` (FrontendController đã load sẵn).

**Version:** 2.0.0

---

## Bảng hàm nhanh (Helper – dùng trong theme/plugin)

| Helper | Mô tả ngắn |
|--------|-------------|
| **Assets** | |
| `add_css($handle, $src, $deps, $version, $media, ...)` | Đăng ký file CSS |
| `add_js($handle, $src, $deps, $version, $defer, $async, ...)` | Đăng ký file JS |
| `enqueue_style($handle, $src, ...)` | Giống add_css (wp_enqueue_style style) |
| `enqueue_script($handle, $src, ...)` | Giống add_js (wp_enqueue_script style) |
| `inline_css($handle, $css, ...)` | CSS nội tuyến |
| `inline_js($handle, $js, ...)` | JS nội tuyến |
| `localize_script($handle, $objectName, $data, ...)` | Inject biến JS từ PHP (ajax_url, nonce…) |
| `view_css($location)` | In HTML các CSS đã đăng ký (`'head'` \| `'footer'`) |
| `view_js($location)` | In HTML các JS đã đăng ký (`'head'` \| `'footer'`) |
| **Head / SEO** | |
| `view_head()` | In toàn bộ &lt;head&gt; (title, meta, schema, CSS/JS head) |
| `view_title($title, $append)` | Set tiêu đề trang |
| `view_description($description)` | Set meta description |
| `view_keywords($keywords)` | Set meta keywords |
| `view_meta($name, $content, $type)` | Thêm meta tag |
| `view_canonical($url)` | Set canonical URL |
| `view_og($key, $value)` | Một cặp Open Graph |
| `view_og_array($array)` | Open Graph từ mảng (title, description, image, url…) |
| `view_twitter_card($array)` | Twitter Card từ mảng |
| `view_schema($schema, $key)` | Thêm JSON-LD schema bổ sung |
| **Template** | |
| `view_render($template, $data)` | Render template, trả về HTML |
| `view_header($name, $data)` | Include header (Frontend/header.php hoặc header-{name}.php) |
| `view_footer($name, $data)` | Include footer |
| `view_sidebar($name, $data)` | Include sidebar |
| `view_template_part($slug, $name, $data)` | Include template part |
| **Output assets** | |
| `assets_head()` | In CSS + JS cho head (và hooks) |
| `assets_footer()` | In CSS + JS cho footer (và hooks) |

---

## Class API (khi cần full control)

| Class | Method chính |
|-------|----------------|
| **View** | `make($template, $data)->render()`, `include()`, `includeIf()`, `share()`, `getShared()`, `clearShared()`, `namespace()`, `getNamespace()`, `exists()`, `getPath()`, `addCss()`, `addJs()`, `inlineCss()`, `inlineJs()`, `localizeScript()`, `css()`, `js()`, `clearAssets()`, `setTitle()`, `setDescription()`, `setKeywords()`, `addMeta()`, `setCanonical()`, `setOpenGraph()`, `setTwitterCard()`, `addSchema()`, `renderHead()` |
| **Head** | `setTitle()`, `setTitleSeparator()`, `setSiteName()`, `getTitle()`, `addMeta()`, `setDescription()`, `setCanonical()`, `addLink()`, `addSchema()`, `render()`, `clear()` |
| **Schema** | `setCurrentContext()`, `getCurrentContext()`, `render()`, `get()` |

---

## Ví dụ nhanh (dùng Helper)

```php
// Trong theme (đã load_helpers(['view']))

// 1. Đăng ký CSS/JS (add_* hoặc enqueue_* – tương đương)
add_css('bootstrap', 'css/bootstrap.min.css', [], '5.3.0');
enqueue_script('jquery', 'js/jquery.min.js');
enqueue_script('app', 'js/app.js', ['jquery'], null, true, false, null, true, true);

// 2. Inject biến JS (ajax_url, nonce)
localize_script('app', 'myConfig', [
    'ajax_url' => base_url('api'),
    'nonce'   => csrf_token(),
]);

// 3. Head & SEO
view_title('Trang chủ');
view_description('Mô tả trang');
view_canonical(current_url());
view_og_array([
    'title' => 'Trang chủ',
    'description' => 'Mô tả',
    'image' => base_url('img/og.jpg'),
    'url'   => current_url(),
    'type'  => 'website',
]);

// 4. Layout – trong file head
<?php view_head(); ?>

// Hoặc tách: chỉ CSS/JS
<?php echo view_css('head'); ?>
<?php echo Head::render(); ?>
<?php assets_head(); ?>

// 5. Footer – in JS
<?php assets_footer(); ?>
// hoặc chỉ JS:
<?php echo view_js('footer'); ?>

// 6. Render template
echo view_render('Frontend/Home/index', ['posts' => $posts]);
```

---

## Chi tiết từng nhóm

### Assets (CSS/JS)

- **add_css** / **enqueue_style:** Đăng ký CSS. Tham số: handle, src, deps, version, media (`'all'`), area, in_footer, preload, minify. Gọi ngắn: `add_css('handle', 'css/file.css');` hoặc `enqueue_style('handle', 'css/file.css');`
- **add_js** / **enqueue_script:** Đăng ký JS (ánh xạ cùng View::addJs). Tham số: handle, src, deps, version, defer, async, area, in_footer (true), minify. Gọi ngắn: `add_js('handle', 'js/file.js');` hoặc `enqueue_script('handle', 'js/file.js');`
- **localize_script:** Gắn dữ liệu PHP vào một script đã đăng ký; trong JS dùng biến global `objectName` (vd. `myConfig.ajax_url`).
- **view_css('head'|'footer')**, **view_js('head'|'footer'):** Trả về chuỗi HTML; thường dùng `echo view_css('head');` trong layout.

### Head / SEO

- **view_head():** In toàn bộ head (title, meta, canonical, OG, Twitter, schema, rồi gọi assets_head). Dùng một lần trong layout.
- **view_title**, **view_description**, **view_meta**, **view_canonical:** Set từng phần; **view_og_array** / **view_twitter_card:** Set theo mảng.
- **view_schema($array, $key):** Thêm schema JSON-LD bổ sung (key tùy chọn).

### Template

- **view_render($template, $data):** Tương đương `View::make($template, $data)->render()`.
- **view_header**, **view_footer:** Tìm template `header.php` / `header-{name}.php` (trong namespace hiện tại).
- **view_template_part($slug, $name, $data):** Include phần template (thử `$slug-$name` rồi `$slug`).

---

## Template path (View::make)

- **Theme:** `View::namespace('Frontend'); View::make('page', $data)` → `themes/{theme}/Frontend/page.php`
- **Plugin:** `View::make('@Ecommerce/Frontend/products/index', $data)`
- **Block:** `View::make('#Header/default', $data)`

---

## Hooks & Filter

Danh sách đầy đủ filter/action: **[RENDER_HOOKS.md](RENDER_HOOKS.md)**.

---

## Best practices

- Dùng **helper** trong theme/plugin cho gọn (`add_css`/`enqueue_style`, `add_js`/`enqueue_script`, `localize_script`, `view_title`, `view_head`, …).
- Luôn khai báo **dependencies** khi add_js/add_css (vd. Bootstrap phụ thuộc jQuery).
- Production: bật **minify** và có file `.min.css` / `.min.js` trong theme.
- SEO: set **view_title**, **view_description**, **view_canonical**, **view_og_array** (và Twitter nếu cần).
