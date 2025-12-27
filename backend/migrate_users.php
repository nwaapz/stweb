<?php
/**
 * User Tables Migration Script
 * اسکریپت ایجاد جداول کاربران و سبد خرید
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = false;
$tablesCreated = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    try {
        $conn = getConnection();

        // Create users table
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `phone` VARCHAR(15) NOT NULL UNIQUE,
                `name` VARCHAR(255),
                `email` VARCHAR(255),
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
        $tablesCreated[] = 'users';

        // Create OTP codes table
        $conn->exec("
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
        $tablesCreated[] = 'otp_codes';

        // Create user sessions table
        $conn->exec("
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
        $tablesCreated[] = 'user_sessions';

        // Create user addresses table
        $conn->exec("
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
        $tablesCreated[] = 'user_addresses';

        // Create cart table
        $conn->exec("
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
        $tablesCreated[] = 'cart';

        // Create orders table
        $conn->exec("
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
        $tablesCreated[] = 'orders';

        // Create order items table
        $conn->exec("
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
        $tablesCreated[] = 'order_items';

        // Create wishlist table
        $conn->exec("
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
        $tablesCreated[] = 'wishlists';

        // Create compare table
        $conn->exec("
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
        $tablesCreated[] = 'compares';

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
    <title>ایجاد جداول کاربران | استارتک</title>
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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }

        .setup-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .setup-body {
            padding: 30px;
        }

        .table-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .table-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="setup-card">
        <div class="setup-header">
            <h1><i class="bi bi-people-fill"></i></h1>
            <h2>ایجاد جداول کاربران</h2>
        </div>
        <div class="setup-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>موفقیت!</strong> جداول با موفقیت ایجاد شدند.
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <h5>جداول ایجاد شده:</h5>
                    <div class="table-items">
                        <?php foreach ($tablesCreated as $table): ?>
                            <div class="table-item">
                                <i class="bi bi-check-circle text-success"></i>
                                <code><?= $table ?></code>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <a href="admin/index.php" class="btn btn-primary w-100">
                    <i class="bi bi-speedometer2"></i> بازگشت به داشبورد
                </a>

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

            <?php else: ?>
                <p class="text-muted mb-4">
                    با کلیک روی دکمه زیر، جداول مورد نیاز برای سیستم کاربران ایجاد می‌شوند.
                </p>

                <div class="bg-light p-3 rounded mb-4">
                    <h6>جداول مورد نیاز:</h6>
                    <ul class="small mb-0">
                        <li><strong>users</strong> - اطلاعات مشتریان</li>
                        <li><strong>otp_codes</strong> - کدهای تایید پیامکی</li>
                        <li><strong>user_sessions</strong> - نشست‌های کاربری</li>
                        <li><strong>user_addresses</strong> - آدرس‌های کاربران</li>
                        <li><strong>cart</strong> - سبد خرید</li>
                        <li><strong>orders</strong> - سفارشات</li>
                        <li><strong>order_items</strong> - آیتم‌های سفارش</li>
                        <li><strong>wishlists</strong> - لیست علاقه‌مندی</li>
                    </ul>
                </div>

                <form method="POST">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-database-add"></i> ایجاد جداول
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>