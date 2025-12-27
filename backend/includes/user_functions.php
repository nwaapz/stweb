<?php
/**
 * User Helper Functions
 * توابع کمکی کاربران
 */

require_once __DIR__ . '/../config/database.php';

// SMS Configuration - Melli Payamak
define('SMS_USERNAME', '09121777039');
define('SMS_PASSWORD', '4thvahdati@FB');
define('SMS_FROM', '20001390');
define('SMS_WSDL', 'http://api.payamak-panel.com/post/Send.asmx?wsdl');

// Session cookie name
define('USER_SESSION_COOKIE', 'st_user_token');
define('SESSION_DURATION_DAYS', 30);
define('OTP_EXPIRY_MINUTES', 15);

/**
 * Send SMS message via Melli Payamak
 * @param string $phone Phone number
 * @param string $message Message text
 * @return bool Success status
 */
/**
 * Send SMS message via Melli Payamak
 * @param string $phone Phone number
 * @param string $message Message text
 * @return bool Success status
 */
function sendSMS($phone, $message)
{
    // Method 1: Try REST-like POST to the ASMX endpoint (standard for this provider)
    $url = 'http://api.payamak-panel.com/post/Send.asmx/SendSimpleSMS';
    $params = [
        'username' => SMS_USERNAME,
        'password' => SMS_PASSWORD,
        'to' => $phone,
        'from' => SMS_FROM,
        'text' => $message,
        'isflash' => 'false'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disable SSL verification for compatibility if needed (though using http here)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        // Parse simple XML response: <string xmlns="...">result code</string>
        if (preg_match('/>(\d+)</', $response, $matches)) {
            $resultCode = intval($matches[1]);
            // Codes > 15 usually indicate success (message ID)
            // 0, 1, etc are errors
            if ($resultCode > 15) {
                return true;
            }
        }
    }

    // Method 2: Fallback to manual SOAP envelope if REST failed
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
      <soap:Body>
        <SendSimpleSMS xmlns="http://api.payamak-panel.com/post/Send.asmx">
          <username>' . SMS_USERNAME . '</username>
          <password>' . SMS_PASSWORD . '</password>
          <to><string>' . $phone . '</string></to>
          <from>' . SMS_FROM . '</from>
          <text>' . htmlspecialchars($message) . '</text>
          <isflash>false</isflash>
        </SendSimpleSMS>
      </soap:Body>
    </soap:Envelope>';

    $headers = [
        "Content-type: text/xml;charset=\"utf-8\"",
        "Accept: text/xml",
        "SOAPAction: \"http://api.payamak-panel.com/post/Send.asmx/SendSimpleSMS\"",
        "Content-length: " . strlen($xml),
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://api.payamak-panel.com/post/Send.asmx");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response && strpos($response, 'SendSimpleSMSResult') !== false) {
        return true;
    }

    error_log("SMS Failed: " . ($response ?: 'No response'));
    return false;
}

/**
 * Generate random OTP code
 * @return string 6-digit code
 */
function generateOTP()
{
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Request OTP for phone number
 * @param string $phone Phone number
 * @return array Result with success status
 */
function requestOTP($phone)
{
    $conn = getConnection();

    // Validate phone format (Iranian mobile)
    $phone = normalizePhone($phone);
    if (!preg_match('/^09[0-9]{9}$/', $phone)) {
        return ['success' => false, 'error' => 'شماره موبایل نامعتبر است'];
    }

    // Check rate limiting (max 3 OTP per 10 minutes)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count FROM otp_codes 
        WHERE phone = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$phone]);
    $row = $stmt->fetch();

    if ($row['count'] >= 3) {
        return ['success' => false, 'error' => 'لطفاً ۱۰ دقیقه صبر کنید و دوباره تلاش کنید'];
    }

    // Generate OTP
    $code = generateOTP();

    // Save OTP to database
    $stmt = $conn->prepare("
        INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
    ");
    $stmt->execute([$phone, $code, OTP_EXPIRY_MINUTES]);

    // Send SMS
    $message = "استارتک\nکد تایید شما: " . $code . "\nاین کد تا " . OTP_EXPIRY_MINUTES . " دقیقه معتبر است.";
    $smsSent = sendSMS($phone, $message);

    if (!$smsSent) {
        // Log error but return code for development
        error_log("Failed to send SMS to $phone, OTP: $code");
    }

    return [
        'success' => true,
        'message' => 'کد تایید ارسال شد'
    ];
}

/**
 * Verify OTP and authenticate user
 * @param string $phone Phone number
 * @param string $code OTP code
 * @return array Result with session token
 */
function verifyOTP($phone, $code)
{
    $conn = getConnection();

    $phone = normalizePhone($phone);

    // Find valid OTP
    $stmt = $conn->prepare("
        SELECT * FROM otp_codes 
        WHERE phone = ? AND code = ? AND is_used = 0 AND expires_at > NOW()
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$phone, $code]);
    $otp = $stmt->fetch();

    if (!$otp) {
        return ['success' => false, 'error' => 'کد تایید نامعتبر یا منقضی شده است'];
    }

    // Mark OTP as used
    $stmt = $conn->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
    $stmt->execute([$otp['id']]);

    // Find or create user
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (phone) VALUES (?)");
        $stmt->execute([$phone]);
        $userId = $conn->lastInsertId();
    } else {
        if ($user['is_blocked']) {
            return ['success' => false, 'error' => 'حساب کاربری شما مسدود شده است'];
        }
        $userId = $user['id'];
    }

    // Update last login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$userId]);

    // Create session
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . SESSION_DURATION_DAYS . ' days'));
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO user_sessions (user_id, token, ip_address, user_agent, expires_at) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $token, $ipAddress, $userAgent, $expiresAt]);

    // Set cookie
    setcookie(
        USER_SESSION_COOKIE,
        $token,
        time() + (SESSION_DURATION_DAYS * 24 * 60 * 60),
        '/',
        '',
        false,
        true
    );

    return [
        'success' => true,
        'message' => 'ورود موفق',
        'token' => $token,
        'user_id' => $userId
    ];
}

/**
 * Get current logged in user from session
 * @return array|null User data or null
 */
function getCurrentUser()
{
    $token = $_COOKIE[USER_SESSION_COOKIE] ?? null;

    if (!$token) {
        return null;
    }

    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT u.*, s.token, s.expires_at as session_expires
        FROM user_sessions s
        JOIN users u ON s.user_id = u.id
        WHERE s.token = ? AND s.expires_at > NOW() AND u.is_active = 1 AND u.is_blocked = 0
    ");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Require user login, redirect if not logged in
 */
function requireUserLogin()
{
    $user = getCurrentUser();
    if (!$user) {
        header('Location: /account/login.php');
        exit;
    }
    return $user;
}

/**
 * Check if user is logged in (for API)
 */
function checkUserAuth()
{
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'لطفاً وارد شوید'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    return $user;
}

/**
 * Logout user
 */
function logoutUser()
{
    $token = $_COOKIE[USER_SESSION_COOKIE] ?? null;

    if ($token) {
        $conn = getConnection();
        $stmt = $conn->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$token]);
    }

    // Remove cookie
    setcookie(USER_SESSION_COOKIE, '', time() - 3600, '/');

    return ['success' => true, 'message' => 'خروج موفق'];
}

/**
 * Normalize phone number to 09XXXXXXXXX format (for mobile)
 */
function normalizePhone($phone)
{
    // Remove all non-digits
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Convert +98 or 98 to 0
    if (substr($phone, 0, 2) === '98') {
        $phone = '0' . substr($phone, 2);
    }
    if (substr($phone, 0, 3) === '+98') {
        $phone = '0' . substr($phone, 3);
    }

    // Add leading 0 if needed
    if (strlen($phone) === 10 && substr($phone, 0, 1) === '9') {
        $phone = '0' . $phone;
    }

    return $phone;
}

/**
 * Normalize landline number (Iranian landline format)
 * Iranian landlines: 0 + area code (2-4 digits) + number (6-8 digits)
 * Examples: 02112345678, 03112345678, 04112345678
 */
function normalizeLandline($landline)
{
    // Remove all non-digits
    $landline = preg_replace('/[^0-9]/', '', $landline);

    // Convert +98 or 98 to 0
    if (substr($landline, 0, 2) === '98') {
        $landline = '0' . substr($landline, 2);
    }
    if (substr($landline, 0, 3) === '+98') {
        $landline = '0' . substr($landline, 3);
    }

    // Ensure it starts with 0
    if (strlen($landline) > 0 && substr($landline, 0, 1) !== '0') {
        $landline = '0' . $landline;
    }

    return $landline;
}

/**
 * Validate landline number (must be landline, not mobile)
 * @param string $landline Landline number
 * @return bool|string True if valid, error message if invalid
 */
function validateLandline($landline)
{
    if (empty($landline)) {
        return 'شماره تلفن ثابت الزامی است';
    }

    $normalized = normalizeLandline($landline);

    // Must start with 0
    if (substr($normalized, 0, 1) !== '0') {
        return 'شماره تلفن ثابت باید با 0 شروع شود';
    }

    // Must NOT be a mobile number (mobile numbers start with 09)
    if (substr($normalized, 0, 2) === '09') {
        return 'شماره تلفن ثابت نمی‌تواند شماره موبایل باشد. لطفاً شماره تلفن ثابت وارد کنید';
    }

    // Iranian landline format: 0 + area code (2-4 digits) + number (6-8 digits)
    // Total length: 10-13 digits
    if (strlen($normalized) < 10 || strlen($normalized) > 13) {
        return 'شماره تلفن ثابت نامعتبر است';
    }

    // Area code should be 2-4 digits after the leading 0
    // Common area codes: 021 (Tehran), 031 (Isfahan), 041 (Tabriz), etc.
    $areaCode = substr($normalized, 1, 4);
    if (!preg_match('/^[1-9][0-9]{1,3}$/', $areaCode)) {
        return 'کد شهر نامعتبر است';
    }

    return true;
}

/**
 * Get user by ID
 */
function getUserById($id)
{
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data)
{
    $conn = getConnection();

    $allowedFields = ['name', 'email'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($updates)) {
        return ['success' => false, 'error' => 'هیچ فیلدی برای بروزرسانی وجود ندارد'];
    }

    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return ['success' => true, 'message' => 'پروفایل بروزرسانی شد'];
}

// ============================================
// CART FUNCTIONS
// ============================================

/**
 * Get user's cart items
 */
function getCartItems($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.discount_price, p.discount_percent,
               p.discount_start, p.discount_end, p.image, p.stock, p.slug
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    // Calculate effective prices
    foreach ($items as &$item) {
        // Calculate discount_price from percent if needed
        if (empty($item['discount_price']) && !empty($item['discount_percent'])) {
            $item['discount_price'] = round($item['price'] - ($item['price'] * $item['discount_percent'] / 100));
        }

        $item['has_discount'] = hasActiveDiscount($item);
        $item['effective_price'] = getEffectivePrice($item);
        $item['line_total'] = $item['effective_price'] * $item['quantity'];
        $item['image_url'] = $item['image'] ? UPLOAD_URL . $item['image'] : null;
    }

    return $items;
}

/**
 * Get cart summary
 */
function getCartSummary($userId)
{
    $items = getCartItems($userId);

    $subtotal = 0;
    $itemCount = 0;

    foreach ($items as $item) {
        $subtotal += $item['line_total'];
        $itemCount += $item['quantity'];
    }

    return [
        'items' => $items,
        'item_count' => $itemCount,
        'subtotal' => $subtotal,
        'formatted_subtotal' => formatPrice($subtotal)
    ];
}

/**
 * Add item to cart
 */
function addToCart($userId, $productId, $quantity = 1)
{
    $conn = getConnection();

    // Check if product exists and is active
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return ['success' => false, 'error' => 'محصول یافت نشد'];
    }

    // Check stock
    if ($product['stock'] !== null && $product['stock'] < $quantity) {
        return ['success' => false, 'error' => 'موجودی کافی نیست'];
    }

    // Add or update cart
    $stmt = $conn->prepare("
        INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");
    $stmt->execute([$userId, $productId, $quantity]);

    return ['success' => true, 'message' => 'محصول به سبد خرید اضافه شد'];
}

/**
 * Update cart item quantity
 */
function updateCartItem($userId, $productId, $quantity)
{
    $conn = getConnection();

    if ($quantity <= 0) {
        return removeFromCart($userId, $productId);
    }

    // Check stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] !== null && $product['stock'] < $quantity) {
        return ['success' => false, 'error' => 'موجودی کافی نیست'];
    }

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $userId, $productId]);

    return ['success' => true, 'message' => 'سبد خرید بروزرسانی شد'];
}

/**
 * Remove item from cart
 */
function removeFromCart($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    return ['success' => true, 'message' => 'محصول از سبد خرید حذف شد'];
}

/**
 * Clear user's cart
 */
function clearCart($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);

    return ['success' => true];
}

// ============================================
// ORDER FUNCTIONS
// ============================================

/**
 * Generate unique order number
 */
function generateOrderNumber()
{
    return 'ST' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Create order from cart
 */
function createOrder($userId, $addressData, $notes = '')
{
    $conn = getConnection();

    // Get cart items
    $cartSummary = getCartSummary($userId);

    if (empty($cartSummary['items'])) {
        return ['success' => false, 'error' => 'سبد خرید خالی است'];
    }

    // Start transaction
    $conn->beginTransaction();

    try {
        // Calculate totals
        $subtotal = $cartSummary['subtotal'];
        $shippingCost = 0; // Free shipping for MVP
        $total = $subtotal + $shippingCost;

        // Generate order number
        $orderNumber = generateOrderNumber();

        // Get landline from address data (support both 'landline' and 'phone' for backward compatibility)
        $landline = $addressData['landline'] ?? $addressData['phone'] ?? '';
        
        // Validate landline if provided
        if (!empty($landline)) {
            $landlineValidation = validateLandline($landline);
            if ($landlineValidation !== true) {
                throw new Exception($landlineValidation);
            }
            $landline = normalizeLandline($landline);
        }

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, order_number, status, subtotal, shipping_cost, total,
                shipping_name, shipping_phone, shipping_province, shipping_city,
                shipping_address, shipping_postal_code, notes
            ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $orderNumber,
            $subtotal,
            $shippingCost,
            $total,
            $addressData['name'] ?? '',
            $landline,
            $addressData['province'] ?? '',
            $addressData['city'] ?? '',
            $addressData['address'] ?? '',
            $addressData['postal_code'] ?? '',
            $notes
        ]);

        $orderId = $conn->lastInsertId();

        // Create order items
        $stmt = $conn->prepare("
            INSERT INTO order_items (
                order_id, product_id, product_name, product_image, product_sku,
                price, quantity, total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($cartSummary['items'] as $item) {
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['image'],
                $item['sku'] ?? '',
                $item['effective_price'],
                $item['quantity'],
                $item['line_total']
            ]);
        }

        // Clear cart
        clearCart($userId);

        $conn->commit();

        // Send confirmation SMS
        $user = getUserById($userId);
        if ($user) {
            $message = "استارتک\nسفارش شما با شماره {$orderNumber} ثبت شد.\nمبلغ کل: " . formatPrice($total);
            sendSMS($user['phone'], $message);
        }

        return [
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد',
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ];

    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Order creation error: " . $e->getMessage());
        return ['success' => false, 'error' => 'خطا در ثبت سفارش'];
    }
}

/**
 * Get user's orders
 */
function getUserOrders($userId, $limit = 20, $offset = 0)
{
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT * FROM orders WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?, ?
    ");
    $stmt->execute([$userId, $offset, $limit]);
    $orders = $stmt->fetchAll();

    foreach ($orders as &$order) {
        $order['status_text'] = getOrderStatusText($order['status']);
        $order['formatted_total'] = formatPrice($order['total']);
        
        // Get items count
        $countStmt = $conn->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
        $countStmt->execute([$order['id']]);
        $order['items_count'] = (int)($countStmt->fetchColumn() ?: 0);
    }

    return $orders;
}

/**
 * Get order by ID
 */
function getOrderById($orderId, $userId = null)
{
    $conn = getConnection();

    $sql = "SELECT * FROM orders WHERE id = ?";
    $params = [$orderId];

    if ($userId) {
        $sql .= " AND user_id = ?";
        $params[] = $userId;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch();

    if ($order) {
        $order['status_text'] = getOrderStatusText($order['status']);
        $order['formatted_total'] = formatPrice($order['total']);
        $order['formatted_subtotal'] = formatPrice($order['subtotal']);
        $order['formatted_shipping'] = formatPrice($order['shipping_cost']);

        // Get order items
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order['items'] = $stmt->fetchAll();

        foreach ($order['items'] as &$item) {
            $item['formatted_price'] = formatPrice($item['price']);
            $item['formatted_total'] = formatPrice($item['total']);
            $item['image_url'] = $item['product_image'] ? UPLOAD_URL . $item['product_image'] : null;
        }
    }

    return $order;
}

/**
 * Get order status text in Farsi
 */
function getOrderStatusText($status)
{
    $statuses = [
        'pending' => 'در انتظار تایید',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'delivered' => 'تحویل شده',
        'cancelled' => 'لغو شده'
    ];
    return $statuses[$status] ?? $status;
}

/**
 * Get order status badge class
 */
function getOrderStatusBadge($status)
{
    $badges = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $badges[$status] ?? 'secondary';
}

// ============================================
// ADDRESS FUNCTIONS
// ============================================

/**
 * Get user addresses
 */
function getUserAddresses($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT * FROM user_addresses WHERE user_id = ?
        ORDER BY is_default DESC, created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Add user address
 */
function addUserAddress($userId, $data)
{
    $conn = getConnection();

    // Get landline from data (support both 'phone' and 'landline' for backward compatibility)
    $landline = $data['landline'] ?? $data['phone'] ?? '';

    // Validate landline
    $landlineValidation = validateLandline($landline);
    if ($landlineValidation !== true) {
        return ['success' => false, 'error' => $landlineValidation];
    }

    // Normalize landline
    $landline = normalizeLandline($landline);

    // If this is the first address or marked as default, update others
    if (!empty($data['is_default'])) {
        $stmt = $conn->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
    }

    $stmt = $conn->prepare("
        INSERT INTO user_addresses (
            user_id, title, recipient_name, landline, province, city, address, postal_code, is_default
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $data['title'] ?? '',
        $data['recipient_name'] ?? '',
        $landline,
        $data['province'] ?? '',
        $data['city'] ?? '',
        $data['address'] ?? '',
        $data['postal_code'] ?? '',
        !empty($data['is_default']) ? 1 : 0
    ]);

    return ['success' => true, 'address_id' => $conn->lastInsertId()];
}

/**
 * Delete user address
 */
function deleteUserAddress($userId, $addressId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userId]);

    return ['success' => true];
}

// ============================================
// WISHLIST FUNCTIONS
// ============================================

/**
 * Get user wishlist
 */
function getWishlist($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT w.*, p.name, p.price, p.discount_price, p.discount_percent,
               p.image, p.slug, p.stock
        FROM wishlists w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $item['has_discount'] = hasActiveDiscount($item);
        $item['effective_price'] = getEffectivePrice($item);
        $item['formatted_price'] = formatPrice($item['price']);
        $item['formatted_effective_price'] = formatPrice($item['effective_price']);
        $item['image_url'] = $item['image'] ? UPLOAD_URL . $item['image'] : null;
    }

    return $items;
}

/**
 * Add to wishlist
 */
function addToWishlist($userId, $productId)
{
    $conn = getConnection();

    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        return ['success' => true, 'message' => 'به لیست علاقه‌مندی اضافه شد'];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'خطا در افزودن به لیست'];
    }
}

/**
 * Remove from wishlist
 */
function removeFromWishlist($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    return ['success' => true, 'message' => 'از لیست علاقه‌مندی حذف شد'];
}

/**
 * Check if product is in wishlist
 */
function isInWishlist($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT 1 FROM wishlists WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return $stmt->fetch() !== false;
}
?>