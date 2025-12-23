<?php
/**
 * Populate Iran Provinces Script
 * اسکریپت افزودن استان‌های ایران
 * 
 * This script will add all 31 provinces of Iran to the database
 * Run this file once via browser or command line
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in (optional - remove if you want to run without login)
// requireLogin();

$result = populateIranProvinces();

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن استان‌های ایران | استارتک</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
        }
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }
        .result-header {
            background: linear-gradient(135deg, <?= $result['success'] ? '#11998e' : '#e74c3c' ?> 0%, <?= $result['success'] ? '#38ef7d' : '#c0392b' ?> 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .result-body {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <div class="result-header">
            <h1><i class="bi bi-<?= $result['success'] ? 'check-circle-fill' : 'x-circle-fill' ?>"></i></h1>
            <h2><?= $result['success'] ? 'موفقیت!' : 'خطا!' ?></h2>
        </div>
        <div class="result-body">
            <div class="alert alert-<?= $result['success'] ? 'success' : 'danger' ?>">
                <i class="bi bi-<?= $result['success'] ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($result['message']) ?>
            </div>
            
            <?php if ($result['success']): ?>
            <div class="bg-light p-3 rounded mb-3">
                <h6>جزئیات:</h6>
                <ul class="mb-0">
                    <li>استان‌های جدید اضافه شده: <strong><?= $result['added'] ?></strong></li>
                    <?php if ($result['skipped'] > 0): ?>
                    <li>استان‌های موجود (رد شده): <strong><?= $result['skipped'] ?></strong></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="d-grid gap-2">
                <a href="admin/provinces.php" class="btn btn-primary">
                    <i class="bi bi-map"></i> مشاهده لیست استان‌ها
                </a>
                <a href="admin/index.php" class="btn btn-secondary">
                    <i class="bi bi-house"></i> بازگشت به پنل مدیریت
                </a>
            </div>
        </div>
    </div>
</body>
</html>

