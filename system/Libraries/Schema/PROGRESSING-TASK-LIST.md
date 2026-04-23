# Schema Library (JSON-LD) – PROGRESSING & TASK LIST

## Mục tiêu tổng quát

- Schema Library đặt tại `system/Libraries/Schema/`
- **KHÔNG** render JSON trong view; view chỉ gọi `Schema::render()`
- View / theme / plugin can thiệp schema qua **Hooks có sẵn** (add_action, do_action, add_filter, apply_filters)
- Hook chỉ can thiệp **DATA ARRAY**, không can thiệp JSON string
- Kiến trúc sạch, dễ mở rộng, dễ đọc với dev newbie (WordPress-style)

---

## Ràng buộc bắt buộc

- [x] Dùng hooks có sẵn: `add_action`, `do_action`, `add_filter`, `apply_filters`
 - [x] **KHÔNG** tự viết hook system mới
- [x] **KHÔNG** sửa `System\Libraries\Hooks`

---

## Cấu trúc thư mục (checklist)

```
system/Libraries/Schema/
├── Schema.php          // Entry point (public API), setCurrentContext/getCurrentContext
├── Context.php         // Thông tin page hiện tại (type + payload)
├── Builder.php         // Build schema data array
├── Graph.php           // Convert array -> JSON-LD @graph
├── PROGRESSING-TASK-LIST.md
├── README.md
├── Types/
│   ├── website.php
│   ├── organization.php
│   ├── breadcrumb.php
│   ├── webpage.php
│   ├── page.php
│   ├── article.php
│   ├── product.php
│   ├── archive.php
│   ├── search.php
│   ├── person.php
│   ├── course.php
│   ├── event.php
│   ├── faq.php
│   ├── localbusiness.php
│   ├── video.php
│   ├── recipe.php
│   └── review.php
```

- [x] `Schema.php` – entry, lifecycle, setCurrentContext/getCurrentContext, gọi Context → Builder → filter → Graph
- [x] `Context.php` – class đơn giản: `$type`, `$payload`
- [x] `Builder.php` – build array, load Types, `apply_filters('schema.build', ...)`, webpage cho front
- [x] `Graph.php` – array → JSON-LD @graph, không hook, không sửa JSON string
- [x] `Types/website.php` – return array, nhận `$context`
- [x] `Types/organization.php` – return array, nhận `$context`
- [x] `Types/breadcrumb.php` – return array, nhận `$context`, URL hiện tại từ payload
- [x] `Types/webpage.php` – WebPage generic (trang chủ)
- [x] `Types/page.php` – WebPage trang tĩnh CMS
- [x] `Types/article.php` – return array, nhận `$context`
- [x] `Types/product.php` – return array, nhận `$context`
- [x] `Types/archive.php` – CollectionPage / ItemList
- [x] `Types/search.php` – SearchResultsPage
- [x] `Types/person.php` – Person (tác giả)
- [x] `Types/course.php` – Course
- [x] `Types/event.php` – Event
- [x] `Types/faq.php` – FAQPage
- [x] `Types/localbusiness.php` – LocalBusiness
- [x] `Types/video.php` – VideoObject
- [x] `Types/recipe.php` – Recipe
- [x] `Types/review.php` – Review

---

## API công khai (bắt buộc)

- [x] `Schema::render()` – không tham số (context từ global/current page)
- [x] `Schema::render('product', $product)` – type + payload
- [x] `Schema::render('article', $post)` – type + payload
- [x] Được gọi trong view; **không** echo JSON trực tiếp trong view
- [x] Mọi hook chạy **trước** khi render JSON

---

## Context.php – yêu cầu

- [x] Class đơn giản
- [x] Chỉ chứa: `$type` (front, product, article, …), `$payload` (object từ view)
- [x] `$context->type;` `$context->payload;`

---

## Builder.php – trách nhiệm

- [x] Build schema dưới dạng **array**; **KHÔNG** build JSON
- [x] Mỗi schema type = 1 file trong `Types/`
- [x] Core schema luôn có: **website**, **organization**
- [x] Hook bắt buộc: `apply_filters('schema.build', $schemas, $context);`
- [x] Sau mỗi type: `apply_filters('schema.{type}', $schema, $context);` (ví dụ `schema.product`, `schema.article`)

---

## Types/*.php – quy ước

- [x] Mỗi file **return array** schema
- [x] **KHÔNG** class
- [x] **KHÔNG** hook trong file type
- [x] Nhận `$context` từ scope (Builder truyền vào)
- [x] Chuỗi từ payload/option dùng `schema_safe_string()` (chống XSS)

---

## Graph.php – trách nhiệm

- [x] Nhận array schema
- [x] Convert sang JSON-LD dạng `['@context' => 'https://schema.org', '@graph' => [...]]`
- [x] **KHÔNG** hook trong Graph
- [x] **KHÔNG** cho phép chỉnh JSON string (chỉ convert array → JSON, XSS-safe: strip_tags, JSON_HEX_TAG)

---

## Schema.php – lifecycle bắt buộc

```
Schema::render()
  → Context::make()
  → Builder::build()
  → apply_filters('schema.render', $schemas, $context)
  → Graph::render()
```

- [x] Hook toàn cục: `apply_filters('schema.render', $schemas, $context);`

---

## Hook granular theo schema type (bắt buộc)

- [x] Sau khi load từng schema type, gọi: `apply_filters('schema.product', $schema, $context);` (và tương tự cho article, website, organization, breadcrumb)
- [x] Cho phép dev can thiệp từng schema riêng

---

## Chất lượng code

- [x] PHP 7.4+
- [x] Không magic
- [x] Không dependency ngoài (chỉ Hooks có sẵn)
- [x] Comment rõ ràng
- [x] Naming dễ đọc (WordPress/PHP thuần)
- [x] XSS: schema_safe_string() trong Types; Graph sanitizeValue + JSON_HEX_TAG; view dùng e()/h() khi output HTML

---

## KHÔNG được làm

- [x] Không viết schema (JSON) trong view
- [x] Không cho hook sửa JSON string
- [x] Không dùng abstract / interface phức tạp
- [x] Không hardcode DB query trong Schema library

---

## Ví dụ hook từ view / theme (bắt buộc test)

- [x] `add_filter('schema.product', ...)` – thêm/sửa schema product
- [x] `add_filter('schema.render', ...)` – sửa toàn bộ graph (add/remove schema)

---

## Tiến trình (Phases)

| Phase | Nội dung | Trạng thái |
|-------|----------|------------|
| **0** | PROGRESSING-TASK-LIST + tạo cấu trúc thư mục | Done |
| **1** | Context.php, Graph.php, Schema.php (skeleton + lifecycle) | Done |
| **2** | Builder.php + Types: website, organization, breadcrumb | Done |
| **3** | Types: product, article + hooks granular | Done |
| **4** | README, ví dụ gọi từ view / hooks | Done |
| **5** | Schema::setCurrentContext/getCurrentContext; FrontendController set context từ layout | Done |
| **6** | Types: webpage, page, archive, search, person, course, event, faq, localbusiness, video, recipe, review | Done |
| **7** | XSS: schema_safe_string() trong Types; Graph sanitize; load Security_helper; Article → BlogPosting; chuẩn Google | Done |

---

*Cập nhật lần cuối: theo tiến độ thực hiện.*
