<?php
/**
 * Quick check for about page tables
 * بررسی سریع جداول صفحه درباره ما
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بررسی جداول صفحه درباره ما</title>
    <style>
        body {
            font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e74c3c;
        }
        .table-check {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>بررسی جداول صفحه درباره ما</h1>
        
        <?php
        try {
            $conn = getConnection();
            $tables = ['about_page', 'about_team', 'about_testimonials', 'about_statistics'];
            $allExist = true;
            
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SELECT 1 FROM `$table` LIMIT 1");
                    echo "<div class='table-check success'>✓ جدول <strong>$table</strong> وجود دارد</div>";
                } catch (PDOException $e) {
                    echo "<div class='table-check error'>✗ جدول <strong>$table</strong> وجود ندارد</div>";
                    $allExist = false;
                }
            }
            
            if ($allExist) {
                echo "<div class='table-check success'><strong>همه جداول موجود هستند! ✓</strong></div>";
                echo "<p>می‌توانید به <a href='admin/about.php'>صفحه مدیریت درباره ما</a> بروید.</p>";
            } else {
                echo "<div class='table-check error'><strong>برخی جداول وجود ندارند!</strong></div>";
                echo "<p>لطفاً ابتدا فایل مایگریشن را اجرا کنید:</p>";
                echo "<a href='migrate_about.php' class='btn'>اجرای مایگریشن</a>";
            }
        } catch (Exception $e) {
            echo "<div class='table-check error'>خطا: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
    </div>
</body>
</html>

