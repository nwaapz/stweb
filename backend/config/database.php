<?php
/**
 * Database Configuration
 * تنظیمات پایگاه داده
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'startech_cms');
define('DB_CHARSET', 'utf8mb4');

// Site settings
define('SITE_URL', 'http://localhost/backend');
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
function getConnection() {
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
function setupDatabase() {
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
        
        return true;
    } catch (PDOException $e) {
        error_log("Database setup error: " . $e->getMessage());
        return false;
    }
}
?>
