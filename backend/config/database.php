<?php
/**
 * Database Configuration
 * تنظیمات پایگاه داده
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'startech_sms');
define('DB_PASS', '101010');
define('DB_NAME', 'startech_sms');
define('DB_CHARSET', 'utf8mb4');

// Site settings
define('SITE_URL', 'https://startechgroup.ir/test/backend');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get database connection
 * @return PDO|null
 */
function getConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            // First try to connect to the database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If database doesn't exist, try to create it
            if (strpos($e->getMessage(), 'Unknown database') !== false) {
                if (setupDatabase()) {
                    // Retry connection after setup
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    $conn = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } else {
                    die("خطا در ایجاد پایگاه داده");
                }
            } else {
                die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
            }
        }
    }

    return $conn;
}

/**
 * Setup database and tables
 * @return bool
 */
function setupDatabase()
{
    try {
        // Connect without database name
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci");
        $pdo->exec("USE `" . DB_NAME . "`");

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

        // Create default admin user (password: admin123)
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

        // Add vehicle_id column to products table if it doesn't exist (for existing databases)
        try {
            $pdo->exec("ALTER TABLE `products` ADD COLUMN `vehicle_id` INT DEFAULT NULL AFTER `category_id`");
            $pdo->exec("ALTER TABLE `products` ADD FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL");
        } catch (PDOException $e) {
            // Column might already exist, ignore error
        }

        // Add factory_id column to vehicles table if it doesn't exist (for existing databases)
        try {
            $pdo->exec("ALTER TABLE `vehicles` ADD COLUMN `factory_id` INT DEFAULT NULL AFTER `id`");
            $pdo->exec("ALTER TABLE `vehicles` ADD FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL");
        } catch (PDOException $e) {
            // Column might already exist, ignore error
        }

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

        return true;
    } catch (PDOException $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}
?>