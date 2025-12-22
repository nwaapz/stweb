<?php
/**
 * Check if PHP GD extension is enabled
 * بررسی فعال بودن PHP GD extension
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بررسی PHP GD Extension</title>
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
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #e74c3c;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>بررسی PHP GD Extension</h1>
        
        <?php
        // Check if GD is loaded
        $gdLoaded = extension_loaded('gd');
        
        if ($gdLoaded) {
            echo '<div class="success"><strong>✓ موفق:</strong> PHP GD extension فعال است!</div>';
            
            // Get GD info
            if (function_exists('gd_info')) {
                $gdInfo = gd_info();
                echo '<div class="info"><h2>اطلاعات GD Extension:</h2>';
                echo '<ul>';
                echo '<li><strong>نسخه GD:</strong> ' . ($gdInfo['GD Version'] ?? 'نامشخص') . '</li>';
                echo '<li><strong>پشتیبانی از JPEG:</strong> ' . (isset($gdInfo['JPEG Support']) && $gdInfo['JPEG Support'] ? '✓ بله' : '✗ خیر') . '</li>';
                echo '<li><strong>پشتیبانی از PNG:</strong> ' . (isset($gdInfo['PNG Support']) && $gdInfo['PNG Support'] ? '✓ بله' : '✗ خیر') . '</li>';
                echo '<li><strong>پشتیبانی از GIF:</strong> ' . (isset($gdInfo['GIF Read Support']) && $gdInfo['GIF Read Support'] ? '✓ بله' : '✗ خیر') . '</li>';
                echo '<li><strong>پشتیبانی از WebP:</strong> ' . (isset($gdInfo['WebP Support']) && $gdInfo['WebP Support'] ? '✓ بله' : '✗ خیر') . '</li>';
                echo '</ul></div>';
            }
        } else {
            echo '<div class="error"><strong>✗ خطا:</strong> PHP GD extension فعال نیست!</div>';
            echo '<div class="warning"><h2>راهنمای فعال‌سازی GD Extension در XAMPP:</h2>';
            echo '<ol>';
            echo '<li><strong>مسیر فایل php.ini را پیدا کنید:</strong><br>';
            echo 'مسیر معمول: <code>C:\\xampp\\php\\php.ini</code></li>';
            echo '<li><strong>فایل php.ini را با Notepad++ یا ویرایشگر متنی باز کنید</strong> (به عنوان Administrator)</li>';
            echo '<li><strong>خط زیر را پیدا کنید:</strong><br>';
            echo '<code>;extension=gd</code></li>';
            echo '<li><strong>علامت سمی‌کالن (;) را از ابتدای خط حذف کنید:</strong><br>';
            echo '<code>extension=gd</code></li>';
            echo '<li><strong>فایل را ذخیره کنید</strong></li>';
            echo '<li><strong>Apache را در XAMPP Control Panel راه‌اندازی مجدد کنید</strong></li>';
            echo '<li><strong>این صفحه را Refresh کنید تا بررسی کنید</strong></li>';
            echo '</ol></div>';
            
            echo '<div class="info"><h2>مسیر فایل php.ini:</h2>';
            $phpIniPath = php_ini_loaded_file();
            if ($phpIniPath) {
                echo '<p><strong>فایل php.ini فعلی:</strong><br><code>' . htmlspecialchars($phpIniPath) . '</code></p>';
            } else {
                echo '<p>فایل php.ini یافت نشد. مسیر پیش‌فرض: <code>C:\\xampp\\php\\php.ini</code></p>';
            }
            echo '</div>';
        }
        
        // Show PHP version
        echo '<div class="info"><h2>اطلاعات PHP:</h2>';
        echo '<ul>';
        echo '<li><strong>نسخه PHP:</strong> ' . PHP_VERSION . '</li>';
        echo '<li><strong>مسیر PHP:</strong> ' . PHP_BINARY . '</li>';
        echo '<li><strong>مسیر php.ini:</strong> ' . (php_ini_loaded_file() ?: 'نامشخص') . '</li>';
        echo '</ul></div>';
        ?>
        
        <div class="info">
            <h2>نکات مهم:</h2>
            <ul>
                <li>بعد از تغییر فایل php.ini، حتماً Apache را Restart کنید</li>
                <li>اگر بعد از فعال‌سازی هنوز خطا می‌گیرید، مطمئن شوید که فایل php.ini درست را ویرایش کرده‌اید</li>
                <li>برای بررسی فایل php.ini درست، از <code>phpinfo()</code> استفاده کنید</li>
            </ul>
        </div>
        
        <br>
        <a href="admin/about.php?action=team" style="display: inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px;">بازگشت به مدیریت تیم</a>
    </div>
</body>
</html>

