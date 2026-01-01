<?php
/**
 * Compare API
 * API مقایسه
 * 
 * Endpoints:
 * GET - Get user's compare list
 * POST - Add product to compare
 * DELETE - Remove product from compare (requires product_id in body or query)
 * PUT - Clear compare list
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/user_functions.php';

try {
    // Require authentication
    $user = checkUserAuth();

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    switch ($method) {
        case 'GET':
            // Get compare list
            $items = getCompare($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $items,
                'count' => count($items)
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'POST':
            // Add to compare
            $productId = $input['product_id'] ?? $_POST['product_id'] ?? null;

            if (!$productId) {
                throw new Exception('شناسه محصول الزامی است');
            }

            $result = addToCompare($user['id'], $productId);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'DELETE':
            // Remove from compare
            $productId = $input['product_id'] ?? $_GET['product_id'] ?? null;

            if (!$productId) {
                throw new Exception('شناسه محصول الزامی است');
            }

            $result = removeFromCompare($user['id'], $productId);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'PUT':
            // Clear compare list
            $result = clearCompare($user['id']);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        default:
            throw new Exception('Method not allowed', 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() ?: 400;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>


