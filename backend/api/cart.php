<?php
/**
 * Cart API
 * API سبد خرید
 * 
 * Endpoints:
 * GET - Get user's cart
 * POST - Add item to cart
 * PUT - Update item quantity
 * DELETE - Remove item from cart
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
            // Get cart items
            $summary = getCartSummary($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $summary
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'POST':
            // Add item to cart
            $productId = $input['product_id'] ?? $_POST['product_id'] ?? null;
            $quantity = $input['quantity'] ?? $_POST['quantity'] ?? 1;

            if (!$productId) {
                throw new Exception('شناسه محصول الزامی است');
            }

            $result = addToCart($user['id'], $productId, (int) $quantity);

            // Return updated cart
            if ($result['success']) {
                $result['cart'] = getCartSummary($user['id']);
            }

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'PUT':
            // Update cart item
            $productId = $input['product_id'] ?? null;
            $quantity = $input['quantity'] ?? null;

            if (!$productId || $quantity === null) {
                throw new Exception('شناسه محصول و تعداد الزامی است');
            }

            $result = updateCartItem($user['id'], $productId, (int) $quantity);

            // Return updated cart
            if ($result['success']) {
                $result['cart'] = getCartSummary($user['id']);
            }

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'DELETE':
            // Remove item from cart
            $productId = $input['product_id'] ?? $_GET['product_id'] ?? null;

            if (!$productId) {
                throw new Exception('شناسه محصول الزامی است');
            }

            $result = removeFromCart($user['id'], $productId);

            // Return updated cart
            if ($result['success']) {
                $result['cart'] = getCartSummary($user['id']);
            }

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