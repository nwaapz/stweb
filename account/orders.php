<?php
/**
 * User Orders
 * سفارشات کاربر
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();
$orders = getUserOrders($user['id']);

$pageTitle = 'سفارشات من';
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
                        <i class="fas fa-shopping-bag"></i> سفارشات من
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($orders)): ?>
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
                                            <th>مبلغ کل</th>
                                            <th>وضعیت</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                                </td>
                                                <td>
                                                    <?= date('Y/m/d H:i', strtotime($order['created_at'])) ?>
                                                </td>
                                                <td><?= $order['formatted_total'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>">
                                                        <?= $order['status_text'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="order-details.php?id=<?= $order['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> جزئیات
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