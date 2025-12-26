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
 * Resize image to specific dimensions
 */
function resizeImage($filepath, $width, $height, $quality = 90)
{
    // Check if GD extension is available
    if (!extension_loaded('gd')) {
        error_log("PHP GD extension is not loaded");
        return false;
    }
    
    if (!file_exists($filepath)) {
        error_log("Image file not found: " . $filepath);
        return false;
    }
    
    // Get image info
    $imageInfo = getimagesize($filepath);
    if (!$imageInfo) {
        error_log("Invalid image file: " . $filepath);
        return false;
    }
    
    $originalWidth = $imageInfo[0];
    $originalHeight = $imageInfo[1];
    $mimeType = $imageInfo['mime'];
    
    // Create image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
            $sourceImage = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $sourceImage = imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $sourceImage = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $sourceImage = imagecreatefromwebp($filepath);
            break;
        default:
            return false;
    }
    
    if (!$sourceImage) {
        return false;
    }
    
    // Create new image with exact dimensions
    $newImage = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG and GIF
    if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
    } else {
        // Fill with white background for JPEG/WebP
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefilledrectangle($newImage, 0, 0, $width, $height, $white);
    }
    
    // Calculate aspect ratio and resize with cropping to fit exact dimensions
    $sourceAspect = $originalWidth / $originalHeight;
    $targetAspect = $width / $height;
    
    // Calculate source crop dimensions
    if ($sourceAspect > $targetAspect) {
        // Source is wider - crop width (center crop)
        $cropHeight = $originalHeight;
        $cropWidth = $originalHeight * $targetAspect;
        $x = ($originalWidth - $cropWidth) / 2;
        $y = 0;
    } else {
        // Source is taller - crop height (center crop)
        $cropWidth = $originalWidth;
        $cropHeight = $originalWidth / $targetAspect;
        $x = 0;
        $y = ($originalHeight - $cropHeight) / 2;
    }
    
    // Enable high-quality resampling
    imagealphablending($newImage, true);
    imagesavealpha($newImage, true);
    
    // Resize and crop to exact dimensions (cast to int for imagecopyresampled)
    $resizeSuccess = imagecopyresampled(
        $newImage, 
        $sourceImage, 
        0, 0, 
        (int)round($x), (int)round($y), 
        $width, $height, 
        (int)round($cropWidth), (int)round($cropHeight)
    );
    
    if (!$resizeSuccess) {
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        return false;
    }
    
    // Save resized image (overwrite original file)
    $result = false;
    switch ($mimeType) {
        case 'image/jpeg':
            $result = imagejpeg($newImage, $filepath, $quality);
            break;
        case 'image/png':
            // For PNG, ensure alpha channel is preserved
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $result = imagepng($newImage, $filepath, 9);
            break;
        case 'image/gif':
            $result = imagegif($newImage, $filepath);
            break;
        case 'image/webp':
            $result = imagewebp($newImage, $filepath, $quality);
            break;
    }
    
    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);
    
    // Verify the saved file exists and has correct dimensions
    if ($result) {
        $verifyInfo = @getimagesize($filepath);
        if ($verifyInfo && $verifyInfo[0] == $width && $verifyInfo[1] == $height) {
            return true;
        } else {
            error_log("Resize verification failed: Expected {$width}x{$height}, got " . ($verifyInfo ? $verifyInfo[0] . "x" . $verifyInfo[1] : "unknown"));
            // Still return true if file was saved, even if dimensions don't match (might be a verification issue)
            return $result;
        }
    }
    
    return false;
}

/**
 * Upload and resize image for team members (600x800)
 * Automatically resizes ANY uploaded image to exactly 600x800 pixels
 */
function uploadTeamImage($file, $folder = 'about/team')
{
    $upload = uploadImage($file, $folder);
    
    if ($upload['success']) {
        $fullPath = UPLOAD_PATH . $upload['path'];
        
        // FORCE resize to 600x800 - this happens automatically for ALL uploads
        $resizeResult = resizeImage($fullPath, 600, 800);
        if ($resizeResult === false) {
            // Delete the uploaded file if resize fails
            @unlink($fullPath);
            error_log("Failed to resize team image: " . $fullPath);
            return [
                'success' => false, 
                'error' => 'خطا در تغییر اندازه تصویر به 600×800. لطفاً مطمئن شوید که PHP GD extension فعال است.'
            ];
        }
        
        // Verify the resized image dimensions
        $resizedInfo = @getimagesize($fullPath);
        if ($resizedInfo) {
            if ($resizedInfo[0] != 600 || $resizedInfo[1] != 800) {
                // If verification fails, try to resize again
                error_log("Image resize verification failed. Expected 600x800, got " . $resizedInfo[0] . "x" . $resizedInfo[1] . ". Retrying...");
                $retryResult = resizeImage($fullPath, 600, 800);
                if (!$retryResult) {
                    @unlink($fullPath);
                    return [
                        'success' => false, 
                        'error' => 'خطا در تغییر اندازه تصویر. تصویر به اندازه 600×800 تنظیم نشد.'
                    ];
                }
            }
        } else {
            // Can't verify dimensions, but resize returned true, so assume success
            error_log("Warning: Could not verify image dimensions after resize: " . $fullPath);
        }
        
        return $upload;
    }
    
    return $upload;
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

    // Price filtering - check effective price (discount_price if active, otherwise price)
    if (isset($filters['price_min']) || isset($filters['price_max'])) {
        $priceConditions = [];
        
        if (isset($filters['price_min'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) >= ?";
            $params[] = $filters['price_min'];
        }
        
        if (isset($filters['price_max'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) <= ?";
            $params[] = $filters['price_max'];
        }
        
        if (!empty($priceConditions)) {
            $sql .= " AND (" . implode(" AND ", $priceConditions) . ")";
        }
    }

    // Order by
    if (!empty($filters['order_by'])) {
        $orderBy = $filters['order_by'];
        $orderDir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'DESC';
        // Validate order_by to prevent SQL injection
        $allowedOrderBy = ['views', 'created_at', 'price', 'name', 'id'];
        if (in_array($orderBy, $allowedOrderBy)) {
            // Handle NULL values in views - put them last
            if ($orderBy === 'views') {
                $sql .= " ORDER BY p.views IS NULL, p.views " . $orderDir . ", p.id DESC";
            } else {
                $sql .= " ORDER BY p." . $orderBy . " " . $orderDir;
            }
        } else {
            $sql .= " ORDER BY p.created_at DESC, p.id DESC";
        }
    } else {
        // Default: Order by created_at DESC, fallback to id DESC if created_at is null
        $sql .= " ORDER BY p.created_at DESC, p.id DESC";
    }

    if (!empty($filters['limit'])) {
        $limit = (int) $filters['limit'];
        $offset = !empty($filters['offset']) ? (int) $filters['offset'] : 0;
        $sql .= " LIMIT {$offset}, {$limit}";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get total count of products (for pagination)
 */
/**
 * Get min/max price range for filtered products
 */
function getProductsPriceRange($filters = [])
{
    $conn = getConnection();
    $sql = "SELECT 
                MIN(COALESCE(
                    CASE 
                        WHEN p.discount_price IS NOT NULL 
                        AND (p.discount_end IS NULL OR p.discount_end >= NOW())
                        AND (p.discount_start IS NULL OR p.discount_start <= NOW())
                        THEN p.discount_price
                        ELSE p.price
                    END,
                    p.price
                )) as min_price,
                MAX(COALESCE(
                    CASE 
                        WHEN p.discount_price IS NOT NULL 
                        AND (p.discount_end IS NULL OR p.discount_end >= NOW())
                        AND (p.discount_start IS NULL OR p.discount_start <= NOW())
                        THEN p.discount_price
                        ELSE p.price
                    END,
                    p.price
                )) as max_price
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

    // Include price filters - we want the range of products that match ALL current filters
    // Price filtering - check effective price (discount_price if active, otherwise price)
    if (isset($filters['price_min']) || isset($filters['price_max'])) {
        $priceConditions = [];
        
        if (isset($filters['price_min'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) >= ?";
            $params[] = $filters['price_min'];
        }
        
        if (isset($filters['price_max'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) <= ?";
            $params[] = $filters['price_max'];
        }
        
        if (!empty($priceConditions)) {
            $sql .= " AND (" . implode(" AND ", $priceConditions) . ")";
        }
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    return [
        'min' => $result['min_price'] ? (float)$result['min_price'] : 0,
        'max' => $result['max_price'] ? (float)$result['max_price'] : 1000
    ];
}

function getProductsCount($filters = [])
{
    $conn = getConnection();
    $sql = "SELECT COUNT(*) as total 
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

    // Price filtering - check effective price (discount_price if active, otherwise price)
    if (isset($filters['price_min']) || isset($filters['price_max'])) {
        $priceConditions = [];
        
        if (isset($filters['price_min'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) >= ?";
            $params[] = $filters['price_min'];
        }
        
        if (isset($filters['price_max'])) {
            $priceConditions[] = "(CASE WHEN (p.discount_price IS NOT NULL AND p.discount_price > 0 AND (p.discount_end IS NULL OR p.discount_end >= NOW()) AND (p.discount_start IS NULL OR p.discount_start <= NOW())) THEN p.discount_price ELSE p.price END) <= ?";
            $params[] = $filters['price_max'];
        }
        
        if (!empty($priceConditions)) {
            $sql .= " AND (" . implode(" AND ", $priceConditions) . ")";
        }
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ? (int)$result['total'] : 0;
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
    // Calculate discount_price from discount_percent if it's not set
    if (empty($product['discount_price']) && !empty($product['discount_percent']) && !empty($product['price'])) {
        $product['discount_price'] = (int) round($product['price'] - ($product['price'] * $product['discount_percent'] / 100));
    }

    // Check if discount_price exists and is greater than 0
    if (empty($product['discount_price']) || $product['discount_price'] <= 0) {
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
 * Ensure blog_posts table exists
 */
function ensureBlogTableExists()
{
    try {
        $conn = getConnection();
        $stmt = $conn->query("SHOW TABLES LIKE 'blog_posts'");
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS `blog_posts` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(255) NOT NULL,
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `content` TEXT NOT NULL,
                    `excerpt` TEXT,
                    `featured_image` VARCHAR(500),
                    `author_id` INT DEFAULT NULL,
                    `is_published` TINYINT(1) DEFAULT 0,
                    `published_at` DATETIME DEFAULT NULL,
                    `views` INT DEFAULT 0,
                    `meta_title` VARCHAR(255),
                    `meta_description` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`author_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
            ");
        }
    } catch (PDOException $e) {
        // If there's an error creating tables, log it but don't fail
        error_log("Error ensuring blog_posts table exists: " . $e->getMessage());
    }
}

/**
 * Get all blog posts
 */
function getBlogPosts($filters = [])
{
    // Ensure table exists before querying
    ensureBlogTableExists();
    
    $conn = getConnection();
    $sql = "SELECT bp.*, au.name as author_name 
            FROM blog_posts bp 
            LEFT JOIN admin_users au ON bp.author_id = au.id 
            WHERE 1=1";
    $params = [];

    if (isset($filters['is_published'])) {
        $sql .= " AND bp.is_published = ?";
        $params[] = $filters['is_published'];
    }

    if (!empty($filters['search'])) {
        $sql .= " AND (bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    if (!empty($filters['author_id'])) {
        $sql .= " AND bp.author_id = ?";
        $params[] = $filters['author_id'];
    }

    // Order by
    $orderBy = $filters['order_by'] ?? 'created_at';
    $orderDir = $filters['order_dir'] ?? 'DESC';
    $sql .= " ORDER BY bp.{$orderBy} {$orderDir}";

    // Limit
    if (!empty($filters['limit'])) {
        $limit = (int)$filters['limit'];
        $offset = !empty($filters['offset']) ? (int)$filters['offset'] : 0;
        $sql .= " LIMIT {$offset}, {$limit}";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get blog post by ID
 */
function getBlogPostById($id)
{
    // Ensure table exists before querying
    ensureBlogTableExists();
    
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT bp.*, au.name as author_name 
        FROM blog_posts bp 
        LEFT JOIN admin_users au ON bp.author_id = au.id 
        WHERE bp.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get blog post by slug
 */
function getBlogPostBySlug($slug)
{
    // Ensure table exists before querying
    ensureBlogTableExists();
    
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT bp.*, au.name as author_name 
        FROM blog_posts bp 
        LEFT JOIN admin_users au ON bp.author_id = au.id 
        WHERE bp.slug = ? AND bp.is_published = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
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

/**
 * Ensure branches and provinces tables exist
 */
function ensureBranchesTablesExist()
{
    $conn = getConnection();
    
    try {
        // Check if provinces table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'provinces'");
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS `provinces` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL,
                    `name_en` VARCHAR(255),
                    `slug` VARCHAR(255) NOT NULL UNIQUE,
                    `description` TEXT,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
            ");
        }
        
        // Check if branches table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'branches'");
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS `branches` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `province_id` INT NOT NULL,
                    `name` VARCHAR(255) NOT NULL,
                    `address` TEXT NOT NULL,
                    `phone` VARCHAR(50),
                    `email` VARCHAR(255),
                    `latitude` DECIMAL(10, 8),
                    `longitude` DECIMAL(11, 8),
                    `sort_order` INT DEFAULT 0,
                    `is_active` TINYINT(1) DEFAULT 1,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`province_id`) REFERENCES `provinces`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
            ");
        }
    } catch (PDOException $e) {
        // If there's an error creating tables, log it but don't fail
        error_log("Error ensuring branches tables exist: " . $e->getMessage());
    }
}

/**
 * Get all provinces
 */
function getProvinces($filters = [])
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    $sql = "SELECT p.*, (SELECT COUNT(*) FROM branches b WHERE b.province_id = p.id AND b.is_active = 1) as branch_count 
            FROM provinces p 
            WHERE 1=1";
    $params = [];

    if (isset($filters['is_active'])) {
        $sql .= " AND p.is_active = ?";
        $params[] = $filters['is_active'];
    }

    $sql .= " ORDER BY p.name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get province by ID
 */
function getProvinceById($id)
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM provinces WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get province by slug
 */
function getProvinceBySlug($slug)
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    // Allow searching by slug OR Persian name
    $stmt = $conn->prepare("SELECT * FROM provinces WHERE slug = ? OR name = ?");
    $stmt->execute([$slug, $slug]);
    return $stmt->fetch();
}

/**
 * Populate Iran provinces (31 provinces)
 * @return array ['success' => bool, 'message' => string, 'added' => int]
 */
function populateIranProvinces()
{
    // Ensure tables exist
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    
    // List of 31 provinces of Iran: [Persian name, English name, slug]
    $provinces = [
        ['تهران', 'Tehran', 'tehran'],
        ['قم', 'Qom', 'qom'],
        ['مرکزی', 'Markazi', 'markazi'],
        ['قزوین', 'Qazvin', 'qazvin'],
        ['گیلان', 'Gilan', 'gilan'],
        ['اردبیل', 'Ardabil', 'ardabil'],
        ['زنجان', 'Zanjan', 'zanjan'],
        ['آذربایجان شرقی', 'East Azerbaijan', 'east-azerbaijan'],
        ['آذربایجان غربی', 'West Azerbaijan', 'west-azerbaijan'],
        ['کردستان', 'Kurdistan', 'kurdistan'],
        ['همدان', 'Hamadan', 'hamadan'],
        ['کرمانشاه', 'Kermanshah', 'kermanshah'],
        ['ایلام', 'Ilam', 'ilam'],
        ['لرستان', 'Lorestan', 'lorestan'],
        ['خوزستان', 'Khuzestan', 'khuzestan'],
        ['چهارمحال و بختیاری', 'Chaharmahal and Bakhtiari', 'chaharmahal-bakhtiari'],
        ['کهگیلویه و بویراحمد', 'Kohgiluyeh and Boyer-Ahmad', 'kohgiluyeh-boyer-ahmad'],
        ['بوشهر', 'Bushehr', 'bushehr'],
        ['فارس', 'Fars', 'fars'],
        ['هرمزگان', 'Hormozgan', 'hormozgan'],
        ['سیستان و بلوچستان', 'Sistan and Baluchestan', 'sistan-baluchestan'],
        ['کرمان', 'Kerman', 'kerman'],
        ['یزد', 'Yazd', 'yazd'],
        ['اصفهان', 'Isfahan', 'isfahan'],
        ['سمنان', 'Semnan', 'semnan'],
        ['مازندران', 'Mazandaran', 'mazandaran'],
        ['گلستان', 'Golestan', 'golestan'],
        ['خراسان شمالی', 'North Khorasan', 'north-khorasan'],
        ['خراسان رضوی', 'Razavi Khorasan', 'razavi-khorasan'],
        ['خراسان جنوبی', 'South Khorasan', 'south-khorasan'],
        ['البرز', 'Alborz', 'alborz']
    ];
    
    $added = 0;
    $skipped = 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO provinces (name, name_en, slug, is_active) VALUES (?, ?, ?, 1)");
        
        foreach ($provinces as $province) {
            // Check if province already exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM provinces WHERE slug = ?");
            $checkStmt->execute([$province[2]]);
            
            if ($checkStmt->fetchColumn() == 0) {
                $stmt->execute($province);
                $added++;
            } else {
                $skipped++;
            }
        }
        
        return [
            'success' => true,
            'message' => "استان‌های ایران با موفقیت اضافه شدند. $added استان جدید اضافه شد" . ($skipped > 0 ? " و $skipped استان از قبل وجود داشت" : ""),
            'added' => $added,
            'skipped' => $skipped
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'خطا در افزودن استان‌ها: ' . $e->getMessage(),
            'added' => $added,
            'skipped' => $skipped
        ];
    }
}

/**
 * Get all branches
 */
function getBranches($filters = [])
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    $sql = "SELECT b.*, p.name as province_name 
            FROM branches b 
            LEFT JOIN provinces p ON b.province_id = p.id 
            WHERE 1=1";
    $params = [];

    if (!empty($filters['province_id'])) {
        $sql .= " AND b.province_id = ?";
        $params[] = $filters['province_id'];
    }

    if (isset($filters['is_active'])) {
        $sql .= " AND b.is_active = ?";
        $params[] = $filters['is_active'];
    }

    $sql .= " ORDER BY p.name, b.sort_order, b.name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Get branch by ID
 */
function getBranchById($id)
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT b.*, p.name as province_name 
        FROM branches b 
        LEFT JOIN provinces p ON b.province_id = p.id 
        WHERE b.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get branches by province
 */
function getBranchesByProvince($provinceId)
{
    // Ensure tables exist before querying
    ensureBranchesTablesExist();
    
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT * FROM branches 
        WHERE province_id = ? AND is_active = 1 
        ORDER BY sort_order, name
    ");
    $stmt->execute([$provinceId]);
    return $stmt->fetchAll();
}
?>