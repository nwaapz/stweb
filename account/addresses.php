<?php
/**
 * User Addresses
 * آدرس‌های کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();

$error = '';
$success = '';

// Handle add address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_address'])) {
    $result = addUserAddress($user['id'], [
        'title' => $_POST['title'] ?? '',
        'recipient_name' => $_POST['recipient_name'] ?? '',
        'landline' => $_POST['landline'] ?? $_POST['phone'] ?? '',
        'province' => $_POST['province'] ?? '',
        'city' => $_POST['city'] ?? '',
        'address' => $_POST['address'] ?? '',
        'postal_code' => $_POST['postal_code'] ?? '',
        'is_default' => isset($_POST['is_default'])
    ]);

    if ($result['success']) {
        $success = 'آدرس با موفقیت اضافه شد';
    } else {
        $error = $result['error'] ?? 'خطا در افزودن آدرس';
    }
}

// Handle delete address
if (isset($_GET['delete'])) {
    deleteUserAddress($user['id'], $_GET['delete']);
    header('Location: addresses.php');
    exit;
}

$addresses = getUserAddresses($user['id']);

$pageTitle = 'آدرس‌ها';
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

                <!-- Saved Addresses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt"></i> آدرس‌های ذخیره شده
                    </div>
                    <div class="card-body">
                        <?php if (empty($addresses)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                <p>هنوز آدرسی ذخیره نکرده‌اید</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($addresses as $addr): ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 <?= $addr['is_default'] ? 'border-primary' : '' ?>">
                                            <div class="card-body">
                                                <?php if ($addr['is_default']): ?>
                                                    <span class="badge bg-primary float-end">پیش‌فرض</span>
                                                <?php endif; ?>
                                                <h6><?= htmlspecialchars($addr['title'] ?: 'آدرس') ?></h6>
                                                <p class="mb-1">
                                                    <strong><?= htmlspecialchars($addr['recipient_name']) ?></strong>
                                                </p>
                                                <p class="mb-1 text-muted small">
                                                    تلفن ثابت: <?= htmlspecialchars($addr['landline'] ?? $addr['phone'] ?? '') ?>
                                                </p>
                                                <p class="mb-0 small">
                                                    <?= htmlspecialchars($addr['province']) ?> -
                                                    <?= htmlspecialchars($addr['city']) ?><br>
                                                    <?= htmlspecialchars($addr['address']) ?>
                                                    <?php if ($addr['postal_code']): ?>
                                                        <br>کد پستی: <?= htmlspecialchars($addr['postal_code']) ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <a href="?delete=<?= $addr['id'] ?>" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('آیا از حذف این آدرس مطمئن هستید؟')">
                                                    <i class="fas fa-trash"></i> حذف
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add New Address -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus"></i> افزودن آدرس جدید
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="add_address" value="1">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">عنوان آدرس</label>
                                    <input type="text" name="title" class="form-control"
                                        placeholder="مثلاً: خانه، محل کار">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">نام گیرنده <span class="text-danger">*</span></label>
                                    <input type="text" name="recipient_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">تلفن ثابت <span class="text-danger">*</span></label>
                                    <input type="tel" name="landline" class="form-control" 
                                           placeholder="مثال: 02112345678" required
                                           pattern="0[1-9][0-9]{1,3}[0-9]{6,8}"
                                           title="شماره تلفن ثابت (نه موبایل). مثال: 02112345678">
                                    <small class="form-text text-muted">لطفاً شماره تلفن ثابت وارد کنید (نه موبایل)</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">استان <span class="text-danger">*</span></label>
                                    <input type="text" name="province" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">شهر <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">آدرس کامل <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">کد پستی</label>
                                    <input type="text" name="postal_code" class="form-control" maxlength="10">
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_default" class="form-check-input"
                                            id="is_default">
                                        <label class="form-check-label" for="is_default">آدرس پیش‌فرض</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ذخیره آدرس
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