<?php
/**
 * Categories Management
 * مدیریت دسته‌بندی‌ها
 */

$pageTitle = 'دسته‌بندی‌ها';
require_once 'header.php';

$conn = getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Handle image upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'categories');
        if ($upload['success']) {
            $image = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing category
            $sql = "UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, sort_order = ?, is_active = ?";
            $params = [$name, $slug, $description, $parent_id, $sort_order, $is_active];
            
            if ($image) {
                $sql .= ", image = ?";
                $params[] = $image;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('success', 'دسته‌بندی با موفقیت ویرایش شد');
            header('Location: categories.php');
            exit;
        } else {
            // Add new category
            $stmt = $conn->prepare("
                INSERT INTO categories (name, slug, description, image, parent_id, sort_order, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $description, $image, $parent_id, $sort_order, $is_active]);
            
            setFlashMessage('success', 'دسته‌بندی جدید با موفقیت اضافه شد');
            header('Location: categories.php');
            exit;
        }
    }
}

// Handle delete
if ($action === 'delete' && $id) {
    // Check if category has products
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        setFlashMessage('error', 'این دسته‌بندی دارای محصول است و قابل حذف نیست');
    } else {
        // Delete image
        $category = getCategoryById($id);
        if ($category && $category['image']) {
            deleteImage($category['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'دسته‌بندی با موفقیت حذف شد');
    }
    header('Location: categories.php');
    exit;
}

// Get category for editing
$category = null;
if ($action === 'edit' && $id) {
    $category = getCategoryById($id);
    if (!$category) {
        header('Location: categories.php');
        exit;
    }
}

// Get all categories for parent select
$categories = getCategories();

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-folder"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش دسته‌بندی' : 'افزودن دسته‌بندی') : 'دسته‌بندی‌ها' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن دسته‌بندی
    </a>
    <?php else: ?>
    <a href="categories.php" class="btn btn-secondary">
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
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $category ? sanitize($category['name']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" class="form-control" rows="4"><?= $category ? sanitize($category['description']) : '' ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">دسته‌بندی والد</label>
                                    <select name="parent_id" class="form-select">
                                        <option value="">بدون والد (دسته اصلی)</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <?php if ($cat['id'] != $id): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                            <?= ($category && $category['parent_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= sanitize($cat['name']) ?>
                                        </option>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ترتیب نمایش</label>
                                    <input type="number" name="sort_order" class="form-control" 
                                           value="<?= $category ? $category['sort_order'] : 0 ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">تصویر دسته‌بندی</label>
                            <input type="file" name="image" class="form-control" accept="image/*"
                                   onchange="previewImage(this, 'imagePreview')">
                            <div class="mt-2">
                                <img id="imagePreview" class="img-thumbnail" style="max-width: 100%; display: <?= ($category && $category['image']) ? 'block' : 'none' ?>"
                                     src="<?= ($category && $category['image']) ? '../uploads/' . $category['image'] : '' ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                       <?= (!$category || $category['is_active']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">فعال</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن دسته‌بندی' ?>
                </button>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Categories List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($categories)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-folder display-4"></i>
                <p class="mt-2">هنوز دسته‌بندی‌ای اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین دسته‌بندی</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px">تصویر</th>
                            <th>نام دسته‌بندی</th>
                            <th>دسته والد</th>
                            <th>تعداد محصولات</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($categories as $cat):
                            // Get product count
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                            $stmt->execute([$cat['id']]);
                            $productCount = $stmt->fetchColumn();
                            
                            // Get parent name
                            $parentName = '-';
                            if ($cat['parent_id']) {
                                foreach ($categories as $parent) {
                                    if ($parent['id'] == $cat['parent_id']) {
                                        $parentName = $parent['name'];
                                        break;
                                    }
                                }
                            }
                        ?>
                        <tr>
                            <td>
                                <?php if ($cat['image']): ?>
                                <img src="../uploads/<?= $cat['image'] ?>" class="product-thumb" alt="">
                                <?php else: ?>
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-folder text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($cat['name']) ?></strong>
                                <?php if ($cat['description']): ?>
                                <br><small class="text-muted"><?= mb_substr(sanitize($cat['description']), 0, 50) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= sanitize($parentName) ?></td>
                            <td>
                                <span class="badge bg-info"><?= $productCount ?> محصول</span>
                            </td>
                            <td>
                                <?php if ($cat['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirmDelete('آیا از حذف این دسته‌بندی اطمینان دارید؟')">
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
