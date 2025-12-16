# سیستم مدیریت محتوا استارتک

## نصب و راه‌اندازی

### پیش‌نیازها
- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر
- Apache یا Nginx

### مراحل نصب

1. **تنظیم پایگاه داده**
   - فایل `config/database.php` را باز کنید
   - اطلاعات اتصال به MySQL را تنظیم کنید:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'startech_cms');
   ```

2. **راه‌اندازی دیتابیس**
   - در مرورگر به آدرس زیر بروید:
   ```
   http://your-site.com/backend/setup.php
   ```
   - روی دکمه "ایجاد پایگاه داده" کلیک کنید

3. **ورود به پنل مدیریت**
   - به آدرس زیر بروید:
   ```
   http://your-site.com/backend/admin/
   ```
   - با اطلاعات پیش‌فرض وارد شوید:
     - **نام کاربری:** admin
     - **رمز عبور:** admin123

4. **امنیت**
   - بعد از نصب، فایل `setup.php` را حذف کنید
   - رمز عبور پیش‌فرض را تغییر دهید

---

## API برای فرانت‌اند

### دریافت محصولات
```
GET /backend/api/products.php
GET /backend/api/products.php?id=1           // محصول خاص
GET /backend/api/products.php?category=1     // بر اساس دسته‌بندی
GET /backend/api/products.php?vehicle=1      // بر اساس وسیله نقلیه
GET /backend/api/products.php?featured=1     // محصولات ویژه
GET /backend/api/products.php?discounted=1   // محصولات تخفیف‌دار
GET /backend/api/products.php?search=کلمه   // جستجو
GET /backend/api/products.php?limit=10       // محدود کردن نتایج
```

### دریافت دسته‌بندی‌ها
```
GET /backend/api/categories.php
GET /backend/api/categories.php?id=1         // دسته‌بندی خاص با محصولات
GET /backend/api/categories.php?tree=1       // ساختار درختی
```

### دریافت وسایل نقلیه
```
GET /backend/api/vehicles.php
GET /backend/api/vehicles.php?id=1          // وسیله نقلیه خاص با محصولات
GET /backend/api/vehicles.php?include_inactive=1  // شامل غیرفعال‌ها
```

### دریافت کارخانجات خودروسازی
```
GET /backend/api/factories.php
GET /backend/api/factories.php?id=1         // کارخانه خاص با وسایل نقلیه
GET /backend/api/factories.php?include_inactive=1  // شامل غیرفعال‌ها
```

### نمونه پاسخ
```json
{
    "success": true,
    "count": 10,
    "data": [
        {
            "id": 1,
            "name": "محصول نمونه",
            "price": 1000000,
            "discount_price": 800000,
            "has_discount": true,
            "formatted_price": "1,000,000 تومان",
            "image_url": "http://site.com/backend/uploads/products/image.jpg"
        }
    ]
}
```

---

## استفاده از API در JavaScript

```javascript
// دریافت همه محصولات
fetch('/backend/api/products.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(product => {
                console.log(product.name, product.formatted_price);
            });
        }
    });

// دریافت محصولات تخفیف‌دار
fetch('/backend/api/products.php?discounted=1')
    .then(response => response.json())
    .then(data => {
        // نمایش محصولات تخفیف‌دار
    });

// دریافت همه وسایل نقلیه
fetch('/backend/api/vehicles.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(vehicle => {
                console.log(vehicle.name, vehicle.product_count + ' محصول');
            });
        }
    });

// دریافت محصولات یک وسیله نقلیه خاص
fetch('/backend/api/products.php?vehicle=1')
    .then(response => response.json())
    .then(data => {
        // نمایش محصولات وسیله نقلیه
    });

// دریافت همه کارخانجات
fetch('/backend/api/factories.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.data.forEach(factory => {
                console.log(factory.name, factory.vehicle_count + ' وسیله');
            });
        }
    });
```

---

## ساختار پوشه‌ها

```
backend/
├── admin/              # پنل مدیریت
│   ├── index.php       # داشبورد
│   ├── products.php    # مدیریت محصولات
│   ├── categories.php  # مدیریت دسته‌بندی‌ها
│   ├── factories.php   # مدیریت کارخانجات خودروسازی
│   ├── vehicles.php    # مدیریت وسایل نقلیه
│   ├── discounts.php   # مدیریت تخفیف‌ها
│   ├── settings.php    # تنظیمات
│   └── ...
├── api/                # API endpoints
│   ├── products.php
│   ├── categories.php
│   ├── vehicles.php
│   └── factories.php
├── config/             # تنظیمات
│   └── database.php
├── includes/           # توابع کمکی
│   └── functions.php
├── uploads/            # فایل‌های آپلود شده
│   ├── products/
│   └── categories/
├── setup.php           # راه‌اندازی اولیه
└── .htaccess           # تنظیمات امنیتی
```

---

## نکات امنیتی

1. پس از نصب، فایل `setup.php` را حذف کنید
2. رمز عبور پیش‌فرض را فوراً تغییر دهید
3. دسترسی پوشه `config` را محدود کنید
4. از HTTPS استفاده کنید

---

## پشتیبانی

برای سوالات و مشکلات، یک Issue در مخزن ایجاد کنید.
