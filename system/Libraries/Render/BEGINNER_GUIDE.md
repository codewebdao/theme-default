# 🚀 Render V2 Library - Hướng Dẫn Cho Người Mới Bắt Đầu

**Version:** 2.0.0  
**Mục đích:** Hướng dẫn chi tiết, dễ hiểu cho developers mới bắt đầu

---

## 📋 MỤC LỤC

1. [Giới Thiệu](#giới-thiệu)
2. [Cài Đặt & Cấu Hình](#cài-đặt--cấu-hình)
3. [Kiến Trúc Dự Án](#kiến-trúc-dự-án)
4. [Ví Dụ Thực Tế](#ví-dụ-thực-tế)
   - [Header Template](#header-template)
   - [Footer Template](#footer-template)
   - [Single Page Template](#single-page-template)
5. [Best Practices](#best-practices)
6. [FAQ](#faq)

---

## 🎯 GIỚI THIỆU

### **Render V2 là gì?**

Render V2 là thư viện render giao diện cho CMS của bạn. Nó giúp bạn:
- ✅ Render HTML từ PHP templates
- ✅ Quản lý CSS/JS files (assets)
- ✅ Quản lý meta tags, SEO
- ✅ Cache tự động (development vs production)

### **Tại sao dùng Render V2?**

- 🚀 **Nhanh:** Cache tự động, tối ưu I/O
- 🎨 **Dễ dùng:** API đơn giản, rõ ràng
- 🔧 **Linh hoạt:** Support theme, plugin, block
- 📦 **Tổ chức tốt:** Cấu trúc thư mục khoa học

---

## ⚙️ CÀI ĐẶT & CẤU HÌNH

### **1. Cấu Hình Development/Production**

Mở file `application/Config/Constants.php`:

```php
// Development Mode (khi đang code)
define('APP_DEVELOPMENT', true);   // Bật development mode
define('APP_DEBUG', true);         // Hiển thị lỗi
define('APP_DEBUGBAR', true);      // Hiển thị debugbar

// Production Mode (khi deploy lên server)
define('APP_DEVELOPMENT', false);  // Tắt development mode
define('APP_DEBUG', false);        // Ẩn lỗi
define('APP_DEBUGBAR', false);     // Tắt debugbar
```

**Lưu ý:**
- **Development:** Không cache, dễ debug
- **Production:** Có cache, performance tốt

---

## 🏗️ KIẾN TRÚC DỰ ÁN

### **Cấu Trúc Thư Mục Theme**

```
content/themes/your-theme/
├── Frontend/              # Templates cho frontend
│   ├── Home/
│   │   └── index.php      # Trang chủ
│   ├── Products/
│   │   ├── index.php      # Danh sách sản phẩm
│   │   └── single.php     # Chi tiết sản phẩm
│   ├── header.php         # Header (dùng chung)
│   ├── footer.php         # Footer (dùng chung)
│   └── ...
└── assets/                # CSS, JS, images
    ├── css/
    │   ├── style.css
    │   └── style.min.css  # Minified (production)
    └── js/
        ├── main.js
        └── main.min.js    # Minified (production)
```

### **Quy Tắc Đặt Tên**

1. **Templates:**
   - Tên file: `camelCase.php` hoặc `kebab-case.php`
   - Ví dụ: `single.php`, `product-list.php`

2. **Assets:**
   - CSS: `style.css`, `style.min.css`
   - JS: `main.js`, `main.min.js`

3. **Thư Mục:**
   - PascalCase cho controllers: `Home/`, `Products/`
   - camelCase cho components: `common/`, `partials/`

### **Luồng Hoạt Động**

```
1. Controller nhận request
   ↓
2. Controller xử lý logic, lấy data
   ↓
3. Controller gọi View::make() để render template
   ↓
4. View::make() tìm template trong theme
   ↓
5. Template được render với data từ controller
   ↓
6. HTML được trả về cho browser
```

### **Ví Dụ Controller**

**File:** `application/Controllers/ProductsController.php`

```php
<?php

namespace App\Controllers;

use System\Libraries\Render\View;

class ProductsController
{
    public function index()
    {
        // 1. Lấy data từ database
        $products = $this->getProducts();
        
        // 2. Share global data (optional)
        View::share('pageTitle', 'Danh Sách Sản Phẩm');
        
        // 3. Render template với data
        return View::make('Frontend/Products/index', [
            'products' => $products,
            'total' => count($products)
        ])->render();
    }
    
    public function single($id)
    {
        // 1. Lấy product từ database
        $product = $this->getProduct($id);
        
        if (!$product) {
            // 404 - Product not found
            return View::make('Common/404', [
                'message' => 'Sản phẩm không tồn tại'
            ])->render();
        }
        
        // 2. Lấy related products
        $relatedProducts = $this->getRelatedProducts($product['category_id'], $id);
        
        // 3. Render template
        return View::make('Frontend/Products/single', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ])->render();
    }
    
    private function getProducts()
    {
        // Logic lấy products từ database
        return [
            ['id' => 1, 'name' => 'Sản phẩm 1', 'price' => 100000],
            ['id' => 2, 'name' => 'Sản phẩm 2', 'price' => 200000],
        ];
    }
    
    private function getProduct($id)
    {
        // Logic lấy product từ database
        return ['id' => $id, 'name' => 'Sản phẩm', 'price' => 100000];
    }
    
    private function getRelatedProducts($categoryId, $excludeId)
    {
        // Logic lấy related products
        return [];
    }
}
```

**Giải thích:**
- Controller xử lý logic, lấy data
- Controller gọi `View::make()` để render template
- Data được truyền vào template qua array
- Template sử dụng data để render HTML

---

## 📝 VÍ DỤ THỰC TẾ

### **1. Header Template**

**File:** `content/themes/your-theme/Common/header.php`

```php
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php
    // Render <head> content (title, meta, CSS, etc.)
    echo \System\Libraries\Render\View::renderHead([
        'title' => $pageTitle ?? 'Trang Chủ',
        'description' => $pageDescription ?? 'Mô tả trang web'
    ]);
    ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="/">
                    <img src="/assets/images/logo.png" alt="Logo">
                </a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="/">Trang Chủ</a></li>
                    <li><a href="/products">Sản Phẩm</a></li>
                    <li><a href="/about">Giới Thiệu</a></li>
                    <li><a href="/contact">Liên Hệ</a></li>
                </ul>
            </nav>
            
            <?php
            // Include user menu if logged in
            if (isset($user) && $user) {
                echo \System\Libraries\Render\View::include('Common/user-menu', ['user' => $user]);
            }
            ?>
        </div>
    </header>
    
    <main class="site-main">
```

**Giải thích:**
- `View::renderHead()` - Render tất cả `<head>` content (title, meta, CSS)
- `View::include()` - Include partial template (user-menu)
- Biến `$pageTitle`, `$pageDescription` được truyền từ controller

---

### **2. Footer Template**

**File:** `content/themes/your-theme/Common/footer.php`

```php
    </main> <!-- End .site-main -->
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Về Chúng Tôi</h3>
                    <p>Thông tin về công ty...</p>
                </div>
                
                <div class="footer-column">
                    <h3>Liên Kết</h3>
                    <ul>
                        <li><a href="/about">Giới Thiệu</a></li>
                        <li><a href="/contact">Liên Hệ</a></li>
                        <li><a href="/privacy">Chính Sách</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Kết Nối</h3>
                    <div class="social-links">
                        <a href="#" class="social-link">Facebook</a>
                        <a href="#" class="social-link">Twitter</a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>
    
    <?php
    // Render JS files (đặt ở cuối trang để tăng tốc độ load)
    echo \System\Libraries\Render\View::js('footer');
    ?>
</body>
</html>
```

**Giải thích:**
- `View::js('footer')` - Render tất cả JS files ở footer
- Footer thường chứa thông tin liên hệ, links, copyright

---

### **3. Single Page Template**

**File:** `content/themes/your-theme/Frontend/Products/single.php`

```php
<?php
// 1. Set namespace (optional - nếu template trong Frontend/)
\System\Libraries\Render\View::namespace('Frontend');

// 2. Include header
echo \System\Libraries\Render\View::include('Common/header', [
    'pageTitle' => $product['name'] ?? 'Sản Phẩm',
    'pageDescription' => $product['description'] ?? ''
]);

// 3. Add CSS cho trang này
\System\Libraries\Render\View::addCss('product-single', 'css/product-single.css', [], null, 'all', null, false, false, true);

// 4. Add JS cho trang này
\System\Libraries\Render\View::addJs('product-gallery', 'js/product-gallery.js', ['jquery'], 'footer', false, '1.0.0', false, false, true);
?>

<div class="product-single">
    <div class="container">
        <div class="product-content">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="/">Trang Chủ</a> / 
                <a href="/products">Sản Phẩm</a> / 
                <span><?php echo htmlspecialchars($product['name'] ?? ''); ?></span>
            </nav>
            
            <!-- Product Info -->
            <div class="product-info">
                <div class="product-images">
                    <?php if (!empty($product['images'])): ?>
                        <div class="main-image">
                            <img src="<?php echo htmlspecialchars($product['images'][0]); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <?php if (count($product['images']) > 1): ?>
                            <div class="thumbnail-images">
                                <?php foreach ($product['images'] as $image): ?>
                                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Thumbnail">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="product-details">
                    <h1><?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
                    
                    <div class="product-price">
                        <span class="price"><?php echo number_format($product['price'] ?? 0); ?> đ</span>
                        <?php if (!empty($product['old_price'])): ?>
                            <span class="old-price"><?php echo number_format($product['old_price']); ?> đ</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-description">
                        <?php echo $product['description'] ?? ''; ?>
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn-add-to-cart" data-product-id="<?php echo $product['id'] ?? ''; ?>">
                            Thêm Vào Giỏ
                        </button>
                        <button class="btn-buy-now" data-product-id="<?php echo $product['id'] ?? ''; ?>">
                            Mua Ngay
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
                <div class="related-products">
                    <h2>Sản Phẩm Liên Quan</h2>
                    <div class="product-grid">
                        <?php foreach ($relatedProducts as $related): ?>
                            <div class="product-item">
                                <a href="/products/<?php echo $related['slug']; ?>">
                                    <img src="<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['name']); ?>">
                                    <h3><?php echo htmlspecialchars($related['name']); ?></h3>
                                    <p class="price"><?php echo number_format($related['price']); ?> đ</p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// 5. Include footer
echo \System\Libraries\Render\View::include('Common/footer');

// 6. Set SEO meta tags
\System\Libraries\Render\View::setTitle($product['name'] . ' - Sản Phẩm');
\System\Libraries\Render\View::setDescription($product['description'] ?? '');
\System\Libraries\Render\View::setCanonical('/products/' . ($product['slug'] ?? ''));

// 7. Add Open Graph tags (for social sharing)
\System\Libraries\Render\View::setOpenGraph([
    'title' => $product['name'],
    'description' => $product['description'] ?? '',
    'image' => $product['images'][0] ?? '',
    'url' => '/products/' . ($product['slug'] ?? ''),
    'type' => 'product'
]);

// 8. Add JSON-LD schema (for SEO)
\System\Libraries\Render\View::addSchema([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product['name'],
    'description' => $product['description'] ?? '',
    'image' => $product['images'] ?? [],
    'offers' => [
        '@type' => 'Offer',
        'price' => $product['price'] ?? 0,
        'priceCurrency' => 'VND'
    ]
], 'product-schema');
?>
```

**Giải thích từng bước:**

1. **Set namespace:** (Optional) Nếu template trong `Frontend/`, có thể set namespace
2. **Include header:** Load header template với data
3. **Add CSS:** Thêm CSS file cho trang này (minify=true cho production)
4. **Add JS:** Thêm JS file với dependencies (jquery phải load trước)
5. **Include footer:** Load footer template
6. **Set SEO:** Set title, description, canonical URL
7. **Open Graph:** Tags cho Facebook, Twitter sharing
8. **JSON-LD Schema:** Structured data cho Google SEO

---

## 🎨 BEST PRACTICES

### **1. Tổ Chức Code**

```php
// ✅ GOOD: Tổ chức rõ ràng
<?php
// 1. Setup
View::namespace('Frontend');
View::addCss('page-css', 'css/page.css', [], null, 'all', null, false, false, true);

// 2. Include header
echo View::include('Common/header', $headerData);

// 3. Main content
?>
<div class="content">
    <!-- HTML content -->
</div>
<?php

// 4. Include footer
echo View::include('Common/footer');

// 5. SEO & Assets
View::setTitle('Page Title');
echo View::js('footer');
?>
```

```php
// ❌ BAD: Lộn xộn, khó đọc
<?php View::namespace('Frontend'); View::addCss('page-css', 'css/page.css'); echo View::include('Common/header'); ?>
<div>Content</div>
<?php echo View::include('Common/footer'); View::setTitle('Title'); ?>
```

### **2. Security**

```php
// ✅ GOOD: Luôn escape output
<h1><?php echo htmlspecialchars($title); ?></h1>
<img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($alt); ?>">

// ❌ BAD: Không escape → XSS vulnerability
<h1><?php echo $title; ?></h1>
<img src="<?php echo $image; ?>">
```

### **3. Assets Management**

```php
// ✅ GOOD: Khai báo dependencies đúng
View::addJs('bootstrap', 'js/bootstrap.js', ['jquery'], 'footer', false, '1.0.0', false, false, true);
View::addJs('jquery', 'js/jquery.js', [], 'footer', false, '1.0.0', false, false, true);

// ❌ BAD: Không khai báo dependencies → load sai thứ tự
View::addJs('bootstrap', 'js/bootstrap.js', [], 'footer', false, '1.0.0', false, false, true);
View::addJs('jquery', 'js/jquery.js', [], 'footer', false, '1.0.0', false, false, true);
```

### **4. Performance**

```php
// ✅ GOOD: Minify cho production
View::addCss('style', 'css/style.css', [], null, 'all', null, false, false, true); // minify=true

// ✅ GOOD: Preload critical CSS
View::addCss('critical', 'css/critical.css', [], null, 'all', null, true, false, true); // preload=true

// ✅ GOOD: Defer non-critical JS
View::addJs('analytics', 'js/analytics.js', [], 'footer', true, '1.0.0', false, false, true); // defer=true
```

---

## ❓ FAQ

### **Q1: Làm sao để include một template?**

```php
// Include template trong cùng namespace
echo View::include('partials/sidebar', ['data' => $sidebarData]);

// Include template từ namespace khác
echo View::include('Common/header', ['title' => 'Page Title']);
```

### **Q2: Làm sao để share data cho tất cả templates?**

```php
// Share global data (trong controller hoặc bootstrap)
View::share('siteName', 'My Website');
View::share('user', $currentUser);

// Tất cả templates sẽ có $siteName và $user
```

### **Q3: Làm sao để check template exists?**

```php
if (View::exists('Products/single')) {
    // Template exists
}
```

### **Q4: Làm sao để get template path?**

```php
$path = View::getPath('Products/single');
// Returns: /path/to/themes/your-theme/Frontend/Products/single.php
```

### **Q5: Development vs Production khác nhau như thế nào?**

| Feature | Development | Production |
|---------|-------------|------------|
| Cache | ❌ Không cache | ✅ Có cache |
| Minify | ❌ File gốc | ✅ File .min |
| Debug | ✅ Hiển thị lỗi | ❌ Ẩn lỗi |
| Performance | Chậm hơn | Nhanh hơn |

---

## 📚 TÀI LIỆU THAM KHẢO

- [RENDER_API_GUIDE.md](./RENDER_API_GUIDE.md) - Bảng hàm helper & API (add_css, add_js, localize_script, view_head, …)
- [RENDER_HOOKS.md](./RENDER_HOOKS.md) - Danh sách filter/action
- [RENDER_V2_USAGE_GUIDE.md](./RENDER_V2_USAGE_GUIDE.md) - Hướng dẫn chi tiết (advanced)
- [COMPREHENSIVE_OPTIMIZATION_REPORT.md](./COMPREHENSIVE_OPTIMIZATION_REPORT.md) - Báo cáo tối ưu

---

## 🎓 TÓM TẮT NHANH

### **Workflow Cơ Bản:**

1. **Controller:**
   ```php
   return View::make('Frontend/Products/single', ['product' => $product])->render();
   ```

2. **Template:**
   ```php
   <?php echo View::include('Common/header'); ?>
   <div>Content</div>
   <?php echo View::include('Common/footer'); ?>
   ```

3. **Assets:**
   ```php
   View::addCss('style', 'css/style.css', [], null, 'all', null, false, false, true);
   View::addJs('main', 'js/main.js', [], 'footer', false, '1.0.0', false, false, true);
   ```

4. **SEO:**
   ```php
   View::setTitle('Page Title');
   View::setDescription('Page Description');
   View::setCanonical('/page-url');
   ```

### **Nhớ:**
- ✅ Luôn escape output: `htmlspecialchars($var)`
- ✅ Khai báo dependencies đúng cho assets
- ✅ Sử dụng `minify=true` cho production
- ✅ Tổ chức code rõ ràng, dễ đọc

---

**Chúc bạn code vui vẻ! 🚀**
