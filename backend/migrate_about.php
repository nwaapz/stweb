<?php
/**
 * About Page Database Migration
 * مایگریشن پایگاه داده صفحه درباره ما
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    // Create about_page table for main content
    $conn->exec("
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
    
    // Create about_team table for team members
    $conn->exec("
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
    
    // Create about_testimonials table for customer testimonials
    $conn->exec("
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
    
    // Create about_statistics table for statistics/indicators
    $conn->exec("
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
    
    // Insert default about page content if not exists
    $stmt = $conn->query("SELECT COUNT(*) FROM about_page");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO about_page (title, description, author_name, author_title) 
            VALUES (
                'درباره ما',
                'RedParts یک شرکت بین‌المللی با ۳۰ سال سابقه در فروش قطعات یدکی خودرو، کامیون و موتورسیکلت است. در طول فعالیت خود، موفق به ایجاد یک سرویس منحصر به فرد برای فروش و تحویل قطعات یدکی در سراسر جهان شده‌ایم.',
                'حسین عبدالمحمدی',
                'CEO RedParts'
            )
        ");
    }
    
    // Insert default statistics if not exists
    $stmt = $conn->query("SELECT COUNT(*) FROM about_statistics");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("
            INSERT INTO about_statistics (value, title, sort_order) VALUES
            ('350', 'در سراسر ایران', 1),
            ('80 000', 'قطعات اصلی خودرو', 2),
            ('5 000', 'مشتریان راضی', 3)
        ");
    }
    
    echo "✓ Tables created successfully!\n";
    echo "✓ Default data inserted!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

