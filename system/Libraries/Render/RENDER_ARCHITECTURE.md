# Kiến trúc Render Library

Tóm tắt luồng và trách nhiệm từng phần (sau khi bỏ Template Cache & PhpCompiler).

**Yêu cầu:** PHP 7.4 trở lên; extension thông thường (json, mbstring). Theme/plugin gọi View, Head, Schema, Asset qua API công khai.

---

## Luồng chính

```
Controller (FrontendController)
    │
    ├─► Schema::setCurrentContext($layout, $payload)
    ├─► Head\Context::setCurrent($layout, $payload)
    │
    └─► View::make($template, $data)->render()
            │
            ├─► View::getEngine() → PhpTemplate()  (không dependency)
            ├─► PhpTemplate::render($template, $data)
            │       ├─► PhpTemplate::locate($template, namespace)
            │       │       → theme (current → parent) / plugin / block
            │       └─► loadPhpFile($path, $data)  // extract + ob_start + include
            │
            └─► (nếu debugbar) trackView + injectDebugbar
    │
    (sau khi có HTML) Controller có thể gọi View::minify($html) nếu bật minify_html
```

- **View::minify($html):** Facade gọi `Minify::html()`, dùng khi option `minify_html` bật (thường gọi từ controller sau khi có output).

---

## Thành phần

| Thành phần | Trách nhiệm |
|------------|-------------|
| **View** | API công khai: `make`, `include`, `includeIf`, `share`, `exists`, `getPath`. Delegation: Assets → AssetManager, Head → Head. Xử lý lỗi render (theme 404/errors → _critical_error). |
| **PhpTemplate** | Một class: locate + render. Resolve path (theme → parent, plugin, block), validate qua PathValidator, cache locate/file_exists trong request, load PHP trực tiếp (không cache). Hooks: `render.template_name`, `render.data`, `render.output`. |
| **Head** | Title, meta, canonical, OG, Twitter, link (profile, hreflang, icon). Mặc định từ Head\Builder::build(layout, payload); view override bằng set* hoặc filter `render.head.defaults`. |
| **Head\Context** | Lưu layout + payload hiện tại (Controller set một lần). |
| **Head\Builder** | Build mảng defaults theo layout (404, front-page, search, page, single-*, archive, taxonomy). |
| **AssetManager** | Đăng ký CSS/JS/inline, resolve dependencies, output theo area (Frontend/Backend). |
| **PathValidator** | Validate tên template, namespace, path đã resolve (chống path traversal, LFI). |

---

## Hooks (dot-notation)

Danh sách đầy đủ filter/action của Render: xem **[RENDER_HOOKS.md](RENDER_HOOKS.md)**. Bảng hàm helper và API: **[RENDER_API_GUIDE.md](RENDER_API_GUIDE.md)**.

- **Template:** `render.template_name`, `render.data`, `render.output`, `render.template_found`
- **Head:** `render.head.set_title`, `render.head.title_parts`, `render.head.defaults`, `render.head.add_meta`, `render.head.meta.before`, `render.head.schema.*`, `render.head.canonical.before`, `render.head.after`
- **Schema:** `schema.context`, `schema.build`, `schema.{type}`, `schema.render`
- **Asset:** `render.css.before`, `render.css.inline.before`, `render.css.after`, `render.js.before`, `render.js.inline.before`, `render.js.after`
- **Minify:** `minify.html.before`, `minify.html.after`

**Lưu ý Asset:** Không có removeCss/removeJs; can thiệp qua filter `render.css.before` / `render.js.before`. Có **localizeScript** (AssetManager::localizeScript, View::localizeScript): inject biến JS từ PHP cho một handle (vd. ajax_url, nonce).

---

## Template path

- **Theme:** `View::namespace('Frontend'); View::make('page', $data)` → `themes/{theme}/Frontend/page.php`
- **Plugin:** `View::make('@Ecommerce/Frontend/products/index', $data)` → theme override hoặc `plugins/Ecommerce/Views/Frontend/products/index.php`
- **Block:** `View::make('#Header/default', $data)` → theme override hoặc `application/Blocks/Header/Views/default.php`

Template luôn load trực tiếp từ file (không cache, không compile). Không dùng class TemplateLocator riêng: PhpTemplate gộp locate + render.

**Parent theme:** Theme con khai báo parent trong `content/themes/{theme}/Config/Config.php`: `return ['parent' => 'tên-theme-cha'];`. PhpTemplate::detectParentTheme() đọc file này; locate tìm file ở theme hiện tại rồi mới tới parent.

---

## Security (PathValidator)

PathValidator kiểm tra tên template, namespace và path đã resolve trước khi include file:

- **Allowed bases:** PATH_THEMES, PATH_PLUGINS, `application/Blocks`. Path cuối phải nằm dưới một trong các base (realpath hoặc prefix chuẩn hóa).
- **Template name:** Chỉ cho phép ký tự `A-Za-z0-9/_:.-` và prefix `@`, `#`; max length 500. Chặn `..`, null byte, `\`, path tuyệt đối (`/`, `C:`), URL (`http://`, `file://`).
- **Component/area:** isValidComponentName, isValidArea – whitelist tên plugin/block và area (Frontend, Backend).
- **validateResolvedPath($path):** Trả về path đã chuẩn hóa nếu nằm trong allowed bases, ngược lại null (PhpTemplate sẽ throw).

---

## Asset versioning

- **addCss/addJs** có tham số `$version`: khi render từng file, version gắn vào query `?ver=...`.
- **Built URL** (khi bật combine): URL build không append `?ver=` từ từng asset; bản build được nhận diện bằng signature (hash). Cache bust khi option hoặc file thay đổi.
