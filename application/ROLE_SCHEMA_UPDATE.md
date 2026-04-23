# User Role Schema Update - Dynamic Role Support

## 📋 **Overview**

Updated the `users` table `role` column from **ENUM** (fixed values) to **VARCHAR(64)** to support dynamic roles from plugins. This allows plugins to define their own custom roles without modifying the database schema.

---

## ❌ **Previous Problem**

### **Schema Conflict:**
```php
// UsersModel.php - Fixed ENUM
['type' => 'enum', 'name' => 'role', 'options' => [
    'values' => ['admin', 'moderator', 'author', 'member'], 
    'default' => 'member'
]]
```

### **Dynamic Roles Loading:**
```php
// config_roles() - Dynamic from Application + Plugins
function config_roles() {
    // Load from application/Config/Roles.php
    $roles = require PATH_APP . 'Config/Roles.php';
    
    // Load from plugins/*/Config/Roles.php
    foreach ($activePlugins as $plugin) {
        $pluginRoles = require PATH_PLUGINS . $plugin . '/Config/Roles.php';
        $roles = array_merge($roles, $pluginRoles);
    }
    
    return $roles; // Could have 'shop_owner', 'instructor', etc.
}
```

### **Result:**
```
❌ Plugin defines role 'shop_owner'
❌ Form shows 'shop_owner' option
❌ User submits with role = 'shop_owner'
❌ Database rejects: "Data truncated for column 'role' at row 1"
❌ Error: Role not in ENUM('admin','moderator','author','member')
```

**The Issue:** ENUM can't handle dynamic roles from plugins!

---

## ✅ **Solution**

### **1. Updated Schema in `UsersModel.php`**

**Before:**
```php
['type' => 'enum', 'name' => 'role', 'options' => [
    'values' => ['admin', 'moderator', 'author', 'member'], 
    'default' => 'member'
]]
```

**After:**
```php
['type' => 'string', 'name' => 'role', 'options' => [
    'length' => 64, 
    'default' => 'member',
    'comment' => 'User role (dynamic from config_roles())'
]]
```

**Changes:**
- ✅ Changed from `enum` to `string` (VARCHAR)
- ✅ Set max length to `64` characters (matches `config_roles()` limit)
- ✅ Removed `null` option (role must always have a value)
- ✅ Added comment explaining dynamic nature

---

### **2. Updated Validation in `UsersController.php`**

#### **In `add()` method (line 222-240):**
```php
'role' => [
    'rules' => [
        Validate::notEmpty(),
        Validate::length(1, 64),
        Validate::callback(function($value) {
            // Validate role exists in config_roles()
            $roles = config_roles();
            if (!isset($roles[$value])) {
                return false;
            }
            // Validate role is active
            return isset($roles[$value]['is_active']) && $roles[$value]['is_active'] === true;
        }),
    ],
    'messages' => [
        __('role_option'),
        __('Role must be between 1 and 64 characters'),
        __('Invalid role or role is inactive'),
    ]
],
```

#### **In `edit()` method (line 760-782):**
```php
if (HAS_POST('role')) {
    $input['role'] = S_POST('role') ?? '';
    $rules['role'] = [
        'rules' => [
            Validate::notEmpty(),
            Validate::length(1, 64),
            Validate::callback(function($value) {
                // Validate role exists in config_roles()
                $roles = config_roles();
                if (!isset($roles[$value])) {
                    return false;
                }
                // Validate role is active
                return isset($roles[$value]['is_active']) && $roles[$value]['is_active'] === true;
            }),
        ],
        'messages' => [
            __('role_option'),
            __('Role must be between 1 and 64 characters'),
            __('Invalid role or role is inactive'),
        ]
    ];
}
```

**Validation Rules:**
1. ✅ **notEmpty** - Role is required
2. ✅ **length(1, 64)** - Must be between 1-64 characters
3. ✅ **callback** - Custom validation:
   - Check if role exists in `config_roles()`
   - Check if role is active (`is_active === true`)

---

## 🎯 **Benefits**

### **1. Plugin Extensibility**
Plugins can now define custom roles without modifying core:

**Plugin: `plugins/shop/Config/Roles.php`**
```php
return [
    'shop_owner' => [
        'name' => 'Shop Owner',
        'description' => 'Manages shop products and orders',
        'permissions' => [
            'Backend\Shop' => ['index', 'add', 'edit', 'delete'],
        ],
        'is_active' => true,
        'order' => 5,
        'roles_type' => 'add', // Add to existing roles
    ],
    'shop_staff' => [
        'name' => 'Shop Staff',
        'description' => 'Assists with order processing',
        'permissions' => [
            'Backend\Shop' => ['index', 'edit'],
        ],
        'is_active' => true,
        'order' => 6,
        'roles_type' => 'add',
    ],
];
```

✅ **Result:** Users can now be assigned `shop_owner` or `shop_staff` roles!

---

### **2. Role Management**

**Default Roles (from `application/Config/Roles.php`):**
```php
return [
    'admin' => [...],      // Full access
    'moderator' => [...],  // Content management
    'author' => [...],     // Own content
    'member' => [...],     // Basic access
];
```

**Plugin Roles (loaded dynamically):**
```php
// From plugins/shop/Config/Roles.php
'shop_owner' => [...]

// From plugins/lms/Config/Roles.php
'instructor' => [...]
'student' => [...]

// From plugins/forum/Config/Roles.php
'moderator_forum' => [...]
```

**Combined Result:**
```php
config_roles() returns:
[
    'admin' => [...],
    'moderator' => [...],
    'author' => [...],
    'member' => [...],
    'shop_owner' => [...],        // ✅ From shop plugin
    'instructor' => [...],        // ✅ From LMS plugin
    'student' => [...],           // ✅ From LMS plugin
    'moderator_forum' => [...],   // ✅ From forum plugin
]
```

---

### **3. Validation Security**

**Prevents Invalid Roles:**
```php
// User tries to submit role = 'super_admin' (doesn't exist)
❌ Validation fails: "Invalid role or role is inactive"

// Plugin defines role but sets is_active = false
❌ Validation fails: "Invalid role or role is inactive"

// Role name too long (> 64 chars)
❌ Validation fails: "Role must be between 1 and 64 characters"
```

**Allows Valid Roles:**
```php
// User submits role = 'shop_owner' (defined by plugin)
✅ Validation passes (role exists and is_active = true)

// User submits role = 'admin' (core role)
✅ Validation passes
```

---

## 🔄 **Migration Path**

### **For Existing Databases:**

**Option 1: Automatic via Schema Sync**
```php
// Run schema sync (if implemented)
php artisan schema:sync users
```

**Option 2: Manual ALTER TABLE**
```sql
-- Convert ENUM to VARCHAR(64)
ALTER TABLE `fast_users` 
MODIFY COLUMN `role` VARCHAR(64) NOT NULL DEFAULT 'member' 
COMMENT 'User role (dynamic from config_roles())';
```

**Data Safety:**
- ✅ Existing values ('admin', 'moderator', 'author', 'member') remain valid
- ✅ No data loss
- ✅ Fully backward compatible

---

## 📊 **Schema Comparison**

| Aspect | ENUM (Old) | VARCHAR(64) (New) |
|--------|-----------|-------------------|
| **Values** | Fixed: 4 values | Dynamic: unlimited |
| **Plugin Support** | ❌ No | ✅ Yes |
| **Modify Schema** | ❌ ALTER TABLE required | ✅ No change needed |
| **Validation** | ⚠️ Database-level only | ✅ Application-level |
| **Max Length** | N/A | 64 characters |
| **Default Value** | 'member' | 'member' |
| **Performance** | ~1 byte | ~2-65 bytes |
| **Flexibility** | ❌ Low | ✅ High |

---

## 🛡️ **Security Considerations**

### **1. Role Validation**
```php
// BEFORE (ENUM): Database validates values
// If value not in ENUM → Error (but admin could add invalid value via SQL)

// AFTER (VARCHAR): Application validates values
Validate::callback(function($value) {
    $roles = config_roles();
    return isset($roles[$value]) && $roles[$value]['is_active'] === true;
});
// ✅ More secure: Checks both existence AND active status
```

### **2. SQL Injection Protection**
```php
// VARCHAR fields are properly escaped by Query Builder
$this->usersModel->updateUser($id, ['role' => $role]);
// Internally uses prepared statements:
// UPDATE users SET role = ? WHERE id = ? ← Safe!
```

### **3. Length Limit**
```php
// config_roles() already limits to 64 chars (line 343-345)
if (strlen($roleName) > 64) {
    $roleName = substr($roleName, 0, 63);
}

// Controller validation also enforces limit
Validate::length(1, 64),
```

---

## 📝 **Example Use Cases**

### **Use Case 1: E-commerce Plugin**
```php
// plugins/shop/Config/Roles.php
'shop_owner' => [
    'name' => 'Shop Owner',
    'permissions' => [
        'Backend\Products' => ['index', 'add', 'edit', 'delete'],
        'Backend\Orders' => ['index', 'view', 'process', 'refund'],
        'Backend\ShopSettings' => ['index', 'edit'],
    ],
    'is_active' => true,
];
```

**Form Display:**
```html
<select name="role">
    <option value="admin">Administrator</option>
    <option value="moderator">Moderator</option>
    <option value="author">Author</option>
    <option value="member">Member</option>
    <option value="shop_owner">Shop Owner</option> <!-- ✅ From plugin -->
</select>
```

**Submission:**
```php
POST: role=shop_owner
✅ Validation passes
✅ INSERT INTO users (..., role) VALUES (..., 'shop_owner')
✅ Success!
```

---

### **Use Case 2: LMS Plugin**
```php
// plugins/lms/Config/Roles.php
'instructor' => [
    'name' => 'Instructor',
    'permissions' => [
        'Backend\Courses' => ['index', 'add', 'edit', 'delete'],
        'Backend\Students' => ['index', 'view', 'grade'],
    ],
    'is_active' => true,
],
'student' => [
    'name' => 'Student',
    'permissions' => [
        'Frontend\Courses' => ['view', 'enroll', 'learn'],
    ],
    'is_active' => true,
],
```

**User Assignment:**
```php
// Assign instructor role
$usersModel->updateUser($teacherId, ['role' => 'instructor']);

// Assign student role
$usersModel->updateUser($studentId, ['role' => 'student']);

// Both work! ✅
```

---

## 🧪 **Testing**

### **Test Case 1: Valid Core Role**
```php
$input = ['role' => 'admin'];
$validation = Validate::check($input, $rules);
// ✅ Expected: Pass
```

### **Test Case 2: Valid Plugin Role**
```php
$input = ['role' => 'shop_owner']; // Plugin-defined
$validation = Validate::check($input, $rules);
// ✅ Expected: Pass (if plugin active)
```

### **Test Case 3: Invalid Role**
```php
$input = ['role' => 'super_admin']; // Not defined
$validation = Validate::check($input, $rules);
// ❌ Expected: Fail - "Invalid role or role is inactive"
```

### **Test Case 4: Inactive Role**
```php
// Plugin defines role with is_active = false
$input = ['role' => 'suspended_role'];
$validation = Validate::check($input, $rules);
// ❌ Expected: Fail - "Invalid role or role is inactive"
```

### **Test Case 5: Too Long**
```php
$input = ['role' => str_repeat('a', 65)]; // 65 characters
$validation = Validate::check($input, $rules);
// ❌ Expected: Fail - "Role must be between 1 and 64 characters"
```

---

## 📚 **Related Files**

| File | Purpose | Changes |
|------|---------|---------|
| `application/Models/UsersModel.php` | Schema definition | Changed `enum` to `string` (VARCHAR 64) |
| `application/Controllers/Backend/UsersController.php` | Validation | Added length + callback validation |
| `application/Config/Roles.php` | Core roles | No changes (still defines default roles) |
| `system/Helpers/Core_helper.php` | `config_roles()` function | No changes (already supports plugins) |

---

## 🎉 **Summary**

| Before | After |
|--------|-------|
| ❌ Fixed 4 roles only | ✅ Dynamic unlimited roles |
| ❌ Plugins can't add roles | ✅ Plugins can add roles |
| ❌ Schema change needed | ✅ Auto-loaded from config |
| ❌ Database validates | ✅ Application validates |
| ⚠️ Less flexible | ✅ Highly flexible |

**Result:** 
- 🚀 **Plugins can extend user roles**
- 🔒 **Secure validation** (checks existence + active status)
- ♻️ **Backward compatible** (existing data works)
- 📈 **Scalable** (add unlimited roles)

**Developer Experience:** 📈 **Massively improved!**

