# Builders — `giao-dien-education` (asset pipeline)

**Architecture:** [`tools/theme-assets/PLAN.md`](../../../../tools/theme-assets/PLAN.md) (rev **2.2**).

**Scope:** Node toolchain **chỉ trong theme** này. **Production PHP không đọc `builders/`** — chỉ artifact dưới `../assets/`.

**CSS:** mỗi file `src/css/entries/<tên>.css` → `../assets/css/<tên>.css` khi `npm run build`. Partial dùng chung: `tailwind.css` (có `@tailwind base` — trang chỉ một bundle Tailwind), `_tailwind-addon.css` (không base — dùng sau `index.css`). **Không** JS bundling, **không** manifest.

| Entry | Partial | Ghi chú trang |
|-------|---------|----------------|
| `usage-guide.css` | addon | `page-usage-guide.php` + `index.css` |
| `download.css` | standalone | `page-download.php` |
| `contact.css` | standalone | `page-contact.php` |
| `blog.css` | standalone | `archive-blog.php` |
| `blog-detail.css` | standalone | `single.php` |
| `review-cms.css` | addon | `archive-reviews.php` (**thêm** `index.css`); `single-reviews.php` sau `blog-detail.css` |
| `tutorial.css` | addon | `archive-tutorial.php`, `single-tutorial.php` + `index.css` |
| `features.css` | addon | `page-features.php` + `index.css` |
| `page.css` | addon | `page.php` + `index.css` |

CSS **thuần** (chưa qua Tailwind builders): `faq-accordion.css`, `features-scroll-reveal.css`, `see-how-yt.css`, `404-page.css`, …

**Tham chiếu (read-only):** `tools/theme-assets/laragon/` — markup (`*.html`), `globals.css` / `styles.css` / `home-theme.css` dùng để **đồng bộ partial + Tailwind trong `builders/src/css/`**, rồi `npm run build`. **Không** sửa trực tiếp repo laragon trong quy trình theme; mọi thay đổi CSS chuẩn nằm dưới `builders/` (vd. `_usage-doc-globals.css` cắt từ tinh thần `globals.css` @layer base).

---

## `@import` / PostCSS

`postcss-import` chạy **trước** Tailwind (`postcss.config.cjs`) để gộp partial vào artifact — tránh `@import` tương đối còn sót trong file build (browser resolve theo `assets/css/` → 404).

**Partial:** `_home-tokens.css`, `_usage-doc-globals.css` được kéo vào `tailwind.css` / `_tailwind-addon.css`.

---

## Commands

```bash
cd content/themes/giao-dien-education/builders
npm ci
npm run build
```

**Artifact:** `content/themes/giao-dien-education/assets/css/*.css` tương ứng từng entry (vd. `usage-guide.css`, `download.css`, …).

`npm run build` (và `npm run build:preview`, tương đương) chạy `scripts/build-page-css.cjs` với **`cross-env`** để hoạt động trên **Windows và Linux/macOS**: mặc định bật `THEME_CSS_PREVIEW=1` và `THEME_PREVIEW_SKIP_SSL_VERIFY=1`. Entry có `@page-url` thì fetch HTML → `.build-preview/<slug>.html` → JIT thu hẹp theo DOM; entry không có tag vẫn dùng content glob PHP.

**Build không preview (chỉ glob `tailwind.config.cjs`, không fetch):** `npm run build:wide`.

**Biến môi trường (khi dùng preview / `@page-url`):**

| Biến | Ý nghĩa |
|------|--------|
| `THEME_CSS_PREVIEW=1` | Bật fetch + JIT theo HTML (đã gắn sẵn trong `npm run build`). |
| `THEME_PREVIEW_BASE_URL` | Bắt buộc nếu `@page-url` là đường dẫn tương đối (vd. `/usage-guide`). |
| `THEME_PREVIEW_USE_CURL=1` | Dùng `curl` thay `fetch`. |
| `THEME_PREVIEW_SKIP_SSL_VERIFY=1` | Bỏ verify TLS (đã gắn sẵn trong `npm run build`). |
| `CURL_BIN` | Đường dẫn `curl`. |
| `THEME_PREVIEW_COOKIE` | Cookie (trang cần session). |
| `THEME_PREVIEW_TIMEOUT_MS` | Timeout ms (mặc định 30000). |

Ví dụ build preview (site chạy local, URL tương đối trong `@page-url`):

```bash
THEME_PREVIEW_BASE_URL=http://laragon.test npm run build
```

```bash
npm run dev
```

**`dev`:** `postcss-cli --watch` + glob PHP trong `tailwind.config.cjs` — không fetch HTML.

---

## `@page-url` (tuỳ chọn)

Dùng khi chạy **`npm run build`** (mặc định đã bật preview). Trong comment đầu entry:

```css
/**
 * @page-url /usage-guide
 */
```

Hoặc URL tuyệt đối (không cần `THEME_PREVIEW_BASE_URL`). `npm run build` thường **bỏ qua** URL này và dùng content rộng.

---

## Tailwind `content` globs (thực tế)

Trong `tailwind.config.cjs` (đường dẫn qua `path.join(__dirname, …)`):

| Glob | Mục đích |
|------|----------|
| `../*.php` | Template gốc theme |
| `../parts/**/*.php` | Partials (gồm `parts/documentation/*`) |
| `../common/**/*.php` | Auth / errors / email |

**Không scan:** `../assets/**`, `../builders/**` (mặc định PLAN). `../config/**` chưa gồm (slice tối thiểu; tab/documentation không nằm trong `config/`).

---

## Bundle registry (PLAN D.4)

| logical_name | asset_type | source_entry | built_output | php_loader_call | page_or_template_scope | notes |
|--------------|------------|--------------|--------------|-----------------|-------------------------|-------|
| `home-index` | `css` | `src/css/entries/home-index.css` | `assets/css/home-index.css` | `View::addCss('home-index', 'css/home-index.css', [], ASSETS_VERSION)` | e.g. `front-page.php`, `index.php` | **Hàng ví dụ PLAN** — bundle này **chưa** build từ `builders/`; vẫn là `css/index.css` / asset legacy trên các trang khác. |
| `home-index` | `js` | `src/js/entries/home-index.js` | `assets/js/home-index.js` | `View::addJs('home-index', 'js/home-index.js', [], ASSETS_VERSION)` | same as CSS when paired | **P1+** — chưa có pipeline JS. |
| `usage-guide` | `css` | `src/css/entries/usage-guide.css` | `assets/css/usage-guide.css` | `View::addCss(..., THEME_VER)` | `page-usage-guide.php` | addon + `index.css` |
| `download` | `css` | `entries/download.css` | `assets/css/download.css` | THEME_VER | `page-download.php` | standalone |
| `contact` | `css` | `entries/contact.css` | `assets/css/contact.css` | THEME_VER | `page-contact.php` | standalone |
| `blog` | `css` | `entries/blog.css` | `assets/css/blog.css` | THEME_VER | `archive-blog.php` | standalone |
| `blog-detail` | `css` | `entries/blog-detail.css` | `assets/css/blog-detail.css` | THEME_VER | `single.php` (+ `single-reviews` trước `review-cms`) | standalone |
| `review-cms` | `css` | `entries/review-cms.css` | `assets/css/review-cms.css` | THEME_VER | `archive-reviews.php`, `single-reviews.php` | addon (cần base từ `index` hoặc `blog-detail`) |
| `tutorial` | `css` | `entries/tutorial.css` | `assets/css/tutorial.css` | THEME_VER | `archive-tutorial.php`, `single-tutorial.php` | addon |
| `features` | `css` | `entries/features.css` | `assets/css/features.css` | THEME_VER | `page-features.php` | addon |
| `page` (cms) | `css` | `entries/page.css` | `assets/css/page.css` | THEME_VER | `page.php` | addon |

---

## `build-smoke` (đã gỡ)

Bundle smoke **đã xóa** sau khi có bundle thật `usage-guide`: cùng trang Usage Guide, smoke không còn vai trò riêng. Không giữ file tạm để tránh hai pipeline song song gây nhầm.

---

## Verification

1. `cd content/themes/giao-dien-education/builders && npm ci && npm run build`
2. Tồn tại các file output ứng từng entry (vd. `usage-guide.css`, `download.css`, …), `grep -c '@import'` từng file → **0**.
3. Mở vài route (usage-guide, download, blog…) — kiểm tra layout; `archive-reviews` cần `index.css` + `review-cms.css`.

---

## Rollback

1. Khôi phục `page-usage-guide.php` về enqueue `usage-guide` cũ (và đổi cách `?ver=` nếu muốn đúng trạng thái cũ).
2. Khôi phục nội dung `assets/css/usage-guide.css` từ git (file legacy cũ lớn) hoặc bản backup.
3. Xóa / revert `builders/src/css/entries/usage-guide.css`, `partials/_home-tokens.css`, chỉnh lại `tailwind.config.cjs` / `package.json` nếu cần.

---

## Chưa làm (cố ý)

- JS bundling, manifest
- `index.css` / `home-index` từ builders
- Mở rộng scan Tailwind ngoài 3 glob hiện tại
