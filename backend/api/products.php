<?php
/**
 * Products API
 * API محصولات
 * 
 * Endpoints:
 * GET /api/products.php                    - Get all products
 * GET /api/products.php?id=1               - Get single product
 * GET /api/products.php?category=1         - Get products by category
 * GET /api/products.php?vehicle=1         - Get products by vehicle
 * GET /api/products.php?featured=1         - Get featured products
 * GET /api/products.php?discounted=1       - Get discounted products
 * GET /api/products.php?search=keyword     - Search products
 * GET /api/products.php?limit=10           - Limit results
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();

    // Single product by Slug
    if (isset($_GET['slug'])) {
        $slug = $_GET['slug'];
        $product = getProductBySlug($slug);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'محصول یافت نشد', 'success' => false]);
            exit;
        }

        // Increment views
        $id = $product['id'];
        $stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Format response
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['price']);
        $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;

        echo json_encode([
            'success' => true,
            'data' => $product
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Single product by ID
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $product = getProductById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'محصول یافت نشد', 'success' => false]);
            exit;
        }

        // Increment views
        $stmt = $conn->prepare("UPDATE products SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);

        // Format response
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['price']);
        $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;

        echo json_encode([
            'success' => true,
            'data' => $product
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Build filters
    $filters = [];

    if (isset($_GET['category'])) {
        $filters['category_id'] = (int) $_GET['category'];
    }

    if (isset($_GET['vehicle'])) {
        $filters['vehicle_id'] = (int) $_GET['vehicle'];
    }

    if (isset($_GET['featured'])) {
        $filters['is_featured'] = true;
    }

    if (isset($_GET['discounted'])) {
        $filters['has_discount'] = true;
    }

    if (isset($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }

    if (isset($_GET['limit'])) {
        $filters['limit'] = (int) $_GET['limit'];
    }

    // Only active products for API (unless debug mode)
    if (!isset($_GET['include_inactive'])) {
        $filters['is_active'] = 1;
    }

    $products = getProducts($filters);

    // Format products
    foreach ($products as &$product) {
        $product['has_discount'] = hasActiveDiscount($product);
        $product['effective_price'] = getEffectivePrice($product);
        $product['formatted_price'] = formatPrice($product['price']);
        $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
        $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
        // Vehicle info is already included from the JOIN in getProducts()
    }

    echo json_encode([
        'success' => true,
        'count' => count($products),
        'data' => $products
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>