<?php
/**
 * Products Management
 * مدیریت محصولات
 */

// Include functions first (before any output)
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$conn = getConnection();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error = '';

// Handle form submissions (BEFORE including header.php to allow redirects)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = $_POST['description'] ?? '';
    $short_description = sanitize($_POST['short_description'] ?? '');
    $price = (int)str_replace(',', '', $_POST['price'] ?? 0);
    $sku = sanitize($_POST['sku'] ?? '');
    $stock = (int)($_POST['stock'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Discount fields
    $discount_price = !empty($_POST['discount_price']) ? (int)str_replace(',', '', $_POST['discount_price']) : null;
    $discount_percent = !empty($_POST['discount_percent']) ? (int)$_POST['discount_percent'] : null;
    $discount_start = !empty($_POST['discount_start']) ? $_POST['discount_start'] : null;
    $discount_end = !empty($_POST['discount_end']) ? $_POST['discount_end'] : null;
    
    // Generate slug
    $slug = generateSlug($name);
    
    // Handle image upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], 'products');
        if ($upload['success']) {
            $image = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    if (empty($error)) {
        if ($action === 'edit' && $id) {
            // Update existing product
            $sql = "UPDATE products SET 
                    name = ?, slug = ?, category_id = ?, description = ?, short_description = ?,
                    price = ?, discount_price = ?, discount_percent = ?, discount_start = ?, discount_end = ?,
                    sku = ?, stock = ?, is_active = ?, is_featured = ?";
            $params = [$name, $slug, $category_id, $description, $short_description,
                      $price, $discount_price, $discount_percent, $discount_start, $discount_end,
                      $sku, $stock, $is_active, $is_featured];
            
            if ($image) {
                $sql .= ", image = ?";
                $params[] = $image;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('success', 'محصول با موفقیت ویرایش شد');
            header('Location: products.php');
            exit;
        } else {
            // Add new product
            $stmt = $conn->prepare("
                INSERT INTO products (name, slug, category_id, description, short_description, price, 
                    discount_price, discount_percent, discount_start, discount_end, image, sku, stock, is_active, is_featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $category_id, $description, $short_description, $price,
                           $discount_price, $discount_percent, $discount_start, $discount_end, $image, $sku, $stock, $is_active, $is_featured]);
            
            setFlashMessage('success', 'محصول جدید با موفقیت اضافه شد');
            header('Location: products.php');
            exit;
        }
    }
}

// Handle delete (BEFORE including header.php)
if ($action === 'delete' && $id) {
    $product = getProductById($id);
    if ($product) {
        // Delete image
        if ($product['image']) {
            deleteImage($product['image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'محصول با موفقیت حذف شد');
    }
    header('Location: products.php');
    exit;
}

// Get product for editing (check before redirect)
$product = null;
if ($action === 'edit' && $id) {
    $product = getProductById($id);
    if (!$product) {
        header('Location: products.php');
        exit;
    }
}

// Now include header.php (after all redirects are handled)
$pageTitle = 'محصولات';
require_once 'header.php';

// Get categories for select
$categories = getCategories();

// Get products with filters
$filters = [
    'category_id' => $_GET['category'] ?? null,
    'search' => $_GET['search'] ?? null
];
$products = getProducts($filters);

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-box"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش محصول' : 'افزودن محصول') : 'محصولات' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن محصول
    </a>
    <?php else: ?>
    <a href="products.php" class="btn btn-secondary">
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
    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">اطلاعات اصلی</div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">نام محصول <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $product ? sanitize($product['name']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیح کوتاه</label>
                            <input type="text" name="short_description" class="form-control" maxlength="500"
                                   value="<?= $product ? sanitize($product['short_description']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات کامل</label>
                            <textarea name="description" class="form-control" rows="6"><?= $product ? $product['description'] : '' ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">قیمت و تخفیف</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">قیمت (تومان) <span class="text-danger">*</span></label>
                                    <input type="text" name="price" class="form-control price-input" required
                                           value="<?= $product ? number_format($product['price']) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">قیمت با تخفیف (تومان)</label>
                                    <input type="text" name="discount_price" class="form-control price-input"
                                           value="<?= ($product && $product['discount_price']) ? number_format($product['discount_price']) : '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">درصد تخفیف</label>
                                    <div class="input-group">
                                        <input type="number" name="discount_percent" class="form-control" min="0" max="100"
                                               value="<?= $product ? $product['discount_percent'] : '' ?>">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">شروع تخفیف</label>
                                    <input type="datetime-local" name="discount_start" class="form-control"
                                           value="<?= ($product && $product['discount_start']) ? date('Y-m-d\TH:i', strtotime($product['discount_start'])) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">پایان تخفیف</label>
                                    <input type="datetime-local" name="discount_end" class="form-control"
                                           value="<?= ($product && $product['discount_end']) ? date('Y-m-d\TH:i', strtotime($product['discount_end'])) : '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">تصویر محصول</div>
                    <div class="card-body">
                        <input type="file" name="image" class="form-control mb-2" accept="image/*"
                               onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" class="img-thumbnail w-100" 
                             style="display: <?= ($product && $product['image']) ? 'block' : 'none' ?>"
                             src="<?= ($product && $product['image']) ? '../uploads/' . $product['image'] : '' ?>">
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">دسته‌بندی و موجودی</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">دسته‌بندی</label>
                            <select name="category_id" class="form-select">
                                <option value="">انتخاب دسته‌بندی</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= ($product && $product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">کد محصول (SKU)</label>
                            <input type="text" name="sku" class="form-control"
                                   value="<?= $product ? sanitize($product['sku']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">موجودی انبار</label>
                            <input type="number" name="stock" class="form-control" min="0"
                                   value="<?= $product ? $product['stock'] : 0 ?>">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">وضعیت</div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-2">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                   <?= (!$product || $product['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">محصول فعال</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured"
                                   <?= ($product && $product['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isFeatured">محصول ویژه</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن محصول' ?>
                </button>
            </div>
        </div>
    </form>
    
    <?php else: ?>
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">همه دسته‌بندی‌ها</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="جستجو در محصولات..."
                           value="<?= sanitize($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> جستجو
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Products List -->
    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($products)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-box display-4"></i>
                <p class="mt-2">هنوز محصولی اضافه نشده است</p>
                <a href="?action=add" class="btn btn-primary">افزودن اولین محصول</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px">تصویر</th>
                            <th>نام محصول</th>
                            <th>دسته‌بندی</th>
                            <th>قیمت</th>
                            <th>موجودی</th>
                            <th>وضعیت</th>
                            <th style="width: 150px">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['image']): ?>
                                <img src="../uploads/<?= $p['image'] ?>" class="product-thumb" alt="">
                                <?php else: ?>
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($p['name']) ?></strong>
                                <?php if ($p['sku']): ?>
                                <br><small class="text-muted">SKU: <?= sanitize($p['sku']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $p['category_name'] ?? 'بدون دسته' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (hasActiveDiscount($p)): ?>
                                <del class="text-muted small"><?= formatPrice($p['price']) ?></del><br>
                                <span class="text-danger fw-bold"><?= formatPrice($p['discount_price']) ?></span>
                                <?php if ($p['discount_percent']): ?>
                                <span class="badge bg-danger"><?= $p['discount_percent'] ?>%</span>
                                <?php endif; ?>
                                <?php else: ?>
                                <?= formatPrice($p['price']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['stock'] > 10): ?>
                                <span class="badge bg-success"><?= $p['stock'] ?></span>
                                <?php elseif ($p['stock'] > 0): ?>
                                <span class="badge bg-warning"><?= $p['stock'] ?></span>
                                <?php else: ?>
                                <span class="badge bg-danger">ناموجود</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['is_active']): ?>
                                <span class="badge badge-status bg-success">فعال</span>
                                <?php else: ?>
                                <span class="badge badge-status bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                                <?php if ($p['is_featured']): ?>
                                <span class="badge badge-status bg-warning">ویژه</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="ویرایش">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" title="حذف"
                                   onclick="return confirmDelete('آیا از حذف این محصول اطمینان دارید؟')">
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

<?php 
$extraScripts = <<<SCRIPT
<script>
// Format price inputs
document.querySelectorAll('.price-input').forEach(function(input) {
    input.addEventListener('input', function(e) {
        let value = this.value.replace(/,/g, '');
        if (!isNaN(value) && value !== '') {
            this.value = Number(value).toLocaleString();
        }
    });
});
</script>
SCRIPT;
require_once 'footer.php'; 
?>
