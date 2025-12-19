<?php
/**
 * Admin Header
 * هدر پنل مدیریت
 */

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'پنل مدیریت' ?> | استارتک</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e74c3c;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background: #f4f6f9;
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand h2 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
        }

        .sidebar-brand span {
            color: var(--primary-color);
        }

        .sidebar-menu {
            padding: 15px 0;
        }

        .nav-item {
            padding: 0 15px;
            margin-bottom: 5px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary-color);
            color: white;
        }

        .nav-link i {
            font-size: 1.2rem;
        }

        .main-content {
            margin-right: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-navbar {
            background: white;
            padding: 15px 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header {
            padding: 25px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #2c3e50;
        }

        .content-wrapper {
            padding: 0 25px 25px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            font-weight: bold;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: #c0392b;
            border-color: #c0392b;
        }

        .stat-card {
            padding: 25px;
            border-radius: 15px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .stat-card.bg-products {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.bg-categories {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-card.bg-discounts {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }

        .stat-card.bg-views {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            margin: 0;
        }

        .stat-card p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .stat-card i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 4rem;
            opacity: 0.2;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: normal;
        }

        .user-dropdown .dropdown-toggle::after {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-right: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h2><span>استار</span>تک</h2>
            <small class="text-muted">سیستم مدیریت محتوا</small>
        </div>
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> داشبورد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="categories.php">
                        <i class="bi bi-folder"></i> دسته‌بندی‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'factories' ? 'active' : '' ?>" href="factories.php">
                        <i class="bi bi-building"></i> کارخانجات خودروسازی
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'vehicles' ? 'active' : '' ?>" href="vehicles.php">
                        <i class="bi bi-car-front"></i> وسایل نقلیه
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>" href="products.php">
                        <i class="bi bi-box"></i> محصولات
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'discounts' ? 'active' : '' ?>" href="discounts.php">
                        <i class="bi bi-percent"></i> تخفیف‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>" href="settings.php">
                        <i class="bi bi-gear"></i> تنظیمات
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'provinces' ? 'active' : '' ?>" href="provinces.php">
                        <i class="bi bi-map"></i> استان‌ها
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'branches' ? 'active' : '' ?>" href="branches.php">
                        <i class="bi bi-building"></i> شعب
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> خروج
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <div>
                <span class="text-muted">خوش آمدید،</span>
                <strong><?= $_SESSION['admin_name'] ?? 'مدیر' ?></strong>
            </div>
            <div class="dropdown user-dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-start">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> پروفایل</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i>
                            خروج</a></li>
                </ul>
            </div>
        </div>