<?php
/**
 * Blog Management
 * مدیریت وبلاگ
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
    $title = sanitize($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : null;
    $meta_title = sanitize($_POST['meta_title'] ?? '');
    $meta_description = sanitize($_POST['meta_description'] ?? '');
    $author_id = $_SESSION['admin_id'] ?? null;
    
    // Generate slug
    $slug = generateSlug($title);
    
    // Handle featured image upload
    $featured_image = null;
    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadImage($_FILES['featured_image'], 'blog');
        if ($upload['success']) {
            $featured_image = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    if (empty($error) && !empty($title) && !empty($content)) {
        if ($action === 'edit' && $id) {
            // Update existing post
            $sql = "UPDATE blog_posts SET 
                    title = ?, slug = ?, content = ?, excerpt = ?, 
                    is_published = ?, published_at = ?, 
                    meta_title = ?, meta_description = ?";
            $params = [$title, $slug, $content, $excerpt, 
                      $is_published, $published_at, 
                      $meta_title, $meta_description];
            
            if ($featured_image) {
                $sql .= ", featured_image = ?";
                $params[] = $featured_image;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            setFlashMessage('success', 'پست با موفقیت ویرایش شد');
            header('Location: blog.php');
            exit;
        } else {
            // Add new post
            $stmt = $conn->prepare("
                INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, 
                    author_id, is_published, published_at, meta_title, meta_description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $slug, $content, $excerpt, $featured_image, 
                           $author_id, $is_published, $published_at, $meta_title, $meta_description]);
            
            setFlashMessage('success', 'پست جدید با موفقیت اضافه شد');
            header('Location: blog.php');
            exit;
        }
    } else {
        if (empty($title)) {
            $error = 'عنوان پست الزامی است';
        } elseif (empty($content)) {
            $error = 'محتوای پست الزامی است';
        }
    }
}

// Handle delete (BEFORE including header.php)
if ($action === 'delete' && $id) {
    $post = getBlogPostById($id);
    if ($post) {
        // Delete featured image
        if ($post['featured_image']) {
            deleteImage($post['featured_image']);
        }
        
        $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$id]);
        
        setFlashMessage('success', 'پست با موفقیت حذف شد');
    }
    header('Location: blog.php');
    exit;
}

// Get post for editing (check before redirect)
$post = null;
if ($action === 'edit' && $id) {
    $post = getBlogPostById($id);
    if (!$post) {
        header('Location: blog.php');
        exit;
    }
}

// Now include header.php (after all redirects are handled)
$pageTitle = 'وبلاگ';
require_once 'header.php';

// Get blog posts with filters
$filters = [
    'is_published' => isset($_GET['status']) ? (int)$_GET['status'] : null,
    'search' => $_GET['search'] ?? null
];
$posts = getBlogPosts($filters);

// Flash message
$flash = getFlashMessage();
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>
        <i class="bi bi-journal-text"></i> 
        <?= ($action === 'add' || $action === 'edit') ? ($action === 'edit' ? 'ویرایش پست' : 'افزودن پست') : 'وبلاگ' ?>
    </h1>
    <?php if ($action === 'list'): ?>
    <a href="?action=add" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> افزودن پست
    </a>
    <?php else: ?>
    <a href="blog.php" class="btn btn-secondary">
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
                            <label class="form-label">عنوان پست <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   value="<?= $post ? sanitize($post['title']) : '' ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">خلاصه پست</label>
                            <textarea name="excerpt" class="form-control" rows="3" 
                                      placeholder="خلاصه کوتاه از محتوای پست"><?= $post ? sanitize($post['excerpt']) : '' ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">محتوای پست <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="15" required
                                      placeholder="محتوای کامل پست را اینجا بنویسید"><?= $post ? $post['content'] : '' ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">سئو (SEO)</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">عنوان متا (Meta Title)</label>
                            <input type="text" name="meta_title" class="form-control"
                                   value="<?= $post ? sanitize($post['meta_title']) : '' ?>"
                                   placeholder="اگر خالی باشد، از عنوان پست استفاده می‌شود">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات متا (Meta Description)</label>
                            <textarea name="meta_description" class="form-control" rows="3"
                                      placeholder="توضیحات کوتاه برای موتورهای جستجو"><?= $post ? sanitize($post['meta_description']) : '' ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">تصویر شاخص</div>
                    <div class="card-body">
                        <input type="file" name="featured_image" class="form-control mb-2" accept="image/*"
                               onchange="previewImage(this, 'imagePreview')">
                        <img id="imagePreview" class="img-thumbnail w-100" 
                             style="display: <?= ($post && $post['featured_image']) ? 'block' : 'none' ?>"
                             src="<?= ($post && $post['featured_image']) ? '../uploads/' . $post['featured_image'] : '' ?>">
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">وضعیت انتشار</div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" name="is_published" class="form-check-input" id="isPublished"
                                   <?= ($post && $post['is_published']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isPublished">پست منتشر شده</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">تاریخ انتشار</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                   value="<?= ($post && $post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>
                </div>
                
                <?php if ($post): ?>
                <div class="card mb-4">
                    <div class="card-header">اطلاعات</div>
                    <div class="card-body">
                        <small class="text-muted d-block mb-2">
                            <strong>نویسنده:</strong> <?= $post['author_name'] ?? 'نامشخص' ?>
                        </small>
                        <small class="text-muted d-block mb-2">
                            <strong>تاریخ ایجاد:</strong> <?= date('Y/m/d H:i', strtotime($post['created_at'])) ?>
                        </small>
                        <small class="text-muted d-block mb-2">
                            <strong>آخرین بروزرسانی:</strong> <?= date('Y/m/d H:i', strtotime($post['updated_at'])) ?>
                        </small>
                        <small class="text-muted d-block">
                            <strong>بازدید:</strong> <?= number_format($post['views']) ?>
                        </small>
                    </div>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle"></i> 
                    <?= $action === 'edit' ? 'ذخیره تغییرات' : 'افزودن پست' ?>
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
                    <select name="status" class="form-select">
                        <option value="">همه پست‌ها</option>
                        <option value="1" <?= (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : '' ?>>منتشر شده</option>
                        <option value="0" <?= (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : '' ?>>پیش‌نویس</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="جستجو در عنوان و محتوا..."
                           value="<?= $_GET['search'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> جستجو
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Posts List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>تصویر</th>
                            <th>عنوان</th>
                            <th>نویسنده</th>
                            <th>وضعیت</th>
                            <th>بازدید</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> پستی یافت نشد
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <?php if ($post['featured_image']): ?>
                                <img src="../uploads/<?= $post['featured_image'] ?>" 
                                     class="product-thumb" alt="<?= sanitize($post['title']) ?>">
                                <?php else: ?>
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($post['title']) ?></strong>
                                <?php if ($post['excerpt']): ?>
                                <br><small class="text-muted"><?= mb_substr(sanitize($post['excerpt']), 0, 50) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?= sanitize($post['author_name'] ?? 'نامشخص') ?></td>
                            <td>
                                <?php if ($post['is_published']): ?>
                                <span class="badge bg-success badge-status">منتشر شده</span>
                                <?php else: ?>
                                <span class="badge bg-secondary badge-status">پیش‌نویس</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($post['views']) ?></td>
                            <td>
                                <?php if ($post['published_at']): ?>
                                <?= date('Y/m/d H:i', strtotime($post['published_at'])) ?>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $post['id'] ?>" class="btn btn-outline-primary" title="ویرایش">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="?action=delete&id=<?= $post['id'] ?>" 
                                       class="btn btn-outline-danger" 
                                       title="حذف"
                                       onclick="return confirm('آیا از حذف این پست اطمینان دارید؟')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once 'footer.php'; ?>

