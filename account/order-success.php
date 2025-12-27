<?php
/**
 * Order Success Page
 * صفحه تکمیل سفارش
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();
$orderNumber = $_GET['order'] ?? '';

$pageTitle = 'سفارش ثبت شد';
include 'header.php';
?>

<div class="block-space block-space--layout--after-header"></div>

<div class="block">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="mb-3">سفارش شما با موفقیت ثبت شد!</h2>
                        <p class="text-muted mb-4">
                            از خرید شما متشکریم. سفارش شما با شماره پیگیری زیر ثبت شده است.
                        </p>

                        <?php if ($orderNumber): ?>
                            <div class="bg-light p-3 rounded mb-4">
                                <small class="text-muted">شماره پیگیری سفارش</small>
                                <h3 class="mb-0"><?= htmlspecialchars($orderNumber) ?></h3>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted mb-4">
                            پیامک تایید سفارش برای شما ارسال شد.<br>
                            می‌توانید وضعیت سفارش خود را از پنل کاربری پیگیری کنید.
                        </p>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="orders.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> مشاهده سفارشات
                            </a>
                            <a href="../index.html" class="btn btn-outline-secondary">
                                <i class="fas fa-home"></i> صفحه اصلی
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>