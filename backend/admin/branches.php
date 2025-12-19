<?php
/**
 * Branches Management
 * مدیریت شعب
 */

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
    $province_id = (int)($_POST['province_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $latitude = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $error = 'نام شعبه الزامی است';
    } elseif (empty($address)) {
        $error = 'آدرس الزامی است';
    } elseif ($province_id <= 0) {
        $error = 'استان الزامی است';
    }
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing branch
            $stmt = $conn->prepare("
                UPDATE branches 
                SET province_id = ?, name = ?, address = ?, phone = ?, email = ?, 
                    latitude = ?, longitude = ?, sort_order = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$province_id, $name, $address, $phone, $email, 
                           $latitude, $longitude, $sort_order, $is_active, $id]);
            
            setFlashMessage('success', 'شعبه با موفقیت ویرایش شد');
            ob_end_clean();
            header('Location: branches.php');
            exit;
        } else {
            // Add new branch
            $stmt = $conn->prepare("
                INSERT INTO branches (province_id, name, address, phone, email, latitude, longitude, sort_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$province_id, $name, $address, $phone, $email, 
                           $latitude, $longitude, $sort_order, $is_active]);
            
            setFlashMessage('success', 'شعبه جدید با موفقیت اضافه شد');
            ob_end_clean();
            header('Location: branches.php');
            exit;
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
    $stmt->execute([$id]);
    
    setFlashMessage('success', 'شعبه با موفقیت حذف شد');
    ob_end_clean();
    header('Location: branches.php');
    exit;
}

// Get branch for editing
$branch = null;
if ($action === 'edit' && $id) {
    $branch = getBranchById($id);
    if (!$branch) {
        ob_end_clean();
        header('Location: branches.php');
        exit;
    }
}

// Set page title and include header
$pageTitle = 'شعب';
require_once 'header.php';

// Get all branches and provinces
$branches = getBranches();
$provinces = getProvinces(['is_active' => 1]);

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-building"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش شعبه' : 'افزودن شعبه') : 'شعب' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن شعبه
    </a>
    <?php else: ?>
    <a href="branches.php" class="btn btn-secondary">
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
            
            <form method="POST">
                <?php if ($action === 'edit' && $id): ?>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">استان <span class="text-danger">*</span></label>
                            <select name="province_id" class="form-select" required>
                                <option value="">انتخاب استان</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['id'] ?>" 
                                    <?= ($branch && $branch['province_id'] == $province['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($province['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">نام شعبه <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $branch ? sanitize($branch['name']) : '' ?>"
                                   placeholder="مثال: شعبه مرکزی، شعبه شرق، ...">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">آدرس <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required
                                      placeholder="آدرس کامل شعبه"><?= $branch ? sanitize($branch['address']) : '' ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">تلفن</label>
                                    <input type="text" name="phone" class="form-control"
                                           value="<?= $branch ? sanitize($branch['phone']) : '' ?>"
                                           placeholder="021-12345678">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ایمیل</label>
                                    <input type="email" name="email" class="form-control"
                                           value="<?= $branch ? sanitize($branch['email']) : '' ?>"
                                           placeholder="branch@example.com">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">عرض جغرافیایی (Latitude)</label>
                                    <input type="number" step="0.00000001" name="latitude" class="form-control"
                                           value="<?= $branch ? $branch['latitude'] : '' ?>"
                                           placeholder="35.6892">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">طول جغرافیایی (Longitude)</label>
                                    <input type="number" step="0.00000001" name="longitude" class="form-control"
                                           value="<?= $branch ? $branch['longitude'] : '' ?>"
                                           placeholder="51.3890">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">ترتیب نمایش</label>
                            <input type="number" name="sort_order" class="form-control" 
                                   value="<?= $branch ? $branch['sort_order'] : 0 ?>"
                                   placeholder="0">
                            <small class="text-muted">عدد کمتر = اولویت بیشتر</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                       <?= (!$branch || $branch['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن شعبه' ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Branches List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($branches)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-building display-4"></i>
                <p class="mt-2">هنوز شعبه‌ای اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین شعبه</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>نام شعبه</th>
                            <th>استان</th>
                            <th>آدرس</th>
                            <th>تلفن</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branches as $b): ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($b['name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= sanitize($b['province_name']) ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?= mb_substr(sanitize($b['address']), 0, 50) ?>...</small>
                            </td>
                            <td>
                                <?php if ($b['phone']): ?>
                                <a href="tel:<?= sanitize($b['phone']) ?>"><?= sanitize($b['phone']) ?></a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($b['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف"
                                   onclick="return confirmDelete('آیا از حذف این شعبه اطمینان دارید؟')">
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
