<?php
/**
 * Admin Order View
 * نمایش سفارش
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
$orderId = $_GET['id'] ?? 0;

// Handle status update
if (isset($_POST['update_status'])) {
    $newStatus = $_POST['status'];
    $adminNotes = $_POST['admin_notes'] ?? '';

    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $conn->prepare("UPDATE orders SET status = ?, admin_notes = ? WHERE id = ?")
            ->execute([$newStatus, $adminNotes, $orderId]);
    }
    header('Location: order-view.php?id=' . $orderId);
    exit;
}

// Get order with user info
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders-admin.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$pageTitle = 'سفارش ' . $order['order_number'];
include 'header.php';
?>

<main class="main">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>
                <i class="bi bi-receipt text-primary"></i>
                سفارش <?= htmlspecialchars($order['order_number']) ?>
            </h4>
            <a href="orders-admin.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right"></i> بازگشت
            </a>
        </div>

        <div class="row">
            <!-- Order Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>اقلام سفارش</span>
                        <span class="badge bg-<?= getOrderStatusBadge($order['status']) ?>">
                            <?= getOrderStatusText($order['status']) ?>
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>محصول</th>
                                    <th>قیمت واحد</th>
                                    <th>تعداد</th>
                                    <th>جمع</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['product_image']): ?>
                                                    <img src="<?= UPLOAD_URL . $item['product_image'] ?>" alt=""
                                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;"
                                                        class="me-2">
                                                <?php endif; ?>
                                                <div>
                                                    <?= htmlspecialchars($item['product_name']) ?>
                                                    <?php if ($item['product_sku']): ?>
                                                        <br><small class="text-muted">کد:
                                                            <?= htmlspecialchars($item['product_sku']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= formatPrice($item['price']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= formatPrice($item['total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-start">جمع اقلام:</td>
                                    <td><?= formatPrice($order['subtotal']) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-start">هزینه ارسال:</td>
                                    <td><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : 'رایگان' ?>
                                    </td>
                                </tr>
                                <?php if ($order['discount_amount'] > 0): ?>
                                    <tr class="text-success">
                                        <td colspan="3" class="text-start">تخفیف:</td>
                                        <td>- <?= formatPrice($order['discount_amount']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-start">مبلغ کل:</td>
                                    <td><?= formatPrice($order['total']) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Update Status -->
                <div class="card">
                    <div class="card-header">تغییر وضعیت</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="update_status" value="1">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">وضعیت</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>در
                                            انتظار تایید</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>در حال پردازش</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>
                                            ارسال شده</option>
                                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>تحویل شده</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">یادداشت مدیر</label>
                                    <input type="text" name="admin_notes" class="form-control"
                                        value="<?= htmlspecialchars($order['admin_notes'] ?? '') ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> ذخیره تغییرات
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping Info -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">اطلاعات مشتری</div>
                    <div class="card-body">
                        <p class="mb-1">
                            <i class="bi bi-person"></i>
                            <strong><?= htmlspecialchars($order['user_name'] ?: '-') ?></strong>
                        </p>
                        <p class="mb-1">
                            <i class="bi bi-telephone"></i>
                            <a href="tel:<?= $order['user_phone'] ?>"><?= htmlspecialchars($order['user_phone']) ?></a>
                        </p>
                        <?php if ($order['user_email']): ?>
                            <p class="mb-0">
                                <i class="bi bi-envelope"></i>
                                <?= htmlspecialchars($order['user_email']) ?>
                            </p>
                        <?php endif; ?>
                        <hr>
                        <a href="user-details.php?id=<?= $order['user_id'] ?>" class="btn btn-sm btn-outline-primary">
                            مشاهده پروفایل مشتری
                        </a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">آدرس تحویل</div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?= htmlspecialchars($order['shipping_name']) ?></strong></p>
                        <p class="mb-1"><?= htmlspecialchars($order['shipping_phone']) ?></p>
                        <p class="mb-1">
                            <?= htmlspecialchars($order['shipping_province']) ?> -
                            <?= htmlspecialchars($order['shipping_city']) ?>
                        </p>
                        <p class="mb-0"><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <?php if ($order['shipping_postal_code']): ?>
                            <p class="mb-0"><small>کد پستی: <?= htmlspecialchars($order['shipping_postal_code']) ?></small>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">اطلاعات سفارش</div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>تاریخ ثبت:</td>
                                <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                            <tr>
                                <td>آخرین بروزرسانی:</td>
                                <td><?= date('Y/m/d H:i', strtotime($order['updated_at'])) ?></td>
                            </tr>
                            <?php if ($order['notes']): ?>
                                <tr>
                                    <td colspan="2">
                                        <strong>یادداشت مشتری:</strong><br>
                                        <?= nl2br(htmlspecialchars($order['notes'])) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>