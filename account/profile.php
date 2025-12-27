<?php
/**
 * User Profile
 * پروفایل کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = updateUserProfile($user['id'], [
        'name' => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? ''
    ]);

    if ($result['success']) {
        $success = 'پروفایل با موفقیت بروزرسانی شد';
        // Refresh user data
        $user = getCurrentUser();
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'ویرایش پروفایل';
include 'header.php';
?>

<div class="block-space block-space--layout--after-header"></div>

<div class="block">
    <div class="container container--max--xl">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-12 col-lg-3 mb-4">
                <?php include 'sidebar.php'; ?>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-edit"></i> ویرایش پروفایل
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">نام و نام خانوادگی</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">شماره موبایل</label>
                                    <input type="tel" class="form-control"
                                        value="<?= htmlspecialchars($user['phone']) ?>" disabled>
                                    <small class="text-muted">شماره موبایل قابل تغییر نیست</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ایمیل</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                    placeholder="example@email.com">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">تاریخ عضویت</label>
                                    <p><?= date('Y/m/d', strtotime($user['created_at'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">آخرین ورود</label>
                                    <p><?= $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : '-' ?>
                                    </p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ذخیره تغییرات
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>