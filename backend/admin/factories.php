<?php
/**
 * Factories Management
 * مدیریت کارخانجات خودروسازی
 */

// Start output buffering to prevent headers already sent errors
ob_start();

require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$conn = getConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$id = $_GET['id'] ?? $_POST['id'] ?? null;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Handle logo upload
    $logo = null;
    if (!empty($_FILES['logo']['name'])) {
        $upload = uploadImage($_FILES['logo'], 'factories');
        if ($upload['success']) {
            $logo = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing factory
            $sql = "UPDATE factories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?";
            $params = [$name, $slug, $description, $sort_order, $is_active];
            
            if ($logo) {
                $sql .= ", logo = ?";
                $params[] = $logo;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('success', 'کارخانه با موفقیت ویرایش شد');
            ob_end_clean(); // Clear any output before redirect
            header('Location: factories.php');
            exit;
        } else {
            // Add new factory
            $stmt = $conn->prepare("
                INSERT INTO factories (name, slug, description, logo, sort_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $description, $logo, $sort_order, $is_active]);
            
            setFlashMessage('success', 'کارخانه جدید با موفقیت اضافه شد');
            ob_end_clean(); // Clear any output before redirect
            header('Location: factories.php');
            exit;
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    // Check if factory has vehicles
    $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicles WHERE factory_id = ?");
    $stmt->execute([$id]);
    $vehicleCount = $stmt->fetchColumn();
    
    if ($vehicleCount > 0) {
        setFlashMessage('error', 'این کارخانه دارای وسیله نقلیه است و قابل حذف نیست');
    } else {
        // Delete logo
        $factory = getFactoryById($id);
        if ($factory && $factory['logo']) {
            deleteImage($factory['logo']);
        }
        
        $stmt = $conn->prepare("DELETE FROM factories WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'کارخانه با موفقیت حذف شد');
    }
    ob_end_clean(); // Clear any output before redirect
    header('Location: factories.php');
    exit;
}

// Get factory for editing
$factory = null;
if ($action === 'edit' && $id) {
    $factory = getFactoryById($id);
    if (!$factory) {
        ob_end_clean(); // Clear any output before redirect
        header('Location: factories.php');
        exit;
    }
}

// Set page title and include header AFTER all redirects are handled
$pageTitle = 'کارخانجات خودروسازی';
require_once 'header.php';

// Get all factories
$factories = getFactories();

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-building"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش کارخانه' : 'افزودن کارخانه') : 'کارخانجات خودروسازی' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن کارخانه
    </a>
    <?php else: ?>
    <a href="factories.php" class="btn btn-secondary">
        <i class="bi bi-arrow-right"></i> بازگشت به لیست
    </a>
    <?php endif; ?>
</div>

<div class="content-wrapper">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($action === 'add' || $action === 'edit'): ?>
    <!-- Add/Edit Form -->
    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <?php if ($action === 'edit' && $id): ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">نام کارخانه <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $factory ? sanitize($factory['name']) : '' ?>"
                                   placeholder="مثال: ایران خودرو، سایپا، پژو و...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="توضیحات اختیاری درباره کارخانه"><?= $factory ? sanitize($factory['description']) : '' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">لوگو کارخانه</label>
                            <input type="file" name="logo" class="form-control" accept="image/*"
                                   onchange="previewImage(this, 'logoPreview')">
                            <div class="mt-2">
                                <img id="logoPreview" class="img-thumbnail" style="max-width: 100%; display: <?= ($factory && $factory['logo']) ? 'block' : 'none' ?>"
                                     src="<?= ($factory && $factory['logo']) ? '../uploads/' . $factory['logo'] : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="<?= $factory ? $factory['sort_order'] : 0 ?>"
                                   placeholder="0">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                       <?= (!$factory || $factory['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن کارخانه' ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Factories List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($factories)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-building display-4"></i>
                <p class="mt-2">هنوز کارخانه‌ای اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین کارخانه</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px">لوگو</th>
                            <th>نام کارخانه</th>
                            <th>توضیحات</th>
                            <th>تعداد وسایل نقلیه</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($factories as $f):
                            // Get vehicle count
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicles WHERE factory_id = ?");
                            $stmt->execute([$f['id']]);
                            $vehicleCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td>
                                <?php if ($f['logo']): ?>
                                <img src="../uploads/<?= $f['logo'] ?>" class="product-thumb" alt="">
                                <?php else: ?>
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-building text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($f['name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($f['description']): ?>
                                <small class="text-muted"><?= mb_substr(sanitize($f['description']), 0, 50) ?>...</small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $vehicleCount ?> وسیله</span>
                            </td>
                            <td>
                                <?php if ($f['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف"
                                   onclick="return confirmDelete('آیا از حذف این کارخانه اطمینان دارید؟')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>






