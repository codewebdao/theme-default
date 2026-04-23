# Changelog – Render Library

Tóm tắt thay đổi API và hành vi (phiên bản / mốc).

---

## [2.0.0] – Hiện tại

### Entry & cấu trúc

- Head, View, Schema, Minify cùng cấp trong `system/Libraries/Render/`.
- Schema entry tại `Render/Schema.php` (không nằm trong thư mục Schema/).
- View::minify($html) – facade gọi Minify::html().

### View

- API: make, with, render, include, includeIf, share, getShared, clearShared, namespace, getNamespace, clearCache, forgetCache.
- Delegation: addCss, addJs, inlineCss, inlineJs, localizeScript, css(), js(), clearAssets → AssetManager; setTitle, setDescription, … renderHead, addSchema → Head.
- getShared($key = null), clearShared() – cho test/cleanup.

### Head

- Head::clear() – reset toàn bộ title, meta, schemas, canonical, links (cho test hoặc nhiều head/request).

### Schema

- setCurrentContext, getCurrentContext, clearCurrentContext; render(), get().
- Hooks: schema.context, schema.build, schema.{type}, schema.render.

### Asset (AssetManager)

- addCss, addJs, inlineCss, inlineJs, localizeScript; css(), js(); clearAssets.
- localizeScript($handle, $objectName, $data, $area = null, $in_footer = true) – inject biến JS từ PHP (wp_localize_script style).
- Không có removeCss/removeJs – can thiệp qua filter render.css.before / render.js.before.
- Build: combine/minify theo option; getBuiltAssetUrl (1 URL hoặc mảng khi không combine); signature có combine.

### Template (PhpTemplate)

- Filter render.template_fallback – khi template không tìm thấy, plugin có thể trả về path thay thế (string) hoặc null.
- Parent theme: Config/Config.php `return ['parent' => 'theme-name'];`.

### Minify

- Filter minify.html.before, minify.html.after – can thiệp HTML trước/sau minify.

### Security (PathValidator)

- isValidTemplateName, isValidComponentName, isValidArea, validateResolvedPath.
- Quy tắc: whitelist ký tự, max length 500, chặn traversal, null byte, absolute path, URL; allowed bases: PATH_THEMES, PATH_PLUGINS, application/Blocks.

### Tài liệu

- RENDER_API_GUIDE.md – bảng hàm helper (add_css, add_js, localize_script, view_head, …) và Class API cho developer.
- RENDER_HOOKS.md – danh sách đầy đủ filter/action.
- RENDER_ARCHITECTURE.md – luồng, Hooks link, Security (PathValidator), Parent theme, Asset versioning.
- View_helper (system/Helpers): add_css, add_js, enqueue_style, enqueue_script, inline_css, inline_js, localize_script, view_css, view_js, view_og_array, view_twitter_card, view_render (và view_head, view_title, view_schema, view_header, view_footer, assets_head, assets_footer).
- PHP 7.4+; extension json, mbstring.

---

Khi đổi API hoặc behavior (breaking hoặc quan trọng), cập nhật mục tương ứng trên đây và ghi rõ ngày hoặc version nếu cần.
