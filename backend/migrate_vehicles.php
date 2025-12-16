<?php
/**
 * Migration Script - Add Vehicles Support
 * اسکریپت مهاجرت - افزودن پشتیبانی از وسایل نقلیه
 * 
 * این فایل را یکبار اجرا کنید تا جدول vehicles و ستون vehicle_id به جدول products اضافه شود
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = false;
$messages = [];

// Run migration automatically when accessed
try {
    $conn = getConnection();
    
    // Check if factories table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'factories'");
    $factoriesTableExists = $stmt->rowCount() > 0;
    
    // Check if vehicles table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'vehicles'");
    $vehiclesTableExists = $stmt->rowCount() > 0;
    
    // Check if vehicle_id column exists in products table
    $stmt = $conn->query("SHOW COLUMNS FROM products LIKE 'vehicle_id'");
    $vehicleIdColumnExists = $stmt->rowCount() > 0;
    
    // Check if factory_id column exists in vehicles table
    $stmt = $conn->query("SHOW COLUMNS FROM vehicles LIKE 'factory_id'");
    $factoryIdColumnExists = $stmt->rowCount() > 0;
    
    if (!$factoriesTableExists) {
        // Create factories table
        $conn->exec("
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
        $success = true;
        $messages[] = "جدول factories با موفقیت ایجاد شد";
    } else {
        $messages[] = "جدول factories از قبل وجود دارد";
    }
    
    if (!$vehiclesTableExists) {
        // Create vehicles table
        $conn->exec("
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
        $success = true;
        $messages[] = "جدول vehicles با موفقیت ایجاد شد";
    } else {
        $messages[] = "جدول vehicles از قبل وجود دارد";
    }
    
    if (!$factoryIdColumnExists && $vehiclesTableExists) {
        // Add factory_id column to vehicles table
        $conn->exec("ALTER TABLE `vehicles` ADD COLUMN `factory_id` INT DEFAULT NULL AFTER `id`");
        
        // Add foreign key constraint
        try {
            $conn->exec("ALTER TABLE `vehicles` ADD FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL");
        } catch (PDOException $e) {
            // Foreign key might already exist, ignore
            if (strpos($e->getMessage(), 'Duplicate foreign key') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
        
        $success = true;
        $messages[] = "ستون factory_id به جدول vehicles اضافه شد";
    } else {
        if ($factoryIdColumnExists) {
            $messages[] = "ستون factory_id از قبل وجود دارد";
        }
    }
    
    if (!$vehicleIdColumnExists) {
        // Add vehicle_id column to products table
        $conn->exec("ALTER TABLE `products` ADD COLUMN `vehicle_id` INT DEFAULT NULL AFTER `category_id`");
        
        // Add foreign key constraint
        try {
            $conn->exec("ALTER TABLE `products` ADD FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL");
        } catch (PDOException $e) {
            // Foreign key might already exist, ignore
            if (strpos($e->getMessage(), 'Duplicate foreign key') === false && 
                strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
        
        $success = true;
        $messages[] = "ستون vehicle_id به جدول products اضافه شد";
    } else {
        $messages[] = "ستون vehicle_id از قبل وجود دارد";
    }
    
    if ($factoriesTableExists && $vehiclesTableExists && $factoryIdColumnExists && $vehicleIdColumnExists) {
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
            max-width: 600px;
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
    </style>
</head>
<body>
    <div class="migration-card">
        <div class="migration-header">
            <h1><i class="bi bi-database-check"></i></h1>
            <h2>مهاجرت پایگاه داده</h2>
            <p class="mb-0">افزودن پشتیبانی از وسایل نقلیه</p>
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
            <?php elseif ($success || isset($messages)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <strong>موفقیت!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($messages as $message): ?>
                    <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="bg-light p-3 rounded mb-3">
                <h6>تغییرات اعمال شده:</h6>
                <ul class="small mb-0">
                    <li>جدول <code>factories</code> ایجاد شد</li>
                    <li>جدول <code>vehicles</code> ایجاد شد</li>
                    <li>ستون <code>factory_id</code> به جدول <code>vehicles</code> اضافه شد</li>
                    <li>ستون <code>vehicle_id</code> به جدول <code>products</code> اضافه شد</li>
                    <li>رابطه Foreign Key بین جداول برقرار شد</li>
                </ul>
            </div>
            
            <a href="admin/vehicles.php" class="btn btn-success w-100 mb-2">
                <i class="bi bi-car-front"></i> مدیریت وسایل نقلیه
            </a>
            <a href="admin/products.php" class="btn btn-primary w-100">
                <i class="bi bi-box"></i> بازگشت به محصولات
            </a>
            
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                <small>برای امنیت بیشتر، پس از اطمینان از صحت کارکرد، این فایل (migrate_vehicles.php) را حذف کنید.</small>
            </div>
            
            <?php else: ?>
            <p class="text-muted mb-4">
                این اسکریپت جدول vehicles و ستون vehicle_id را به پایگاه داده شما اضافه می‌کند.
            </p>
            
            <div class="bg-light p-3 rounded mb-4">
                <h6>تغییراتی که اعمال می‌شود:</h6>
                <ul class="small mb-0">
                    <li>ایجاد جدول <code>factories</code> برای ذخیره کارخانجات خودروسازی</li>
                    <li>ایجاد جدول <code>vehicles</code> برای ذخیره وسایل نقلیه</li>
                    <li>افزودن ستون <code>factory_id</code> به جدول <code>vehicles</code></li>
                    <li>افزودن ستون <code>vehicle_id</code> به جدول <code>products</code></li>
                    <li>ایجاد رابطه Foreign Key بین جداول</li>
                </ul>
            </div>
            
            <p class="text-center text-muted">
                <small>اسکریپت به صورت خودکار اجرا شد. صفحه را رفرش کنید تا نتیجه را ببینید.</small>
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

