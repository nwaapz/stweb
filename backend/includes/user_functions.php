<?php
/**
 * Get compare list
 */
function getCompare($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT c.*, p.name, p.price, p.discount_price, p.discount_percent,
               p.image, p.slug, p.stock, p.rating, p.reviews, p.sku
        FROM compares c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();

    foreach ($items as &$item) {
        $item['has_discount'] = hasActiveDiscount($item);
        $item['effective_price'] = getEffectivePrice($item);
        $item['formatted_price'] = formatPrice($item['price']);
        $item['formatted_effective_price'] = formatPrice($item['effective_price']);
        $item['image_url'] = $item['image'] ? UPLOAD_URL . $item['image'] : null;
        // Default values for fields that may not exist in products table
        $item['weight'] = $item['weight'] ?? null;
        $item['color'] = $item['color'] ?? null;
        $item['material'] = $item['material'] ?? null;
    }

    return $items;
}

/**
 * Add to compare
 */
function addToCompare($userId, $productId)
{
    $conn = getConnection();

    // Check if already in compare (limit to 5 products)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM compares WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();

    if ($result['count'] >= 5) {
        return ['success' => false, 'error' => 'حداکثر ۵ محصول را می‌توانید مقایسه کنید'];
    }

    try {
        $stmt = $conn->prepare("INSERT IGNORE INTO compares (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        return ['success' => true, 'message' => 'به لیست مقایسه اضافه شد'];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'خطا در افزودن به لیست مقایسه'];
    }
}

/**
 * Remove from compare
 */
function removeFromCompare($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM compares WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    return ['success' => true, 'message' => 'از لیست مقایسه حذف شد'];
}

/**
 * Clear compare list
 */
function clearCompare($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("DELETE FROM compares WHERE user_id = ?");
    $stmt->execute([$userId]);

    return ['success' => true, 'message' => 'لیست مقایسه پاک شد'];
}

/**
 * Check if product is in compare
 */
function isInCompare($userId, $productId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT 1 FROM compares WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return $stmt->fetch() !== false;
}

/**
 * Normalize phone number
 */
function normalizePhone($phone)
{
    if (empty($phone)) {
        return '';
    }
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', (string) $phone);
    // Remove leading zero if present (for Iranian numbers)
    if (strlen($phone) > 10 && $phone[0] == '0') {
        $phone = substr($phone, 1);
    }
    return $phone;
}

/**
 * Get current logged-in user
 */
function getCurrentUser()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

/**
 * Logout user
 */
function logoutUser()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    return ['success' => true, 'message' => 'با موفقیت خارج شدید'];
}

/**
 * Require user login - redirect if not logged in
 */
function requireUserLogin()
{
    $user = getCurrentUser();

    if (!$user) {
        header('Location: account/login.php');
        exit;
    }

    return $user;
}

/**
 * Check user authentication (for API)
 */
function checkUserAuth()
{
    $user = getCurrentUser();

    if (!$user) {
        throw new Exception('کاربر وارد نشده است', 401);
    }

    return $user;
}

/**
 * Request OTP code
 */
function requestOTP($phone)
{
    // Include SMS service
    require_once __DIR__ . '/sms_service.php';

    $phone = normalizePhone($phone);

    if (empty($phone) || strlen($phone) < 10) {
        return ['success' => false, 'error' => 'شماره موبایل معتبر نیست'];
    }


    $conn = getConnection();

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Store OTP in session (expires in 15 minutes)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['otp_phone'] = $phone;
    $_SESSION['otp_code'] = $otp;
    $_SESSION['otp_expires'] = time() + 900; // 15 minutes

    // Prepare SMS message
    $message = "کد تایید شما: {$otp}\nاعتبار: ۱۵ دقیقه\nاستارتک";

    // Send SMS
    $smsResult = sendSMS($phone, $message);

    // Check SMS sending result
    if (!$smsResult['success']) {
        // Log the error
        error_log("SMS sending failed for {$phone}: " . ($smsResult['error'] ?? 'Unknown error'));

        // Return error with details
        return [
            'success' => false,
            'error' => 'خطا در ارسال پیامک: ' . ($smsResult['error'] ?? 'خطای نامشخص'),
            'sms_status' => $smsResult['status'] ?? 'unknown',
            'sms_provider' => $smsResult['provider'] ?? null
        ];
    }

    // Check if SMS was actually sent (not test mode)
    $isTestMode = (strtolower(SMS_PROVIDER) === 'test');
    $actuallySent = $smsResult['success'] && !$isTestMode;

    // SMS sent successfully
    $result = [
        'success' => true,
        'message' => $actuallySent ? 'کد تایید ارسال شد' : 'کد تایید آماده است (حالت تست)',
        'sms_status' => $smsResult['status'] ?? 'sent',
        'sms_message_id' => $smsResult['message_id'] ?? null,
        'sms_provider' => $smsResult['provider'] ?? null,
        'is_test_mode' => $isTestMode,
        'actually_sent' => $actuallySent
    ];

    // Development mode or test mode - include OTP in response (for testing)
    if (defined('DEV_MODE') && DEV_MODE || $isTestMode) {
        $result['dev_code'] = $otp;
    }

    return $result;
}

/**
 * Verify OTP and login
 */
function verifyOTP($phone, $code)
{
    $phone = normalizePhone($phone);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if OTP exists and is not expired
    if (!isset($_SESSION['otp_phone']) || $_SESSION['otp_phone'] !== $phone) {
        return ['success' => false, 'error' => 'کد تایید یافت نشد'];
    }

    if (!isset($_SESSION['otp_code']) || $_SESSION['otp_code'] !== $code) {
        return ['success' => false, 'error' => 'کد تایید اشتباه است'];
    }

    if (!isset($_SESSION['otp_expires']) || $_SESSION['otp_expires'] < time()) {
        unset($_SESSION['otp_phone'], $_SESSION['otp_code'], $_SESSION['otp_expires']);
        return ['success' => false, 'error' => 'کد تایید منقضی شده است'];
    }

    // OTP is valid, create or get user
    $conn = getConnection();

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        // Create new user
        $stmt = $conn->prepare("INSERT INTO users (phone, created_at) VALUES (?, NOW())");
        $stmt->execute([$phone]);
        $userId = $conn->lastInsertId();

        // Get the new user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_phone'] = $user['phone'];

    // Clear OTP
    unset($_SESSION['otp_phone'], $_SESSION['otp_code'], $_SESSION['otp_expires']);

    return [
        'success' => true,
        'message' => 'ورود موفقیت‌آمیز بود',
        'user' => $user
    ];
}

/**
 * Update user profile
 */
function updateUserProfile($userId, $data)
{
    $conn = getConnection();

    $allowedFields = ['name', 'email', 'phone'];
    $updates = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updates[] = "`$field` = ?";
            $params[] = sanitize($data[$field]);
        }
    }

    if (empty($updates)) {
        return ['success' => false, 'error' => 'هیچ فیلدی برای بروزرسانی وجود ندارد'];
    }

    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        // Get updated user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Update session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_phone'] = $user['phone'];

        return ['success' => true, 'message' => 'پروفایل با موفقیت بروزرسانی شد', 'user' => $user];
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'خطا در بروزرسانی پروفایل'];
    }
}

/**
 * Get user addresses
 */
function getUserAddresses($userId)
{
    $conn = getConnection();

    $stmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get user orders
 */
function getUserOrders($userId, $limit = null)
{
    $conn = getConnection();

    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int) $limit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Create order from cart
 */
function createOrder($userId, $addressData, $notes = '')
{
    require_once __DIR__ . '/sms_service.php';

    $conn = getConnection();

    try {
        $conn->beginTransaction();

        // Get cart items
        $cartSummary = getCartSummary($userId);
        if (empty($cartSummary['items'])) {
            throw new Exception('سبد خرید خالی است');
        }

        // Generate order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Get user phone for SMS
        $userStmt = $conn->prepare("SELECT phone FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        $userPhone = $user['phone'] ?? '';

        // Calculate totals
        $subtotal = $cartSummary['subtotal'] ?? 0;
        $shippingCost = 0; // Free shipping
        $total = $subtotal + $shippingCost;

        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (
                user_id, order_number, status,
                shipping_name, shipping_phone, shipping_province, 
                shipping_city, shipping_address, shipping_postal_code,
                subtotal, shipping_cost, total, notes, created_at
            ) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userId,
            $orderNumber,
            $addressData['name'] ?? '',
            $addressData['landline'] ?? '',
            $addressData['province'] ?? '',
            $addressData['city'] ?? '',
            $addressData['address'] ?? '',
            $addressData['postal_code'] ?? '',
            $subtotal,
            $shippingCost,
            $total,
            $notes
        ]);

        $orderId = $conn->lastInsertId();

        // Create order items
        foreach ($cartSummary['items'] as $item) {
            $itemStmt = $conn->prepare("
                INSERT INTO order_items (
                    order_id, product_id, product_name, product_sku,
                    product_image, price, quantity, line_total
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $itemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['sku'] ?? '',
                $item['image'] ?? null,
                $item['price'],
                $item['quantity'],
                $item['line_total']
            ]);
        }

        // Clear cart
        $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

        $conn->commit();

        // Send SMS notification
        if (!empty($userPhone) && SMS_ENABLED) {
            $message = "استارتک\nسفارش شما با شماره {$orderNumber} ثبت شد.\nمبلغ: " . formatPrice($total);
            sendSMS($userPhone, $message);
        }

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'message' => 'سفارش با موفقیت ثبت شد'
        ];

    } catch (Exception $e) {
        $conn->rollBack();
        return [
            'success' => false,
            'error' => 'خطا در ثبت سفارش: ' . $e->getMessage()
        ];
    }
}
?>