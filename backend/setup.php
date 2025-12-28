<?php
/**
 * Database Setup Script
 * اسکریپت راه‌اندازی پایگاه داده
 * 
 * این فایل را یکبار اجرا کنید تا پایگاه داده ایجاد شود
 */

// Database credentials - Edit these values
$DB_HOST = 'localhost';
$DB_USER = 'startech_sms';
$DB_PASS = '101010';
$DB_NAME = 'startech_sms';
$DB_CHARSET = 'utf8mb4';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    try {
        // Connect without database
        $pdo = new PDO(
            "mysql:host=$DB_HOST;charset=$DB_CHARSET",
            $DB_USER,
            $DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci");
        $pdo->exec("USE `$DB_NAME`");
        
        // Create categories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `image` VARCHAR(500),
                `parent_id` INT DEFAULT NULL,
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create factories table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `factories` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `logo` VARCHAR(500),
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create vehicles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `vehicles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `factory_id` INT DEFAULT NULL,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `is_active` TINYINT(1) DEFAULT 1,
                `sort_order` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create products table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `products` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `category_id` INT,
                `vehicle_id` INT DEFAULT NULL,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `short_description` VARCHAR(500),
                `price` DECIMAL(15, 0) NOT NULL DEFAULT 0,
                `discount_price` DECIMAL(15, 0) DEFAULT NULL,
                `discount_percent` INT DEFAULT NULL,
                `discount_start` DATETIME DEFAULT NULL,
                `discount_end` DATETIME DEFAULT NULL,
                `image` VARCHAR(500),
                `gallery` TEXT,
                `sku` VARCHAR(100),
                `stock` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `is_featured` TINYINT(1) DEFAULT 0,
                `views` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create admin users table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255),
                `email` VARCHAR(255),
                `is_active` TINYINT(1) DEFAULT 1,
                `last_login` DATETIME,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create default admin user
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT IGNORE INTO `admin_users` (`username`, `password`, `name`) 
            VALUES ('admin', '$defaultPassword', 'مدیر سیستم')
        ");
        
        // Create settings table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create blog_posts table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `blog_posts` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `content` TEXT NOT NULL,
                `excerpt` TEXT,
                `featured_image` VARCHAR(500),
                `author_id` INT DEFAULT NULL,
                `is_published` TINYINT(1) DEFAULT 0,
                `published_at` DATETIME DEFAULT NULL,
                `views` INT DEFAULT 0,
                `meta_title` VARCHAR(255),
                `meta_description` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`author_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create users table (matching migrate_users.php structure)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `phone` VARCHAR(15) NOT NULL UNIQUE,
                `name` VARCHAR(255),
                `email` VARCHAR(255),
                `password` VARCHAR(255),
                `avatar` VARCHAR(500),
                `is_active` TINYINT(1) DEFAULT 1,
                `is_blocked` TINYINT(1) DEFAULT 0,
                `last_login` DATETIME,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_phone` (`phone`),
                INDEX `idx_is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Migrate existing users table from old structure to new structure
        try {
            // Check if table has old structure (first_name column exists)
            $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'first_name'");
            if ($stmt->rowCount() > 0) {
                // Old structure exists - migrate it
                // Check if phone column exists
                $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'phone'");
                $phoneExists = $stmt->rowCount() > 0;
                
                if (!$phoneExists) {
                    // Add phone column (nullable for now, will be populated from email or set to default)
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(15) NULL AFTER `id`");
                }
                
                // Add name column if it doesn't exist
                $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'name'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `name` VARCHAR(255) NULL AFTER `phone`");
                    // Populate name from first_name and last_name
                    $pdo->exec("UPDATE `users` SET `name` = CONCAT(COALESCE(`first_name`, ''), ' ', COALESCE(`last_name`, '')) WHERE `name` IS NULL OR `name` = ''");
                }
                
                // Add missing columns
                $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'avatar'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(500) NULL AFTER `email`");
                }
                
                $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'is_blocked'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_blocked` TINYINT(1) DEFAULT 0 AFTER `is_active`");
                }
                
                $stmt = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'last_login'");
                if ($stmt->rowCount() == 0) {
                    $pdo->exec("ALTER TABLE `users` ADD COLUMN `last_login` DATETIME NULL AFTER `is_blocked`");
                }
                
                // Set default phone for users without phone (use a placeholder)
                $pdo->exec("UPDATE `users` SET `phone` = CONCAT('09', LPAD(`id`, 9, '0')) WHERE `phone` IS NULL OR `phone` = ''");
                
                // Make phone NOT NULL and UNIQUE after populating
                try {
                    $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `phone` VARCHAR(15) NOT NULL");
                    $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_phone_unique` (`phone`)");
                } catch (PDOException $e) {
                    // Index might already exist, ignore
                }
                
                // Remove old columns (optional - comment out if you want to keep them)
                // $pdo->exec("ALTER TABLE `users` DROP COLUMN `first_name`");
                // $pdo->exec("ALTER TABLE `users` DROP COLUMN `last_name`");
            }
        } catch (PDOException $e) {
            // Migration failed, but continue
            error_log("Users table migration error: " . $e->getMessage());
        }
        
        // Create OTP codes table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `otp_codes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `phone` VARCHAR(15) NOT NULL,
                `code` VARCHAR(6) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `is_used` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_phone_code` (`phone`, `code`),
                INDEX `idx_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create user_sessions table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_sessions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `token` VARCHAR(64) NOT NULL UNIQUE,
                `ip_address` VARCHAR(45),
                `user_agent` TEXT,
                `expires_at` DATETIME NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_token` (`token`),
                INDEX `idx_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create user_addresses table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_addresses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `title` VARCHAR(100),
                `recipient_name` VARCHAR(255) NOT NULL,
                `landline` VARCHAR(15) NOT NULL,
                `province` VARCHAR(100) NOT NULL,
                `city` VARCHAR(100) NOT NULL,
                `address` TEXT NOT NULL,
                `postal_code` VARCHAR(20),
                `is_default` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create cart table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cart` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
                INDEX `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create orders table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `orders` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `order_number` VARCHAR(20) NOT NULL UNIQUE,
                `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                `subtotal` DECIMAL(15, 0) NOT NULL DEFAULT 0,
                `shipping_cost` DECIMAL(15, 0) DEFAULT 0,
                `discount_amount` DECIMAL(15, 0) DEFAULT 0,
                `total` DECIMAL(15, 0) NOT NULL DEFAULT 0,
                `shipping_address_id` INT,
                `shipping_name` VARCHAR(255),
                `shipping_phone` VARCHAR(15),
                `shipping_province` VARCHAR(100),
                `shipping_city` VARCHAR(100),
                `shipping_address` TEXT,
                `shipping_postal_code` VARCHAR(20),
                `notes` TEXT,
                `admin_notes` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                INDEX `idx_user` (`user_id`),
                INDEX `idx_status` (`status`),
                INDEX `idx_order_number` (`order_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create order_items table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `order_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT NOT NULL,
                `product_id` INT,
                `product_name` VARCHAR(255) NOT NULL,
                `product_image` VARCHAR(500),
                `product_sku` VARCHAR(100),
                `price` DECIMAL(15, 0) NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `total` DECIMAL(15, 0) NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
                INDEX `idx_order` (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create wishlists table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `wishlists` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
                INDEX `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create compares table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `compares` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
                INDEX `idx_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create provinces table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `provinces` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `name_en` VARCHAR(255),
                `slug` VARCHAR(255) NOT NULL UNIQUE,
                `description` TEXT,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create branches table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `branches` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `province_id` INT NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `address` TEXT NOT NULL,
                `phone` VARCHAR(50),
                `email` VARCHAR(255),
                `latitude` DECIMAL(10, 8),
                `longitude` DECIMAL(11, 8),
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`province_id`) REFERENCES `provinces`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create user_vehicles table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `user_vehicles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `vehicle_id` INT DEFAULT NULL,
                `factory_id` INT DEFAULT NULL,
                `custom_brand` VARCHAR(255) DEFAULT NULL,
                `custom_model` VARCHAR(255) DEFAULT NULL,
                `engine` VARCHAR(255) DEFAULT NULL,
                `year` INT DEFAULT NULL,
                `vin` VARCHAR(255) DEFAULT NULL,
                `type` ENUM('car', 'motorcycle', 'truck') DEFAULT 'car',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create about_page table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `about_page` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL DEFAULT 'درباره ما',
                `description` TEXT,
                `author_name` VARCHAR(255),
                `author_title` VARCHAR(255),
                `feature_image` VARCHAR(500),
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create about_team table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `about_team` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `position` VARCHAR(255) NOT NULL,
                `description` TEXT,
                `image` VARCHAR(500),
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create about_testimonials table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `about_testimonials` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `text` TEXT NOT NULL,
                `author_name` VARCHAR(255) NOT NULL,
                `author_title` VARCHAR(255),
                `rating` INT DEFAULT 5,
                `avatar` VARCHAR(500),
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Create about_statistics table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `about_statistics` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `value` VARCHAR(50) NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `sort_order` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
        
        // Add default settings
        $pdo->exec("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('site_name', 'استارتک')");
        $pdo->exec("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES ('currency', 'تومان')");
        
        $success = true;
        
    } catch (PDOException $e) {
        $errors[] = "خطا: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>راه‌اندازی پایگاه داده | استارتک</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .setup-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .setup-body {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-header">
            <h1><i class="bi bi-database-gear"></i></h1>
            <h2>راه‌اندازی پایگاه داده</h2>
        </div>
        <div class="setup-body">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <strong>موفقیت!</strong> پایگاه داده با موفقیت ایجاد شد.
            </div>
            
            <div class="bg-light p-3 rounded mb-3">
                <h5>اطلاعات ورود پیش‌فرض:</h5>
                <p class="mb-1"><strong>نام کاربری:</strong> <code>admin</code></p>
                <p class="mb-0"><strong>رمز عبور:</strong> <code>admin123</code></p>
            </div>
            
            <a href="admin/login.php" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-left"></i> ورود به پنل مدیریت
            </a>
            
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                <small>برای امنیت بیشتر، پس از راه‌اندازی این فایل (setup.php) را حذف کنید.</small>
            </div>
            
            <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle-fill"></i>
                <strong>خطا!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="bg-light p-3 rounded">
                <h6>راهنمای رفع مشکل:</h6>
                <ul class="small mb-0">
                    <li>اطمینان حاصل کنید که MySQL/MariaDB در حال اجراست</li>
                    <li>تنظیمات اتصال را در فایل <code>config/database.php</code> بررسی کنید</li>
                    <li>مطمئن شوید کاربر MySQL دسترسی ایجاد دیتابیس دارد</li>
                </ul>
            </div>
            
            <?php else: ?>
            <p class="text-muted mb-4">
                با کلیک روی دکمه زیر، پایگاه داده و جداول مورد نیاز ایجاد می‌شوند.
            </p>
            
            <div class="bg-light p-3 rounded mb-4">
                <h6>تنظیمات اتصال:</h6>
                <ul class="small mb-0">
                    <li><strong>سرور:</strong> <?= $DB_HOST ?></li>
                    <li><strong>نام کاربری:</strong> <?= $DB_USER ?></li>
                    <li><strong>نام دیتابیس:</strong> <?= $DB_NAME ?></li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-database-add"></i> ایجاد پایگاه داده
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
