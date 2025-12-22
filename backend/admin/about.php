<?php
/**
 * About Page Management
 * مدیریت صفحه درباره ما
 */

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$conn = getConnection();
$action = $_GET['action'] ?? 'main';
$id = $_GET['id'] ?? null;
$error = '';
$success = '';
$tablesExist = false;

// Silently check if tables exist without throwing fatal errors
try {
    $testQuery = $conn->query("SHOW TABLES LIKE 'about_page'");
    $tablesExist = ($testQuery->rowCount() > 0);
} catch (Exception $e) {
    $tablesExist = false;
}

// Handle form submissions (BEFORE including header.php to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'main') {
        // Update main about page content
        $title = sanitize($_POST['title'] ?? '');
        $description = $_POST['description'] ?? '';
        $author_name = sanitize($_POST['author_name'] ?? '');
        $author_title = sanitize($_POST['author_title'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle feature image upload
        $feature_image = null;
        if (!empty($_FILES['feature_image']['name'])) {
            $upload = uploadImage($_FILES['feature_image'], 'about');
            if ($upload['success']) {
                $feature_image = $upload['path'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            // Check if about page exists
            $stmt = $conn->query("SELECT COUNT(*) FROM about_page");
            if ($stmt->fetchColumn() > 0) {
                // Update existing
                if ($feature_image) {
                    $stmt = $conn->prepare("UPDATE about_page SET title = ?, description = ?, author_name = ?, author_title = ?, feature_image = ?, is_active = ? WHERE id = 1");
                    $stmt->execute([$title, $description, $author_name, $author_title, $feature_image, $is_active]);
                } else {
                    $stmt = $conn->prepare("UPDATE about_page SET title = ?, description = ?, author_name = ?, author_title = ?, is_active = ? WHERE id = 1");
                    $stmt->execute([$title, $description, $author_name, $author_title, $is_active]);
                }
            } else {
                // Insert new
                $stmt = $conn->prepare("INSERT INTO about_page (title, description, author_name, author_title, feature_image, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $author_name, $author_title, $feature_image, $is_active]);
            }
            $success = 'اطلاعات صفحه درباره ما با موفقیت ذخیره شد';
        }
    } elseif ($action === 'team_add' || $action === 'team_edit') {
        // Add/Edit team member
        $name = sanitize($_POST['name'] ?? '');
        $position = sanitize($_POST['position'] ?? '');
        $description = $_POST['description'] ?? '';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle image upload (with forced resize to 600x800)
        $image = null;
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadTeamImage($_FILES['image'], 'about/team');
            if ($upload['success']) {
                $image = $upload['path'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            if ($action === 'team_edit' && $id) {
                // Get old image path before updating
                $oldImage = null;
                if ($image) {
                    $stmt = $conn->prepare("SELECT image FROM about_team WHERE id = ?");
                    $stmt->execute([$id]);
                    $oldMember = $stmt->fetch();
                    $oldImage = $oldMember['image'] ?? null;
                }
                
                // Update existing team member
                if ($image) {
                    $stmt = $conn->prepare("UPDATE about_team SET name = ?, position = ?, description = ?, image = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $position, $description, $image, $sort_order, $is_active, $id]);
                    
                    // Delete old image if exists
                    if ($oldImage && file_exists(UPLOAD_PATH . $oldImage)) {
                        @unlink(UPLOAD_PATH . $oldImage);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE about_team SET name = ?, position = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$name, $position, $description, $sort_order, $is_active, $id]);
                }
                $success = 'عضو تیم با موفقیت ویرایش شد';
            } else {
                // Add new team member
                $stmt = $conn->prepare("INSERT INTO about_team (name, position, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $position, $description, $image, $sort_order, $is_active]);
                $success = 'عضو تیم جدید با موفقیت اضافه شد';
            }
            header('Location: about.php?action=team');
            exit;
        }
    } elseif ($action === 'testimonial_add' || $action === 'testimonial_edit') {
        // Add/Edit testimonial
        $text = $_POST['text'] ?? '';
        $author_name = sanitize($_POST['author_name'] ?? '');
        $author_title = sanitize($_POST['author_title'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle avatar upload
        $avatar = null;
        if (!empty($_FILES['avatar']['name'])) {
            $upload = uploadImage($_FILES['avatar'], 'about/testimonials');
            if ($upload['success']) {
                $avatar = $upload['path'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            if ($action === 'testimonial_edit' && $id) {
                // Update existing testimonial
                if ($avatar) {
                    $stmt = $conn->prepare("UPDATE about_testimonials SET text = ?, author_name = ?, author_title = ?, rating = ?, avatar = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$text, $author_name, $author_title, $rating, $avatar, $sort_order, $is_active, $id]);
                } else {
                    $stmt = $conn->prepare("UPDATE about_testimonials SET text = ?, author_name = ?, author_title = ?, rating = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$text, $author_name, $author_title, $rating, $sort_order, $is_active, $id]);
                }
                $success = 'نظر مشتری با موفقیت ویرایش شد';
            } else {
                // Add new testimonial
                $stmt = $conn->prepare("INSERT INTO about_testimonials (text, author_name, author_title, rating, avatar, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$text, $author_name, $author_title, $rating, $avatar, $sort_order, $is_active]);
                $success = 'نظر مشتری جدید با موفقیت اضافه شد';
            }
            header('Location: about.php?action=testimonials');
            exit;
        }
    } elseif ($action === 'statistic_add' || $action === 'statistic_edit') {
        // Add/Edit statistic
        $value = sanitize($_POST['value'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($error)) {
            if ($action === 'statistic_edit' && $id) {
                // Update existing statistic
                $stmt = $conn->prepare("UPDATE about_statistics SET value = ?, title = ?, sort_order = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$value, $title, $sort_order, $is_active, $id]);
                $success = 'آمار با موفقیت ویرایش شد';
            } else {
                // Add new statistic
                $stmt = $conn->prepare("INSERT INTO about_statistics (value, title, sort_order, is_active) VALUES (?, ?, ?, ?)");
                $stmt->execute([$value, $title, $sort_order, $is_active]);
                $success = 'آمار جدید با موفقیت اضافه شد';
            }
            header('Location: about.php?action=statistics');
            exit;
        }
    }
}

// Handle delete actions (BEFORE including header.php to allow redirects)
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $table = $_GET['table'] ?? '';
    
    if ($table === 'team') {
        // Get image path before deleting
        $stmt = $conn->prepare("SELECT image FROM about_team WHERE id = ?");
        $stmt->execute([$deleteId]);
        $teamMember = $stmt->fetch();
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM about_team WHERE id = ?");
        $stmt->execute([$deleteId]);
        
        // Delete image file if exists
        if (!empty($teamMember['image']) && file_exists(UPLOAD_PATH . $teamMember['image'])) {
            @unlink(UPLOAD_PATH . $teamMember['image']);
        }
        
        $success = 'عضو تیم با موفقیت حذف شد';
    } elseif ($table === 'testimonial') {
        // Get avatar path before deleting
        $stmt = $conn->prepare("SELECT avatar FROM about_testimonials WHERE id = ?");
        $stmt->execute([$deleteId]);
        $testimonial = $stmt->fetch();
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM about_testimonials WHERE id = ?");
        $stmt->execute([$deleteId]);
        
        // Delete avatar file if exists
        if (!empty($testimonial['avatar']) && file_exists(UPLOAD_PATH . $testimonial['avatar'])) {
            @unlink(UPLOAD_PATH . $testimonial['avatar']);
        }
        
        $success = 'نظر مشتری با موفقیت حذف شد';
    } elseif ($table === 'statistic') {
        $stmt = $conn->prepare("DELETE FROM about_statistics WHERE id = ?");
        $stmt->execute([$deleteId]);
        $success = 'آمار با موفقیت حذف شد';
    }
    
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=' . $action);
    exit;
}

$pageTitle = 'مدیریت صفحه درباره ما';
require_once 'header.php';

// Get data
$aboutPage = null;
$teamMembers = [];
$testimonials = [];
$statistics = [];

// Check if tables exist, if not show migration message
$tablesExist = false;
try {
    $conn->query("SELECT 1 FROM about_page LIMIT 1");
    $tablesExist = true;
} catch (PDOException $e) {
    $error = 'جداول پایگاه داده ایجاد نشده‌اند. لطفاً ابتدا فایل migrate_about.php را اجرا کنید.';
    $action = 'main'; // Force main action to show error
}

// Always try to load data, even if tables don't exist (will show error but tabs will be visible)
if ($action === 'main') {
    try {
        $stmt = $conn->query("SELECT * FROM about_page LIMIT 1");
        $aboutPage = $stmt->fetch();
    } catch (PDOException $e) {
        if ($tablesExist) {
            $error = 'خطا در دریافت اطلاعات: ' . $e->getMessage();
        }
    }
} elseif ($action === 'team') {
    try {
        $stmt = $conn->query("SELECT * FROM about_team ORDER BY sort_order ASC, id ASC");
        $teamMembers = $stmt->fetchAll();
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM about_team WHERE id = ?");
            $stmt->execute([$id]);
            $editItem = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $error = 'خطا در دریافت اطلاعات تیم: ' . $e->getMessage();
    }
} elseif ($action === 'testimonials') {
    try {
        $stmt = $conn->query("SELECT * FROM about_testimonials ORDER BY sort_order ASC, id ASC");
        $testimonials = $stmt->fetchAll();
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM about_testimonials WHERE id = ?");
            $stmt->execute([$id]);
            $editItem = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $error = 'خطا در دریافت نظرات: ' . $e->getMessage();
    }
} elseif ($action === 'statistics') {
    try {
        $stmt = $conn->query("SELECT * FROM about_statistics ORDER BY sort_order ASC, id ASC");
        $statistics = $stmt->fetchAll();
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM about_statistics WHERE id = ?");
            $stmt->execute([$id]);
            $editItem = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $error = 'خطا در دریافت آمار: ' . $e->getMessage();
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-info-circle"></i> مدیریت صفحه درباره ما</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <strong>خطا:</strong> <?= htmlspecialchars($error) ?>
        <?php if (strpos($error, 'جداول پایگاه داده') !== false): ?>
            <br><br>
            <a href="../migrate_about.php" class="btn btn-primary" target="_blank">
                <i class="bi bi-database"></i> اجرای مایگریشن پایگاه داده
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="content-wrapper">
    <!-- Tabs - Always visible -->
    <style>
        /* Override sidebar nav styles for tabs */
        .content-wrapper .nav-tabs {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-direction: row !important;
            list-style: none !important;
            padding: 0 !important;
            margin-bottom: 1.5rem !important;
            border-bottom: 1px solid #dee2e6 !important;
            background: transparent !important;
        }
        .content-wrapper .nav-tabs .nav-item {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            padding: 0 !important;
            margin-bottom: 0 !important;
            margin-left: 0.5rem !important;
        }
        .content-wrapper .nav-tabs .nav-link {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            padding: 0.75rem 1.5rem !important;
            color: #495057 !important;
            text-decoration: none !important;
            border: 1px solid transparent !important;
            border-top-left-radius: 0.375rem !important;
            border-top-right-radius: 0.375rem !important;
            margin-bottom: -1px !important;
            background: transparent !important;
            border-radius: 0 !important;
            align-items: center !important;
            gap: 0 !important;
            transition: all 0.2s !important;
        }
        .content-wrapper .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6 !important;
            color: #e74c3c !important;
            background: #f8f9fa !important;
        }
        .content-wrapper .nav-tabs .nav-link.active {
            color: #e74c3c !important;
            background-color: #fff !important;
            border-color: #dee2e6 #dee2e6 #fff !important;
            font-weight: 600 !important;
        }
        .content-wrapper .nav-tabs .nav-link i {
            display: none !important;
        }
    </style>
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $action === 'main' ? 'active' : '' ?>" href="?action=main">محتوای اصلی</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $action === 'team' ? 'active' : '' ?>" href="?action=team">اعضای تیم</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $action === 'testimonials' ? 'active' : '' ?>" href="?action=testimonials">نظرات مشتریان</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $action === 'statistics' ? 'active' : '' ?>" href="?action=statistics">آمار و ارقام</a>
        </li>
    </ul>

    <?php if ($action === 'main'): ?>
        <!-- Main Content Form -->
        <div class="card">
            <div class="card-header">
                <h5>محتوای اصلی صفحه درباره ما</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">عنوان</label>
                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($aboutPage['title'] ?? 'درباره ما') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea class="form-control" name="description" rows="5" required><?= htmlspecialchars($aboutPage['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">نام نویسنده</label>
                                <input type="text" class="form-control" name="author_name" value="<?= htmlspecialchars($aboutPage['author_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">عنوان نویسنده</label>
                                <input type="text" class="form-control" name="author_title" value="<?= htmlspecialchars($aboutPage['author_title'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تصویر اصلی</label>
                        <input type="file" class="form-control" name="feature_image" accept="image/*">
                        <?php if (!empty($aboutPage['feature_image'])): ?>
                            <div class="mt-2">
                                <img src="<?= UPLOAD_URL . $aboutPage['feature_image'] ?>" alt="Feature Image" style="max-width: 300px; max-height: 300px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= ($aboutPage['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">فعال</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </form>
            </div>
        </div>

    <?php elseif ($action === 'team'): ?>
        <!-- Team Members -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>اعضای تیم</h5>
                <div>
                    <a href="../check_gd.php" class="btn btn-sm btn-info me-2" target="_blank" title="بررسی فعال بودن GD Extension">
                        <i class="bi bi-check-circle"></i> بررسی GD
                    </a>
                    <a href="../resize_existing_team_images.php" class="btn btn-sm btn-warning me-2" target="_blank">
                        <i class="bi bi-arrow-repeat"></i> تغییر اندازه تصاویر موجود
                    </a>
                    <a href="?action=team&id=new" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> افزودن عضو جدید
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($id && $id !== 'new'): ?>
                    <!-- Edit Form -->
                    <?php if (empty($editItem)): ?>
                        <div class="alert alert-danger">عضو تیم یافت نشد</div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" action="?action=team_edit&id=<?= $id ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">نام</label>
                                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editItem['name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">سمت</label>
                                        <input type="text" class="form-control" name="position" value="<?= htmlspecialchars($editItem['position']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">توضیحات</label>
                                <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تصویر <small class="text-muted">(اندازه: 600×800 پیکسل - به صورت خودکار تنظیم می‌شود)</small></label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="form-text text-muted">تصویر به صورت خودکار به اندازه 600×800 پیکسل تغییر اندازه داده می‌شود.</small>
                                <?php if (!empty($editItem['image'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= UPLOAD_URL . $editItem['image'] ?>" alt="Team Member" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ترتیب نمایش</label>
                                        <input type="number" class="form-control" name="sort_order" value="<?= $editItem['sort_order'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $editItem['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_active">فعال</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">ذخیره</button>
                            <a href="?action=team" class="btn btn-secondary">انصراف</a>
                        </form>
                    <?php endif; ?>
                <?php elseif ($id === 'new'): ?>
                    <!-- Add Form -->
                    <form method="POST" enctype="multipart/form-data" action="?action=team_add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">نام</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">سمت</label>
                                    <input type="text" class="form-control" name="position" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                            <div class="mb-3">
                                <label class="form-label">تصویر <small class="text-muted">(اندازه: 600×800 پیکسل - به صورت خودکار تنظیم می‌شود)</small></label>
                                <input type="file" class="form-control" name="image" accept="image/*" required>
                                <small class="form-text text-muted">تصویر به صورت خودکار به اندازه 600×800 پیکسل تغییر اندازه داده می‌شود.</small>
                            </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ترتیب نمایش</label>
                                    <input type="number" class="form-control" name="sort_order" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">فعال</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                        <a href="?action=team" class="btn btn-secondary">انصراف</a>
                    </form>
                <?php else: ?>
                    <!-- List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>تصویر</th>
                                    <th>نام</th>
                                    <th>سمت</th>
                                    <th>ترتیب</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($teamMembers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">هیچ عضوی اضافه نشده است</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($teamMembers as $member): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($member['image'])): ?>
                                                    <img src="<?= UPLOAD_URL . $member['image'] ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="product-thumb">
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($member['name']) ?></td>
                                            <td><?= htmlspecialchars($member['position']) ?></td>
                                            <td><?= $member['sort_order'] ?></td>
                                            <td>
                                                <span class="badge badge-status <?= $member['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $member['is_active'] ? 'فعال' : 'غیرفعال' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?action=team&id=<?= $member['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=team&delete=<?= $member['id'] ?>&table=team" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($action === 'testimonials'): ?>
        <!-- Testimonials -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>نظرات مشتریان</h5>
                <a href="?action=testimonials&id=new" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> افزودن نظر جدید
                </a>
            </div>
            <div class="card-body">
                <?php if ($id && $id !== 'new'): ?>
                    <!-- Edit Form -->
                    <?php if (empty($editItem)): ?>
                        <div class="alert alert-danger">نظر یافت نشد</div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data" action="?action=testimonial_edit&id=<?= $id ?>">
                            <div class="mb-3">
                                <label class="form-label">متن نظر</label>
                                <textarea class="form-control" name="text" rows="4" required><?= htmlspecialchars($editItem['text']) ?></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">نام نویسنده</label>
                                        <input type="text" class="form-control" name="author_name" value="<?= htmlspecialchars($editItem['author_name']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان نویسنده</label>
                                        <input type="text" class="form-control" name="author_title" value="<?= htmlspecialchars($editItem['author_title'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">امتیاز (1-5)</label>
                                        <input type="number" class="form-control" name="rating" value="<?= $editItem['rating'] ?>" min="1" max="5" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تصویر پروفایل</label>
                                <input type="file" class="form-control" name="avatar" accept="image/*">
                                <?php if (!empty($editItem['avatar'])): ?>
                                    <div class="mt-2">
                                        <img src="<?= UPLOAD_URL . $editItem['avatar'] ?>" alt="Avatar" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ترتیب نمایش</label>
                                        <input type="number" class="form-control" name="sort_order" value="<?= $editItem['sort_order'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $editItem['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_active">فعال</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">ذخیره</button>
                            <a href="?action=testimonials" class="btn btn-secondary">انصراف</a>
                        </form>
                    <?php endif; ?>
                <?php elseif ($id === 'new'): ?>
                    <!-- Add Form -->
                    <form method="POST" enctype="multipart/form-data" action="?action=testimonial_add">
                        <div class="mb-3">
                            <label class="form-label">متن نظر</label>
                            <textarea class="form-control" name="text" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">نام نویسنده</label>
                                    <input type="text" class="form-control" name="author_name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">عنوان نویسنده</label>
                                    <input type="text" class="form-control" name="author_title">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">امتیاز (1-5)</label>
                                    <input type="number" class="form-control" name="rating" value="5" min="1" max="5" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تصویر پروفایل</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ترتیب نمایش</label>
                                    <input type="number" class="form-control" name="sort_order" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">فعال</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                        <a href="?action=testimonials" class="btn btn-secondary">انصراف</a>
                    </form>
                <?php else: ?>
                    <!-- List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>تصویر</th>
                                    <th>متن</th>
                                    <th>نویسنده</th>
                                    <th>امتیاز</th>
                                    <th>ترتیب</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($testimonials)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">هیچ نظری اضافه نشده است</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($testimonials as $testimonial): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($testimonial['avatar'])): ?>
                                                    <img src="<?= UPLOAD_URL . $testimonial['avatar'] ?>" alt="Avatar" class="product-thumb" style="border-radius: 50%;">
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars(mb_substr($testimonial['text'], 0, 100)) ?>...</td>
                                            <td><?= htmlspecialchars($testimonial['author_name']) ?></td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $testimonial['rating'] ? '-fill text-warning' : '' ?>"></i>
                                                <?php endfor; ?>
                                            </td>
                                            <td><?= $testimonial['sort_order'] ?></td>
                                            <td>
                                                <span class="badge badge-status <?= $testimonial['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $testimonial['is_active'] ? 'فعال' : 'غیرفعال' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?action=testimonials&id=<?= $testimonial['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=testimonials&delete=<?= $testimonial['id'] ?>&table=testimonial" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($action === 'statistics'): ?>
        <!-- Statistics -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>آمار و ارقام</h5>
                <a href="?action=statistics&id=new" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> افزودن آمار جدید
                </a>
            </div>
            <div class="card-body">
                <?php if ($id && $id !== 'new'): ?>
                    <!-- Edit Form -->
                    <?php if (empty($editItem)): ?>
                        <div class="alert alert-danger">آمار یافت نشد</div>
                    <?php else: ?>
                        <form method="POST" action="?action=statistic_edit&id=<?= $id ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">مقدار</label>
                                        <input type="text" class="form-control" name="value" value="<?= htmlspecialchars($editItem['value']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">عنوان</label>
                                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editItem['title']) ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">ترتیب نمایش</label>
                                        <input type="number" class="form-control" name="sort_order" value="<?= $editItem['sort_order'] ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $editItem['is_active'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="is_active">فعال</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">ذخیره</button>
                            <a href="?action=statistics" class="btn btn-secondary">انصراف</a>
                        </form>
                    <?php endif; ?>
                <?php elseif ($id === 'new'): ?>
                    <!-- Add Form -->
                    <form method="POST" action="?action=statistic_add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">مقدار</label>
                                    <input type="text" class="form-control" name="value" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عنوان</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ترتیب نمایش</label>
                                    <input type="number" class="form-control" name="sort_order" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                        <label class="form-check-label" for="is_active">فعال</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                        <a href="?action=statistics" class="btn btn-secondary">انصراف</a>
                    </form>
                <?php else: ?>
                    <!-- List -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>مقدار</th>
                                    <th>عنوان</th>
                                    <th>ترتیب</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statistics)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">هیچ آماری اضافه نشده است</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($statistics as $stat): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($stat['value']) ?></strong></td>
                                            <td><?= htmlspecialchars($stat['title']) ?></td>
                                            <td><?= $stat['sort_order'] ?></td>
                                            <td>
                                                <span class="badge badge-status <?= $stat['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $stat['is_active'] ? 'فعال' : 'غیرفعال' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?action=statistics&id=<?= $stat['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?action=statistics&delete=<?= $stat['id'] ?>&table=statistic" class="btn btn-sm btn-danger" onclick="return confirm('آیا مطمئن هستید؟')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>

