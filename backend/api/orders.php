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

// Suppress any output before JSON and disable error display
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error handler to return JSON
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'خطای سرور: ' . $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return false;
});

// Set exception handler
set_exception_handler(function($exception) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'خطای سرور: ' . $exception->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    exit(0);
}

// Clear any output from require statements
ob_clean();

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/user_functions.php';

// Clear any output after require
ob_clean();

/**
 * Normalize phone numbers for comparison
 */
function normalizePhoneForTracking($phone) {
    if (empty($phone)) {
        return '';
    }
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', (string)$phone);
    // Remove leading zero if present (for Iranian numbers)
    if (strlen($phone) > 10 && $phone[0] == '0') {
        $phone = substr($phone, 1);
    }
    return $phone;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Public tracking endpoint - verify by order number and phone
            if (isset($_GET['track']) && isset($_GET['order_number']) && isset($_GET['phone'])) {
                ob_end_clean(); // Clear any output buffer
                
                try {
                    $orderNumber = trim($_GET['order_number']);
                    $phone = trim($_GET['phone']);
                    
                    if (empty($orderNumber) || empty($phone)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'شماره سفارش و شماره تلفن الزامی است'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Check if normalizePhone function exists
                    if (!function_exists('normalizePhone')) {
                        throw new Exception('Function normalizePhone not found');
                    }
                    
                    $conn = getConnection();
                    
                    // Normalize phone for comparison (use the same function as OTP system)
                    $normalizedPhone = normalizePhone($phone);
                    
                    // Find order by order number first
                    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
                    $stmt->execute([$orderNumber]);
                    $order = $stmt->fetch();
                    
                    if (!$order) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'سفارش با این شماره سفارش یافت نشد'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Get user's mobile phone number from users table
                    $userStmt = $conn->prepare("SELECT phone FROM users WHERE id = ?");
                    $userStmt->execute([$order['user_id']]);
                    $user = $userStmt->fetch();
                    
                    if (!$user || empty($user['phone'])) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'اطلاعات کاربر یافت نشد'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Verify phone number matches user's mobile phone (normalize both for comparison)
                    $userPhone = normalizePhone($user['phone']);
                    
                    if ($normalizedPhone !== $userPhone) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'شماره تلفن همراه با شماره سفارش مطابقت ندارد'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Check if phone number is registered (exists in users table)
                    $checkUserStmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
                    $checkUserStmt->execute([$normalizedPhone]);
                    $registeredUser = $checkUserStmt->fetch();
                    
                    // If phone is not registered, send OTP and return flag
                    if (!$registeredUser) {
                        // Check if requestOTP function exists
                        if (!function_exists('requestOTP')) {
                            throw new Exception('Function requestOTP not found');
                        }
                        
                        // Send OTP
                        $otpResult = requestOTP($normalizedPhone);
                        
                        if (!$otpResult['success']) {
                            http_response_code(400);
                            echo json_encode([
                                'success' => false,
                                'error' => $otpResult['error'] ?? 'خطا در ارسال کد تایید'
                            ], JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                        
                        // Return flag indicating OTP is required
                        echo json_encode([
                            'success' => true,
                            'data' => [
                                'requires_otp' => true,
                                'message' => 'کد تایید به شماره موبایل شما ارسال شد',
                                'order_number' => $orderNumber,
                                'phone' => $normalizedPhone
                            ]
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Get user email
                    $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $userStmt->execute([$order['user_id']]);
                    $user = $userStmt->fetch();
                    $order['user_email'] = $user['email'] ?? null;
                    
                    // Format order data
                    $order['status_text'] = getOrderStatusText($order['status']);
                    $order['formatted_total'] = formatPrice($order['total']);
                    $order['formatted_subtotal'] = formatPrice($order['subtotal']);
                    $order['formatted_shipping'] = formatPrice($order['shipping_cost']);
                    
                    // Get order items
                    $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $stmt->execute([$order['id']]);
                    $order['items'] = $stmt->fetchAll();
                    
                    foreach ($order['items'] as &$item) {
                        $item['formatted_price'] = formatPrice($item['price']);
                        $item['formatted_total'] = formatPrice($item['price'] * $item['quantity']);
                        $item['image_url'] = $item['product_image'] ? UPLOAD_URL . $item['product_image'] : null;
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $order
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } catch (Exception $e) {
                    ob_end_clean();
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'خطا در دریافت اطلاعات سفارش: ' . $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            // OTP verification endpoint for tracking
            if (isset($_GET['verify_track_otp']) && isset($_GET['order_number']) && isset($_GET['phone']) && isset($_GET['otp_code'])) {
                ob_end_clean();
                
                try {
                    $orderNumber = trim($_GET['order_number']);
                    $phone = trim($_GET['phone']);
                    $otpCode = trim($_GET['otp_code']);
                    
                    if (empty($orderNumber) || empty($phone) || empty($otpCode)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => 'شماره سفارش، شماره تلفن و کد تایید الزامی است'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Check if functions exist
                    if (!function_exists('normalizePhone') || !function_exists('verifyOTP')) {
                        throw new Exception('Required functions not found');
                    }
                    
                    $conn = getConnection();
                    $normalizedPhone = normalizePhone($phone);
                    
                    // Verify OTP
                    $otpResult = verifyOTP($normalizedPhone, $otpCode);
                    
                    if (!$otpResult['success']) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'error' => $otpResult['error'] ?? 'کد تایید نامعتبر است'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // OTP verified, now return order data
                    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
                    $stmt->execute([$orderNumber]);
                    $order = $stmt->fetch();
                    
                    if (!$order) {
                        http_response_code(404);
                        echo json_encode([
                            'success' => false,
                            'error' => 'سفارش با این شماره سفارش یافت نشد'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Verify phone matches order's user
                    $userStmt = $conn->prepare("SELECT phone FROM users WHERE id = ?");
                    $userStmt->execute([$order['user_id']]);
                    $user = $userStmt->fetch();
                    
                    if (!$user || normalizePhone($user['phone']) !== $normalizedPhone) {
                        http_response_code(403);
                        echo json_encode([
                            'success' => false,
                            'error' => 'شماره تلفن با سفارش مطابقت ندارد'
                        ], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                    
                    // Get user email
                    $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $userStmt->execute([$order['user_id']]);
                    $user = $userStmt->fetch();
                    $order['user_email'] = $user['email'] ?? null;
                    
                    // Format order data
                    $order['status_text'] = getOrderStatusText($order['status']);
                    $order['formatted_total'] = formatPrice($order['total']);
                    $order['formatted_subtotal'] = formatPrice($order['subtotal']);
                    $order['formatted_shipping'] = formatPrice($order['shipping_cost']);
                    
                    // Get order items
                    $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $stmt->execute([$order['id']]);
                    $order['items'] = $stmt->fetchAll();
                    
                    foreach ($order['items'] as &$item) {
                        $item['formatted_price'] = formatPrice($item['price']);
                        $item['formatted_total'] = formatPrice($item['price'] * $item['quantity']);
                        $item['image_url'] = $item['product_image'] ? UPLOAD_URL . $item['product_image'] : null;
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $order
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                } catch (Exception $e) {
                    ob_end_clean();
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'خطا در تایید کد: ' . $e->getMessage()
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            // Require authentication for other endpoints
            $user = checkUserAuth();
            
            if (isset($_GET['id'])) {
                // Get single order by ID
                $order = getOrderById($_GET['id'], $user['id']);

                if (!$order) {
                    throw new Exception('سفارش یافت نشد', 404);
                }

                echo json_encode([
                    'success' => true,
                    'data' => $order
                ], JSON_UNESCAPED_UNICODE);
            } elseif (isset($_GET['order_number']) || isset($_GET['order'])) {
                // Get single order by order number
                $orderNumber = $_GET['order_number'] ?? $_GET['order'];
                $conn = getConnection();
                
                $stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
                $stmt->execute([$orderNumber, $user['id']]);
                $order = $stmt->fetch();
                
                if (!$order) {
                    throw new Exception('سفارش یافت نشد', 404);
                }
                
                // Get user email
                $userStmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                $userStmt->execute([$order['user_id']]);
                $user = $userStmt->fetch();
                $order['user_email'] = $user['email'] ?? null;
                
                // Format order data
                $order['status_text'] = getOrderStatusText($order['status']);
                $order['formatted_total'] = formatPrice($order['total']);
                $order['formatted_subtotal'] = formatPrice($order['subtotal']);
                $order['formatted_shipping'] = formatPrice($order['shipping_cost']);
                
                // Get order items
                $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmt->execute([$order['id']]);
                $order['items'] = $stmt->fetchAll();
                
                foreach ($order['items'] as &$item) {
                    $item['formatted_price'] = formatPrice($item['price']);
                    $item['formatted_total'] = formatPrice($item['price'] * $item['quantity']);
                    // Format image URL (matching getOrderById pattern)
                    $item['image_url'] = $item['product_image'] ? UPLOAD_URL . $item['product_image'] : null;
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
            // Support both 'landline' and 'phone' for backward compatibility
            $landline = $input['shipping_landline'] ?? $input['shipping_phone'] ?? '';
            
            $addressData = [
                'name' => $input['shipping_name'] ?? $user['name'] ?? '',
                'landline' => $landline,
                'province' => $input['shipping_province'] ?? '',
                'city' => $input['shipping_city'] ?? '',
                'address' => $input['shipping_address'] ?? '',
                'postal_code' => $input['shipping_postal_code'] ?? ''
            ];

            // Validate required fields
            if (
                empty($addressData['name']) || empty($addressData['landline']) ||
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