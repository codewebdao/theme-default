# Settings – Cấu trúc & Thêm trang mới

## Luồng hoạt động

- **URL:** `GET|POST /admin/settings` → trang tổng (danh sách nhóm).
- **URL:** `GET|POST /admin/settings/{type}` → trang form của nhóm (vd: `general`, `media`, `performance`).

Route: `admin/(:any)/(:any)` → `SettingsController::{action}` (vd: `general`, `performance`). Mỗi trang settings = một action riêng để **phân quyền theo từng trang** (role có thể chỉ được phép access general, hoặc performance, v.v.).

## Thành phần

| Thành phần | Vai trò |
|------------|--------|
| **Routes** | Admin group: `/(:any)/(:any)` → `$1Controller::$2` (settings/general → SettingsController::general). |
| **SettingsService** | Registry `$settingTypeMethods` (type → method form), load/save storage, `getSettingsGroups()` build từ registry. |
| **Traits (Traits/*.php)** | Mỗi trait: `getXxxSettingsGroup()` (card + tabs + url + form_options nếu cần), `getXxxSettings()` (tabs + fields). |
| **SettingsController** | `index()` = danh sách nhóm; mỗi action (general, performance, …) gọi `showSettingsPage($type)` – mỗi action tương ứng một quyền trong Roles. |

**Storage:** (1) Toàn trang `all`: performance, media, email, social_api, security, developer — thêm `form_options => ['app_lang' => ['all']]`. (2) Mixed (general, seo): field có `storage_lang => 'all'` lưu chung, còn lại theo ngôn ngữ; URL và redirect dùng `post_lang`.  
Form đặc biệt (vd tab “ALL”): trong group thêm `'form_options' => ['app_lang' => ['all']]`
---

## Thêm một trang settings mới (vd: Booking / Website A)

### Bước 1: Tạo trait

Tạo file `application/Services/Settings/Traits/BookingSettingsTrait.php` (đổi tên theo module):

```php
<?php
namespace App\Services\Settings\Traits;

trait BookingSettingsTrait
{
    public function getBookingSettingsGroup(): array
    {
        return [
            'id' => 'booking',
            'icon' => 'calendar',
            'title' => __('Booking'),
            'description' => __('Booking options'),
            'detail' => __('…'),
            'url' => admin_url('settings/booking'),
            'tabs' => [
                ['id' => 'general', 'label' => __('General')],
            ],
            // 'form_options' => ['app_lang' => ['all']],  // nếu cần 1 tab ALL
        ];
    }

    public function getBookingSettings(): array
    {
        $tabs = [
            forms_tab('general', __('General'), ['icon' => 'settings']),
        ];
        $fields = [
            forms_field('text', 'booking_slot_duration', __('Slot duration (min)'), [
                'tab' => 'general', 'default_value' => '30',
            ]),
            // …
        ];
        return ['tabs' => $tabs, 'fields' => $fields];
    }
}
```

- **id** trong group phải trùng với **key** đăng ký ở Bước 2 (vd: `booking`).
- Field dùng `forms_field(..., 'field_name', ...)`. Giá trị load/save theo `storage_get`/`storage_set` (scope `application`, lang theo type).

### Bước 2: Đăng ký trong SettingsService

Trong `application/Services/Settings/SettingsService.php`:

1. **Use trait:**
   ```php
   use App\Services\Settings\Traits\BookingSettingsTrait;
   // …
   use BookingSettingsTrait;
   ```

2. **Thêm một dòng vào registry** `$settingTypeMethods`:
   ```php
   'booking' => 'getBookingSettings',
   ```

`getSettingsGroups()` build tự động từ registry.

### Bước 3: Thêm action trong Controller (để phân quyền)

Trong `application/Controllers/Backend/SettingsController.php` thêm một method (để role/permission nhận diện từng trang):

```php
/** GET|POST /admin/settings/booking – phân quyền theo action. */
public function booking(): void { $this->showSettingsPage('booking'); }
```

Sau đó trong Roles có thể cấp quyền `SettingsController` → actions `['index','general','booking',...]` tùy nhóm.

### Bước 4 (tùy chọn): Lang lưu trữ

- Toàn trang một lang: thêm type vào `getStorageLang()` (mảng `$globalTypes`) và trong trait thêm `form_options => ['app_lang' => ['all']]`.
- Mixed (một số field all, một số theo ngôn ngữ): thêm type vào `hasMixedStorageLang()`, trong trait thêm `storage_lang => 'all'` cho field lưu chung; controller xử lý `post_lang` cho general/seo.
- Nếu cần lưu “global” (một bộ cho mọi ngôn ngữ): trong `SettingsService::getStorageLang()` thêm nhánh, vd:
  ```php
  if ($settingType === 'performance' || $settingType === 'booking') {
      return 'all';
  }
  ```
  Và nếu cần form chỉ 1 tab ngôn ngữ, trong trait thêm `'form_options' => ['app_lang' => ['all']]`.

---

## Tóm tắt checklist thêm trang mới

1. Tạo trait trong `Traits/` với `getXxxSettingsGroup()` và `getXxxSettings()`.
2. Trong **SettingsService**: `use XxxSettingsTrait` và thêm `'type_id' => 'getXxxSettings'` vào `$settingTypeMethods`.
3. Trong **SettingsController**: thêm method `public function type_id(): void { $this->showSettingsPage('type_id'); }` (để phân quyền theo từng trang).
4. (Tùy chọn) Đổi lang storage hoặc `form_options` trong group nếu cần.

Sau đó truy cập `GET /admin/settings/{type_id}` (vd: `/admin/settings/booking`) để chỉnh hoặc phát triển thêm.
