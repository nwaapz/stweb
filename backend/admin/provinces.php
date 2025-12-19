<?php
/**
 * Provinces Management
 * مدیریت استان‌ها
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
    $name = sanitize($_POST['name'] ?? '');
    $name_en = sanitize($_POST['name_en'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate slug from Persian name
    $slug = generateSlug($name_en ?: $name);
    
    if (empty($name)) {
        $error = 'نام استان الزامی است';
    }
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing province
            $stmt = $conn->prepare("
                UPDATE provinces SET name = ?, name_en = ?, slug = ?, description = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $name_en, $slug, $description, $is_active, $id]);
            
            setFlashMessage('success', 'استان با موفقیت ویرایش شد');
            ob_end_clean();
            header('Location: provinces.php');
            exit;
        } else {
            // Check if slug exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM provinces WHERE slug = ?");
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'این استان قبلاً اضافه شده است';
            } else {
                // Add new province
                $stmt = $conn->prepare("
                    INSERT INTO provinces (name, name_en, slug, description, is_active) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $name_en, $slug, $description, $is_active]);
                
                setFlashMessage('success', 'استان جدید با موفقیت اضافه شد');
                ob_end_clean();
                header('Location: provinces.php');
                exit;
            }
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    // Check if province has branches
    $stmt = $conn->prepare("SELECT COUNT(*) FROM branches WHERE province_id = ?");
    $stmt->execute([$id]);
    $branchCount = $stmt->fetchColumn();
    
    if ($branchCount > 0) {
        setFlashMessage('error', 'این استان دارای شعبه است و قابل حذف نیست');
    } else {
        $stmt = $conn->prepare("DELETE FROM provinces WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'استان با موفقیت حذف شد');
    }
    ob_end_clean();
    header('Location: provinces.php');
    exit;
}

// Get province for editing
$province = null;
if ($action === 'edit' && $id) {
    $province = getProvinceById($id);
    if (!$province) {
        ob_end_clean();
        header('Location: provinces.php');
        exit;
    }
}

// Set page title and include header
$pageTitle = 'استان‌ها';
require_once 'header.php';

// Get all provinces
$provinces = getProvinces();

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-map"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش استان' : 'افزودن استان') : 'استان‌ها' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن استان
    </a>
    <?php else: ?>
    <a href="provinces.php" class="btn btn-secondary">
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">نام استان (فارسی) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $province ? sanitize($province['name']) : '' ?>"
                                   placeholder="مثال: تهران، اصفهان، ...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">نام استان (انگلیسی)</label>
                            <input type="text" name="name_en" class="form-control"
                                   value="<?= $province ? sanitize($province['name_en']) : '' ?>"
                                   placeholder="Tehran, Isfahan, ...">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="توضیحات اختیاری درباره استان"><?= $province ? sanitize($province['description']) : '' ?></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                               <?= (!$province || $province['is_active']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isActive">فعال</label>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن استان' ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Provinces List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($provinces)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-map display-4"></i>
                <p class="mt-2">هنوز استانی اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین استان</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>نام استان</th>
                            <th>نام انگلیسی</th>
                            <th>شناسه (Slug)</th>
                            <th>تعداد شعب</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($provinces as $p):
                            // Get branch count
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM branches WHERE province_id = ?");
                            $stmt->execute([$p['id']]);
                            $branchCount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td>
                                <strong><?= sanitize($p['name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($p['name_en']): ?>
                                <span class="text-muted"><?= sanitize($p['name_en']) ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?= sanitize($p['slug']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= $branchCount ?> شعبه</span>
                            </td>
                            <td>
                                <?php if ($p['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف"
                                   onclick="return confirmDelete('آیا از حذف این استان اطمینان دارید؟')">
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
