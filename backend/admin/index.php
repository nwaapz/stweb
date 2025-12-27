<?php
/**
 * Admin Dashboard
 * داشبورد مدیریت
 */

$pageTitle = 'داشبورد';
require_once 'header.php';
require_once __DIR__ . '/../includes/user_functions.php';

$conn = getConnection();

// Check if users table exists
$usersTableExists = false;
try {
    $conn->query("SELECT 1 FROM users LIMIT 1");
    $usersTableExists = true;
} catch (Exception $e) {
    $usersTableExists = false;
}

// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
    'discounted' => $conn->query("SELECT COUNT(*) FROM products WHERE discount_price IS NOT NULL AND (discount_end IS NULL OR discount_end >= NOW())")->fetchColumn(),
    'views' => $conn->query("SELECT COALESCE(SUM(views), 0) FROM products")->fetchColumn()
];

// User/Order stats (only if tables exist)
if ($usersTableExists) {
    $stats['users'] = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['orders'] = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['pending_orders'] = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $stats['revenue'] = $conn->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();

    // Recent orders
    $stmt = $conn->query("
        SELECT o.*, u.name as user_name, u.phone as user_phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC LIMIT 5
    ");
    $recentOrders = $stmt->fetchAll();
}

// Get recent products
$recentProducts = getProducts(['limit' => 5]);
?>

<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> داشبورد</h1>
</div>

<div class="content-wrapper">
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-products">
                <h3><?= number_format($stats['products']) ?></h3>
                <p>تعداد محصولات</p>
                <i class="bi bi-box"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-categories">
                <h3><?= number_format($stats['categories']) ?></h3>
                <p>دسته‌بندی‌ها</p>
                <i class="bi bi-folder"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-discounts">
                <h3><?= number_format($stats['discounted']) ?></h3>
                <p>محصولات تخفیف‌دار</p>
                <i class="bi bi-percent"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-views">
                <h3><?= number_format($stats['views']) ?></h3>
                <p>بازدید کل</p>
                <i class="bi bi-eye"></i>
            </div>
        </div>
    </div>

    <?php if ($usersTableExists): ?>
        <!-- User/Order Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                    <h3><?= number_format($stats['users']) ?></h3>
                    <p>مشتریان ثبت‌نام شده</p>
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                    <h3><?= number_format($stats['orders']) ?></h3>
                    <p>کل سفارشات</p>
                    <i class="bi bi-bag"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);">
                    <h3><?= number_format($stats['pending_orders']) ?></h3>
                    <p>در انتظار تایید</p>
                    <i class="bi bi-clock"></i>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);">
                    <h3><?= formatPrice($stats['revenue']) ?></h3>
                    <p>مجموع فروش</p>
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle"></i>
            برای فعال‌سازی سیستم کاربران و سفارشات، <a href="../migrate_users.php">اینجا کلیک کنید</a>.
        </div>
    <?php endif; ?>


    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning"></i> دسترسی سریع
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="products.php?action=add" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> افزودن محصول جدید
                        </a>
                        <a href="categories.php?action=add" class="btn btn-success">
                            <i class="bi bi-folder-plus"></i> افزودن دسته‌بندی
                        </a>
                        <a href="discounts.php" class="btn btn-danger">
                            <i class="bi bi-percent"></i> مدیریت تخفیف‌ها
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> راهنما
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            برای افزودن محصول، ابتدا یک دسته‌بندی ایجاد کنید
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            تصاویر محصولات باید کمتر از 5 مگابایت باشند
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            برای تخفیف می‌توانید تاریخ شروع و پایان تعیین کنید
                        </li>
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            از API برای نمایش محصولات در فرانت‌اند استفاده کنید
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clock-history"></i> آخرین محصولات</span>
            <a href="products.php" class="btn btn-sm btn-outline-primary">مشاهده همه</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentProducts)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-2">هنوز محصولی اضافه نشده است</p>
                    <a href="products.php?action=add" class="btn btn-primary">افزودن اولین محصول</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>تصویر</th>
                                <th>نام محصول</th>
                                <th>دسته‌بندی</th>
                                <th>قیمت</th>
                                <th>وضعیت</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProducts as $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="../uploads/<?= $product['image'] ?>" class="product-thumb" alt="">
                                        <?php else: ?>
                                            <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= sanitize($product['name']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $product['category_name'] ?? 'بدون دسته' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (hasActiveDiscount($product)): ?>
                                            <del class="text-muted small"><?= formatPrice($product['price']) ?></del><br>
                                            <span class="text-danger"><?= formatPrice($product['discount_price']) ?></span>
                                        <?php else: ?>
                                            <?= formatPrice($product['price']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product['is_active']): ?>
                                            <span class="badge badge-status bg-success">فعال</span>
                                        <?php else: ?>
                                            <span class="badge badge-status bg-secondary">غیرفعال</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('Y/m/d', strtotime($product['created_at'])) ?>
                                        </small>
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

<?php require_once 'footer.php'; ?>