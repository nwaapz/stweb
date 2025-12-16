<?php
/**
 * Vehicles API
 * API وسایل نقلیه
 * 
 * Endpoints:
 * GET /api/vehicles.php              - Get all vehicles
 * GET /api/vehicles.php?id=1        - Get single vehicle with products
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();
    
    // Single vehicle with products
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $vehicle = getVehicleById($id);
        
        if (!$vehicle) {
            http_response_code(404);
            echo json_encode(['error' => 'وسیله نقلیه یافت نشد', 'success' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Get products for this vehicle
        $products = getProducts(['vehicle_id' => $id, 'is_active' => 1]);
        
        // Format products
        foreach ($products as &$product) {
            $product['has_discount'] = hasActiveDiscount($product);
            $product['effective_price'] = getEffectivePrice($product);
            $product['formatted_price'] = formatPrice($product['price']);
            $product['formatted_discount_price'] = $product['discount_price'] ? formatPrice($product['discount_price']) : null;
            $product['image_url'] = $product['image'] ? UPLOAD_URL . $product['image'] : null;
        }
        
        $vehicle['products'] = $products;
        $vehicle['product_count'] = count($products);
        
        echo json_encode([
            'success' => true,
            'data' => $vehicle
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get vehicles filtered by factory_id if provided
    if (isset($_GET['factory_id'])) {
        $factoryId = (int)$_GET['factory_id'];
        $conn = getConnection();
        $sql = "SELECT v.*, f.name as factory_name 
                FROM vehicles v 
                LEFT JOIN factories f ON v.factory_id = f.id 
                WHERE v.factory_id = ? AND v.is_active = 1 
                ORDER BY v.sort_order, v.name";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$factoryId]);
        $vehicles = $stmt->fetchAll();
    } else {
        // Get all vehicles (only active by default)
        $activeOnly = !isset($_GET['include_inactive']);
        $vehicles = getVehicles($activeOnly);
    }
    
    // Format vehicles for frontend
    foreach ($vehicles as &$vehicle) {
        // Add product count for each vehicle
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vehicle_id = ? AND is_active = 1");
        $stmt->execute([$vehicle['id']]);
        $vehicle['product_count'] = $stmt->fetchColumn();
        
        // Factory information is already included from the JOIN in getVehicles()
        // Add factory_id if not present
        if (!isset($vehicle['factory_id']) && isset($vehicle['factory_name'])) {
            // Get factory_id from database
            $stmt = $conn->prepare("SELECT id FROM factories WHERE name = ?");
            $stmt->execute([$vehicle['factory_name']]);
            $factory = $stmt->fetch();
            if ($factory) {
                $vehicle['factory_id'] = $factory['id'];
            }
        }
        
        // Format vehicle details (use description if available, otherwise construct from other fields)
        if (empty($vehicle['description']) && !empty($vehicle['year']) && !empty($vehicle['make']) && !empty($vehicle['model'])) {
            $vehicle['description'] = $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'];
        }
        // Use description as details field for frontend
        $vehicle['details'] = $vehicle['description'] ?? ($vehicle['engine'] ?? '');
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($vehicles),
        'data' => $vehicles
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>

