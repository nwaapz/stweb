<?php
/**
 * Branches Migration Script
 * اسکریپت مهاجرت شعب و استان‌ها
 * 
 * این فایل برای ایجاد جداول provinces و branches استفاده می‌شود
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$messages = [];
$success = false;

try {
    $conn = getConnection();

    // Create provinces table
    $provincesTable = "
        CREATE TABLE IF NOT EXISTS `provinces` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `name_en` VARCHAR(255),
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `description` TEXT,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
    ";

    $stmt = $conn->query("SHOW TABLES LIKE 'provinces'");
    if ($stmt->rowCount() == 0) {
        $conn->exec($provincesTable);
        $messages[] = "جدول provinces با موفقیت ایجاد شد";
        $success = true;
    } else {
        $messages[] = "جدول provinces از قبل وجود دارد";
    }

    // Create branches table
    $branchesTable = "
        CREATE TABLE IF NOT EXISTS `branches` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `province_id` INT NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `address` TEXT NOT NULL,
            `phone` VARCHAR(50),
            `email` VARCHAR(255),
            `latitude` DECIMAL(10, 8),
            `longitude` DECIMAL(11, 8),
            `sort_order` INT DEFAULT 0,
            `is_active` TINYINT(1) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`province_id`) REFERENCES `provinces`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
    ";

    $stmt = $conn->query("SHOW TABLES LIKE 'branches'");
    if ($stmt->rowCount() == 0) {
        $conn->exec($branchesTable);
        $messages[] = "جدول branches با موفقیت ایجاد شد";
        $success = true;
    } else {
        $messages[] = "جدول branches از قبل وجود دارد";
    }

    // Insert sample provinces (31 provinces of Iran)
    $stmt = $conn->query("SELECT COUNT(*) FROM provinces");
    if ($stmt->fetchColumn() == 0) {
        $provinces = [
            ['تهران', 'Tehran', 'tehran'],
            ['قم', 'Qom', 'qom'],
            ['مرکزی', 'Markazi', 'markazi'],
            ['قزوین', 'Qazvin', 'qazvin'],
            ['گیلان', 'Gilan', 'gilan'],
            ['اردبیل', 'Ardabil', 'ardabil'],
            ['زنجان', 'Zanjan', 'zanjan'],
            ['آذربایجان شرقی', 'East Azerbaijan', 'east-azerbaijan'],
            ['آذربایجان غربی', 'West Azerbaijan', 'west-azerbaijan'],
            ['کردستان', 'Kurdistan', 'kurdistan'],
            ['همدان', 'Hamadan', 'hamadan'],
            ['کرمانشاه', 'Kermanshah', 'kermanshah'],
            ['ایلام', 'Ilam', 'ilam'],
            ['لرستان', 'Lorestan', 'lorestan'],
            ['خوزستان', 'Khuzestan', 'khuzestan'],
            ['چهارمحال و بختیاری', 'Chaharmahal and Bakhtiari', 'chaharmahal-bakhtiari'],
            ['کهگیلویه و بویراحمد', 'Kohgiluyeh and Boyer-Ahmad', 'kohgiluyeh-boyer-ahmad'],
            ['بوشهر', 'Bushehr', 'bushehr'],
            ['فارس', 'Fars', 'fars'],
            ['هرمزگان', 'Hormozgan', 'hormozgan'],
            ['سیستان و بلوچستان', 'Sistan and Baluchestan', 'sistan-baluchestan'],
            ['کرمان', 'Kerman', 'kerman'],
            ['یزد', 'Yazd', 'yazd'],
            ['اصفهان', 'Isfahan', 'isfahan'],
            ['سمنان', 'Semnan', 'semnan'],
            ['مازندران', 'Mazandaran', 'mazandaran'],
            ['گلستان', 'Golestan', 'golestan'],
            ['خراسان شمالی', 'North Khorasan', 'north-khorasan'],
            ['خراسان رضوی', 'Razavi Khorasan', 'razavi-khorasan'],
            ['خراسان جنوبی', 'South Khorasan', 'south-khorasan'],
            ['البرز', 'Alborz', 'alborz']
        ];

        $stmt = $conn->prepare("INSERT INTO provinces (name, name_en, slug) VALUES (?, ?, ?)");
        foreach ($provinces as $province) {
            $stmt->execute($province);
        }
        $messages[] = "31 استان ایران با موفقیت اضافه شد";
        $success = true;
    } else {
        $messages[] = "استان‌ها از قبل در پایگاه داده وجود دارند";
    }

    // Insert sample branches for Tehran
    $stmt = $conn->query("SELECT COUNT(*) FROM branches");
    if ($stmt->fetchColumn() == 0) {
        // Get Tehran province ID
        $stmt = $conn->query("SELECT id FROM provinces WHERE slug = 'tehran' LIMIT 1");
        $tehranId = $stmt->fetchColumn();

        if ($tehranId) {
            $sampleBranches = [
                ['شعبه مرکزی تهران', 'تهران، خیابان ولیعصر، پلاک 100', '021-12345678', 'tehran@startech.ir', 1],
                ['شعبه شرق تهران', 'تهران، میدان تجریش، پلاک 50', '021-87654321', 'east@startech.ir', 2],
                ['شعبه غرب تهران', 'تهران، میدان آزادی، پلاک 25', '021-55555555', 'west@startech.ir', 3]
            ];

            $stmt = $conn->prepare("INSERT INTO branches (province_id, name, address, phone, email, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($sampleBranches as $branch) {
                $stmt->execute(array_merge([$tehranId], $branch));
            }
            $messages[] = "3 شعبه نمونه برای تهران اضافه شد";
            $success = true;
        }
    }

    if (empty($messages)) {
        $messages[] = "همه تغییرات از قبل اعمال شده‌اند";
    }

} catch (PDOException $e) {
    $errors[] = "خطا: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مهاجرت شعب و استان‌ها | استارتک</title>
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

        .migration-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 700px;
            width: 100%;
        }

        .migration-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .migration-body {
            padding: 30px;
        }

        .message-item {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .message-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <div class="migration-card">
        <div class="migration-header">
            <h1><i class="bi bi-geo-alt-fill"></i></h1>
            <h2>مهاجرت شعب و استان‌ها</h2>
            <p class="mb-0">ایجاد جداول مدیریت شعب در استان‌های مختلف</p>
        </div>
        <div class="migration-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle-fill"></i>
                    <strong>خطا!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($messages)): ?>
                <div class="alert alert-<?= $success ? 'success' : 'info' ?>">
                    <i class="bi bi-<?= $success ? 'check-circle-fill' : 'info-circle-fill' ?>"></i>
                    <strong><?= $success ? 'موفقیت!' : 'اطلاعات' ?></strong>
                    <div class="mt-3">
                        <?php foreach ($messages as $message): ?>
                            <div class="message-item">
                                <i
                                    class="bi bi-<?= $success ? 'check' : 'info' ?>-circle text-<?= $success ? 'success' : 'info' ?>"></i>
                                <?= htmlspecialchars($message) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <h6>جداول ایجاد شده:</h6>
                    <ul class="small mb-0">
                        <li><code>provinces</code> - جدول استان‌ها (31 استان ایران)</li>
                        <li><code>branches</code> - جدول شعب (با رابطه به استان‌ها)</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <a href="admin/provinces.php" class="btn btn-primary">
                        <i class="bi bi-map"></i> مدیریت استان‌ها
                    </a>
                    <a href="admin/branches.php" class="btn btn-success">
                        <i class="bi bi-building"></i> مدیریت شعب
                    </a>
                    <a href="admin/index.php" class="btn btn-secondary">
                        <i class="bi bi-house"></i> بازگشت به پنل مدیریت
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>