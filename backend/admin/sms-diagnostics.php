<?php
/**
 * SMS Diagnostics Page
 * صفحه تشخیص مشکلات پیامک
 */

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/sms_service.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'تشخیص مشکلات پیامک';
include 'header.php';

// Get recent SMS logs
$recentLogs = getSMSLogs(null, 20);
$stats = getSMSStats(null, 7);

// Test SMS if requested
$testResult = null;
if (isset($_POST['test_sms'])) {
    $testPhone = sanitize($_POST['test_phone'] ?? '');
    if (!empty($testPhone)) {
        $testResult = sendSMS($testPhone, 'تست ارسال پیامک - ' . date('Y-m-d H:i:s'));
    }
}
?>

<main class="main">
    <div class="container-fluid">
        <h4 class="mb-4">
            <i class="bi bi-envelope-check text-primary"></i> تشخیص مشکلات پیامک
        </h4>

        <!-- Configuration Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>وضعیت تنظیمات</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="200">ارائه‌دهنده:</th>
                        <td>
                            <strong><?= htmlspecialchars(SMS_PROVIDER) ?></strong>
                            <?php if (SMS_PROVIDER === 'test'): ?>
                                <span class="badge bg-warning">حالت تست (پیامک واقعی ارسال نمی‌شود)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>کلید API:</th>
                        <td>
                            <?php if (empty(SMS_API_KEY)): ?>
                                <span class="text-danger">❌ تنظیم نشده</span>
                            <?php else: ?>
                                <span class="text-success">✓ تنظیم شده</span>
                                <code><?= htmlspecialchars(substr(SMS_API_KEY, 0, 10)) ?>...</code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>شماره فرستنده:</th>
                        <td><?= htmlspecialchars(SMS_SENDER) ?></td>
                    </tr>
                    <tr>
                        <th>وضعیت ارسال:</th>
                        <td>
                            <?php if (SMS_ENABLED): ?>
                                <span class="badge bg-success">فعال</span>
                            <?php else: ?>
                                <span class="badge bg-danger">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>ثبت لاگ:</th>
                        <td>
                            <?php if (SMS_LOG_ENABLED): ?>
                                <span class="badge bg-success">فعال</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">غیرفعال</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>افزونه SOAP:</th>
                        <td>
                            <?php if (extension_loaded('soap')): ?>
                                <span class="badge bg-success">✓ نصب شده</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✗ نصب نشده</span>
                                <br><small class="text-danger">
                                    در cPanel: Select PHP Version → Extensions → soap را فعال کنید
                                </small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>محیط:</th>
                        <td>
                            <?php 
                            $isCpanel = (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'cPanel') !== false) 
                                     || (isset($_SERVER['CPANEL']) && $_SERVER['CPANEL'] == '1')
                                     || file_exists('/usr/local/cpanel/version');
                            if ($isCpanel): ?>
                                <span class="badge bg-info">cPanel</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Local/Other</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <div class="alert alert-info mt-3">
                    <strong>نکته:</strong> برای تغییر تنظیمات، فایل <code>backend/config/sms_config.php</code> را ویرایش کنید.
                </div>
            </div>
        </div>

        <!-- Test SMS -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>تست ارسال پیامک</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">شماره موبایل</label>
                                <input type="tel" name="test_phone" class="form-control" 
                                    placeholder="09123456789" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" name="test_sms" class="btn btn-primary w-100">
                                    ارسال پیامک تست
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <?php if ($testResult): ?>
                    <div class="alert <?= $testResult['success'] ? 'alert-success' : 'alert-danger' ?> mt-3">
                        <strong>نتیجه:</strong>
                        <?php if ($testResult['success']): ?>
                            ✓ پیامک با موفقیت ارسال شد
                            <?php if ($testResult['message_id']): ?>
                                <br><small>شناسه پیام: <?= htmlspecialchars($testResult['message_id']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            ✗ خطا: <?= htmlspecialchars($testResult['error']) ?>
                            <br><small>وضعیت: <?= htmlspecialchars($testResult['status']) ?></small>
                        <?php endif; ?>
                        <?php if ($testResult['provider_response']): ?>
                            <br><small class="text-muted">
                                پاسخ ارائه‌دهنده: 
                                <pre class="d-inline"><?= htmlspecialchars(json_encode($testResult['provider_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics -->
        <?php if ($stats): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5>آمار ۷ روز گذشته</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3><?= number_format($stats['total']) ?></h3>
                                <small>کل ارسال‌ها</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3><?= number_format($stats['sent']) ?></h3>
                                <small>موفق</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3><?= number_format($stats['failed']) ?></h3>
                                <small>ناموفق</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Logs -->
        <div class="card">
            <div class="card-header">
                <h5>لاگ‌های اخیر</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>تاریخ</th>
                                <th>شماره</th>
                                <th>پیام</th>
                                <th>ارائه‌دهنده</th>
                                <th>وضعیت</th>
                                <th>خطا</th>
                                <th>پاسخ ارائه‌دهنده</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        هیچ لاگی یافت نشد
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $log): ?>
                                    <tr>
                                        <td><?= date('Y/m/d H:i:s', strtotime($log['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($log['phone']) ?></td>
                                        <td>
                                            <small><?= htmlspecialchars(mb_substr($log['message'], 0, 50)) ?>...</small>
                                        </td>
                                        <td><?= htmlspecialchars($log['provider']) ?></td>
                                        <td>
                                            <?php if ($log['status'] === 'sent'): ?>
                                                <span class="badge bg-success">ارسال شد</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">ناموفق</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['error_message']): ?>
                                                <small class="text-danger"><?= htmlspecialchars($log['error_message']) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($log['provider_response']): ?>
                                                <button class="btn btn-sm btn-outline-info" 
                                                    onclick="alert(<?= htmlspecialchars(json_encode(json_decode($log['provider_response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>)">
                                                    مشاهده
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

