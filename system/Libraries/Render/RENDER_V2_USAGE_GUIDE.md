# 📖 Render V2 Library - Hướng Dẫn Sử Dụng Chi Tiết

**Version:** 2.0.0  
**Status:** ✅ PRODUCTION READY

**→ Bảng hàm helper (add_css, add_js, localize_script, view_head, …) và Class API:** [RENDER_API_GUIDE.md](RENDER_API_GUIDE.md)  
**→ Hooks (filter/action):** [RENDER_HOOKS.md](RENDER_HOOKS.md)

---

## 📋 MỤC LỤC

1. [Tổng Quan](#tổng-quan)
2. [Template Loading (không cache)](#template-loading-không-cache)
3. [Quản Lý Assets (CSS/JS)](#quản-lý-assets)
4. [Quản Lý Head (Meta, Schema, SEO)](#quản-lý-head)
5. [Render Giao Diện](#render-giao-diện)
6. [Best Practices](#best-practices)

---

## 🎯 TỔNG QUAN

Render V2 là thư viện hiện đại để render giao diện trong CMS, với các tính năng:

- ✅ **Pure PHP Rendering** - Chỉ dùng PHP files từ theme (content/themes/...), không cache, giống WordPress
- ✅ **Asset Management** - Quản lý CSS/JS với dependencies, minify
- ✅ **Head Management** - Quản lý meta tags, schema, SEO
- ✅ **Template Inheritance** - Support parent theme
- ✅ **Plugin Support** - Dễ dàng override từ plugins

---

## 📄 TEMPLATE LOADING (không cache)

Template **luôn load trực tiếp** từ `content/themes/{theme}/...` (giống WordPress):

- **View** → **PhpTemplate** (locate + `include` file `.php`)
- Không compile, không cache file; sửa theme có hiệu lực ngay
- `View::clearCache()` / `View::forgetCache()` giữ API nhưng là no-op (luôn thành công)

---

## 📦 QUẢN LÝ ASSETS

### **1. Thêm CSS File**

**Helper (khuyến nghị trong theme/plugin):**
```php
add_css('bootstrap', 'css/bootstrap.css');
// hoặc wp-style: enqueue_style('bootstrap', 'css/bootstrap.css');
```

**Hoặc dùng class View:**
```php
use System\Libraries\Render\View;
View::addCss('bootstrap', 'css/bootstrap.css');

// Full options
View::addCss(
    'handle',              // Unique handle
    'css/style.css',       // Source path
    ['jquery'],            // Dependencies (load jquery first)
    '1.0.0',               // Version (for cache busting)
    'all',                 // Media type (all, screen, print)
    'Frontend',            // Area (Frontend, Backend) - auto-detect if null
    false,                 // In footer? (default: false)
    false,                 // Preload? (default: false)
    true                   // Minify in production? (default: false)
);
```

**Ví dụ thực tế:**
```php
// Bootstrap CSS với dependencies
View::addCss('bootstrap', 'css/bootstrap.min.css', [], '5.3.0', 'all', null, false, false, true);

// Custom CSS (minify trong production)
View::addCss('custom', 'css/custom.css', ['bootstrap'], null, 'all', null, false, false, true);

// Critical CSS (preload)
View::addCss('critical', 'css/critical.css', [], null, 'all', null, false, true, false);
```

### **2. Thêm JS File**

**Helper (khuyến nghị):**
```php
add_js('jquery', 'js/jquery.js');
enqueue_script('app', 'js/app.js', ['jquery'], null, true);  // defer (enqueue_script = add_js)
```

**Hoặc View::addJs(...) – Full options:**
```php
View::addJs(
    'handle',              // Unique handle
    'js/script.js',        // Source path
    ['jquery'],            // Dependencies (load jquery first)
    '1.0.0',               // Version (for cache busting)
    true,                  // Defer? (default: false)
    false,                 // Async? (default: false)
    'Frontend',            // Area (Frontend, Backend) - auto-detect if null
    true,                  // In footer? (default: true)
    true                   // Minify in production? (default: false)
);
```

**Ví dụ thực tế:**
```php
// jQuery (load in head, no defer)
View::addJs('jquery', 'js/jquery.min.js', [], '3.7.0', false, false, null, false, true);

// Bootstrap JS (depends on jQuery, defer, in footer)
View::addJs('bootstrap', 'js/bootstrap.bundle.min.js', ['jquery'], '5.3.0', true, false, null, true, true);

// Custom script (async, minify)
View::addJs('custom', 'js/custom.js', ['jquery'], null, false, true, null, true, true);
```

### **3. Minify Assets**

**Cách hoạt động:**
- ✅ **Development Mode:** Luôn dùng file gốc (không minify)
- ✅ **Production Mode:** Tự động dùng file `.min.*` nếu `minify=true` và file tồn tại

**Cấu trúc files:**
```
themes/your-theme/Frontend/assets/
├── css/
│   ├── style.css          ← Development
│   └── style.min.css      ← Production (nếu minify=true)
└── js/
    ├── script.js          ← Development
    └── script.min.js      ← Production (nếu minify=true)
```

**Lưu ý:**
- Bạn cần tự tạo file `.min.*` trước khi deploy production
- Nếu file `.min.*` không tồn tại, hệ thống sẽ dùng file gốc

### **4. Inline CSS/JS**

**Helper:**
```php
inline_css('custom-style', '.my-class { color: red; }');
inline_js('custom-script', 'console.log("Hello World");');
```

**Inject biến JS từ PHP (wp_localize_script style):**
```php
add_js('app', 'js/app.js');
localize_script('app', 'myConfig', ['ajax_url' => base_url('api'), 'nonce' => csrf_token()]);
```

### **5. Render Assets**

**Helper (trong layout):**
```php
<head>
    <?php echo view_css('head'); ?>
    <?php view_head(); ?>   <!-- hoặc: echo \System\Libraries\Render\Head::render(); rồi assets_head(); -->
</head>
<body>
    <!-- Content -->
    <?php echo view_js('footer'); ?>
</body>
```
Hoặc dùng một lần trong &lt;head&gt;: `<?php view_head(); ?>` (đã gồm CSS + JS head + title/meta/schema).

### **6. Dependencies Resolution**

Hệ thống tự động resolve dependencies và load theo thứ tự đúng:

```php
// jQuery sẽ được load trước Bootstrap
View::addJs('bootstrap', 'js/bootstrap.js', ['jquery']);
View::addJs('jquery', 'js/jquery.js');

// Output:
// 1. <script src="js/jquery.js"></script>
// 2. <script src="js/bootstrap.js"></script>
```

---

## 🎨 QUẢN LÝ HEAD

### **1. Title (Tiêu Đề)**

```php
use System\Libraries\Render\View;

// Set title đơn giản
View::setTitle('Home Page');

// Set title với nhiều phần
View::setTitle(['Home', 'My Site']);

// Append title
View::setTitle('Home', true);  // Append vào existing

// Set separator
View::setTitleSeparator('|');  // Default: '|'

// Set site name
View::setSiteName('My Site');

// Render title
echo View::renderHead();  // <title>Home | My Site</title>
```

### **2. Meta Tags**

```php
// Meta description
View::setDescription('This is a description of the page');

// Meta keywords
View::setKeywords(['php', 'cms', 'framework']);
// hoặc
View::setKeywords('php, cms, framework');

// Custom meta tag
View::addMeta('author', 'John Doe');
View::addMeta('robots', 'index, follow');

// Meta với property (Open Graph)
View::addMeta('og:title', 'My Page', 'property');

// Meta với http-equiv
View::addMeta('refresh', '30', 'http-equiv');
```

### **3. Open Graph (Facebook, LinkedIn)**

```php
View::setOpenGraph([
    'title' => 'My Page Title',
    'description' => 'Page description',
    'image' => 'https://example.com/image.jpg',
    'url' => 'https://example.com/page',
    'type' => 'website',
    'site_name' => 'My Site',
    'locale' => 'en_US'
]);
```

### **4. Twitter Card**

```php
View::setTwitterCard([
    'card' => 'summary_large_image',
    'title' => 'My Page Title',
    'description' => 'Page description',
    'image' => 'https://example.com/image.jpg',
    'site' => '@mysite',
    'creator' => '@author'
]);
```

### **5. Canonical URL**

```php
View::setCanonical('https://example.com/page');
```

### **6. JSON-LD Schema (Nhiều Tầng)**

```php
// Single schema
View::addSchema([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'My Site',
    'url' => 'https://example.com'
]);

// Multiple schemas (với key)
View::addSchema([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'My Company',
    'url' => 'https://example.com'
], 'organization');

View::addSchema([
    '@context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => 'Home Page',
    'url' => 'https://example.com'
], 'webpage');

// Nested schema (nhiều tầng)
View::addSchema([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => 'Article Title',
    'author' => [
        '@type' => 'Person',
        'name' => 'John Doe',
        'url' => 'https://example.com/author'
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'My Company',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://example.com/logo.jpg'
        ]
    ]
], 'article');
```

### **7. Render Head**

```php
// Render tất cả head content
echo View::renderHead();

// Với options
echo View::renderHead([
    'include_assets' => false,  // Không include assets (render riêng)
    'include_title' => true,    // Include title
    'include_meta' => true,     // Include meta tags
    'include_schema' => true    // Include JSON-LD schemas
]);
```

**Output mẫu:**
```html
<title>Home | My Site</title>
<meta name="description" content="Page description" />
<meta name="keywords" content="php, cms, framework" />
<meta property="og:title" content="My Page Title" />
<meta property="og:description" content="Page description" />
<link rel="canonical" href="https://example.com/page" />
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "My Site"
}
</script>
```

---

## 🖼️ RENDER GIAO DIỆN

### **1. Render View Cơ Bản**

```php
use System\Libraries\Render\View;

// Render view với data
$html = View::make('Frontend/Home/index', [
    'title' => 'Home Page',
    'posts' => $posts
])->render();

// Fluent interface
$html = View::make('Home/index', $data)
    ->with('title', 'Home Page')
    ->with('meta', $meta)
    ->render();
```

### **2. Namespace**

```php
// Set namespace (tất cả views sau sẽ dùng namespace này)
View::namespace('Frontend');
$html = View::make('Home/index', $data)->render();

// Clear namespace
View::clearNamespace();
```

### **3. Include Partials**

```php
// Include partial
$html = View::include('Common/Header/header', $data);

// Include if exists (không lỗi nếu không tìm thấy)
$html = View::includeIf('Common/Custom/custom', $data);
```

### **4. Plugin & Block Templates**

```php
// Plugin template
$html = View::make('@Ecommerce/Frontend/products/index', $data)->render();

// Block template
$html = View::make('#Header/default', $data)->render();
```

### **5. Share Global Data**

```php
// Share data với tất cả views
View::share('siteName', 'My Site');
View::share([
    'user' => $user,
    'settings' => $settings
]);

// Tất cả views sẽ có access đến shared data
View::make('Home/index', $data)->render();
```

### **6. Check Template Exists**

```php
if (View::exists('Home/index')) {
    // Template exists
}

// Get full path
$path = View::getPath('Home/index');
```

### **7. Layout File Mẫu**

```php
<?php
// themes/your-theme/Frontend/layout.php

use System\Libraries\Render\View;

// Set head
View::setTitle('Home Page');
View::setDescription('Page description');
View::addCss('bootstrap', 'css/bootstrap.css', [], null, 'all', null, false, false, true);
View::addJs('jquery', 'js/jquery.js', [], null, false, false, null, false, true);
View::addJs('bootstrap', 'js/bootstrap.js', ['jquery'], null, true, false, null, true, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo View::css('head'); ?>
    <?php echo View::renderHead(); ?>
</head>
<body>
    <?php echo View::include('Common/Header/header', $data); ?>
    
    <main>
        <?php echo $content; ?>
    </main>
    
    <?php echo View::include('Common/Footer/footer', $data); ?>
    
    <?php echo View::js('footer'); ?>
</body>
</html>
```

---

## 💡 BEST PRACTICES

### **1. Asset Organization**

```
themes/your-theme/Frontend/assets/
├── css/
│   ├── bootstrap.css
│   ├── bootstrap.min.css      ← Production
│   ├── custom.css
│   └── custom.min.css         ← Production
└── js/
    ├── jquery.js
    ├── jquery.min.js          ← Production
    ├── bootstrap.js
    ├── bootstrap.min.js       ← Production
    ├── custom.js
    └── custom.min.js          ← Production
```

### **2. Development vs Production**

**Development:**
```php
// Constants.php
define('APP_DEVELOPMENT', true);
define('APP_DEBUG', true);

// Sử dụng file gốc, không cache
View::addCss('custom', 'css/custom.css', [], null, 'all', null, false, false, false);
```

**Production:**
```php
// Constants.php
define('APP_DEVELOPMENT', false);

// Sử dụng file minified, có cache
View::addCss('custom', 'css/custom.css', [], null, 'all', null, false, false, true);
```

### **3. Dependencies**

Luôn khai báo dependencies đúng:
```php
// ✅ CORRECT
View::addJs('bootstrap', 'js/bootstrap.js', ['jquery']);
View::addJs('jquery', 'js/jquery.js');

// ❌ WRONG (không khai báo dependencies)
View::addJs('bootstrap', 'js/bootstrap.js');
View::addJs('jquery', 'js/jquery.js');
```

### **4. Performance**

- ✅ Sử dụng `minify=true` cho production
- ✅ Preload critical CSS
- ✅ Defer non-critical JS
- ✅ Clear cache khi deploy

### **5. SEO**

- ✅ Luôn set title, description
- ✅ Sử dụng Open Graph cho social sharing
- ✅ Thêm JSON-LD schema
- ✅ Set canonical URL

---

## 📝 TÓM TẮT

### **Cache:**
- Development: Không cache, load từ source
- Production: Ưu tiên cache, fallback source

### **Assets:**
- Development: File gốc
- Production: File `.min.*` nếu `minify=true`

### **Head:**
- Title, Meta, OG, Twitter Card, Schema
- Support nhiều tầng schema

### **Render:**
- View::make() - Render view
- View::include() - Include partial
- View::share() - Share global data

---

**Status:** ✅ PRODUCTION READY
