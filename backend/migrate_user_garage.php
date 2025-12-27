<?php
/**
 * Migration Script - User Garage
 * اسکریپت مهاجرت - گاراژ کاربر
 */

require_once __DIR__ . '/config/database.php';

$errors = [];
$success = false;
$messages = [];

try {
    $conn = getConnection();

    // Check if user_vehicles table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'user_vehicles'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `user_vehicles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NOT NULL,
                `vehicle_id` INT DEFAULT NULL,
                `factory_id` INT DEFAULT NULL,
                `custom_brand` VARCHAR(255) DEFAULT NULL,
                `custom_model` VARCHAR(255) DEFAULT NULL,
                `engine` VARCHAR(255) DEFAULT NULL,
                `year` INT DEFAULT NULL,
                `vin` VARCHAR(255) DEFAULT NULL,
                `type` ENUM('car', 'motorcycle', 'truck') DEFAULT 'car',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
                FOREIGN KEY (`factory_id`) REFERENCES `factories`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ";

        $conn->exec($sql);
        $success = true;
        $messages[] = "جدول user_vehicles با موفقیت ایجاد شد";
    } else {
        $messages[] = "جدول user_vehicles از قبل وجود دارد";
    }

} catch (PDOException $e) {
    $errors[] = "خطا: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>مهاجرت گاراژ کاربران</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4" style="max-width: 500px; width: 100%;">
        <h3 class="text-center mb-4">مهاجرت دیتابیس (گاراژ)</h3>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <ul>
                    <?php foreach ($messages as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <ul>
                    <?php foreach ($messages as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="api/user_garage.php" class="btn btn-outline-primary">تست API</a>
        </div>
    </div>
</body>

</html>