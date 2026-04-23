# Schema Library (JSON-LD)

Thư viện core tạo JSON-LD schema cho CMS FullForm. **Không** render JSON trong view; view chỉ gọi `Schema::render()`. Mọi can thiệp qua **Hooks có sẵn** (add_filter, apply_filters).

## Cấu trúc

- **../Schema.php** (cùng cấp với Head, View) – Entry: `Schema::render()` / `Schema::get()`; `setCurrentContext()` / `getCurrentContext()` (context từ layout)
- **Context.php** – `$type`, `$payload` (page hiện tại)
- **Builder.php** – Build array schema, load Types, hook `schema.build` + `schema.{type}`
- **Graph.php** – Array → JSON-LD @graph (XSS-safe), không hook
- **Types/*.php** – Mỗi file return array, nhận `$context` từ scope

## Context từ layout (tự động)

**FrontendController** trước khi render view gọi `Schema::setCurrentContext($type, $payload)` dựa trên layout hiện tại. Filter **`schema.context`** cho phép plugin/theme sửa context: `apply_filters('schema.context', $context, $layout, $data)`. View chỉ cần gọi `Schema::render()` không tham số → thư viện biết đang ở page type nào và dùng đúng schema.

Ánh xạ layout → schema type:

| Layout | Schema type | Payload |
|--------|-------------|--------|
| front-page, index | front | null |
| page, page-{slug} | page | $page |
| single-blogs, single-{posttype} | article, product, course, event, … | $post |
| archive, archive-{posttype} | archive | post_type, title |
| search, search-* | search | query |
| author, author-* | person | slug, author data |
| taxonomy-* | archive | taxonomy, term |

Post type → schema type: `blogs` → article, `products` → product, `courses` → course, `events` → event, `faqs` → faq, `videos` → video, `recipes` → recipe.

## Lifecycle

```
Schema::render($type, $payload)
  → Nếu không truyền tham số: dùng getCurrentContext() (đã set bởi FrontendController)
  → Context::make($type, $payload)
  → Builder::build($context)   [load Types, apply_filters('schema.build'), apply_filters('schema.{type}')]
  → apply_filters('schema.render', $schemas, $context)
  → Graph::render($schemas)    [array → JSON-LD, echo <script>]
```

## Gọi schema

Schema chính (website, organization, breadcrumb, page type) được render trong **Head::render()** qua `Schema::get()` – view chỉ cần gọi `view_head()` (hoặc `Head::render()`). Không cần gọi Schema trong template.

Khi cần render thủ công (vd. buffer, hook):

```php
\System\Libraries\Render\Schema::render();  // echo
$html = \System\Libraries\Render\Schema::get();  // return string
```

Với type + payload tùy chỉnh:

```php
\System\Libraries\Render\Schema::render('product', $product);
\System\Libraries\Render\Schema::render('article', $post);
```

## Types có sẵn

- **website** – WebSite (Sitelinks Search Box)
- **organization** – Organization
- **breadcrumb** – BreadcrumbList
- **webpage** – WebPage (generic, dùng cho trang chủ)
- **page** – WebPage (trang tĩnh CMS)
- **article** – Article / BlogPosting
- **product** – Product
- **archive** – CollectionPage / ItemList
- **search** – SearchResultsPage
- **person** – Person (tác giả)
- **course** – Course
- **event** – Event
- **faq** – FAQPage
- **localbusiness** – LocalBusiness
- **video** – VideoObject
- **recipe** – Recipe
- **review** – Review (thường lồng trong Product/Article)

## Hook từ theme / plugin

**Sửa từng schema type:**

```php
add_filter('schema.product', function ($schema, $ctx) {
    $schema['brand'] = [
        '@type' => 'Brand',
        'name'  => 'Apple',
    ];
    return $schema;
}, 10, 2);
```

**Sửa toàn bộ graph (thêm/xóa schema):**

```php
add_filter('schema.render', function ($schemas, $ctx) {
    unset($schemas['breadcrumb']);
    return $schemas;
}, 10, 2);
```

**Sửa sau khi build (trước graph):**

```php
add_filter('schema.build', function ($schemas, $ctx) {
    $schemas['webpage'] = [ /* ... */ ];
    return $schemas;
}, 10, 2);
```

## XSS / Bảo mật

- **Trong schema (JSON-LD):** Mọi chuỗi lấy từ payload/option trong Types/*.php dùng `schema_safe_string()` (Security_helper) – strip_tags, trim, loại control chars. Graph.php sanitize toàn bộ chuỗi + `JSON_HEX_TAG` khi encode để tránh breakout `</script>`. `base_url()` luôn có (Uri_helper), không cần kiểm tra tồn tại.
- **Trong view (HTML):** Khi hiển thị dữ liệu user trong HTML, dùng `e()` hoặc `h()` từ Security_helper. FrontendController load helper `security` nên `e()`/`h()` và `schema_safe_string()` có sẵn trong view.
- Không dùng `e()`/`h()` cho giá trị đưa vào schema vì output là JSON, không phải HTML.

## Ràng buộc

- Dùng **hooks có sẵn**: add_action, do_action, add_filter, apply_filters. Không tự viết hook system, không sửa `System\Libraries\Hooks`.
- Hook chỉ can thiệp **data array**, không can thiệp JSON string.
- Types/*.php: return array, không class, không hook trong file; chuỗi từ user/option dùng `schema_safe_string()` (Security_helper); dùng `base_url()` trực tiếp (Uri_helper luôn có).

## PHP 7.4+

Không magic, không dependency ngoài, naming WordPress-style.
