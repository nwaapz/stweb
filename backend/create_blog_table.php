<?php
/**
 * Quick script to create blog_posts table
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    // Create blog_posts table
    $conn->exec("
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
    
    echo "✓ جدول blog_posts با موفقیت ایجاد شد!\n";
    echo "می‌توانید به /backend/admin/blog.php بروید.\n";
    
} catch (PDOException $e) {
    echo "✗ خطا: " . $e->getMessage() . "\n";
    exit(1);
}

