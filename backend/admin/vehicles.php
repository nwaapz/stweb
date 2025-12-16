<?php
/**
 * Vehicles Management
 * مدیریت وسایل نقلیه
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
    $factory_id = !empty($_POST['factory_id']) ? (int)$_POST['factory_id'] : null;
    $description = sanitize($_POST['description'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate slug
    $slug = generateSlug($name);
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing vehicle
            $stmt = $conn->prepare("
                UPDATE vehicles SET name = ?, slug = ?, factory_id = ?, description = ?, sort_order = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $slug, $factory_id, $description, $sort_order, $is_active, $id]);
            
            setFlashMessage('success', 'وسیله نقلیه با موفقیت ویرایش شد');
            ob_end_clean(); // Clear any output before redirect
            header('Location: vehicles.php');
            exit;
        } else {
            // Add new vehicle
            $stmt = $conn->prepare("
                INSERT INTO vehicles (name, slug, factory_id, description, sort_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $factory_id, $description, $sort_order, $is_active]);
            
            setFlashMessage('success', 'وسیله نقلیه جدید با موفقیت اضافه شد');
            ob_end_clean(); // Clear any output before redirect
            header('Location: vehicles.php');
            exit;
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    // Check if vehicle has products
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vehicle_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        setFlashMessage('error', 'این وسیله نقلیه دارای محصول است و قابل حذف نیست');
    } else {
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'وسیله نقلیه با موفقیت حذف شد');
    }
    ob_end_clean(); // Clear any output before redirect
    header('Location: vehicles.php');
    exit;
}

// Get vehicle for editing
$vehicle = null;
if ($action === 'edit' && $id) {
    $vehicle = getVehicleById($id);
    if (!$vehicle) {
        ob_end_clean(); // Clear any output before redirect
        header('Location: vehicles.php');
        exit;
    }
}

// Set page title and include header AFTER all redirects are handled
$pageTitle = 'وسایل نقلیه';
require_once 'header.php';

// Get all vehicles
$vehicles = getVehicles();
// Get all factories for select
$factories = getFactories();

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-car-front"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش وسیله نقلیه' : 'افزودن وسیله نقلیه') : 'وسایل نقلیه' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن وسیله نقلیه
    </a>
    <?php else: ?>
    <a href="vehicles.php" class="btn btn-secondary">
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
                            <label class="form-label">کارخانه خودروساز <span class="text-danger">*</span></label>
                            <select name="factory_id" class="form-select" required>
                                <option value="">انتخاب کارخانه</option>
                                <?php foreach ($factories as $factory): ?>
                                <option value="<?= $factory['id'] ?>" 
                                    <?= ($vehicle && $vehicle['factory_id'] == $factory['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($factory['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">نام وسیله نقلیه <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $vehicle ? sanitize($vehicle['name']) : '' ?>"
                                   placeholder="مثال: پراید، پژو 206، سمند و...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="توضیحات اختیاری درباره وسیله نقلیه"><?= $vehicle ? sanitize($vehicle['description']) : '' ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="<?= $vehicle ? $vehicle['sort_order'] : 0 ?>"
                                   placeholder="0">
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                       <?= (!$vehicle || $vehicle['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن وسیله نقلیه' ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Vehicles List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($vehicles)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-car-front display-4"></i>
                <p class="mt-2">هنوز وسیله نقلیه‌ای اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین وسیله نقلیه</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>نام وسیله نقلیه</th>
                            <th>کارخانه</th>
                            <th>توضیحات</th>
                            <th>تعداد محصولات</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($vehicles as $v):
                            // Get product count
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vehicle_id = ?");
                            $stmt->execute([$v['id']]);
                            $productCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($v['name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($v['factory_name']): ?>
                                <span class="badge bg-primary"><?= sanitize($v['factory_name']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($v['description']): ?>
                                <small class="text-muted"><?= mb_substr(sanitize($v['description']), 0, 50) ?>...</small>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $productCount ?> محصول</span>
                            </td>
                            <td>
                                <?php if ($v['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف"
                                   onclick="return confirmDelete('آیا از حذف این وسیله نقلیه اطمینان دارید؟')">
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

