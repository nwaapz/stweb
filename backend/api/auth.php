<?php
/**
 * Authentication API
 * API احراز هویت کاربران
 * 
 * Endpoints:
 * POST ?action=request_otp - Request OTP code
 * POST ?action=verify_otp - Verify OTP and login
 * POST ?action=logout - Logout user
 * GET ?action=me - Get current user info
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'request_otp':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $phone = $input['phone'] ?? '';

            if (empty($phone)) {
                throw new Exception('شماره موبایل الزامی است');
            }

            $result = requestOTP($phone);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'verify_otp':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $phone = $input['phone'] ?? '';
            $code = $input['code'] ?? '';

            if (empty($phone) || empty($code)) {
                throw new Exception('شماره موبایل و کد تایید الزامی است');
            }

            $result = verifyOTP($phone, $code);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'logout':
            $result = logoutUser();
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'me':
            $user = getCurrentUser();
            if ($user) {
                // Remove sensitive data
                unset($user['token']);
                unset($user['session_expires']);

                echo json_encode([
                    'success' => true,
                    'data' => $user
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'کاربر وارد نشده است'
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        case 'update_profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }

            $user = checkUserAuth();
            $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $result = updateUserProfile($user['id'], $input);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        default:
            throw new Exception('Action not specified', 400);
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