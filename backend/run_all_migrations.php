<?php
/**
 * Comprehensive Migration Runner
 * اجرای همه مایگریشن‌ها به صورت خودکار
 * 
 * This script checks and runs all migrations in the correct order
 * این اسکریپت همه مایگریشن‌ها را به ترتیب صحیح بررسی و اجرا می‌کند
 */

require_once __DIR__ . '/config/database.php';

$migrations = [
    'migrate.php' => [
        'name' => 'پایگاه داده اصلی',
        'description' => 'ایجاد پایگاه داده و جداول اصلی (categories, factories, vehicles, products, admin_users, settings)',
        'tables' => ['categories', 'factories', 'vehicles', 'products', 'admin_users', 'settings']
    ],
    'migrate_users.php' => [
        'name' => 'جداول کاربران',
        'description' => 'ایجاد جداول کاربران، آدرس‌ها، سبد خرید، سفارشات و لیست علاقه‌مندی',
        'tables' => ['users', 'otp_codes', 'user_sessions', 'user_addresses', 'cart', 'orders', 'order_items', 'wishlists']
    ],
    'migrate_vehicles.php' => [
        'name' => 'پشتیبانی از وسایل نقلیه',
        'description' => 'افزودن پشتیبانی کامل از وسایل نقلیه',
        'tables' => ['vehicles'] // Already in main migration, but may add columns
    ],
    'migrate_user_garage.php' => [
        'name' => 'گاراژ کاربران',
        'description' => 'ایجاد جدول گاراژ کاربران (user_vehicles)',
        'tables' => ['user_vehicles']
    ],
    'migrate_blog_table.php' => [
        'name' => 'جدول بلاگ',
        'description' => 'ایجاد جدول blog_posts',
        'tables' => ['blog_posts']
    ],
    'migrate_about.php' => [
        'name' => 'صفحه درباره ما',
        'description' => 'ایجاد جداول مربوط به صفحه درباره ما',
        'tables' => ['about_page', 'about_team', 'about_testimonials']
    ],
    'migrate_branches.php' => [
        'name' => 'شعبه‌ها',
        'description' => 'ایجاد جداول مربوط به شعبه‌ها',
        'tables' => ['branches']
    ]
];

$results = [];
$allSuccess = true;
$conn = null;

try {
    $conn = getConnection();
} catch (Exception $e) {
    $allSuccess = false;
    $results['error'] = "خطا در اتصال به پایگاه داده: " . $e->getMessage();
}

// Check which tables exist
$existingTables = [];
if ($conn) {
    try {
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $existingTables = $tables;
    } catch (PDOException $e) {
        $results['error'] = "خطا در بررسی جداول: " . $e->getMessage();
    }
}

// Check each migration
$migrationStatus = [];
foreach ($migrations as $file => $info) {
    $filePath = __DIR__ . '/' . $file;
    $fileExists = file_exists($filePath);
    
    $status = [
        'file' => $file,
        'name' => $info['name'],
        'description' => $info['description'],
        'file_exists' => $fileExists,
        'tables_expected' => $info['tables'],
        'tables_exist' => [],
        'tables_missing' => [],
        'needs_migration' => false
    ];
    
    if ($fileExists && $conn) {
        foreach ($info['tables'] as $table) {
            if (in_array($table, $existingTables)) {
                $status['tables_exist'][] = $table;
            } else {
                $status['tables_missing'][] = $table;
                $status['needs_migration'] = true;
            }
        }
    } elseif (!$fileExists) {
        $status['needs_migration'] = true; // Can't check without file
    }
    
    $migrationStatus[] = $status;
}

// Auto-run migrations if requested
$autoRun = isset($_GET['run']) && $_GET['run'] === 'all';
$runResults = [];

if ($autoRun && $conn) {
    foreach ($migrations as $file => $info) {
        $filePath = __DIR__ . '/' . $file;
        if (file_exists($filePath)) {
            // Check if migration is needed
            $needed = false;
            foreach ($info['tables'] as $table) {
                if (!in_array($table, $existingTables)) {
                    $needed = true;
                    break;
                }
            }
            
            if ($needed) {
                // Include and execute migration
                try {
                    ob_start();
                    include $filePath;
                    $output = ob_get_clean();
                    $runResults[$file] = [
                        'success' => true,
                        'output' => $output
                    ];
                    
                    // Refresh table list
                    $stmt = $conn->query("SHOW TABLES");
                    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } catch (Exception $e) {
                    $runResults[$file] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $allSuccess = false;
                }
            } else {
                $runResults[$file] = [
                    'success' => true,
                    'skipped' => true,
                    'message' => 'همه جداول از قبل وجود دارند'
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بررسی و اجرای مایگریشن‌ها | استارتک</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
        }
        .migration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 1200px;
            margin: 0 auto;
        }
        .migration-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .migration-body {
            padding: 30px;
        }
        .migration-item {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .migration-item.complete {
            border-color: #28a745;
            background: #d4edda;
        }
        .migration-item.pending {
            border-color: #ffc107;
            background: #fff3cd;
        }
        .migration-item.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .table-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin: 2px;
        }
        .table-badge.exists {
            background: #28a745;
            color: white;
        }
        .table-badge.missing {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="migration-card">
        <div class="migration-header">
            <h1><i class="bi bi-database-check"></i></h1>
            <h2>بررسی و اجرای مایگریشن‌ها</h2>
            <p class="mb-0">وضعیت همه مایگریشن‌ها و جداول پایگاه داده</p>
        </div>
        <div class="migration-body">
            <?php if (isset($results['error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <strong>خطا!</strong> <?= htmlspecialchars($results['error']) ?>
                </div>
            <?php endif; ?>

            <?php if ($autoRun && !empty($runResults)): ?>
                <div class="alert alert-info mb-4">
                    <h5><i class="bi bi-info-circle"></i> نتایج اجرای مایگریشن‌ها:</h5>
                    <?php foreach ($runResults as $file => $result): ?>
                        <div class="mb-2">
                            <strong><?= htmlspecialchars($file) ?>:</strong>
                            <?php if ($result['success']): ?>
                                <?php if (isset($result['skipped'])): ?>
                                    <span class="badge bg-secondary">رد شد (جداول موجود است)</span>
                                <?php else: ?>
                                    <span class="badge bg-success">موفق</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-danger">خطا: <?= htmlspecialchars($result['error']) ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h4>وضعیت مایگریشن‌ها:</h4>
                <p class="text-muted"><?= count($existingTables) ?> جدول در پایگاه داده وجود دارد</p>
            </div>

            <?php foreach ($migrationStatus as $status): ?>
                <div class="migration-item <?= 
                    !$status['needs_migration'] && $status['file_exists'] ? 'complete' : 
                    ($status['needs_migration'] ? 'pending' : 'error') 
                ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h5>
                                <?php if (!$status['needs_migration'] && $status['file_exists']): ?>
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                <?php elseif ($status['needs_migration']): ?>
                                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                                <?php else: ?>
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($status['name']) ?>
                            </h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($status['description']) ?></p>
                            <p class="small mb-0">
                                <strong>فایل:</strong> 
                                <?php if ($status['file_exists']): ?>
                                    <span class="badge bg-success">موجود</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">یافت نشد</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <?php if (!$status['needs_migration'] && $status['file_exists']): ?>
                                <span class="badge bg-success">کامل ✓</span>
                            <?php elseif ($status['needs_migration']): ?>
                                <span class="badge bg-warning">نیاز به اجرا</span>
                            <?php else: ?>
                                <span class="badge bg-danger">خطا</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <strong>جداول:</strong>
                        <?php foreach ($status['tables_expected'] as $table): ?>
                            <?php if (in_array($table, $status['tables_exist'])): ?>
                                <span class="table-badge exists">
                                    <i class="bi bi-check"></i> <?= htmlspecialchars($table) ?>
                                </span>
                            <?php else: ?>
                                <span class="table-badge missing">
                                    <i class="bi bi-x"></i> <?= htmlspecialchars($table) ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($status['file_exists'] && $status['needs_migration']): ?>
                        <div class="mt-3">
                            <a href="<?= htmlspecialchars($status['file']) ?>?auto=1" 
                               class="btn btn-sm btn-primary" target="_blank">
                                <i class="bi bi-play-circle"></i> اجرای این مایگریشن
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="mt-4 p-3 bg-light rounded">
                <h5>دستورالعمل:</h5>
                <ol>
                    <li>بررسی کنید که کدام مایگریشن‌ها نیاز به اجرا دارند (با نشان زرد مشخص شده‌اند)</li>
                    <li>می‌توانید هر مایگریشن را به صورت جداگانه اجرا کنید</li>
                    <li>یا برای اجرای همه مایگریشن‌های لازم، از دکمه زیر استفاده کنید</li>
                </ol>
            </div>

            <div class="d-grid gap-2 mt-4">
                <?php
                $hasPending = false;
                foreach ($migrationStatus as $status) {
                    if ($status['needs_migration'] && $status['file_exists']) {
                        $hasPending = true;
                        break;
                    }
                }
                ?>
                
                <?php if ($hasPending): ?>
                    <a href="?run=all" class="btn btn-success btn-lg">
                        <i class="bi bi-play-fill"></i> اجرای همه مایگریشن‌های لازم
                    </a>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>عالی!</strong> همه مایگریشن‌ها کامل هستند. نیازی به اجرای مایگریشن نیست.
                    </div>
                <?php endif; ?>

                <a href="admin/index.php" class="btn btn-primary">
                    <i class="bi bi-speedometer2"></i> بازگشت به داشبورد
                </a>
            </div>

            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                <small>
                    <strong>نکته امنیتی:</strong> پس از اطمینان از صحت کارکرد، این فایل را حذف کنید یا دسترسی به آن را محدود کنید.
                </small>
            </div>
        </div>
    </div>
</body>
</html>

