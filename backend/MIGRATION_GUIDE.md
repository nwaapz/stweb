# راهنمای اجرای مایگریشن‌ها (Migration Guide)

## بررسی سریع وضعیت مایگریشن‌ها

برای بررسی و اجرای همه مایگریشن‌ها، فایل زیر را در مرورگر باز کنید:

```
http://localhost/backend/run_all_migrations.php
```

این فایل به صورت خودکار:
- وضعیت همه مایگریشن‌ها را بررسی می‌کند
- جداول موجود و مفقود را نشان می‌دهد
- امکان اجرای همه مایگریشن‌ها را فراهم می‌کند

## فهرست مایگریشن‌ها (به ترتیب اجرا)

### 1. migrate.php
**جداول:** categories, factories, vehicles, products, admin_users, settings
- پایگاه داده اصلی و جداول اولیه
- **اجرا:** `http://localhost/backend/migrate.php`

### 2. migrate_users.php
**جداول:** users, otp_codes, user_sessions, user_addresses, cart, orders, order_items, wishlists
- جداول کاربران، سبد خرید، سفارشات
- **اجرا:** `http://localhost/backend/migrate_users.php?auto=1`

### 3. migrate_vehicles.php
**جداول:** vehicles (افزودن ستون‌های جدید)
- پشتیبانی کامل از وسایل نقلیه
- **اجرا:** `http://localhost/backend/migrate_vehicles.php?auto=1`

### 4. migrate_user_garage.php
**جداول:** user_vehicles
- گاراژ کاربران
- **اجرا:** `http://localhost/backend/migrate_user_garage.php?auto=1`

### 5. migrate_blog_table.php
**جداول:** blog_posts
- سیستم بلاگ
- **اجرا:** `http://localhost/backend/migrate_blog_table.php?auto=1`

### 6. migrate_about.php
**جداول:** about_page, about_team, about_testimonials
- صفحه درباره ما
- **اجرا:** `http://localhost/backend/migrate_about.php?auto=1`

### 7. migrate_branches.php
**جداول:** branches
- مدیریت شعبه‌ها
- **اجرا:** `http://localhost/backend/migrate_branches.php?auto=1`

## دستورالعمل اجرا

### روش 1: اجرای خودکار همه مایگریشن‌ها
1. باز کردن: `http://localhost/backend/run_all_migrations.php`
2. کلیک روی دکمه "اجرای همه مایگریشن‌های لازم"
3. بررسی نتایج

### روش 2: اجرای دستی هر مایگریشن
1. باز کردن هر فایل مایگریشن در مرورگر
2. کلیک روی دکمه "ایجاد جداول" یا "اجرای مایگریشن"
3. تکرار برای همه مایگریشن‌ها

## بررسی دستی جداول

برای بررسی اینکه آیا جدولی وجود دارد یا نه، می‌توانید از phpMyAdmin یا دستور SQL زیر استفاده کنید:

```sql
SHOW TABLES;
```

یا برای بررسی ستون‌های یک جدول:

```sql
DESCRIBE table_name;
```

## نکات مهم

1. **ترتیب اجرا مهم است:** مایگریشن‌ها باید به ترتیب اجرا شوند
2. **پشتیبان‌گیری:** قبل از اجرای مایگریشن‌ها، از پایگاه داده پشتیبان بگیرید
3. **تست:** پس از اجرای مایگریشن‌ها، عملکرد سیستم را تست کنید
4. **امنیت:** پس از اطمینان از صحت کارکرد، فایل‌های مایگریشن را حذف یا محافظت کنید

## عیب‌یابی

### خطا: "Table already exists"
این خطا طبیعی است و به معنای این است که جدول از قبل وجود دارد.

### خطا: "Foreign key constraint fails"
مطمئن شوید که مایگریشن‌های پایه (migrate.php و migrate_users.php) ابتدا اجرا شده‌اند.

### خطا: "Access denied"
اطمینان حاصل کنید که اطلاعات اتصال به پایگاه داده در `config/database.php` صحیح است.


