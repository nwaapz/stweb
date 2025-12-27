<?php
/**
 * User Dashboard
 * داشبورد کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

// Require login
$user = requireUserLogin();

// Get user stats
$recentOrders = getUserOrders($user['id'], 5);
$cartSummary = getCartSummary($user['id']);
$wishlistCount = count(getWishlist($user['id']));

// Get order count by status
$conn = getConnection();
$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM orders WHERE user_id = ? GROUP BY status");
$stmt->execute([$user['id']]);
$orderStats = [];
while ($row = $stmt->fetch()) {
    $orderStats[$row['status']] = $row['count'];
}

$pageTitle = 'داشبورد';
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
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">
                            <i class="fas fa-user-circle text-primary"></i>
                            سلام <?= htmlspecialchars($user['name'] ?: 'کاربر عزیز') ?>!
                        </h4>
                        <p class="text-muted mb-0">
                            به حساب کاربری خود خوش آمدید. از اینجا می‌توانید سفارشات، سبد خرید و پروفایل خود را مدیریت
                            کنید.
                        </p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= array_sum($orderStats) ?></h3>
                                <small>کل سفارشات</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= ($orderStats['pending'] ?? 0) + ($orderStats['processing'] ?? 0) ?>
                                </h3>
                                <small>در حال پردازش</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= $cartSummary['item_count'] ?></h3>
                                <small>سبد خرید</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-1"><?= $wishlistCount ?></h3>
                                <small>علاقه‌مندی</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-shopping-bag"></i> آخرین سفارشات</span>
                        <a href="orders.php" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentOrders)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>هنوز سفارشی ثبت نکرده‌اید</p>
                                <a href="../shop-grid-4-columns-sidebar.html" class="btn btn-primary">
                                    مشاهده محصولات
                                </a>
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
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= date('Y/m/d', strtotime($order['created_at'])) ?>
                                                </td>
                                                <td><?= $order['formatted_total'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>">
                                                        <?= $order['status_text'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-details.php?id=<?= $order['id'] ?>"
                                                        class="btn btn-sm btn-outline-secondary">
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
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>