# PROGRESSING – Render Library (Rà soát & Cải tiến)

**Mục đích:** Rà soát toàn bộ thư viện `system/Libraries/Render`, liệt kê điểm mạnh, thiếu sót và đề xuất cải tiến để hoàn thiện hơn.

**Phạm vi:** View, Head, Schema, Minify, Asset (AssetManager), Template (PhpTemplate), Security (PathValidator), tài liệu (.md), hooks, API, bảo mật, hiệu năng.

---

## 0. Cấu trúc hiện tại

```
system/Libraries/Render/
├── Head.php              # Entry Head (namespace Render)
├── View.php              # Entry View + View::minify()
├── Schema.php            # Entry Schema (cùng cấp Head/View)
├── Minify.php            # Minify HTML/CSS/JS/JSON
├── Asset/
│   └── AssetManager.php
├── Head/
│   ├── Builder.php
│   └── Context.php
├── Schema/
│   ├── Builder.php, Context.php, Graph.php
│   ├── Types/            # 16 type files
│   ├── README.md
│   └── PROGRESSING-TASK-LIST.md
├── Template/
│   └── PhpTemplate.php
├── Security/
│   └── PathValidator.php
├── BEGINNER_GUIDE.md
├── RENDER_ARCHITECTURE.md
├── RENDER_API_GUIDE.md           # Bảng hàm helper + API (developer reference)
├── RENDER_V2_USAGE_GUIDE.md      # Hướng dẫn chi tiết từng phần
├── RENDER_HOOKS.md               # Danh sách filter/action
├── CHANGELOG.md                  # Thay đổi API/behavior
└── PROGRESSING_RENDER_LIBRARY.md  (file này)
```

---

## 1. Đồng bộ & Nhất quán

### 1.1 Namespace & @package

| File | Namespace | @package trong docblock | Ghi chú |
|------|-----------|-------------------------|--------|
| Head.php | `System\Libraries\Render` | `System\Libraries\Render` | Đúng (đã sửa). |
| View.php | `System\Libraries\Render` | `System\Libraries\Render` | Đúng. |
| Schema.php | `System\Libraries\Render` | `System\Libraries\Render` | Đúng. |
| Minify.php | `System\Libraries\Render` | `System\Libraries\Render` | Đúng. |
| AssetManager.php | `System\Libraries\Render\Asset` | `System\Libraries\Render\Asset` | Đúng. |
| PhpTemplate.php | `System\Libraries\Render\Template` | `System\Libraries\Render\Template` | Đúng. |
| PathValidator.php | `System\Libraries\Render\Security` | `System\Libraries\Render\Security` | Đúng. |
| Head/Builder, Head/Context | `System\Libraries\Render\Head` | `System\Libraries\Render\Head` | Đúng. |
| Schema/Builder, Context, Graph | `System\Libraries\Render\Schema` | `System\Libraries\Render\Schema` | Đúng. |

**Đề xuất:**
- [x] Thống nhất @package: đã dùng đủ `System\Libraries\Render` hoặc `System\Libraries\Render\*`.
- [x] Head.php @package đã là `System\Libraries\Render`.

### 1.2 @version / @since

- View.php có `@version 2.0.0`.
- Head, Minify, AssetManager, PhpTemplate, PathValidator không có @version.
- Schema và Schema/* có `@since 1.0.0`.

**Đề xuất:**
- [x] Thêm @since 1.0.0 cho Head, Minify, AssetManager, PhpTemplate, PathValidator (View có @version 2.0.0, Schema/* đã có @since).

### 1.3 Entry point (file cùng cấp)

- Head, View, Schema, Minify đều là file cùng cấp; Schema đã move ra `Render/Schema.php` (đồng bộ với Head/View).
- Minify không có thư mục con, gọi qua `View::minify()` từ controller – đã thống nhất.

---

## 2. View

### 2.1 Đã có

- API: `make`, `with`, `render`, `include`, `includeIf`, `share`, `namespace`, `getNamespace`, `clearCache`, `forgetCache`.
- Delegation: addCss, addJs, inlineCss, inlineJs, css(), js(), clearAssets → AssetManager; setTitle, setDescription, … renderHead, addSchema → Head.
- `View::minify($html)` → Minify::html().
- Hooks: render.template_name, render.data, render.output, render.template_found (trong PhpTemplate).
- Debugbar: trackView, injectDebugbar.
- Xử lý lỗi: handleError, renderThemeErrorPage.

### 2.2 Có thể cải tiến

- [x] **Shared data:** View::getShared(), View::clearShared() đã thêm – dùng cho test/cleanup.
- [ ] **Deprecation:** Nếu có method cũ (từ Render v1) còn gọi trong codebase thì đánh dấu @deprecated và hướng dẫn thay thế.

---

## 3. Head

### 3.1 Đã có

- Title, separator, site name, meta, canonical, OG, Twitter, product meta, link (profile, alternate, icon, apple-touch-icon).
- Context từ Head\Context; defaults từ Head\Builder theo layout.
- Schema chính qua Schema::get(); schema bổ sung addSchema().
- Nhiều filter: render.head.set_title, render.head.title_parts, render.head.add_meta, render.head.defaults, render.head.meta.before, render.head.schema.*, render.head.canonical.before, render.head.after.

### 3.2 Có thể cải tiến

- [x] **Head::clear():** Đã có sẵn – reset titleParts, metaTags, schemas, canonical, links, siteName, separator (cho test hoặc nhiều head trong một request).
- [x] **Document tất cả filter:** Đã liệt kê trong RENDER_HOOKS.md (tên filter, tham số, thứ tự); RENDER_ARCHITECTURE link tới RENDER_HOOKS.md.
- [ ] **Escape:** renderMetaTag/renderLinkTag dùng htmlspecialchars(..., ENT_QUOTES | ENT_XML1, 'UTF-8') – ổn; kiểm tra mọi output khác từ option/user.
- [ ] **Thứ tự phần tử head:** Đã cố định (profile, title, meta, canonical, OG, Twitter, product, other meta, JSON-LD, icon). Có thể thêm filter “render.head.sections_order” nếu cần plugin đổi thứ tự.

---

## 4. Schema

### 4.1 Đã có

- Entry tại Render/Schema.php; Context, Builder, Graph trong Schema/.
- setCurrentContext, getCurrentContext, clearCurrentContext, render(), get().
- Types: website, organization, breadcrumb, webpage, page, article, product, archive, search, person, course, event, faq, localbusiness, video, recipe, review.
- Hooks: schema.context (trong FrontendController), schema.build, schema.{type}, schema.render.
- README.md, PROGRESSING-TASK-LIST.md trong Schema/.

### 4.2 Có thể cải tiến

- [x] **Schema/PROGRESSING-TASK-LIST.md:** Cập nhật mục “Schema.php” – vị trí đã đổi thành `../Schema.php` (cùng cấp với Head, View).
- [ ] **Schema/README.md:** Đã sửa FQCN thành `\System\Libraries\Render\Schema`; kiểm tra toàn bộ ví dụ trong README.
- [ ] **Builder::loadType:** Dùng `require $file` trong closure; $context được use. Đảm bảo không có biến global leak (đã ổn).
- [ ] **Graph::render:** JSON_HEX_TAG cho XSS; kiểm tra mọi chuỗi từ Types đã qua schema_safe_string hoặc tương đương.
- [ ] **Types thiếu:** So với schema.org có thể bổ sung (SoftwareApplication, HowTo, …) – tùy nhu cầu product.
- [ ] **Validation JSON-LD:** Có thể thêm tùy chọn (dev) validate output theo schema.org (chạy ngoài, không bắt buộc).

---

## 5. Minify

### 5.1 Đã có

- html(): protectBlocks (style, script, pre, textarea), removeHtmlComments, collapseWhitespaceBetweenTags, collapseRemainingWhitespace, restoreBlocks.
- css(), js(), json(); js() bảo vệ chuỗi, xóa comment, gộp whitespace.
- Gọi từ View::minify() (controller), AssetManager (inline CSS/JS), AssetsService (build).

### 5.2 Có thể cải tiến

- [x] **Hook minify:** Chưa có filter kiểu `minify.html.before` / `minify.html.after`; nếu plugin cần can thiệp (vd. giữ nguyên một đoạn HTML) có thể thêm.
- [x] **Minify::html() docblock:** Cập nhật bước 4 thành “Thu gọn mọi khoảng trắng còn lại thành một space” (đã đổi từ collapseNewlinesAndTrim sang collapseRemainingWhitespace).
- [ ] **Giới hạn kích thước:** Với HTML rất lớn, có thể giới hạn độ dài đầu vào hoặc bỏ qua minify để tránh timeout (tùy chọn).
- [ ] **Template literal (ES6) trong JS:** Minify::js() bảo vệ " và '; nếu theme dùng backtick `...` cần cân nhắc bảo vệ để không phá nội dung.

---

## 6. Asset (AssetManager)

### 6.1 Đã có

- addCss, addJs, inlineCss, inlineJs; css(), js() theo location/area.
- Build: getBuiltAssetUrl (1 URL hoặc mảng URL khi không combine), recordCacheMiss; combine/minify theo option; signature có combine.
- Hooks: render.css.before, render.css.inline.before, render.css.after; render.js.before, render.js.inline.before, render.js.after.
- Sort dependencies, preload, defer, async.

### 6.2 Có thể cải tiến

- [x] **Document hooks:** Đã liệt kê trong RENDER_HOOKS.md (thứ tự, tham số); RENDER_ARCHITECTURE link RENDER_HOOKS.md.
- [x] **Remove asset:** Không có removeCss/removeJs; đã document – can thiệp qua filter render.css.before / render.js.before.
- [x] **Localization:** Đã có localizeScript (AssetManager + View::localizeScript); inject biến JS từ PHP.
- [x] **Asset versioning:** Đã document trong RENDER_ARCHITECTURE – built URL không append ?ver= từ addCss/addJs; version gắn trong build.

---

## 7. Template (PhpTemplate)

### 7.1 Đã có

- locate(), render(), exists(), getPath(); PathValidator cho template name và namespace.
- Cache locate/fileExists trong request.
- Hooks: render.template_name, render.data, render.output, render.template_found.
- Theme kế thừa (current → parent); plugin/block với prefix @ và #.

### 7.2 Có thể cải tiến

- [x] **Parent theme:** detectParentTheme() phụ thuộc option/constant; document cách theme khai báo parent.
- [x] **Lỗi “Template not found”:** Có throw RuntimeException; View::handleError có xử lý; có thể thêm filter để plugin cung cấp fallback path.
- [ ] **loadPhpFile:** Extract $data và include; đảm bảo không có biến ngoài ý muốn (đã dùng extract với scope) – rà lại nếu có báo lỗi lạ.

---

## 8. Security (PathValidator)

### 8.1 Đã có

- isValidTemplateName, isValidComponentName, isValidArea, isPathUnderAllowedBase.
- Chặn .., null byte, path tuyệt đối, URL; giới hạn độ dài 500.

### 8.2 Có thể cải tiến

- [ ] **Allowed bases:** init() dùng PATH_THEMES, PATH_PLUGINS, application/Blocks; nếu có thêm base (vd. uploads) cần cấu hình rõ.
- [ ] **Log khi reject:** Đã có Logger::error trong PhpTemplate khi reject; PathValidator có thể trả về lý do (enum/string) để log rõ hơn (tùy chọn).
- [x] **Document:** Đã thêm mục Security (PathValidator) trong RENDER_ARCHITECTURE – quy tắc validate, allowed bases, max length.

---

## 10. Hiệu năng

- [ ] **View:** getEngine() singleton; clearCache/forgetCache no-op – ổn.
- [ ] **Head::render():** Gọi Schema::get() mỗi request; Schema đã dùng context, Builder load file Types – có thể cache kết quả Schema::get() theo context key (phức tạp, tùy nhu cầu).
- [ ] **AssetManager:** Đã cache asset_build_version, cachedArea; get_option nhiều lần – đã giảm nhờ cache.
- [ ] **PhpTemplate:** locateCache, fileExistsCache trong request – ổn.
- [ ] **Minify::html():** opcache_invalidate trong filestorage_get (Storage) không nằm trong Render; Minify không gọi DB – ổn.

---

## 11. Bảo mật

- [ ] **PathValidator:** Đã chặn traversal, null byte, absolute, URL – duy trì và test.
- [ ] **Head/Schema output:** Escape HTML; JSON-LD dùng JSON_HEX_TAG – duy trì.
- [ ] **Asset URL:** buildAssetUrl/getBuiltAssetUrl trả về URL; không include user input trực tiếp vào path – kiểm tra mọi nguồn (area, location, type từ code, không từ request).
- [ ] **Minify:** Không eval; chỉ preg_replace và restore – ổn.

---

## 12. Testing & Maintainability

- [ ] **Unit test:** Hiện chưa thấy test cho Render (View, Head, Schema, Minify, PathValidator). Có thể thêm test cho PathValidator (isValidTemplateName, isValidComponentName), Minify::js() (comment, string), Schema::get() với context mock.
- [ ] **Integration test:** Một vài test E2E (vd. render layout index → có title, có schema) giúp refactor an toàn.
- [ ] **Type hint:** View, Head, Schema đã dùng type hint ở nhiều chỗ; AssetManager, PhpTemplate có chỗ còn mixed – có thể bổ sung dần.
- [x] **PHP version:** Đã ghi trong RENDER_ARCHITECTURE.md (PHP 7.4+, json, mbstring).

---

## 13. Checklist tiến trình (ưu tiên)

### P1 – Đồng bộ & doc (nhanh)

- [x] Sửa Head.php @package thành `System\Libraries\Render`.
- [x] Cập nhật Schema/PROGRESSING-TASK-LIST.md: Schema.php ở `../Schema.php`.
- [x] Cập nhật RENDER_ARCHITECTURE.md: thêm View::minify() trong luồng; thêm mục Hooks reference (link tới RENDER_HOOKS.md).
- [x] Kiểm tra Minify.php docblock (bước 4: collapseRemainingWhitespace).

### P2 – API & hook (vừa)

- [x] Liệt kê đầy đủ tất cả filter/action trong một file RENDER_HOOKS.md.
- [x] (Tùy chọn) Head::clear() – đã có sẵn; dùng cho test hoặc khi render nhiều head trong một request.
- [x] (Tùy chọn) View::getShared() / clearShared() – đã thêm.
- [x] (Tùy chọn) Minify filter `minify.html.before` / `minify.html.after` – đã thêm trong Minify::html(); ghi trong RENDER_HOOKS.md.

### P3 – Tính năng (khi cần)

- [x] AssetManager: removeCss/removeJs hoặc document “không hỗ trợ deregister”.
- [x] AssetManager: API localizeScript (inject biến JS từ PHP); View::localizeScript() delegate.
- [ ] Schema: thêm Types (HowTo, SoftwareApplication, …) – tùy nhu cầu product, không bắt buộc.
- [x] Template: filter render.template_fallback khi “Template not found”.

### P4 – Chất lượng (dài hạn)

- [ ] Unit test cho PathValidator, Minify (js/css/html), Schema (get với context).
- [x] Document PHP min version – đã ghi trong RENDER_ARCHITECTURE.md (PHP 7.4+, json, mbstring).
- [x] Changelog – đã tạo CHANGELOG.md; cập nhật khi đổi API/behavior.

---

## 14. Tóm tắt

| Khu vực | Trạng thái | Ưu tiên cải tiến |
|---------|------------|-------------------|
| Cấu trúc / Entry | Ổn (Head, View, Schema, Minify cùng cấp) | Đã hoàn thiện |
| View | Đầy đủ, minify, getShared/clearShared, localizeScript | Đã hoàn thiện |
| Head | Nhiều filter, clear() | Đã hoàn thiện |
| Schema | Đã move, hooks rõ | Optional thêm Types |
| Minify | Hooks before/after, JS an toàn | Đã hoàn thiện |
| Asset | Build, localizeScript, hooks | Đã hoàn thiện |
| Template | PathValidator, template_fallback, parent theme doc | Đã hoàn thiện |
| Security | PathValidator, doc quy tắc trong RENDER_ARCHITECTURE | Đã hoàn thiện |
| Tài liệu | RENDER_API_GUIDE, RENDER_HOOKS, CHANGELOG, RENDER_ARCHITECTURE, View_helper (enqueue_style/script) | Đã hoàn thiện |
| Test | Chưa có unit/integration | P4 dài hạn |

P1, P2, P3 (trừ Schema Types tùy chọn) và phần doc P4 đã xong. Thư viện Render đã đồng bộ, dễ bảo trì và mở rộng. P4 unit test / integration làm khi cần.
