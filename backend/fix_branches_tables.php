<?php
/**
 * Quick Fix Script for Branches Tables
 * اسکریپت سریع برای ایجاد جداول شعب
 * 
 * Run this file once to create the missing branches and provinces tables
 */

require_once __DIR__ . '/config/database.php';

try {
    $conn = getConnection();
    
    // Create provinces table
    $conn->exec("
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
    $conn->exec("
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
    
    echo "✓ Tables created successfully!\n";
    echo "✓ provinces table created\n";
    echo "✓ branches table created\n";
    echo "\nYou can now access the branches page without errors.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

