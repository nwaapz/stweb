<?php
/**
 * Addresses API
 * API آدرس‌ها
 * 
 * Endpoints:
 * GET - Get user's addresses
 * POST - Add new address
 * DELETE ?id=X - Delete address
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
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
            // Get addresses
            $addresses = getUserAddresses($user['id']);
            echo json_encode([
                'success' => true,
                'data' => $addresses
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'POST':
            // Add new address
            $data = [
                'title' => $input['title'] ?? $_POST['title'] ?? '',
                'recipient_name' => $input['recipient_name'] ?? $_POST['recipient_name'] ?? '',
                'phone' => $input['phone'] ?? $_POST['phone'] ?? '',
                'province' => $input['province'] ?? $_POST['province'] ?? '',
                'city' => $input['city'] ?? $_POST['city'] ?? '',
                'address' => $input['address'] ?? $_POST['address'] ?? '',
                'postal_code' => $input['postal_code'] ?? $_POST['postal_code'] ?? '',
                'is_default' => $input['is_default'] ?? $_POST['is_default'] ?? false
            ];

            // Validate required fields
            if (
                empty($data['recipient_name']) || empty($data['phone']) ||
                empty($data['address']) || empty($data['city'])
            ) {
                throw new Exception('فیلدهای الزامی را پر کنید');
            }

            $result = addUserAddress($user['id'], $data);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'DELETE':
            // Delete address
            $addressId = $input['id'] ?? $_GET['id'] ?? null;

            if (!$addressId) {
                throw new Exception('شناسه آدرس الزامی است');
            }

            $result = deleteUserAddress($user['id'], $addressId);
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