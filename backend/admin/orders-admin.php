<?php
/**
 * Admin Orders Management
 * مدیریت سفارشات
 */

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/user_functions.php';
require_once '../includes/sms_service.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = getConnection();

// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = $_POST['status'];

    $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (in_array($newStatus, $allowed)) {
        $conn->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $orderId]);

        // Send SMS notification
        $stmt = $conn->prepare("
            SELECT o.*, u.phone FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if ($order) {
            $statusTexts = [
                'processing' => 'در حال پردازش',
                'shipped' => 'ارسال شده',
                'delivered' => 'تحویل شده',
                'cancelled' => 'لغو شده'
            ];
            if (isset($statusTexts[$newStatus])) {
                $message = "استارتک\nوضعیت سفارش {$order['order_number']} به \"{$statusTexts[$newStatus]}\" تغییر کرد.";
                sendSMS($order['phone'], $message);
            }
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT o.*, u.name as user_name, u.phone as user_phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (o.order_number LIKE ? OR u.phone LIKE ? OR u.name LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($status) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

if ($dateFrom) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $dateFrom;
}

if ($dateTo) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Stats
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$todayOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$totalRevenue = $conn->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn();

$pageTitle = 'مدیریت سفارشات';
include 'header.php';
?>

<main class="main">
    <div class="container-fluid">
        <h4 class="mb-4">
            <i class="bi bi-bag-fill text-primary"></i> مدیریت سفارشات
        </h4>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($totalOrders) ?></h3>
                        <small>کل سفارشات</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($pendingOrders) ?></h3>
                        <small>در انتظار تایید</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($todayOrders) ?></h3>
                        <small>سفارش امروز</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= formatPrice($totalRevenue ?? 0) ?></h3>
                        <small>مجموع فروش</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="شماره سفارش، نام، موبایل..."
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">همه وضعیت‌ها</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>در انتظار تایید</option>
                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>در حال پردازش
                            </option>
                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>ارسال شده</option>
                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>تحویل شده</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>"
                            placeholder="از تاریخ">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>"
                            placeholder="تا تاریخ">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> جستجو
                        </button>
                        <a href="orders-admin.php" class="btn btn-outline-secondary">پاک کردن</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>شماره سفارش</th>
                                <th>مشتری</th>
                                <th>مبلغ</th>
                                <th>تاریخ</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        هیچ سفارشی یافت نشد
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($order['user_name'] ?: '-') ?><br>
                                            <small class="text-muted"><?= htmlspecialchars($order['user_phone']) ?></small>
                                        </td>
                                        <td><?= formatPrice($order['total']) ?></td>
                                        <td><?= date('Y/m/d H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <input type="hidden" name="update_status" value="1">
                                                <select name="status" class="form-select form-select-sm" style="width: auto;"
                                                    onchange="this.form.submit()">
                                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>در انتظار تایید</option>
                                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>در حال پردازش</option>
                                                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>ارسال شده</option>
                                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>تحویل شده</option>
                                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="order-view.php?id=<?= $order['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> جزئیات
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>