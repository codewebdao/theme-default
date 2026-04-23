# PROGRESSING TASK – Các trang Settings (theo Settings Pages.xlsx)

Tài liệu chi tiết để code từng trang settings: tab, danh sách field, kiểu input, mô tả. **Phân biệt rõ: config theo từng ngôn ngữ vs config toàn trang (all).**

---

## Quy ước lưu trữ

| Loại | Storage lang | Ghi chú |
|------|--------------|--------|
| **Theo từng ngôn ngữ** | `APP_LANG` | Mỗi ngôn ngữ một bộ giá trị (title, tagline, meta, template email theo ngôn ngữ, …). |
| **Toàn trang (global)** | `'all'` | Một bộ giá trị dùng chung mọi ngôn ngữ (SMTP, cache, security, backup, API, …). |

---

# SHEET 1 – TỔNG QUAN (Settings Index)

- **Mục đích:** Trang danh sách nhóm settings (cards) với link tới từng trang con.
- **Đã có:** `SettingsController::index()`, `getSettingsGroups()`.
- **Cần đảm bảo:** Mỗi nhóm có: id, icon, title, description, detail, url, tabs (preview). Thứ tự nhóm theo danh sách trong file (General → Email → Media → … → Developer).

---

# SHEET 2 – 1. GENERAL SETTINGS

- **Lưu trữ:** **THEO TỪNG NGÔN NGỮ** (title, description, tagline, brand có thể khác mỗi ngôn ngữ).  
  Riêng **Default Language**, **URL canonical**, **Timezone**, **Date/Time/Week** có thể xem là toàn site (tùy product: nếu multi-language thì default_language thường global).

## Tab và fields

### TAB 1: Site Identity (1. Site Identity)
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| Title | title | text | Nhập title trang web |
| Description | description | text | Mô tả trang web |
| Favicon | favicon | image | Biểu tượng trang web |
| Logo | logo | image | Logo trang web |
| Tagline | tagline | text | Khẩu hiệu / tagline |
| Brand | brand | text | Thương hiệu |
| Button Export/Import Settings | (không lưu) | button | Import/Export setting từ file (backup, di chuyển site) |

### TAB 2: URL & Language (2. URL & Languge)
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| URL | url | text | Canonical domain (www / non-www), HTTPS mặc định |
| Default Language | default_language | select | Chọn ngôn ngữ mặc định cho toàn site |

### TAB 3: Regional Setting (3. Regional Setting)
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| Timezone | timezone | select | Chọn múi giờ |
| Date Format | date_format | select | Format ngày/tháng/năm (d/m/Y, m/d/Y, …) |
| Time Format | time_format | select | Format thời gian (H:i, h:i A, …) |
| Week Starts On | start_week | select | Thứ bắt đầu của tuần (0=Chủ nhật, 1=Thứ 2, …) |

### TAB 4: Index setting (4. Index setting)
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| Đóng/mở index và follow web | enable_index | switch | Checkbox: bật = noindex/nofollow toàn site, tắt = cho phép index |

**Task:** Đối chiếu với `GeneralSettingsTrait`: thêm/bổ sung field (brand, start_week, url canonical, button Export/Import). Chuẩn hóa meta_key (site_name → title hoặc giữ site_name tùy code hiện tại).

---

# SHEET 3 – 2. EMAIL SETTINGS

- **Lưu trữ:** **SMTP & Sender:** **TOÀN TRANG (all)**. **Template & Ngôn ngữ email:** có thể **theo từng ngôn ngữ** (template subject/body theo lang).

## Tab và fields

### TAB 1: SMTP Settings
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| SMTP Host | smtp_host | text | vd: smtp.gmail.com |
| SMTP Port | smtp_port | number | 465 / 587 |
| Encryption | smtp_encryption | select | none / ssl / tls |
| SMTP Username | smtp_username | text | Email gửi |
| SMTP Password | smtp_password | password | Nên encrypt |
| SMTP Auth | smtp_auth | checkbox | true / false |
| Timeout (giây) | smtp_timeout | number | optional |
| Enable SMTP | smtp_enabled | switch | Bật/tắt |

### TAB 2: Sender Info (có thể gộp chung Tab 1)
| Tên hiển thị | meta_key | input_type | Mô tả |
|--------------|----------|------------|--------|
| Tên người gửi | email_sender_name | text | vd: Công ty ABC |
| Email người gửi | email_sender_address | email | From email |
| Email reply-to | email_reply_to | email | Nhận phản hồi |
| Chữ ký mặc định | email_signature | textarea | Optional |
| Ngôn ngữ email | email_language | select | vi / en (hoặc theo danh sách ngôn ngữ site) |

### TAB 3: Test Email (không lưu)
- Email nhận test (email)
- Tiêu đề test (text)
- Nội dung test (textarea)
- Template dùng (select, optional)  
→ Chức năng: gửi email test, không lưu vào settings.

### TAB 4: Template (REPEATER)
- **Repeater:** search/filter theo danh mục (template_category).
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Template key | template_key | text | unique, slug |
| Template Category | template_category | text (hoặc select) | category |
| Tên template | label | text | Hiển thị admin |
| Enable | enabled | switch | Bật/tắt |
| Subject | subject | text | Hỗ trợ biến |
| Nội dung HTML | html | wysiwyg | |
| Nội dung text | plain | textarea | fallback |

**Task:** Cấu hình Email: SMTP + Sender = lang `all`. Template repeater: quyết định lưu theo lang (mỗi ngôn ngữ một bộ template) hay một bộ chung; nếu theo lang thì scope `APP_LANG`. Tab Test Email: chỉ UI + action gửi test, không storage.

---

# SHEET 4 – 3. MEDIA & RESIZE

- **Lưu trữ:** **TOÀN TRANG (all)** – kích thước ảnh, compression, watermark, lazy load áp dụng chung toàn site.

## Tab và fields

### TAB 1: Image Sizes (REPEATER)
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Size key | size_key | text | unique, slug |
| Width | width | number | px |
| Height | height | number | px |
| Crop | crop | select | hard crop |
| Quality override | quality | number | optional |
| Generate WebP | webp | checkbox | override global |
| Enable | enabled | switch | Bật/tắt |

### TAB 2: Image Optimization
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Compression level | media_compression_level | select | low / medium / high |
| JPEG quality (%) | media_jpeg_quality | number | default 82 |
| PNG optimization | media_png_optimize | checkbox | lossless |
| Strip EXIF | media_strip_exif | checkbox | privacy |
| Allowed mime types | media_allowed_types | multiselect | jpg, png, webp |
| Max upload size (MB) | media_max_upload | number | |
| Auto convert WebP | media_webp_enabled | switch | |
| WebP quality (%) | media_webp_quality | number | default 80 |
| Keep original | media_keep_original | checkbox | khi convert |
| Generate WebP on upload | media_webp_on_upload | checkbox | |

### TAB 3: Watermark
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Enable watermark | media_watermark_enabled | switch | |
| Watermark image | media_watermark_image | image/media | PNG |
| Position | media_watermark_position | select | 9 vị trí |
| Opacity (%) | media_watermark_opacity | number | 10–100 |
| Scale (%) | media_watermark_scale | number | theo ảnh |
| Apply to sizes | media_watermark_sizes | multiselect | thumb, medium (lựa ở tab 1) |
| Margin (px) | media_watermark_margin | number | optional |

### TAB 4 (hoặc 5): Media Display
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Lazy load images | media_lazy_load | switch | Bật/tắt |
| Lazy load include | media_lazy_include | multiselect | content / gallery / featured |
| Lazy load exclude | media_lazy_exclude | textarea | class, selector |
| Placeholder loading | media_placeholder | select | none / blur / color |
| Require alt text | media_require_alt | checkbox | SEO |
| Auto apply alt | media_auto_alt | switch | Tự sinh alt |
| Auto apply title | media_auto_title | switch | |
| Auto set width & height | media_auto_dimension | switch | CLS fix |

**Task:** Thêm/sửa `MediaSettingsTrait`: repeater Image Sizes, đủ field Optimization + Watermark + Media Display. Storage lang = `all`; nếu hiện tại đang dùng APP_LANG thì đổi sang `all` và thêm vào `getStorageLang()`.

---

# SHEET 5 – 4. CACHING & OPTIMIZATION

- **Lưu trữ:** **TOÀN TRANG (all)** – đã triển khai `PerformanceSettingsTrait` với lang `all`.

## Tab và fields (đối chiếu với code hiện tại)

### TAB 1: Application cache (Cache General)
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Page Cache | page_cache / enable_app_cache | Boolean | Bật/tắt cache HTML |
| Object Cache | object_cache / object_cache | Boolean | Cache query DB (Redis/Memcached) |
| TTL | cache_ttl | number | Thời gian cache (giây) |
| Cache Key Prefix | cache_key_prefix | text | Tránh trùng cache giữa site |
| Exclude URL | exclude_url | textarea | Danh sách URL không cache |
| Exclude Cookie | exclude_cookie | textarea | Cookie không áp dụng cache |
| Device Variant Cache | device_variant_cache | checkbox | Cache riêng mobile/desktop |
| User Variant Cache | user_variant_cache | checkbox | Cache guest / logged-in |
| Purge Trigger | purge_cache | button | Nút xóa cache thủ công |
| Minify CSS | minify_css | switch | |
| Minify JS | minify_js | switch | |
| Merge CSS | merge_css | switch | |
| Merge JS | merge_js | switch | |
| Cloudflare cache | delete_cache_cloudflare | button | Xóa cache Cloudflare |

### TAB 2: Nginx Cache (nếu dùng)
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| FastCGI Cache | fastcgi_cache | Boolean | Cache PHP bằng Nginx |
| Proxy Cache | proxy_cache | Boolean | Cache reverse proxy |
| FastCGI Cache Key | fastcgi_cache_key | text | Key tạo cache |
| FastCGI Cache Valid | fastcgi_cache_valid | number | Thời gian cache (giây) |
| FastCGI Cache Use Stale | fastcgi_cache_use_stale | checkbox | Dùng cache cũ khi backend lỗi |

### TAB 3: Database Optimization (Redis / Memcached) – Cache Drivers
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Enable Object Cache | object_cache_enabled | Boolean | Bật/tắt cache DB |
| Driver | cache_driver | select | redis / memcached / files |
| Host | cache_host | text | IP Redis/Memcached |
| Username | cache_username | text | Redis ACL user (nếu có) |
| Port | cache_port | number | vd: 6379 |
| Password | cache_password | password | Mã hóa |
| DB Index | cache_db_index | number | Phân vùng Redis (0–15) |
| Cache Params | cache_params | textarea | Tham số nâng cao |
| Cache URI | cache_uri | textarea | URI được cache |
| TTL | cache_ttl | number | Thời gian cache (giây) |
| Connection Timeout | connection_timeout | number | Thời gian chờ kết nối tối đa |

**Task:** Rà soát PerformanceSettingsTrait: đủ field theo bảng trên; thêm tab Nginx (nếu cần); nút Purge / Cloudflare gọi logic backend; giữ storage lang `all`.

---

# SHEET 6 – 5. SEO & SITE META

- **Lưu trữ:** **Phần lớn THEO TỪNG NGÔN NGỮ** (meta pattern, sitemap per lang, breadcrumb text, local business theo lang). Một số option toàn site: Enable sitemap index, Redirect types, Connect (GSC/GA4) có thể global.

## Cấu trúc theo Excel (9 nhóm con)

1. **General** – Site Identity, Domain, Time & Locale, Language, Indexing, Table of Contents  
2. **Titles & Meta** – Posts, Pages, Categories, Authors (group/repeater); Robots Meta; Empty Taxonomy auto noindex  
3. **SEO General** – Remove category base, External/Image links nofollow, External target, Canonical control  
4. **Local SEO** – company, site_address, site_phone, site_email, site_url, site_logo, site_open_hours, site_socials (repeater), Sitemap Local  
5. **Sitemap** – Enable sitemap index, posts, pages, categories, authors, local, image; Exclude noindex  
6. **Redirects** – redirect_url (repeater CRUD), redirect_status, redirect_regex, auto_redirect, Import/Export, 404 Monitor (trang riêng), Redirect Logs  
7. **Breadcrumbs** – enable_breadcrumbs, breadcrumbs_struct, schema_breadcrumbs, breadcrumb_separator  
8. **Connect** – Google Search Console, GA4, Tracking Status (verify/check)  
9. **Social Links** – (có thể trùng Social & API page)

## Tab đề xuất cho form Settings

### TAB 1: General & Titles
- enable_index, enable_toc  
- meta_posts, meta_pages, meta_categories, meta_authors (group hoặc repeater: title pattern, meta description, robots)  
- meta_robots, auto_noindex  

### TAB 2: SEO General & Local
- remove_category_base, external_links_nofollow, image_links_nofollow, external_links_target, canonical_control  
- company, site_address, site_phone, site_email, site_url, site_logo, site_open_hours, site_socials (repeater)  

### TAB 3: Sitemap
- enable_sitemap, enable_sitemap_posts, enable_sitemap_pages, enable_sitemap_categories, enable_sitemap_authors, enable_sitemap_local, enable_sitemap_image, auto_exclude_noindex  

### TAB 4: Redirects
- redirect_url (repeater: from, to, status), redirect_status (default), redirect_regex, auto_redirect; button Import/Export  

### TAB 5: Breadcrumbs & Connect
- enable_breadcrumbs, breadcrumbs_struct, schema_breadcrumbs, breadcrumb_separator  
- (Connect: GSC, GA4 – có thể chỉ hiển thị link/embed, không lưu nhiều field)  

**Task:** Tách/ghép tab cho SeoSettingsTrait; thêm đủ field theo bảng; repeater redirect + group meta; storage phần lớn theo `APP_LANG`, chỉ những field “cấu hình kỹ thuật chung” mới dùng `all` nếu cần.

---

# SHEET 7 – 6. API (Social & API)

- **Lưu trữ:** **TOÀN TRANG (all)** – OAuth, API key, Webhook là cấu hình kỹ thuật chung.

## Tab và fields

### TAB 1: OAuth Login
| meta_key | input_type | Ghi chú |
|----------|------------|--------|
| provider_id | text | ID Of Social |
| access_token | text | Call Api provider |
| refresh_token | text | Get Token New |
| expires_in_auth | text | Usage Time |
| email_provider | text | Email Provider |
| name_user | text | Name User |
| avatar_user | text | Images User |
| scope | text | Access Token |
| provider | text | Social Service |
| user_id | text | Connect User CMS |

(Ghi chú: thường chỉ lưu config app id/secret; token lấy runtime. Cần làm rõ field nào thật sự lưu trong settings.)

### TAB 2: API Keys
| meta_key | input_type | Ghi chú |
|----------|------------|--------|
| url_api_key | text | Url Api Key |
| api_secret_key | text | Api Secret Key |
| expires_at_key | text | Usage Time |

### TAB 3: Webhooks
| meta_key | input_type | Ghi chú |
|----------|------------|--------|
| url_endpoint | text | Url Endpoint |
| secret_token | text | Secret Token |
| is_active_hook | text/switch | Status Hook |

**Task:** SocialApiSettingsTrait: bổ sung tab OAuth (chỉ các field config cần thiết), API Keys, Webhooks; storage lang `all`. Có thể thêm getStorageLang('social_api') = 'all' trong SettingsService.

---

# SHEET 8 – 8. BACKUP

- **Lưu trữ:** **TOÀN TRANG (all)**.

## Tab 1: Backup
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Enable Backup | backup_enable | Boolean | Bật/tắt backup |
| Schedule Frequency | backup_schedule_frequency | select | daily / weekly / monthly |
| Retention Count | backup_retention_count | number | Số bản backup giữ lại |
| Backup Database | backup_include_database | checkbox | Backup kèm database |
| Storage Driver | backup_storage_driver | select | local / s3 / gdrive / gcs |
| Encrypt Password | backup_encrypt_password | password | Mã hóa file zip |

**Task:** Tạo BackupSettingsTrait (hoặc nhóm Backup trong Security): 1 tab, đủ field trên; đăng ký type `backup` trong SettingsService; Controller action `backup()`; storage lang `all`. Nếu gộp vào Security thì thêm tab Backup trong SecuritySettingsTrait.

---

# SHEET 9 – 7. SECURITY

- **Lưu trữ:** **TOÀN TRANG (all)**.

## TAB 1: Login
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Login Max Attempts | login_max_attemps | number | Số lần đăng nhập sai tối đa trước khi khóa tạm thời |
| Login Lockout Time | login_lockout_time | number | Thời gian bị khóa (phút/giây) |
| Admin Url | admin_url | text | Đường dẫn đăng nhập admin tùy chỉnh (tránh dò url) |

## TAB 2: Captcha & 2FA
| Tên hiển thị | meta_key | input_type | Ghi chú |
|--------------|----------|------------|--------|
| Enable 2FA | 2fa_enable | boolean | Bật/tắt xác thực 2 yếu tố (OTP) |
| Captcha Type | captcha_type | select | Loại captcha (login, register, comments, form) |
| Public Key | captcha_public_key | text | Key public captcha |
| Secret Key | captcha_secret_key | text | Key secret để verify |
| Enable Captcha | captcha_enable | boolean | Bật/tắt captcha |

**Task:** SecuritySettingsTrait: 2 tab (Login, Captcha & 2FA), đủ field; meta_key `2fa_enable` nên đổi thành `twofa_enable` hoặc `enable_2fa` (tránh tên bắt đầu số). Storage lang `all`.

---

# SHEET 10 – 8. DEVELOPER TOOLS

- **Lưu trữ:** **TOÀN TRANG (all)**.  
- Excel sheet 10 không liệt kê chi tiết field; giữ theo code hiện tại (REST API, GraphQL, Debug & Logs, Advanced) và bổ sung theo nhu cầu (log level, maintenance mode, custom config, v.v.).

**Task:** DeveloperSettingsTrait: giữ cấu trúc hiện tại; thêm/sửa field nếu có spec bổ sung; storage lang `all`.

---

# TÓM TẮT PHÂN QUYỀN LƯU TRỮ (STORAGE LANG)

| Trang | Lang lưu trữ | Ghi chú |
|-------|--------------|--------|
| General | **Theo từng ngôn ngữ** | title, description, tagline, brand, logo, favicon theo lang; default_language/timezone/date có thể global tùy quyết định product |
| Email | **SMTP/Sender: all**. Template: **theo ngôn ngữ** (nếu có template theo lang) | |
| Media | **all** | Sizes, optimization, watermark, lazy load chung toàn site |
| Caching (Performance) | **all** | Đã implement |
| SEO | **Theo từng ngôn ngữ** (đa số); một số option kỹ thuật có thể all | |
| API (Social & API) | **all** | OAuth, API key, Webhook |
| Security | **all** | |
| Backup | **all** | |
| Developer | **all** | |

---

# CHECKLIST TRIỂN KHAI (PROGRESSING)

- [x] **General:** Bổ sung field (brand, start_week, url canonical); chuẩn hóa meta_key (title, description, favicon, logo, tagline, brand); 4 tab Site Identity, URL & Language, Regional, Index; giữ per-lang. (Button Export/Import: chưa implement – cần action riêng.)
- [x] **Email:** Tab SMTP, Sender, Test Email (hint), Template; đủ field SMTP/Sender/Template; storage = all. (Repeater template: hiện dùng single set + JSON; repeater đầy đủ để sau.)
- [x] **Media:** Image Sizes (JSON); đủ field Optimization, Watermark, Media Display; storage = all; form_options app_lang ['all'].
- [x] **Caching (Performance):** Đã xử lý trước; giữ all. (Nginx tab / Purge / Cloudflare: tùy chọn sau.)
- [x] **SEO:** Tab General & Titles, SEO General & Local, Sitemap, Redirects, Breadcrumbs & Connect; đủ field; redirect_url dạng textarea (from|to|status); storage = APP_LANG.
- [x] **API (Social & API):** Tab Social Links, OAuth, Analytics, API Keys, Webhooks; thêm url_api_key, api_secret_key, expires_at_key, url_endpoint, secret_token, is_active_hook; storage = all; form_options app_lang ['all'].
- [x] **Backup:** Gộp vào Security (tab Backup); đủ field backup_enable, backup_schedule_frequency, backup_retention_count, backup_include_database, backup_storage_driver, backup_encrypt_password; storage = all.
- [x] **Security:** Tab Login, Captcha & 2FA, Backup, Firewall; đủ field login_max_attemps, login_lockout_time, admin_url, enable_2fa, captcha_type, captcha_public_key, captcha_secret_key, captcha_enable + backup + firewall; storage = all; form_options app_lang ['all'].
- [x] **Developer:** Giữ cấu trúc; thêm form_options app_lang ['all']; storage = all.
- [x] **SettingsService:** getStorageLang() trả về `all` cho performance, media, email, social_api, security, developer; `APP_LANG` cho general, seo.
- [x] **Controller:** Đã có action riêng: general, email, media, performance, seo, social_api, security, developer. (Backup = tab trong security, không cần action riêng.)

---

*Tài liệu dựa trên file Settings Pages.xlsx (sheet 1–10). Khi implement từng trang, cập nhật checklist và bổ sung chi tiết field (options, default_value, validation) trực tiếp trong từng Trait.*
