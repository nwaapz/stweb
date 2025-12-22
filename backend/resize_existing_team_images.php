<?php
/**
 * Resize existing team member images to 600x800
 * تغییر اندازه تصاویر موجود اعضای تیم به 600x800
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تغییر اندازه تصاویر تیم</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>تغییر اندازه تصاویر اعضای تیم به 600×800</h1>
        
        <?php
        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            echo '<div class="error"><strong>خطا:</strong> PHP GD extension فعال نیست. لطفاً آن را در فایل php.ini فعال کنید.</div>';
            echo '<div class="info">برای فعال‌سازی GD extension، فایل php.ini را باز کرده و خط <code>;extension=gd</code> را به <code>extension=gd</code> تغییر دهید.</div>';
        } else {
            echo '<div class="success">✓ PHP GD extension فعال است.</div>';
        }
        
        try {
            $conn = getConnection();
            
            // Get all team members with images
            $stmt = $conn->query("SELECT id, name, image FROM about_team WHERE image IS NOT NULL AND image != ''");
            $teamMembers = $stmt->fetchAll();
            
            if (empty($teamMembers)) {
                echo '<div class="info">هیچ تصویر تیمی برای تغییر اندازه یافت نشد.</div>';
            } else {
                echo '<div class="info">' . count($teamMembers) . ' تصویر پیدا شد. در حال تغییر اندازه...</div>';
                
                $successCount = 0;
                $errorCount = 0;
                
                foreach ($teamMembers as $member) {
                    $imagePath = UPLOAD_PATH . $member['image'];
                    
                    if (file_exists($imagePath)) {
                        // Get current dimensions
                        $currentInfo = @getimagesize($imagePath);
                        if ($currentInfo) {
                            $currentWidth = $currentInfo[0];
                            $currentHeight = $currentInfo[1];
                            
                            // Only resize if not already 600x800
                            if ($currentWidth != 600 || $currentHeight != 800) {
                                if (resizeImage($imagePath, 600, 800)) {
                                    // Verify resize
                                    $newInfo = @getimagesize($imagePath);
                                    if ($newInfo && $newInfo[0] == 600 && $newInfo[1] == 800) {
                                        echo '<div class="success">✓ ' . htmlspecialchars($member['name']) . ': ' . $currentWidth . '×' . $currentHeight . ' → 600×800</div>';
                                        $successCount++;
                                    } else {
                                        echo '<div class="error">✗ ' . htmlspecialchars($member['name']) . ': تغییر اندازه انجام نشد</div>';
                                        $errorCount++;
                                    }
                                } else {
                                    echo '<div class="error">✗ ' . htmlspecialchars($member['name']) . ': خطا در تغییر اندازه</div>';
                                    $errorCount++;
                                }
                            } else {
                                echo '<div class="info">- ' . htmlspecialchars($member['name']) . ': قبلاً 600×800 است</div>';
                            }
                        } else {
                            echo '<div class="error">✗ ' . htmlspecialchars($member['name']) . ': فایل تصویر معتبر نیست</div>';
                            $errorCount++;
                        }
                    } else {
                        echo '<div class="error">✗ ' . htmlspecialchars($member['name']) . ': فایل یافت نشد: ' . htmlspecialchars($member['image']) . '</div>';
                        $errorCount++;
                    }
                }
                
                echo '<hr>';
                echo '<div class="success"><strong>خلاصه:</strong> ' . $successCount . ' تصویر با موفقیت تغییر اندازه داده شد. ' . $errorCount . ' خطا.</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">خطا: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <br>
        <a href="admin/about.php?action=team" style="display: inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px;">بازگشت به مدیریت تیم</a>
    </div>
</body>
</html>

