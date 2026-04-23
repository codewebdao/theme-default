# Rà soát tích hợp: Asset, Render, Schema, Head

**Ngày:** 2025-01-29  
**Phạm vi:** Asset Build, Render, Schema, Head Meta, FrontendController, Themes

---

## 1. Asset Build System ✅

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| AssetsService | OK | patternToRegex, fetchExternal User-Agent, ETag/304 trong Controller |
| AssetManager | OK | combine_css/js, defer_js, async_css, recordCacheMiss/Hit |
| AssetsController | OK | ETag, 304, Content-Length, null-byte guard |
| View::css/js | OK | delegate tới AssetManager |
| assets_head() | OK | View::css('head'), View::js('head') |
| assets_footer() | OK | View::css('footer'), View::js('footer') |

**Luồng:** Controller/View addCss/addJs → AssetManager → (combine on) getBuiltAssetUrl/recordCacheMiss/Hit → AssetsService manifest/registry/snapshot → cron build → AssetsController serve

---

## 2. Render (View, Head, Template) ✅

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| Head::render() | OK | title, meta, OG, Twitter, canonical, Schema::get(), addSchema |
| Head::clear() | **FIXED** | Thêm links, siteName, titleSeparator |
| Head\Builder | OK | layout + payload → defaults |
| Head\Context | OK | setCurrent(layout, payload) từ FrontendController |
| View::make | OK | namespace, locate, render |
| PhpTemplate | OK | include với data |

---

## 3. Schema Library ✅

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| Schema::get() | OK | dùng trong Head::render() |
| Schema::setCurrentContext | OK | FrontendController gọi trước render |
| Schema\Builder | OK | load Types theo context type |
| Schema\Graph | OK | sanitizeValue, JSON_HEX_TAG |
| Types/* | OK | website, organization, product, article, … |

**Luồng:** FrontendController setCurrentContext(type, payload) → Head::render() gọi Schema::get() → Builder::build() → apply_filters('schema.render') → Graph::render()

---

## 4. Head / Meta ✅

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| Head\Builder layout mapping | OK | 404, front-page, page, single-*, archive, search, author, taxonomy |
| renderMetaTag | OK | htmlspecialchars cho name, content |
| renderLinkTag | OK | htmlspecialchars |
| renderSchemasFiltered | OK | sanitizeSchemaValue, JSON_HEX_TAG |

---

## 5. Integration (FrontendController, themes) ✅

| Thành phần | Trạng thái | Ghi chú |
|------------|------------|---------|
| FrontendController | OK | Schema + Head context set trước render |
| getSchemaContextForLayout | OK | map layout → type + payload |
| view_head() | OK | Head::render() + assets_head() |
| view_header() | OK | include header template |
| Frontend/header.php | OK | view_head() |
| Frontend@/header.php | OK | $meta, $schema, $append (optional) + view_head() |
| Frontend/footer.php | OK | assets_footer() |
| Frontend@/footer.php | **FIXED** | Đổi View::js('footer') → assets_footer() (đồng bộ hooks + CSS) |

---

## 6. Các thay đổi đã thực hiện

1. **Head::clear()** – Thêm xóa `$links`, `$siteName`, `$titleSeparator` để tránh dữ liệu cũ khi tái sử dụng.
2. **Frontend@/header.php** – Sửa comment cho rõ về flow meta/schema.
3. **Frontend@/footer.php** – Dùng `assets_footer()` thay cho `View::js('footer')` để đồng bộ hooks và footer CSS.

---

## 7. Gợi ý (optional)

- **Frontend@/header.php $meta, $schema** – Nếu view truyền `$meta`/`$schema`, nên cân nhắc dùng `Head::addMeta()` / `Head::addSchema()` thay vì echo trực tiếp để tránh trùng và dễ kiểm soát.
- **Test scenarios** – Chạy thử các kịch bản trong PROGRESSING_TASK_LIST (Page A/B cùng list, đổi combine, defer, plugin lỗi, CDN down).
