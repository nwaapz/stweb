<?php
/**
 * Search Suggestions API
 * API پیشنهادات جستجو
 * 
 * Endpoints:
 * GET /api/search-suggestions.php?q=keyword     - Get search suggestions (products and categories)
 * GET /api/search-suggestions.php?q=keyword&limit_products=3&limit_categories=4  - Limit results
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();
    
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $limitProducts = isset($_GET['limit_products']) ? (int)$_GET['limit_products'] : 3;
    $limitCategories = isset($_GET['limit_categories']) ? (int)$_GET['limit_categories'] : 4;
    
    $response = [
        'success' => true,
        'products' => [],
        'categories' => []
    ];
    
    if (empty($query)) {
        // If no query, return empty results
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Search products
    $searchTerm = '%' . $query . '%';
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name, v.name as vehicle_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN vehicles v ON p.vehicle_id = v.id 
        WHERE p.is_active = 1 
        AND (p.name LIKE ? OR p.description LIKE ?)
        ORDER BY p.created_at DESC, p.id DESC
        LIMIT ?
    ");
    $stmt->execute([$searchTerm, $searchTerm, $limitProducts]);
    $products = $stmt->fetchAll();
    
    // Format products
    foreach ($products as &$product) {
        // Calculate discount_price from discount_percent if needed
        if ((empty($product['discount_price']) || $product['discount_price'] == 0) && !empty($product['discount_percent']) && !empty($product['price'])) {
            $numericPrice = (float)$product['price'];
            $discountPercent = (float)$product['discount_percent'];
            $calculatedPrice = (int)round($numericPrice - ($numericPrice * $discountPercent / 100));
            if ($calculatedPrice > 0 && $calculatedPrice < $numericPrice) {
                $product['discount_price'] = $calculatedPrice;
            }
        }
        
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['effective_price']);
        
        // Set image URL - use relative path from frontend root
        if ($product['image']) {
            // Product image path is stored as "products/filename.jpg" in database
            // Frontend needs relative path: "backend/uploads/products/filename.jpg"
            $product['image_url'] = 'backend/uploads/' . $product['image'];
            $product['thumbnail_url'] = $product['image_url']; // Use same image for now
        } else {
            $product['image_url'] = null;
            $product['thumbnail_url'] = null;
        }
        
        // Create product URL (using slug if available, otherwise ID)
        if (!empty($product['slug'])) {
            $product['url'] = 'product-full.html?slug=' . urlencode($product['slug']);
        } else {
            $product['url'] = 'product-full.html?id=' . $product['id'];
        }
        
        // Rating and reviews
        $product['rating'] = $product['rating'] ?? 0;
        $product['reviews'] = $product['reviews'] ?? 0;
        
        // Round rating to nearest 0.5 for star display
        $product['rating_rounded'] = round($product['rating'] * 2) / 2;
    }
    
    // Search categories
    $stmt = $conn->prepare("
        SELECT * FROM categories 
        WHERE is_active = 1 
        AND name LIKE ?
        ORDER BY sort_order, name
        LIMIT ?
    ");
    $stmt->execute([$searchTerm, $limitCategories]);
    $categories = $stmt->fetchAll();
    
    // Format categories
    foreach ($categories as &$category) {
        // Create category URL
        if (!empty($category['slug'])) {
            $category['url'] = 'category.html?slug=' . urlencode($category['slug']);
        } else {
            $category['url'] = 'category.html?id=' . $category['id'];
        }
    }
    
    $response['products'] = $products;
    $response['categories'] = $categories;
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>

