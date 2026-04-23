# Render & Schema – Kiến trúc và Hooks

## 1. Schema có nên là phần con của Render không?

**Kết luận: Giữ Schema là thư viện riêng (`System\Libraries\Render\Schema`), không gộp vào Render.**

### Lý do giữ Schema tách riêng

| Tiêu chí | Giải thích |
|----------|------------|
| **Trách nhiệm** | Render = lớp trình bày (HTML, head, view). Schema = dữ liệu có cấu trúc (JSON-LD) cho SEO/semantics. Hai mảng khác nhau. |
| **Tái sử dụng** | Schema có thể dùng ngoài Head (API, feed, email). Tách riêng tránh phụ thuộc Render. |
| **Tích hợp** | Head gọi `Schema::get()` khi render; một điểm tích hợp rõ ràng, không cần gộp code. |
| **Breaking change** | Gộp Schema vào Render phải đổi namespace, ảnh hưởng plugin/theme đang dùng `System\Libraries\Render\Schema`. |

### Quan hệ hiện tại

```
FrontendController
  → set Schema::setCurrentContext($type, $payload)
  → set Head\Context::setCurrent($layout, $payload)
  → View::make($layout, $data)

view_head() → Head::render()
  → Head\Builder::build() (defaults) + fill nếu view chưa set
  → Schema::get() (JSON-LD chính)
  → render schemas bổ sung (addSchema)
  → output <head>
```

Schema **không** nằm trong namespace Render; Render **sử dụng** Schema khi xuất head. Tương tự: View dùng Head, Head dùng Schema.

---

## 2. Quy ước đặt tên Hooks (dấu chấm – dễ nhớ)

- **Dùng dấu chấm**: `render.head.<target>` cho Head, `schema.<target>` cho Schema.
- **Phân cấp rõ**: `render.head.defaults` = dữ liệu mặc định head; `render.head.title_parts` = title; `schema.render` = graph trước khi xuất.

### Head hooks (prefix `render.head.`)

| Hook | Ý nghĩa | Tham số |
|------|--------|---------|
| `render.head.defaults` | Filter dữ liệu mặc định (title, meta, OG, canonical) trước khi fill vào Head | `$defaults`, `$layout`, `$payload` |
| `render.head.set_title` | Filter title parts khi set | `$titleParts`, `$append` |
| `render.head.title_parts` | Filter title parts trước khi thêm site name | `$parts` |
| `render.head.set_description` | Filter description khi set | `$description` |
| `render.head.add_meta` | Filter từng meta trước khi thêm | `$meta`, `$name`, `$content`, `$type` |
| `render.head.set_canonical` | Filter canonical URL khi set | `$url` |
| `render.head.schema.add` | Filter schema bổ sung khi addSchema | `$schema`, `$key` |
| `render.head.meta.before` | Filter toàn bộ meta trước khi xuất | `$metaTags` |
| `render.head.schema.item` | Filter từng schema bổ sung | `$schema`, `$key` |
| `render.head.schema.before` | Filter mảng schemas bổ sung | `$schemas` |
| `render.head.canonical.before` | Filter canonical trước khi xuất | `$canonical` |
| `render.head.after` | Filter HTML head sau khi render xong | `$html` |

### Schema hooks (prefix `schema.`)

| Hook | Ý nghĩa | Tham số |
|------|--------|---------|
| `schema.context` | Filter context (type + payload) theo layout | `$context`, `$layout`, `$data` |
| `schema.render` | Filter graph JSON-LD trước khi xuất | `$schemas`, `$context` |
| `schema.build` | Filter toàn bộ schemas sau build | `$schemas`, `$context` |
| `schema.{type}` | Filter từng type (vd. schema.article, schema.product) | `$schema`, `$context` |

---

## 3. Head options (option() – WordPress / Rank Math style)

Builder dùng các option sau khi có:

| Option | Ý nghĩa | Ví dụ |
|--------|----------|--------|
| `hreflang_alternates` | Mảng alternate URL theo ngôn ngữ | `[['href' => 'https://site.com/en/', 'hreflang' => 'en'], ...]` |
| `site_icon` | URL hoặc ID ảnh favicon / icon site | |
| `site_favicon` | URL favicon (ưu tiên nếu có) | |
| `site_apple_touch_icon` | URL apple-touch-icon | |
| `site_tile_image` | URL msapplication-TileImage | |
| `fb_admins` | Facebook admins (og:fb:admins) | `123456789` |
| `og_publisher` | URL trang publisher (article:publisher) | `https://facebook.com/page` |

---

## 4. Ví dụ can thiệp

```php
// Đổi title trang chủ
add_filter('render.head.defaults', function ($defaults, $layout, $payload) {
    if ($layout === 'front-page') {
        $defaults['title_parts'] = [__('My Home')];
    }
    return $defaults;
}, 10, 3);

// Thêm property vào schema Article
add_filter('schema.article', function ($schema, $context) {
    $schema['custom'] = 'value';
    return $schema;
}, 10, 2);

// Sửa graph JSON-LD trước khi xuất
add_filter('schema.render', function ($schemas, $context) {
    unset($schemas['breadcrumb']);
    return $schemas;
}, 10, 2);
```

**Thêm link (hreflang, icon) từ theme:**

```php
Head::addLink('alternate', 'https://site.com/en/', ['hreflang' => 'en']);
Head::addLink('icon', theme_assets('favicon.ico'), ['sizes' => '32x32']);
```
