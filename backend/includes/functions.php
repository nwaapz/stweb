<?php
/**
 * Helper Functions
 * توابع کمکی
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Generate URL-friendly slug
 */
function generateSlug($string)
{
    // Convert Persian/Arabic numerals to English
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $string = str_replace($persian, $english, $string);

    // Replace spaces with hyphens
    $string = preg_replace('/\s+/', '-', trim($string));

    // Remove special characters except Persian/Arabic letters and hyphens
    $string = preg_replace('/[^\p{L}\p{N}\-]/u', '', $string);

    // Convert to lowercase
    $string = mb_strtolower($string, 'UTF-8');

    // Remove multiple hyphens
    $string = preg_replace('/-+/', '-', $string);

    return trim($string, '-');
}

/**
 * Upload image file
 */
function uploadImage($file, $folder = 'products')
{
    $uploadDir = UPLOAD_PATH . $folder . '/';

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'فرمت فایل مجاز نیست'];
    }

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'error' => 'حجم فایل بیش از حد مجاز است'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $folder . '/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'خطا در آپلود فایل'];
}

/**
 * Delete uploaded file
 */
function deleteImage($path)
{
    $fullPath = UPLOAD_PATH . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Format price in Persian
 */
function formatPrice($price)
{
    return number_format($price, 0, '', ',') . ' تومان';
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

/**
 * Require login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Sanitize input
 */
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get all categories
 */
function getCategories($activeOnly = false)
{
    $conn = getConnection();
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY sort_order, name";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get category by ID
 */
function getCategoryById($id)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all factories
 */
function getFactories($activeOnly = false)
{
    $conn = getConnection();
    $sql = "SELECT * FROM factories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY sort_order, name";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get factory by ID
 */
function getFactoryById($id)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM factories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all vehicles
 */
function getVehicles($activeOnly = false)
{
    $conn = getConnection();
    $sql = "SELECT v.*, f.name as factory_name 
            FROM vehicles v 
            LEFT JOIN factories f ON v.factory_id = f.id";
    if ($activeOnly) {
        $sql .= " WHERE v.is_active = 1";
    }
    $sql .= " ORDER BY v.sort_order, v.name";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get vehicle by ID
 */
function getVehicleById($id)
{
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT v.*, f.name as factory_name 
        FROM vehicles v 
        LEFT JOIN factories f ON v.factory_id = f.id 
        WHERE v.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all products
 */
function getProducts($filters = [])
{
    $conn = getConnection();
    $sql = "SELECT p.*, c.name as category_name, v.name as vehicle_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN vehicles v ON p.vehicle_id = v.id 
            WHERE 1=1";
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }

    if (!empty($filters['vehicle_id'])) {
        $sql .= " AND p.vehicle_id = ?";
        $params[] = $filters['vehicle_id'];
    }

    if (isset($filters['is_active'])) {
        $sql .= " AND p.is_active = ?";
        $params[] = $filters['is_active'];
    }

    if (!empty($filters['is_featured'])) {
        $sql .= " AND p.is_featured = 1";
    }

    if (!empty($filters['has_discount'])) {
        $sql .= " AND p.discount_price IS NOT NULL 
                  AND (p.discount_end IS NULL OR p.discount_end >= NOW())
                  AND (p.discount_start IS NULL OR p.discount_start <= NOW())";
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['category_name'])) {
        $sql .= " AND c.name = ?";
        $params[] = $filters['category_name'];
    }

    // Order by created_at DESC, fallback to id DESC if created_at is null
    $sql .= " ORDER BY p.created_at DESC, p.id DESC";

    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . (int) $filters['limit'];
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get product by ID
 */
function getProductById($id)
{
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, v.name as vehicle_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN vehicles v ON p.vehicle_id = v.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get product by Slug
 */
function getProductBySlug($slug)
{
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, v.name as vehicle_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN vehicles v ON p.vehicle_id = v.id 
        WHERE p.slug = ?
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Check if product has active discount
 */
function hasActiveDiscount($product)
{
    if (empty($product['discount_price'])) {
        return false;
    }

    $now = time();

    if (!empty($product['discount_start']) && strtotime($product['discount_start']) > $now) {
        return false;
    }

    if (!empty($product['discount_end']) && strtotime($product['discount_end']) < $now) {
        return false;
    }

    return true;
}

/**
 * Get effective price (considering discount)
 */
function getEffectivePrice($product)
{
    if (hasActiveDiscount($product)) {
        return $product['discount_price'];
    }
    return $product['price'];
}

/**
 * Flash message
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage()
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>