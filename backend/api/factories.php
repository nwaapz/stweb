<?php
/**
 * Factories API
 * API کارخانجات خودروسازی
 * 
 * Endpoints:
 * GET /api/factories.php              - Get all factories
 * GET /api/factories.php?id=1        - Get single factory with vehicles
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();
    
    // Single factory with vehicles
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $factory = getFactoryById($id);
        
        if (!$factory) {
            http_response_code(404);
            echo json_encode(['error' => 'کارخانه یافت نشد', 'success' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Get vehicles for this factory
        $stmt = $conn->prepare("SELECT * FROM vehicles WHERE factory_id = ? AND is_active = 1 ORDER BY sort_order, name");
        $stmt->execute([$id]);
        $vehicles = $stmt->fetchAll();
        
        // Add product count for each vehicle
        foreach ($vehicles as &$vehicle) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE vehicle_id = ? AND is_active = 1");
            $stmt->execute([$vehicle['id']]);
            $vehicle['product_count'] = $stmt->fetchColumn();
        }
        
        $factory['logo_url'] = $factory['logo'] ? UPLOAD_URL . $factory['logo'] : null;
        $factory['vehicles'] = $vehicles;
        $factory['vehicle_count'] = count($vehicles);
        
        echo json_encode([
            'success' => true,
            'data' => $factory
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get all factories (only active by default)
    $activeOnly = !isset($_GET['include_inactive']);
    $factories = getFactories($activeOnly);
    
    // Add vehicle count and logo URL for each factory
    foreach ($factories as &$factory) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicles WHERE factory_id = ? AND is_active = 1");
        $stmt->execute([$factory['id']]);
        $factory['vehicle_count'] = $stmt->fetchColumn();
        $factory['logo_url'] = $factory['logo'] ? UPLOAD_URL . $factory['logo'] : null;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($factories),
        'data' => $factories
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور'
    ], JSON_UNESCAPED_UNICODE);
}
?>

