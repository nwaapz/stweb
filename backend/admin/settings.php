<?php
/**
 * Settings Page
 * تنظیمات سایت
 */

$pageTitle = 'تنظیمات';
require_once 'header.php';

$conn = getConnection();

// Get current settings
function getSetting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : $default;
}

function setSetting($key, $value) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = ?
    ");
    $stmt->execute([$key, $value, $value]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setSetting('site_name', sanitize($_POST['site_name'] ?? ''));
    setSetting('site_description', sanitize($_POST['site_description'] ?? ''));
    setSetting('contact_phone', sanitize($_POST['contact_phone'] ?? ''));
    setSetting('contact_email', sanitize($_POST['contact_email'] ?? ''));
    setSetting('contact_address', sanitize($_POST['contact_address'] ?? ''));
    setSetting('currency', sanitize($_POST['currency'] ?? 'تومان'));
    
    setFlashMessage('success', 'تنظیمات با موفقیت ذخیره شد');
    header('Location: settings.php');
    exit;
}

$flash = getFlashMessage();
?>

<div class="page-header">
    <h1><i class="bi bi-gear"></i> تنظیمات</h1>
</div>

<div class="content-wrapper">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <form method="POST">
                <div class="card mb-4">
                    <div class="card-header">اطلاعات سایت</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">نام سایت</label>
                            <input type="text" name="site_name" class="form-control" 
                                   value="<?= sanitize(getSetting('site_name', 'استارتک')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">توضیحات سایت</label>
                            <textarea name="site_description" class="form-control" rows="3"><?= sanitize(getSetting('site_description')) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">واحد پول</label>
                            <input type="text" name="currency" class="form-control" 
                                   value="<?= sanitize(getSetting('currency', 'تومان')) ?>">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">اطلاعات تماس</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">شماره تماس</label>
                            <input type="text" name="contact_phone" class="form-control" dir="ltr"
                                   value="<?= sanitize(getSetting('contact_phone')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ایمیل</label>
                            <input type="email" name="contact_email" class="form-control" dir="ltr"
                                   value="<?= sanitize(getSetting('contact_email')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">آدرس</label>
                            <textarea name="contact_address" class="form-control" rows="2"><?= sanitize(getSetting('contact_address')) ?></textarea>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> ذخیره تنظیمات
                </button>
            </form>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">اطلاعات API</div>
                <div class="card-body">
                    <p class="text-muted small">از این آدرس‌ها برای دریافت اطلاعات در فرانت‌اند استفاده کنید:</p>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">محصولات:</label>
                        <code class="d-block small bg-light p-2 rounded">/api/products.php</code>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">محصولات تخفیف‌دار:</label>
                        <code class="d-block small bg-light p-2 rounded">/api/products.php?discounted=1</code>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">محصولات ویژه:</label>
                        <code class="d-block small bg-light p-2 rounded">/api/products.php?featured=1</code>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">دسته‌بندی‌ها:</label>
                        <code class="d-block small bg-light p-2 rounded">/api/categories.php</code>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">محصول با ID:</label>
                        <code class="d-block small bg-light p-2 rounded">/api/products.php?id=1</code>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">اطلاعات سیستم</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">نسخه PHP:</small>
                            <span class="float-start"><?= phpversion() ?></span>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">سرور:</small>
                            <span class="float-start"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'نامشخص' ?></span>
                        </li>
                        <li>
                            <small class="text-muted">پایگاه داده:</small>
                            <span class="float-start">MySQL</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
