<?php
/**
 * Admin User Details
 * جزئیات مشتری
 */

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/user_functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = getConnection();
$userId = $_GET['id'] ?? 0;

// Get user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit;
}

// Get user orders
$orders = getUserOrders($userId, 50);

// Get user addresses
$addresses = getUserAddresses($userId);

// Stats
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(total) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

$pageTitle = 'جزئیات مشتری';
include 'header.php';
?>

<main class="main">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                <i class="bi bi-person-fill text-primary"></i>
                جزئیات مشتری
            </h4>
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right"></i> بازگشت
            </a>
        </div>

        <div class="row">
            <!-- User Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle display-1 text-primary"></i>
                        </div>
                        <h5><?= htmlspecialchars($user['name'] ?: 'بدون نام') ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($user['phone']) ?></p>
                        <?php if ($user['email']): ?>
                            <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                        <?php endif; ?>

                        <?php if ($user['is_blocked']): ?>
                            <span class="badge bg-danger">مسدود</span>
                        <?php else: ?>
                            <span class="badge bg-success">فعال</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">اطلاعات حساب</div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td>تاریخ عضویت</td>
                                <td><?= date('Y/m/d H:i', strtotime($user['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td>آخرین ورود</td>
                                <td><?= $user['last_login'] ? date('Y/m/d H:i', strtotime($user['last_login'])) : '-' ?>
                                </td>
                            </tr>
                            <tr>
                                <td>تعداد سفارش</td>
                                <td><?= number_format($stats['count']) ?></td>
                            </tr>
                            <tr>
                                <td>مجموع خرید</td>
                                <td><?= formatPrice($stats['total'] ?? 0) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($addresses)): ?>
                    <div class="card">
                        <div class="card-header">آدرس‌ها</div>
                        <div class="card-body">
                            <?php foreach ($addresses as $addr): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <strong><?= htmlspecialchars($addr['title'] ?: 'آدرس') ?></strong>
                                    <?php if ($addr['is_default']): ?>
                                        <span class="badge bg-primary">پیش‌فرض</span>
                                    <?php endif; ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($addr['province']) ?> - <?= htmlspecialchars($addr['city']) ?><br>
                                        <?= htmlspecialchars($addr['address']) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Orders -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-bag"></i> سفارشات
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-4 text-muted">
                                این مشتری هنوز سفارشی ثبت نکرده است
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>شماره سفارش</th>
                                            <th>تاریخ</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                                <td><?= date('Y/m/d', strtotime($order['created_at'])) ?></td>
                                                <td><?= $order['formatted_total'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>">
                                                        <?= $order['status_text'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-view.php?id=<?= $order['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        جزئیات
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>