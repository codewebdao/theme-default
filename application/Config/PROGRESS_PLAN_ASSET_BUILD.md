# PROGRESS PLAN: Asset Build System (Dynamic, Signature-Based, Option-Based)

**Mục đích:** Tài liệu thiết kế Asset Build System. Danh sách CSS/JS do **Controller + View + Plugins** khai báo (addCss/addJs); dùng **signature (md5 list)** để tái dùng file build; **cron/CLI** build + cleanup theo Settings admin. **Metadata build lưu bằng get_option/set_option** (Storage_helper, scope application); **không** dùng file manifest.json / registry.json / snapshots cho runtime lookup. File build vẫn ghi vào **content/assets/**; serve **tĩnh** qua web server (không qua PHP).

**Phân biệt thư mục (chốt cứng):**
- **content/assets/** = DUY NHẤT nơi lưu các **file build** (minified, combined). URL public = public_url('content/assets/{area}/{filename}').
- **Không dùng writable/build.** Chỉ ghi file build vào **content/assets/**; metadata trong **options** (get_option/set_option).

---

## 0. Tóm tắt luồng (flow tổng quan)

1. **Nguồn asset:** Mỗi page dùng bao nhiêu CSS/JS do **Controller + View + Plugins** khai báo tại runtime (`AssetManager::addCss/addJs`). **Nhận diện asset** bất kể path: theme, plugin, **URL ngoài**. **Self-host external:** Tải URL ngoài về `content/assets/external/` (whitelist skip cho analytics, Facebook SDK, …). **Output built:** `content/assets/{area}/`.
2. **Signature (assetsId):** md5(theme_name, theme_version, area, location, **normalized_asset_list**, **asset_build_version**). Cùng list + version → cùng file build.
3. **Runtime:** `get_option(option_key)` → entries. Tính **assetsId** từ list hiện tại. Lookup entry theo `id === assetsId`. Nếu **không có** hoặc **build === null:** output raw + **set_option chỉ khi chưa có** entry (thêm mới). Nếu **có build:** output link/script tới file build. **Chỉ set_option khi cần:** thêm entry mới (cache miss) hoặc cron cập nhật build; không ghi option mỗi request khi hit.
4. **Cron:** Đọc options; với entry **build === null**: lock; resolve assets; **self-host external fail → fallback URL ngoài, không block build toàn entry**; minify/merge theo Settings (có thể ra **nhiều file** hoặc **1 file** tùy combine + exclude); ghi `content/assets/{area}/`; **set_option** `entry.build` (có **hash**); **last_seen** cập nhật. **Cleanup:** TTL + LRU từ entries.
5. **Inline CSS/JS:** Không combine; không ảnh hưởng signature; minify tại runtime theo option (giới hạn ≤ 10KB).

---

## 1. Nguyên tắc: Danh sách asset do runtime quyết định (không config tĩnh)

- Mỗi page dùng bao nhiêu CSS/JS do **Controller + View + Plugins** gọi `AssetManager::addCss/addJs` tại runtime.
- **Signature (assetsId)** quyết định **content**. Output options (preload, defer, async) **không** nằm trong signature — lấy từ Settings khi render.
- **Metadata build:** Lưu trong **options** qua `get_option` / `set_option` (Storage_helper, scope application). **Không** dùng file manifest.json, registry.json hay thư mục snapshots/ cho runtime hoặc cho nguồn truth build.
- **Option keys (đề xuất):** Một key per vị trí: `asset_build_css_head`, `asset_build_css_footer`, `asset_build_js_head`, `asset_build_js_footer`. Mỗi key lưu array các entry (id, data, build, last_seen).

---

## 2. Hiện trạng AssetManager (tóm tắt)

- **Đăng ký:** addCss / addJs theo area, location (head | footer).
- **Nội bộ:** `$styles[area][location][handle]`, `$scripts[area][location][handle]`.
- **Render:** Sort theo deps → lookup build qua **get_option** → output 1 link/script (nếu có build) hoặc từng file (raw). Output options (defer, preload, async) từ Settings.
- **Nhận diện asset:** Theme relative, plugin relative, absolute URL, path bất kỳ; source_type local | external. Self-host external: option + whitelist skip (§2.1).
- **Area:** Frontend, Backend, Common, … (enum).

### 2.1 Self-host external assets

- **Mục đích:** Tải asset từ URL ngoài về `content/assets/external/`.
- **Cơ chế:** Cron build: nếu asset là external URL → check whitelist skip; nếu không skip và option bật → fetch, lưu `content/assets/external/`, metadata (source_url, fetched_at). **external_ttl_days** → re-fetch khi hết hạn.
- **External self-host failure (chốt):** Nếu CDN/nguồn ngoài **chết** (fetch fail): **fallback dùng URL ngoài** (giữ link gốc trong output); **không block build toàn entry**. Entry vẫn build được với các asset local + asset external đã fetch thành công; asset external fail → output riêng link ngoài hoặc bỏ qua tùy policy. Cron **không** dừng build cả signature vì 1 URL ngoài lỗi.
- **Whitelist skip:** google-analytics.com, connect.facebook.net, gtag, … (giữ link ngoài).
- **Settings:** self_host_external_assets, self_host_skip_whitelist, external_ttl_days.

---

## 3. Luồng đề xuất (option-based)

### 3.1 Option keys và cấu trúc entry

- **Keys (scope application):**
  - `asset_build_css_head` — array entry cho CSS head
  - `asset_build_css_footer` — CSS footer
  - `asset_build_js_head` — JS head
  - `asset_build_js_footer` — JS footer
- **Mỗi key:** Array các entry. **Mỗi entry:**
  - **id** — assetsId = md5(theme_name, theme_version, area, location, normalized_list, asset_build_version) (12 ký tự hoặc full).
  - **data** — list assets **normalized** (cấu trúc chuẩn §3.1b); dùng cho cron build và fallback.
  - **build** — `null` nếu chưa build; hoặc **một object** hoặc **mảng các file** (tùy combine/minify/exclude):
    - **hash** (bắt buộc) — hash signature/version cho cache (vd. 12 ký tự).
    - **path** — relative trong `content/assets/{area}/` (1 file khi merge) hoặc không dùng khi build ra nhiều file.
    - **url** — URL public (1 file) hoặc **files** — mảng `[{ path, url, hash? }, ...]` khi **không merge** (minify từng file, hoặc exclude một số khỏi combine). **Build chưa hẳn chỉ 1 file:** cron/cli dựa trên tùy chọn **minify** (on/off), **merge** (on/off), **exclude** → có thể tạo **nhiều file** (mỗi asset 1 file) hoặc **1 file** (merge, không loại trừ asset nào khỏi combine).
    - **time** — timestamp (optional).
  - **last_seen** (optional) — timestamp request cuối; dùng cho TTL/LRU cleanup.

**3.1b Chuẩn hóa normalized_asset_list (spec — bắt buộc):**

- **Lưu (trong entry.data và dùng cho signature):** Chỉ các field ảnh hưởng **content**:
  - Mỗi phần tử là object: `src`, `media` (CSS only), `type` (`local` | `external`).
  - Ví dụ: `[ [ 'src' => '/themes/a/style.css', 'media' => 'all', 'type' => 'local' ], [ 'src' => 'https://cdn.example.com/lib.css', 'media' => 'all', 'type' => 'external' ] ]`
- **Không lưu (không đưa vào normalized list):**
  - **version** / query string (không ảnh hưởng nội dung build cho signature).
  - **preload** flag (chỉ output option).
  - **async** / **defer** (chỉ output option).
- Sort theo thứ tự sau dependency resolution; order freeze trước khi hash.

### 3.2 Runtime (mỗi request)

1. Controller + View + Plugins gọi addCss/addJs → AssetManager có list đầy đủ, đã sort deps.
2. **Chuẩn hóa list:** Chỉ field ảnh hưởng content: src, media (CSS). **Signature input:** theme_name, theme_version, area, location, normalized_list, asset_build_version. **assetsId** = md5(stableJsonEncode(signature_input)).
3. **Lookup option:** `get_option(option_key)` với option_key theo type + location (vd. `asset_build_css_head`). Trả về `[]` nếu chưa có.
4. **Tìm entry:** entry nào `id === assetsId`.
5. **Hành vi:**
   - **Không có entry hoặc build === null:** Output **từng** link/script (raw). **set_option chỉ khi chưa có:** ghi entry `{ id, area, data, build: null, last_seen }` khi entry chưa tồn tại trong option; nếu đã có thì không ghi lại.
   - **Có entry và build !== null:** Output link/script tới file build. Không ghi option (chỉ đọc get_option).
6. **Inline CSS/JS:** Không đưa vào option; minify runtime theo option (limit ≤ 10KB).

### 3.3 Cron (hoặc CLI)

1. **Kích hoạt:** Cron hoặc admin "Rebuild assets" (CLI/controller).
2. **Build:** Đọc options, resolve, ghi content/assets/, set_option (không dùng lock file).
3. **Đọc Settings:** minify_*, combine_*, exclude lists, self_host_*, build_ttl_days, build_lru_max.
4. **Bốc danh sách cần build:** Với mỗi option_key (css_head, css_footer, js_head, js_footer): `get_option(option_key)` → entries. Lọc entries có **build === null** (hoặc theo policy rebuild: build cũ nhưng file mất, hoặc asset_build_version đổi).
5. **Skip logic:** Nếu entry đã có build và file tồn tại trong `content/assets/{area}/{path}` và mtime ổn → skip (tránh rebuild thừa).
6. **Với mỗi entry cần build:**
   - Acquire per-signature lock (optional).
   - Resolve assets từ **entry.data** (local + external). **Self-host external:** Nếu fetch URL ngoài **fail** → fallback giữ URL ngoài (hoặc skip asset đó); **không block** build toàn entry. Build tiếp các asset còn lại.
   - Theo Settings: **minify** (on/off), **merge** (on/off), **exclude** → có thể tạo **1 file** (merge, không exclude) hoặc **nhiều file** (không merge, hoặc một số asset bị exclude khỏi combine). **build** lưu **hash**; path/url (hoặc mảng files khi nhiều file).
   - Atomic ghi: `content/assets/{area}/` file .tmp → rename (hoặc nhiều file).
   - **set_option:** Cập nhật entry: `build = { hash, path, url, time }` hoặc `build = { hash, files: [{ path, url, hash? }, ...], time }`; `last_seen = time()`.
   - Release lock.
7. **Cleanup 2 tầng:** (Có thể đọc lại toàn bộ entries từ 4 option keys.)
   - **TTL:** Entry có last_seen quá cũ (now - last_seen > build_ttl_days) → xóa file build trong content/assets/, set entry.build = null hoặc xóa entry.
   - **LRU cap:** Tổng file build > build_lru_max → xóa ít dùng nhất (sort theo last_seen).
   - **Safety window:** Không xóa file có mtime mới hơn (deploy song song).
8. **Error policy:** Snapshot/entry.data rỗng hoặc resolve fail → không ghi file rỗng; log; có thể set build_failed flag trên entry (optional). Không silent fail.

### 3.4 Serve file build: tĩnh (không qua PHP)

- File build ghi vào **content/assets/{area}/{filename}** (vd. `Frontend/head.a146c51.min.css`).
- **build.url** trong entry = public_url('content/assets/'.$relPath).

### 3.5 Runtime: không lock I/O, không file_exists trên built path

- **Request path:** Chỉ get_option (metadata) + nếu có build → output URL; không có build → output raw assets. **Không** gọi file_exists/is_file lên file build (tin option).
- **Không lock I/O:** AssetManager/AssetsService không dùng flock/lock file.
- get_option đã có cache trong Storage (scope).

---

## 4. Cấu trúc thư mục & file

**File build (output) — `content/assets/`:**

```
content/
  assets/
    Frontend/
      head.{hash}.min.css
      footer.{hash}.min.js
    Backend/
      ...
    Common/
      ...
    external/
      {url_hash}.min.css|js
```

**Metadata:** Chỉ **options** (get_option/set_option). Đã bỏ manifest.json, registry.json, snapshots/.

**Tóm tắt:**

| Vị trí | Nội dung |
|--------|----------|
| **content/assets/** | File build (minified, combined); self-host external |
| **Options (DB/storage)** | Entries: id, data, build, last_seen (key: asset_build_css_head, …) |

---

## 5. Signature (assetsId)

- **Input:** theme_name, theme_version, area, location, **normalized_asset_list** (spec §3.1b: chỉ `src`, `media`, `type`; **không** version, preload, async/defer; sort sau deps), asset_build_version.
- **assetsId** = md5(stableJsonEncode([...])) (substr 12 hoặc full). Cùng list + version → cùng id.
- **asset_build_version:** Chỉ tăng khi Save settings ảnh hưởng build output (combine_*, minify_*, exclude, self_host_*). **Không** tăng khi defer_js, async_css.

---

## 6. Settings admin (Performance)

- **Build/output:** minify_css, minify_js, combine_css, combine_js, defer_js, async_css, *_exclude.
- **Self-host:** self_host_external_assets, self_host_skip_whitelist, external_ttl_days.
- **Cleanup:** build_ttl_days, build_lru_max.
- **asset_build_version:** Chỉ tăng khi save build-related settings (không tăng khi render-only options).

---

## 7. Inline CSS/JS

- Không combine; không ảnh hưởng signature; không đưa vào option. Minify tại runtime theo option; **giới hạn ≤ 10KB** (lớn hơn skip minify).

---

## 8. Race condition, lock, atomicity

- **Runtime set_option:** Chỉ khi thêm entry mới (cache miss) hoặc cron cập nhật build; merge/update entry theo id.
- **Cron:** Global lock; per-signature lock (optional); ghi file .tmp → rename; set_option cập nhật build.

---

## 9. Đồng bộ Storage API

- Trong thư viện asset (AssetManager, AssetsService): Dùng **get_option** / **set_option** (Storage_helper). Scope luôn **application**. **Lang = 'all'** cho bốn key asset build (asset_build_css_head, …) vì assets giống nhau mọi ngôn ngữ; Storage hỗ trợ key 'all' (synchronous field). Không dùng option() trực tiếp cho build metadata.

---

## 10. Edge-case & Production

1. **Entry data rỗng:** Không build; không ghi file rỗng; log.
2. **Chỉ set_option khi cần:** get_option lấy giá trị; set_option chỉ khi chưa có entry (thêm mới) hoặc khi cron cập nhật build. Không ghi option mỗi request khi hit.
3. **External self-host failure:** CDN/nguồn ngoài chết → fallback URL ngoài; không block build toàn entry. Cron log per asset fail; entry vẫn build với asset còn lại.
4. **asset_build_version** chỉ tăng khi build-related settings.
5. **Cleanup safety window** tránh xóa file mới deploy.
6. **Failure visibility:** Cron log per signature; optional build_failed trên entry; Admin UI "Assets build health" (optional).
7. **LRU last_seen:** Có thể dùng last_seen trong entry (cron cập nhật khi build); cleanup đọc từ option. Runtime không cập nhật last_seen mỗi hit (giữ đơn giản).
8. **URL file build:** public_url('content/assets/{area}/{filename}').
9. **Build output:** Có thể 1 file (merge) hoặc nhiều file (không merge / exclude); entry.build chứa **hash**; path/url hoặc files[].

---

## 11. Deliverables (checklist)

1. **Options:** Keys asset_build_css_head, asset_build_css_footer, asset_build_js_head, asset_build_js_footer. Entry: id, data (normalized §3.1b), build (hash, path, url hoặc files[]), last_seen.
2. **Runtime:** get_option(option_key); assetsId; lookup entry; không có hoặc build null → raw + **set_option chỉ khi entry chưa có**; có build → output link/script. Dùng get_option/set_option (không option() cho build).
3. **Cron/CLI:** Đọc options; entry build null → resolve (external fail → fallback URL ngoài, không block entry); minify/merge theo Settings → 1 hoặc nhiều file; ghi content/assets/; set_option(entry.build có **hash**; last_seen). Cleanup TTL + LRU.
4. **URL build:** build.url = public_url('content/assets/'.$relPath); build.hash, build.url hoặc build.files.
5. **Settings:** asset_build_version, build_ttl_days, build_lru_max, external_ttl_days, exclude, self_host_*.
6. **Normalized list (spec):** Chỉ src, media, type; không version, preload, async/defer. AssetManager/AssetsService dùng get_option/set_option; self-host fail → fallback URL ngoài.

---

## 12. Tài liệu tham khảo

- **AssetManager:** `system/Libraries/Render/Asset/AssetManager.php`
- **AssetsService:** `application/Services/Asset/AssetsService.php`
- **Storage_helper:** `system/Helpers/Storage_helper.php` (get_option, set_option)
- **Settings:** `application/Services/Settings/SettingsService.php`

---

**Kết:** Plan chốt **option-based**: metadata trong get_option/set_option (asset_build_* keys). **Chỉ set_option khi cần:** get_option lấy giá trị; set khi chưa có (thêm entry) hoặc cron cập nhật build; không ghi option mỗi hit. **External fail:** fallback URL ngoài; không block build. **Build:** hash, path, url, time; có thể 1 hoặc nhiều file. **Normalized list:** src, media, type; không version, preload, async/defer. File build trong content/assets/; URL public_url('content/assets/...').

**Đã triển khai:** Option-only (không manifest.json, registry.json, snapshots). Runtime: AssetManager getBuildFromOption, recordCacheMiss setOptionEntry. Cron: AssetsService::build() loop 4 keys, runCleanup() TTL/LRU từ options. CLI: `php cmd assets:build`.
