<?php
/**
 * Profile Page
 * پروفایل کاربری
 */

$pageTitle = 'پروفایل';
require_once 'header.php';

$conn = getConnection();

// Get current user
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Update profile
    if (isset($_POST['update_profile'])) {
        $stmt = $conn->prepare("UPDATE admin_users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $_SESSION['admin_id']]);
        
        $_SESSION['admin_name'] = $name;
        $success = 'پروفایل با موفقیت بروزرسانی شد';
        
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        if (empty($currentPassword) || empty($newPassword)) {
            $error = 'لطفاً همه فیلدها را پر کنید';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = 'رمز عبور فعلی صحیح نیست';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'رمز عبور جدید و تکرار آن مطابقت ندارند';
        } elseif (strlen($newPassword) < 6) {
            $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['admin_id']]);
            
            $success = 'رمز عبور با موفقیت تغییر کرد';
        }
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-person"></i> پروفایل کاربری</h1>
</div>

<div class="content-wrapper">
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= $error ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">اطلاعات کاربری</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label">نام کاربری</label>
                            <input type="text" class="form-control" value="<?= sanitize($user['username']) ?>" disabled>
                            <small class="text-muted">نام کاربری قابل تغییر نیست</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">نام و نام خانوادگی</label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= sanitize($user['name']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ایمیل</label>
                            <input type="email" name="email" class="form-control" dir="ltr"
                                   value="<?= sanitize($user['email']) ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> ذخیره تغییرات
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">تغییر رمز عبور</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label">رمز عبور فعلی</label>
                            <input type="password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">رمز عبور جدید</label>
                            <input type="password" name="new_password" class="form-control">
                            <small class="text-muted">حداقل ۶ کاراکتر</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تکرار رمز عبور جدید</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> تغییر رمز عبور
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">اطلاعات ورود</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">آخرین ورود:</small><br>
                            <?= $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : 'نامشخص' ?>
                        </li>
                        <li>
                            <small class="text-muted">تاریخ ثبت‌نام:</small><br>
                            <?= date('Y/m/d', strtotime($user['created_at'])) ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
