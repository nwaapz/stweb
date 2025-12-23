<?php
/**
 * Migration Script - Add blog_posts table
 * اسکریپت مهاجرت - افزودن جدول blog_posts
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = false;
$tableExists = false;

// Check if table already exists
try {
    $conn = getConnection();
    $stmt = $conn->query("SHOW TABLES LIKE 'blog_posts'");
    $tableExists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    // Ignore
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
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
        
        $success = true;
        $tableExists = true;
        
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
    <title>افزودن جدول وبلاگ | استارتک</title>
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
            max-width: 500px;
            width: 100%;
        }
        .migration-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
            <h1><i class="bi bi-database-add"></i></h1>
            <h2>افزودن جدول وبلاگ</h2>
        </div>
        <div class="migration-body">
            <?php if ($tableExists && !$success): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                <strong>اطلاع!</strong> جدول blog_posts از قبل وجود دارد.
            </div>
            <div class="d-grid gap-2">
                <a href="admin/blog.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> رفتن به مدیریت وبلاگ
                </a>
            </div>
            <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <strong>موفقیت!</strong> جدول blog_posts با موفقیت ایجاد شد.
            </div>
            <div class="d-grid gap-2">
                <a href="admin/blog.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> رفتن به مدیریت وبلاگ
                </a>
            </div>
            <?php elseif (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>خطا!</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <form method="POST">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-arrow-clockwise"></i> تلاش مجدد
                    </button>
                </div>
            </form>
            <?php else: ?>
            <p class="text-muted mb-4">
                این اسکریپت جدول <code>blog_posts</code> را به پایگاه داده شما اضافه می‌کند.
            </p>
            <form method="POST">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-database-check"></i> ایجاد جدول
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

