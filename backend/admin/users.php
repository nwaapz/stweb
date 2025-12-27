<?php
/**
 * Admin Users Management
 * مدیریت مشتریان
 */

session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$conn = getConnection();

// Handle block/unblock
if (isset($_GET['toggle_block'])) {
    $userId = (int) $_GET['toggle_block'];
    $conn->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE id = ?")->execute([$userId]);
    header('Location: users.php');
    exit;
}

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
        (SELECT SUM(total) FROM orders WHERE user_id = u.id AND status != 'cancelled') as total_spent
        FROM users u WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.phone LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($status === 'active') {
    $sql .= " AND u.is_blocked = 0";
} elseif ($status === 'blocked') {
    $sql .= " AND u.is_blocked = 1";
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $conn->query("SELECT COUNT(*) FROM users WHERE is_blocked = 0")->fetchColumn();
$todayUsers = $conn->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();

$pageTitle = 'مدیریت مشتریان';
include 'header.php';
?>

<main class="main">
    <div class="container-fluid">
        <h4 class="mb-4">
            <i class="bi bi-people-fill text-primary"></i> مدیریت مشتریان
        </h4>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($totalUsers) ?></h3>
                        <small>کل مشتریان</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($activeUsers) ?></h3>
                        <small>مشتریان فعال</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3 class="mb-0"><?= number_format($todayUsers) ?></h3>
                        <small>ثبت نام امروز</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control"
                            placeholder="جستجو در نام، موبایل، ایمیل..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">همه وضعیت‌ها</option>
                            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>فعال</option>
                            <option value="blocked" <?= $status === 'blocked' ? 'selected' : '' ?>>مسدود</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> جستجو
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>مشتری</th>
                                <th>موبایل</th>
                                <th>تعداد سفارش</th>
                                <th>مبلغ کل خرید</th>
                                <th>تاریخ عضویت</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        هیچ مشتری‌ای یافت نشد
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= $u['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($u['name'] ?: '-') ?></strong>
                                            <?php if ($u['email']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($u['phone']) ?></td>
                                        <td><?= number_format($u['order_count']) ?></td>
                                        <td><?= formatPrice($u['total_spent'] ?? 0) ?></td>
                                        <td><?= date('Y/m/d', strtotime($u['created_at'])) ?></td>
                                        <td>
                                            <?php if ($u['is_blocked']): ?>
                                                <span class="badge bg-danger">مسدود</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">فعال</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="user-details.php?id=<?= $u['id'] ?>"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?toggle_block=<?= $u['id'] ?>"
                                                class="btn btn-sm btn-outline-<?= $u['is_blocked'] ? 'success' : 'danger' ?>"
                                                onclick="return confirm('<?= $u['is_blocked'] ? 'رفع مسدودیت' : 'مسدود کردن' ?> این کاربر؟')">
                                                <i class="bi bi-<?= $u['is_blocked'] ? 'unlock' : 'lock' ?>"></i>
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