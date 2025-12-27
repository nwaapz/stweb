<?php
/**
 * Account Header
 * هدر صفحات حساب کاربری
 */

if (!isset($user)) {
    require_once __DIR__ . '/../backend/includes/functions.php';
    require_once __DIR__ . '/../backend/includes/user_functions.php';
    $user = getCurrentUser();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'حساب کاربری' ?> | استارتک</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="../vendor/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/style.header-spaceship-variant-one.css" media="(min-width: 1200px)">
    <link rel="stylesheet" href="../css/style.mobile-header-variant-one.css" media="(max-width: 1199px)">
    <link rel="stylesheet" href="../vendor/fontawesome/css/all.min.css">
    <style>
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background-color: #f5f5f5;
        }

        .account-sidebar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .account-sidebar .user-info {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .account-sidebar .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 2rem;
        }

        .account-sidebar .nav-link {
            padding: 12px 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            transition: all 0.2s;
        }

        .account-sidebar .nav-link:hover {
            background: #f8f8f8;
            color: #e74c3c;
        }

        .account-sidebar .nav-link.active {
            background: #fff5f5;
            color: #e74c3c;
            border-right: 3px solid #e74c3c;
        }

        .account-sidebar .nav-link i {
            width: 24px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }

        .simple-header {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Simple Header -->
    <header class="simple-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="../index.html">
                    <img src="../images/sttechLogo.png" alt="استارتک" style="max-height: 40px;">
                </a>
                <div>
                    <a href="../shop-grid-4-columns-sidebar.html" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="fas fa-shopping-bag"></i> فروشگاه
                    </a>
                    <?php if ($user): ?>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> خروج
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>