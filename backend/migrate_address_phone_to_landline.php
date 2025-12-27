<?php
/**
 * Migration: Rename phone to landline in user_addresses table
 * تغییر نام فیلد phone به landline در جدول user_addresses
 * 
 * This ensures addresses have landlines (not mobile phones)
 * آدرس‌ها باید شماره تلفن ثابت داشته باشند (نه موبایل)
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = false;
$changes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    try {
        $conn = getConnection();

        // Check if phone column exists
        $stmt = $conn->query("SHOW COLUMNS FROM `user_addresses` LIKE 'phone'");
        $phoneExists = $stmt->rowCount() > 0;

        // Check if landline column already exists
        $stmt = $conn->query("SHOW COLUMNS FROM `user_addresses` LIKE 'landline'");
        $landlineExists = $stmt->rowCount() > 0;

        if ($phoneExists && !$landlineExists) {
            // Rename phone to landline
            $conn->exec("ALTER TABLE `user_addresses` CHANGE COLUMN `phone` `landline` VARCHAR(15) NOT NULL");
            $changes[] = 'Renamed phone column to landline in user_addresses table';
        } elseif ($phoneExists && $landlineExists) {
            // Both exist - copy data and drop phone
            $conn->exec("UPDATE `user_addresses` SET `landline` = `phone` WHERE `landline` IS NULL OR `landline` = ''");
            $conn->exec("ALTER TABLE `user_addresses` DROP COLUMN `phone`");
            $changes[] = 'Merged phone data into landline and removed phone column';
        } elseif (!$phoneExists && !$landlineExists) {
            // Neither exists - add landline column
            $conn->exec("ALTER TABLE `user_addresses` ADD COLUMN `landline` VARCHAR(15) NOT NULL AFTER `recipient_name`");
            $changes[] = 'Added landline column to user_addresses table';
        } else {
            $changes[] = 'Landline column already exists, no changes needed';
        }

        $success = true;

    } catch (PDOException $e) {
        $errors[] = "خطا: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر نام فیلد phone به landline | استارتک</title>
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

        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 600px;
            width: 100%;
        }

        .setup-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .setup-body {
            padding: 30px;
        }
    </style>
</head>

<body>
    <div class="setup-card">
        <div class="setup-header">
            <h1><i class="bi bi-phone"></i></h1>
            <h2>تغییر نام فیلد phone به landline</h2>
        </div>
        <div class="setup-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <strong>موفقیت!</strong> تغییرات با موفقیت اعمال شد.
                </div>

                <?php if (!empty($changes)): ?>
                    <div class="bg-light p-3 rounded mb-3">
                        <h5>تغییرات اعمال شده:</h5>
                        <ul class="mb-0">
                            <?php foreach ($changes as $change): ?>
                                <li><?= htmlspecialchars($change) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <a href="admin/index.php" class="btn btn-primary w-100">
                    <i class="bi bi-speedometer2"></i> بازگشت به داشبورد
                </a>

            <?php elseif (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <strong>خطا!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php else: ?>
                <p class="text-muted mb-4">
                    این اسکریپت فیلد <code>phone</code> را در جدول <code>user_addresses</code> به <code>landline</code> تغییر می‌دهد.
                    <br><br>
                    <strong>توجه:</strong> آدرس‌ها باید شماره تلفن ثابت (landline) داشته باشند، نه موبایل.
                </p>

                <form method="POST">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-database-check"></i> اعمال تغییرات
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

