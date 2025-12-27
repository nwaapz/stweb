<?php
/**
 * Order Details
 * جزئیات سفارش
 */

require_once __DIR__ . '/../backend/includes/functions.php';
require_once __DIR__ . '/../backend/includes/user_functions.php';

$user = requireUserLogin();

$orderId = $_GET['id'] ?? 0;
$order = getOrderById($orderId, $user['id']);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$pageTitle = 'سفارش ' . $order['order_number'];
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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-receipt"></i>
                            سفارش <?= htmlspecialchars($order['order_number']) ?>
                        </span>
                        <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>">
                            <?= $order['status_text'] ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">تاریخ ثبت</h6>
                                <p><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">آخرین بروزرسانی</h6>
                                <p><?= date('Y/m/d H:i', strtotime($order['updated_at'])) ?></p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-box"></i> اقلام سفارش
                        </h6>
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>محصول</th>
                                        <th>قیمت واحد</th>
                                        <th>تعداد</th>
                                        <th>جمع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['image_url']): ?>
                                                        <img src="<?= $item['image_url'] ?>" alt=""
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                                            class="me-3">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                                        <?php if ($item['product_sku']): ?>
                                                            <br><small class="text-muted">کد:
                                                                <?= htmlspecialchars($item['product_sku']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= $item['formatted_price'] ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= $item['formatted_total'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-map-marker-alt"></i> آدرس تحویل
                                </h6>
                                <p>
                                    <strong><?= htmlspecialchars($order['shipping_name']) ?></strong><br>
                                    <?= htmlspecialchars($order['shipping_phone']) ?><br>
                                    <?= htmlspecialchars($order['shipping_province']) ?> -
                                    <?= htmlspecialchars($order['shipping_city']) ?><br>
                                    <?= htmlspecialchars($order['shipping_address']) ?>
                                    <?php if ($order['shipping_postal_code']): ?>
                                        <br>کد پستی: <?= htmlspecialchars($order['shipping_postal_code']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>جمع اقلام:</td>
                                            <td class="text-start"><?= $order['formatted_subtotal'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>هزینه ارسال:</td>
                                            <td class="text-start">
                                                <?= $order['shipping_cost'] > 0 ? $order['formatted_shipping'] : 'رایگان' ?>
                                            </td>
                                        </tr>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                            <tr class="text-success">
                                                <td>تخفیف:</td>
                                                <td class="text-start">- <?= formatPrice($order['discount_amount']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="fw-bold">
                                            <td>مبلغ کل:</td>
                                            <td class="text-start"><?= $order['formatted_total'] ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <?php if ($order['notes']): ?>
                            <div class="mt-4">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="fas fa-sticky-note"></i> یادداشت
                                </h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> بازگشت به لیست سفارشات
                </a>
            </div>
        </div>
    </div>
</div>

<div class="block-space block-space--layout--before-footer"></div>

<?php include 'footer.php'; ?>