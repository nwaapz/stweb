<?php
/**
 * Discounts Management
 * مدیریت تخفیف‌ها
 */

$pageTitle = 'تخفیف‌ها';
require_once 'header.php';

$conn = getConnection();

// Handle quick discount update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_discount'])) {
    $productId = (int)$_POST['product_id'];
    $discountPrice = !empty($_POST['discount_price']) ? (int)str_replace(',', '', $_POST['discount_price']) : null;
    $discountPercent = !empty($_POST['discount_percent']) ? (int)$_POST['discount_percent'] : null;
    $discountEnd = !empty($_POST['discount_end']) ? $_POST['discount_end'] : null;
    
    // If discount_percent is provided but discount_price is not, calculate it from the product price
    if ($discountPercent && !$discountPrice) {
        $product = getProductById($productId);
        if ($product && $product['price']) {
            $discountPrice = (int)round($product['price'] - ($product['price'] * $discountPercent / 100));
        }
    }
    
    $stmt = $conn->prepare("
        UPDATE products 
        SET discount_price = ?, discount_percent = ?, discount_start = NOW(), discount_end = ?
        WHERE id = ?
    ");
    $stmt->execute([$discountPrice, $discountPercent, $discountEnd, $productId]);
    
    setFlashMessage('success', 'تخفیف با موفقیت اعمال شد');
    header('Location: discounts.php');
    exit;
}

// Handle remove discount
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $stmt = $conn->prepare("
        UPDATE products 
        SET discount_price = NULL, discount_percent = NULL, discount_start = NULL, discount_end = NULL
        WHERE id = ?
    ");
    $stmt->execute([$_GET['remove']]);
    
    setFlashMessage('success', 'تخفیف حذف شد');
    header('Location: discounts.php');
    exit;
}

// Handle bulk discount
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_discount'])) {
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $discountPercent = (int)$_POST['bulk_percent'];
    $discountEnd = !empty($_POST['bulk_end']) ? $_POST['bulk_end'] : null;
    
    $sql = "UPDATE products SET 
            discount_percent = ?,
            discount_price = price - (price * ? / 100),
            discount_start = NOW(),
            discount_end = ?";
    $params = [$discountPercent, $discountPercent, $discountEnd];
    
    if ($categoryId) {
        $sql .= " WHERE category_id = ?";
        $params[] = $categoryId;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $affected = $stmt->rowCount();
    setFlashMessage('success', "تخفیف روی $affected محصول اعمال شد");
    header('Location: discounts.php');
    exit;
}

// Get categories
$categories = getCategories();

// Get all products
$products = getProducts();

// Get discounted products
$discountedProducts = getProducts(['has_discount' => true]);

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header">
    <h1><i class="bi bi-percent"></i> مدیریت تخفیف‌ها</h1>
</div>

<div class="content-wrapper">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= $flash['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Bulk Discount -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-lightning"></i> تخفیف گروهی
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="bulk_discount" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label">دسته‌بندی</label>
                            <select name="category_id" class="form-select">
                                <option value="">همه محصولات</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= sanitize($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">درصد تخفیف <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="bulk_percent" class="form-control" min="1" max="99" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تاریخ پایان</label>
                            <input type="datetime-local" name="bulk_end" class="form-control">
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100" 
                                onclick="return confirm('آیا از اعمال تخفیف گروهی اطمینان دارید؟')">
                            <i class="bi bi-check-circle"></i> اعمال تخفیف
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Quick Discount -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="bi bi-tag"></i> تخفیف سریع محصول
                </div>
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="quick_discount" value="1">
                        
                        <div class="col-md-4">
                            <label class="form-label">انتخاب محصول</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">انتخاب کنید</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>">
                                    <?= sanitize($p['name']) ?> (<?= formatPrice($p['price']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">درصد</label>
                            <div class="input-group">
                                <input type="number" name="discount_percent" class="form-control" min="1" max="99" id="qPercent">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">قیمت تخفیف (تومان)</label>
                            <input type="text" name="discount_price" class="form-control price-input" id="qPrice">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">پایان تخفیف</label>
                            <input type="datetime-local" name="discount_end" class="form-control">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> اعمال تخفیف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Discounted Products -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-tag-fill"></i> محصولات تخفیف‌دار (<?= count($discountedProducts) ?>)</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($discountedProducts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-tag display-4"></i>
                <p class="mt-2">هیچ محصول تخفیف‌داری وجود ندارد</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>نام محصول</th>
                            <th>قیمت اصلی</th>
                            <th>قیمت تخفیف</th>
                            <th>درصد</th>
                            <th>تاریخ پایان</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discountedProducts as $p): ?>
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
                            <td><strong><?= sanitize($p['name']) ?></strong></td>
                            <td><del class="text-muted"><?= formatPrice($p['price']) ?></del></td>
                            <td><span class="text-danger fw-bold"><?= formatPrice($p['discount_price']) ?></span></td>
                            <td>
                                <?php if ($p['discount_percent']): ?>
                                <span class="badge bg-danger"><?= $p['discount_percent'] ?>%</span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['discount_end']): ?>
                                <?= date('Y/m/d H:i', strtotime($p['discount_end'])) ?>
                                <?php else: ?>
                                <span class="text-success">بدون محدودیت</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="?remove=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('آیا از حذف تخفیف اطمینان دارید؟')">
                                    <i class="bi bi-x-circle"></i>
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
</div>

<?php 
$extraScripts = <<<SCRIPT
<script>
// Calculate discount price from percentage
document.getElementById('qPercent').addEventListener('input', function() {
    const select = document.querySelector('select[name="product_id"]');
    const option = select.options[select.selectedIndex];
    if (option && option.dataset.price) {
        const price = parseInt(option.dataset.price);
        const percent = parseInt(this.value) || 0;
        const discountPrice = Math.round(price - (price * percent / 100));
        document.getElementById('qPrice').value = discountPrice.toLocaleString();
    }
});

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
