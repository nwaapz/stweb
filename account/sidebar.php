<?php
/**
 * Account Sidebar
 * سایدبار صفحات حساب کاربری
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<div class="account-sidebar">
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-name"><?= htmlspecialchars($user['name'] ?: 'کاربر') ?></div>
        <small><?= htmlspecialchars($user['phone']) ?></small>
    </div>
    <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> داشبورد
        </a>
        <a href="orders.php" class="nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i> سفارشات من
        </a>
        <a href="cart.php" class="nav-link <?= $currentPage === 'cart' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> سبد خرید
        </a>
        <a href="wishlist.php" class="nav-link <?= $currentPage === 'wishlist' ? 'active' : '' ?>">
            <i class="fas fa-heart"></i> علاقه‌مندی‌ها
        </a>
        <a href="addresses.php" class="nav-link <?= $currentPage === 'addresses' ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt"></i> آدرس‌ها
        </a>
        <a href="profile.php" class="nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-edit"></i> ویرایش پروفایل
        </a>
        <a href="logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i> خروج
        </a>
    </nav>
</div>