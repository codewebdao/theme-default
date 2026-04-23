# PROGRESSING TASK LIST – Asset Build System (Option-Based)

**Mục đích:** Theo dõi tiến độ Asset Build System. Thiết kế **option-based**: metadata build lưu bằng **get_option / set_option** (Storage_helper, scope application). **Không** dùng manifest.json, registry.json hay snapshots/ cho runtime hay cron. File build ghi vào **content/assets/**; URL public_url('content/assets/...').

**Ref:** `PROGRESS_PLAN_ASSET_BUILD.md`

---

## Lưu ý khi CODE

1. **Signature (assetsId):** Dùng stableJsonEncode (sort keys) + md5. Hash **sau** khi deps resolved, order freeze. **Normalized list:** chỉ `src`, `media`, `type` (local|external); **không** lưu version, preload, async/defer (spec plan §3.1b).
2. **Option keys:** `asset_build_css_head`, `asset_build_css_footer`, `asset_build_js_head`, `asset_build_js_footer`. **Lang 'all'** (một bản cho mọi ngôn ngữ). Entry: id, data (normalized), build (có **hash**; path, url hoặc files[]), last_seen.
3. **Chỉ set khi cần:** get_option lấy giá trị; **chỉ set_option khi chưa có** (thêm entry mới) hoặc khi cron cập nhật build. Không set_option mỗi request khi hit.
4. **Runtime:** get_option(key) → tìm entry theo id. Không có hoặc build null → raw + set_option **chỉ khi entry chưa có**. Có build → output link/script (1 hoặc nhiều theo build). Dùng **get_option/set_option** (không option() cho build metadata).
5. **Cron:** get_option từng key → entries build null → resolve (external fail → fallback URL ngoài, **không block** build toàn entry) → minify/merge theo Settings → 1 hoặc nhiều file → ghi content/assets/ → set_option(entry.build có hash; last_seen). Cleanup TTL/LRU.
6. **Build output:** Có thể **1 file** (merge, không exclude) hoặc **nhiều file** (không merge / exclude). entry.build chứa **hash**; path, url hoặc files[].
7. **URL build:** build.url = public_url('content/assets/'.$relPath); hoặc build.files[].url.

---

## Test scenarios tối thiểu

- [ ] Page A & B cùng asset list → dùng chung file build (cùng entry trong option)
- [ ] Đổi combine_css → asset_build_version bump → build lại
- [ ] Đổi defer_js → KHÔNG rebuild
- [ ] Entry data rỗng / resolve fail → không ghi file rỗng; log
- [ ] External CDN down → build vẫn thành (skip asset đó)
- [ ] get_option → không có entry → output raw + set_option entry (build: null); sau cron → có build → output 1 link

---

## Phase 1: Cơ sở (Settings, constants, thư mục)

### 1.1 Settings admin (Performance – Scripts & Styles)

- [x] Thêm `asset_build_version` (number, hidden/auto — tăng khi Save build-related settings)
- [x] Thêm `build_lru_max` (number — LRU cap, default 100)
- [x] Thêm `external_ttl_days` (number — TTL external self-host, default 7)
- [x] Thêm `self_host_external_assets` (boolean, default false)
- [x] Thêm `self_host_skip_whitelist` (textarea — mỗi dòng 1 pattern)
- [x] Logic bump `asset_build_version`: chỉ khi save combine_*, minify_*, exclude, self_host_* (KHÔNG khi defer_js, async_css)

### 1.2 Constants & config

- [x] Define path: `PATH_CONTENT_ASSETS` (output build; không dùng writable/build)

### 1.3 Thư mục

- [x] Tạo `content/assets/` (output file build)
- [x] Tạo `content/assets/Frontend/`, `Backend/`, `Common/`, `external/`
- [x] Output build chỉ trong content/assets/ (không dùng writable/build)

---

## Phase 2: Option keys + AssetsService (build logic)

### 2.1 Option keys và cấu trúc entry (chuẩn)

- [x] Định nghĩa constant/key: OPTION_KEY_CSS_HEAD, OPTION_KEY_CSS_FOOTER, OPTION_KEY_JS_HEAD, OPTION_KEY_JS_FOOTER (AssetsService)
- [x] Cấu trúc entry: `id`, `area`, `data` (normalized: src, media, type), `build` (null hoặc `{ hash, path, url, time }`), `last_seen`
- [x] AssetsService: getOptionEntries(), setOptionEntry(), getBuildFromOption(), getOptionKey(). Chỉ set_option khi entry chưa có (recordCacheMiss) hoặc cron cập nhật build.

### 2.2 Build logic (cho cron) — giữ nguyên / tái dùng

- [x] Helper `stableJsonEncode($data)` — sort keys recursively (cho signature)
- [x] `resolveAssetContent($src, ...)` — theme, same-origin, external
- [x] `fetchExternal($url)` — timeout 5s; fail → log, return null
- [x] `isInWhitelistSkip($url)` — check self_host_skip_whitelist
- [x] `resolveAndCombine` — minify từng file, combine; exclude lists
- [x] Cron dùng **entry.data** (từ option); build() chỉ loop 4 option keys (option-only)

### 2.3 Option-only (đã bỏ file JSON)

- [x] **Option-only:** Runtime chỉ getBuildFromOption; không fallback manifest. Cron chỉ setOptionEntry.
- [x] Đã bỏ hẳn manifest.json, registry.json, snapshots/ — cleanup TTL/LRU từ option entries.

---

## Phase 3: AssetManager — runtime get_option / set_option

### 3.1 Signature (assetsId)

- [x] Normalize list: chỉ src, media (CSS); bỏ preload, defer, async, version
- [x] Sort deps (sortByDependenciesSafe) → freeze order
- [x] Signature input: theme_name, theme_version, area, location, normalized_list, asset_build_version
- [x] assetsId = md5(stableJsonEncode(...))

### 3.2 Lookup qua option (thay manifest file)

- [x] **Option key:** getOptionKey($type, $location) → asset_build_css_head, asset_build_css_footer, asset_build_js_head, asset_build_js_footer
- [x] getBuiltAssetUrl: computeSignature → assetsId; getBuildFromOption(optionKey, assetsId); có build → return url; không có → return '' (raw)

### 3.3 Hành vi output

- [x] **Không có entry hoặc entry.build === null:** Output raw. recordCacheMiss: setOptionEntry **chỉ khi** entry chưa có (id chưa trong option)
- [x] **Có entry và entry.build !== null:** Output 1 link/script tới build.url (file trong content/assets/).
- [x] Dùng get_option/set_option qua AssetsService (getOptionEntries, setOptionEntry)

### 3.4 URL file build

- [x] build.url = public_url('content/assets/'.$relPath)

### 3.5 Inline minify

- [x] Limit size ≤ 10KB; lớn hơn → skip minify

---

## Phase 4: (Bỏ qua)

- Serve file build tĩnh qua web server tại public_url('content/assets/...'); không dùng PHP controller.

---

## Phase 5: Cron/CLI build + cleanup (option-based)

### 5.1 Trigger

- [x] CLI: `php cmd assets:build`
- [x] Trigger khi admin Save Performance (scripts tab)
- [x] Không dùng lock file

### 5.2 Đọc danh sách cần build từ option

- [x] Với mỗi option_key (OPTION_KEY_CSS_HEAD, …): getOptionEntries(option_key) → entries
- [x] Entry có **build === null** hoặc build.path nhưng file không tồn tại → build
- [x] Skip: entry đã có build và file tồn tại trong content/assets/{area}/{path} → skip

### 5.3 Build từng entry

- [x] Đọc **entry.data** (list assets normalized); snapshot = { assets: entry.data, area, location, type }
- [x] resolveAndCombine (local + external; external fail → skip asset, không block entry)
- [x] Atomic ghi: content/assets/{area}/ .tmp → rename
- [x] **setOptionEntry:** entry.build = `{ hash, path, url, time }`; last_seen (option-only, không manifest)
- [x] Log error per signature (không silent fail)

### 5.4 Cleanup (TTL + LRU từ option entries)

- [x] Đọc 4 option keys → entries có build !== null; collect path, last_seen
- [x] **TTL:** last_seen < (now - build_ttl_days) → xóa file, setOptionEntry(..., build: null)
- [x] **LRU cap:** Số built > build_lru_max → xóa oldest (sort last_seen)
- [x] **Safety window:** Không xóa file có mtime trong 5 phút
- [x] setOptionEntry(optionKey, { id, build: null }) sau khi unlink

### 5.5 SettingsController

- [x] Khi Save Performance (scripts) → bump asset_build_version nếu build-related settings đổi
- [x] Trigger build (sync)

---

## Phase 6: Polish & test

### 6.1 Failure visibility

- [x] Cron log error per signature
- [ ] (Optional) build_failed flag trên entry
- [ ] (Optional) Admin UI "Assets build health"

### 6.2 Test scenarios (manual)

- [ ] Cùng asset list → cùng entry; 1 link sau khi build
- [ ] Đổi combine_css → rebuild
- [ ] Đổi defer_js → không rebuild
- [ ] Entry rỗng / resolve fail → không ghi file rỗng
- [ ] External down → skip asset

---

## Trạng thái hiện tại

**Thiết kế chính:** Option-based (get_option/set_option). Không dùng manifest.json, registry.json, snapshots.

**Đã có:** Phase 1–6. Option-only: không manifest.json, registry.json, snapshots. Build + cleanup từ options.

**Đã hoàn thành (option-only):**
- **Metadata:** Chỉ get_option/set_option (Storage_helper). AssetsService::OPTION_KEY_*; getOptionEntries(), setOptionEntry(), getBuildFromOption().
- **Runtime:** getBuiltAssetUrl() chỉ getBuildFromOption(); recordCacheMiss() setOptionEntry khi entry chưa có. Không recordRegistryHit, không manifest/snapshot.
- **Cron build():** Chỉ loop 4 option keys → entry build null hoặc file mất → resolveAndCombine từ entry.data → ghi content/assets/ → setOptionEntry(build, last_seen).
- **Cleanup:** runCleanup() đọc 4 option keys, TTL + LRU, unlink file + setOptionEntry(..., build: null).
- **File cũ:** Không dùng writable/build; manifest/registry/snapshots đã bỏ.

**File tham khảo:**
- `system/Helpers/Storage_helper.php` (get_option, set_option)
- `application/Services/Asset/AssetsService.php`
- `system/Libraries/Render/Asset/AssetManager.php`
- `application/Config/PROGRESS_PLAN_ASSET_BUILD.md`

---

## Final (đã rà soát)

| Hạng mục | Trạng thái |
|----------|------------|
| Metadata | Chỉ options (asset_build_css_head, …). Không manifest/registry/snapshots |
| Runtime | getBuiltAssetUrl() → getBuildFromOption(); recordCacheMiss() → setOptionEntry khi chưa có entry |
| Build | `php cmd assets:build`; loop 4 keys; entry build null/file mất → resolveAndCombine → ghi content/assets/ → setOptionEntry |
| Cleanup | runCleanup() từ options: TTL + LRU; unlink + setOptionEntry(…, build: null) |
| URL build | public_url('content/assets/'.$relPath) |
| Request path | Không file_exists, không lock I/O |

**URL build:** public_url('content/assets/...') (không dùng base_url).

**Giao diện dùng file build:** Cần bật **Combine CSS** và **Combine JS** trong Settings > Performance (Scripts & Styles). Nếu tắt thì luôn output từng file (raw).
