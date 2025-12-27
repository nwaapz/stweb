<?php
/**
 * Orders API
 * API سفارشات
 * 
 * Endpoints:
 * GET - Get user's orders
 * GET ?id=X - Get single order
 * POST - Create new order from cart
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
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

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Get single order
                $order = getOrderById($_GET['id'], $user['id']);

                if (!$order) {
                    throw new Exception('سفارش یافت نشد', 404);
                }

                echo json_encode([
                    'success' => true,
                    'data' => $order
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // Get all orders
                $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
                $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

                $orders = getUserOrders($user['id'], $limit, $offset);
                
                // Get total count for pagination
                $conn = getConnection();
                $countStmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                $countStmt->execute([$user['id']]);
                $total = (int) $countStmt->fetchColumn();

                echo json_encode([
                    'success' => true,
                    'data' => $orders,
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'POST':
            // Create new order
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            // Address data
            $addressData = [
                'name' => $input['shipping_name'] ?? $user['name'] ?? '',
                'phone' => $input['shipping_phone'] ?? $user['phone'] ?? '',
                'province' => $input['shipping_province'] ?? '',
                'city' => $input['shipping_city'] ?? '',
                'address' => $input['shipping_address'] ?? '',
                'postal_code' => $input['shipping_postal_code'] ?? ''
            ];

            // Validate required fields
            if (
                empty($addressData['name']) || empty($addressData['phone']) ||
                empty($addressData['address']) || empty($addressData['city'])
            ) {
                throw new Exception('اطلاعات آدرس ناقص است');
            }

            $notes = $input['notes'] ?? '';

            $result = createOrder($user['id'], $addressData, $notes);
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