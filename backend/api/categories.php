<?php
/**
 * Categories API
 * API دسته‌بندی‌ها
 * 
 * Endpoints:
 * GET /api/categories.php              - Get all categories
 * GET /api/categories.php?id=1         - Get single category with products
 * GET /api/categories.php?tree=1       - Get categories as tree structure
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();
    
    // Single category with products
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $category = getCategoryById($id);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'دسته‌بندی یافت نشد', 'success' => false]);
            exit;
        }
        
        // Get products in this category
        $products = getProducts(['category_id' => $id, 'is_active' => 1]);
        
        // Format products
        foreach ($products as &$product) {
            $product['has_discount'] = hasActiveDiscount($product);
            $product['effective_price'] = getEffectivePrice($product);
            $product['formatted_price'] = formatPrice($product['price']);
            $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
            $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
        }
        
        $category['image_url'] = $category['image'] ? UPLOAD_URL . $category['image'] : null;
        $category['products'] = $products;
        $category['product_count'] = count($products);
        
        echo json_encode([
            'success' => true,
            'data' => $category
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get all categories
    $categories = getCategories(true); // Only active
    
    // Add product count and image URL
    foreach ($categories as &$cat) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND is_active = 1");
        $stmt->execute([$cat['id']]);
        $cat['product_count'] = $stmt->fetchColumn();
        $cat['image_url'] = $cat['image'] ? UPLOAD_URL . $cat['image'] : null;
    }
    
    // Return as tree structure if requested
    if (isset($_GET['tree'])) {
        $tree = buildCategoryTree($categories);
        echo json_encode([
            'success' => true,
            'count' => count($categories),
            'data' => $tree
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($categories),
        'data' => $categories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Build category tree from flat array
 */
function buildCategoryTree($categories, $parentId = null) {
    $tree = [];
    foreach ($categories as $cat) {
        if ($cat['parent_id'] == $parentId) {
            $children = buildCategoryTree($categories, $cat['id']);
            if ($children) {
                $cat['children'] = $children;
            }
            $tree[] = $cat;
        }
    }
    return $tree;
}
?>
