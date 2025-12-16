<?php
/**
 * Database Migration Script
 * اسکریپت مهاجرت و راه‌اندازی پایگاه داده
 * 
 * این فایل را می‌توانید برای راه‌اندازی اولیه یا به‌روزرسانی پایگاه داده استفاده کنید
 * - اگر پایگاه داده وجود نداشته باشد، آن را ایجاد می‌کند
 * - اگر جداول وجود نداشته باشند، آنها را ایجاد می‌کند
 * - اگر ستون‌های جدید نیاز باشند، آنها را اضافه می‌کند
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$messages = [];
$success = false;

// Run migration automatically when accessed
try {
    // First, try to connect to MySQL server (without database)
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $dbExists = $stmt->rowCount() > 0;
    
    if (!$dbExists) {
        // Create database
        $pdo->exec("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci");
        $messages[] = "پایگاه داده با موفقیت ایجاد شد";
        $success = true;
    } else {
        $messages[] = "پایگاه داده از قبل وجود دارد";
    }
    
    // Use the database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Check and create tables
    $tables = [
        'categories' => "
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
        ",
        'factories' => "
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
        ",
        'vehicles' => "
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
        ",
        'products' => "
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
        ",
        'admin_users' => "
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
        ",
        'settings' => "
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                `setting_value` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        "
    ];
    
    // Create tables
    foreach ($tables as $tableName => $sql) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
        $tableExists = $stmt->rowCount() > 0;
        
        if (!$tableExists) {
            $pdo->exec($sql);
            $messages[] = "جدول $tableName با موفقیت ایجاد شد";
            $success = true;
        } else {
            $messages[] = "جدول $tableName از قبل وجود دارد";
        }
    }
    
    // Check and add missing columns to existing tables
    // Check products table for vehicle_id
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'vehicle_id'");
    if ($stmt->rowCount() == 0) {
        try {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `vehicle_id` INT DEFAULT NULL AFTER `category_id`");
            $messages[] = "ستون vehicle_id به جدول products اضافه شد";
            $success = true;
        } catch (PDOException $e) {
            // Column might already exist or table doesn't exist yet
        }
    }
    
    // Check vehicles table for factory_id
    $stmt = $pdo->query("SHOW COLUMNS FROM vehicles LIKE 'factory_id'");
    if ($stmt->rowCount() == 0) {
        try {
            $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `factory_id` INT DEFAULT NULL AFTER `id`");
            $messages[] = "ستون factory_id به جدول vehicles اضافه شد";
            $success = true;
        } catch (PDOException $e) {
            // Column might already exist
        }
    }
    
    // Add foreign keys if they don't exist
    try {
        // Check if vehicle_id foreign key exists
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
            AND TABLE_NAME = 'products' 
            AND COLUMN_NAME = 'vehicle_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `products` ADD FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL");
            $messages[] = "رابطه Foreign Key بین products و vehicles برقرار شد";
            $success = true;
        }
    } catch (PDOException $e) {
        // Foreign key might already exist
    }
    
    try {
        // Check if factory_id foreign key exists
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
            AND TABLE_NAME = 'vehicles' 
            AND COLUMN_NAME = 'factory_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE `vehicles` ADD FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL");
            $messages[] = "رابطه Foreign Key بین vehicles و factories برقرار شد";
            $success = true;
        }
    } catch (PDOException $e) {
        // Foreign key might already exist
    }
    
    // Create default admin user if it doesn't exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("
            INSERT INTO `admin_users` (`username`, `password`, `name`) 
            VALUES ('admin', '$defaultPassword', 'مدیر سیستم')
        ");
        $messages[] = "کاربر پیش‌فرض admin ایجاد شد (رمز: admin123)";
        $success = true;
    }
    
    // Create default settings if they don't exist
    $defaultSettings = [
        ['site_name', 'استارتک'],
        ['currency', 'تومان']
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$setting[0]]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
            $stmt->execute($setting);
            $messages[] = "تنظیم پیش‌فرض {$setting[0]} ایجاد شد";
            $success = true;
        }
    }
    
    if (empty($messages)) {
        $messages[] = "همه تغییرات از قبل اعمال شده‌اند - نیازی به مهاجرت نیست";
    }
    
} catch (PDOException $e) {
    $errors[] = "خطا: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مهاجرت پایگاه داده | استارتک</title>
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
        .migration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 700px;
            width: 100%;
        }
        .migration-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .migration-body {
            padding: 30px;
        }
        .message-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .message-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="migration-card">
        <div class="migration-header">
            <h1><i class="bi bi-database-check"></i></h1>
            <h2>مهاجرت و راه‌اندازی پایگاه داده</h2>
            <p class="mb-0">این صفحه برای راه‌اندازی اولیه یا به‌روزرسانی استفاده می‌شود</p>
        </div>
        <div class="migration-body">
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-x-circle-fill"></i>
                <strong>خطا!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php elseif (!empty($messages)): ?>
            <div class="alert alert-<?= $success ? 'success' : 'info' ?>">
                <i class="bi bi-<?= $success ? 'check-circle-fill' : 'info-circle-fill' ?>"></i>
                <strong><?= $success ? 'موفقیت!' : 'اطلاعات' ?></strong>
                <div class="mt-3">
                    <?php foreach ($messages as $message): ?>
                    <div class="message-item">
                        <i class="bi bi-<?= $success ? 'check' : 'info' ?>-circle text-<?= $success ? 'success' : 'info' ?>"></i>
                        <?= htmlspecialchars($message) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="bg-light p-3 rounded mb-3">
                <h6>تغییرات اعمال شده:</h6>
                <ul class="small mb-0">
                    <li>پایگاه داده و جداول ایجاد/بررسی شدند</li>
                    <li>ستون‌های جدید اضافه شدند (در صورت نیاز)</li>
                    <li>رابطه‌های Foreign Key برقرار شدند</li>
                    <li>کاربر پیش‌فرض و تنظیمات ایجاد شدند</li>
                </ul>
            </div>
            
            <div class="d-grid gap-2">
                <a href="admin/login.php" class="btn btn-success">
                    <i class="bi bi-box-arrow-in-right"></i> ورود به پنل مدیریت
                </a>
                <a href="admin/factories.php" class="btn btn-primary">
                    <i class="bi bi-building"></i> مدیریت کارخانجات
                </a>
                <a href="admin/vehicles.php" class="btn btn-primary">
                    <i class="bi bi-car-front"></i> مدیریت وسایل نقلیه
                </a>
            </div>
            
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                <small>
                    <strong>نکته امنیتی:</strong> برای امنیت بیشتر، پس از اطمینان از صحت کارکرد، 
                    این فایل (migrate.php) را حذف کنید یا دسترسی به آن را محدود کنید.
                </small>
            </div>
            
            <?php else: ?>
            <p class="text-muted mb-4">
                این اسکریپت به صورت خودکار اجرا می‌شود و:
            </p>
            
            <div class="bg-light p-3 rounded mb-4">
                <h6>عملکرد اسکریپت:</h6>
                <ul class="small mb-0">
                    <li>ایجاد پایگاه داده (در صورت عدم وجود)</li>
                    <li>ایجاد جداول مورد نیاز (categories, factories, vehicles, products, admin_users, settings)</li>
                    <li>افزودن ستون‌های جدید به جداول موجود</li>
                    <li>برقراری رابطه‌های Foreign Key</li>
                    <li>ایجاد کاربر پیش‌فرض admin (رمز: admin123)</li>
                    <li>ایجاد تنظیمات پیش‌فرض</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>نکته:</strong> این صفحه به صورت خودکار اجرا می‌شود. 
                صفحه را رفرش کنید تا نتیجه را ببینید.
            </div>
            
            <a href="migrate.php" class="btn btn-primary w-100">
                <i class="bi bi-arrow-clockwise"></i> رفرش صفحه
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

