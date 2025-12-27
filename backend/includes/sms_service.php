<?php
/**
 * SMS Service
 * سرویس ارسال پیامک
 * 
 * Supports multiple SMS providers with error handling and callbacks
 */

require_once __DIR__ . '/../config/database.php';

// Load SMS configuration if exists, otherwise use defaults
if (file_exists(__DIR__ . '/../config/sms_config.php')) {
    require_once __DIR__ . '/../config/sms_config.php';
} else {
    // Default configuration
    define('SMS_PROVIDER', 'test'); // Options: 'kavenegar', 'melipayamak', 'ghasedak', 'test'
    define('SMS_API_KEY', ''); // Your SMS API key
    define('SMS_SENDER', '10001001001'); // Your SMS sender number/line
    define('SMS_ENABLED', true); // Set to false to disable SMS sending (for testing)
    define('SMS_LOG_ENABLED', true); // Log SMS attempts to database
}

/**
 * Normalize phone number
 * This function is needed by SMS service
 */
if (!function_exists('normalizePhone')) {
    function normalizePhone($phone)
    {
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
}

/**
 * Initialize SMS logs table
 */
function initSMSLogTable()
{
    if (!SMS_LOG_ENABLED) {
        return;
    }
    
    try {
        $conn = getConnection();
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `sms_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `phone` VARCHAR(20) NOT NULL,
                `message` TEXT,
                `provider` VARCHAR(50),
                `status` VARCHAR(50) NOT NULL,
                `provider_response` TEXT,
                `error_message` TEXT,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_phone` (`phone`),
                INDEX `idx_status` (`status`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci
        ");
    } catch (PDOException $e) {
        error_log("Error creating SMS logs table: " . $e->getMessage());
    }
}

/**
 * Log SMS attempt to database
 */
function logSMS($phone, $message, $provider, $status, $providerResponse = null, $errorMessage = null)
{
    if (!SMS_LOG_ENABLED) {
        return;
    }
    
    try {
        initSMSLogTable();
        $conn = getConnection();
        $stmt = $conn->prepare("
            INSERT INTO sms_logs (phone, message, provider, status, provider_response, error_message)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $phone,
            $message,
            $provider,
            $status,
            $providerResponse ? json_encode($providerResponse, JSON_UNESCAPED_UNICODE) : null,
            $errorMessage
        ]);
    } catch (PDOException $e) {
        error_log("Error logging SMS: " . $e->getMessage());
    }
}

/**
 * Send SMS using Kavenegar
 */
function sendSMSKavenegar($phone, $message)
{
    if (empty(SMS_API_KEY)) {
        return [
            'success' => false,
            'error' => 'SMS API key not configured',
            'provider_response' => null
        ];
    }
    
    $url = "https://api.kavenegar.com/v1/" . SMS_API_KEY . "/sms/send.json";
    
    $data = [
        'receptor' => $phone,
        'sender' => SMS_SENDER,
        'message' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'provider_response' => null
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['return']['status']) && $responseData['return']['status'] == 200) {
        return [
            'success' => true,
            'message_id' => $responseData['entries'][0]['messageid'] ?? null,
            'cost' => $responseData['entries'][0]['cost'] ?? null,
            'provider_response' => $responseData
        ];
    } else {
        $errorMsg = $responseData['return']['message'] ?? 'Unknown error';
        return [
            'success' => false,
            'error' => $errorMsg,
            'provider_response' => $responseData
        ];
    }
}

/**
 * Send SMS using Melipayamak
 */
function sendSMSMelipayamak($phone, $message)
{
    if (empty(SMS_API_KEY)) {
        return [
            'success' => false,
            'error' => 'SMS API key not configured',
            'provider_response' => null
        ];
    }
    
    $url = "https://api.melipayamak.com/api/send/simple";
    
    $data = [
        'username' => SMS_API_KEY, // Usually username
        'password' => SMS_API_KEY, // Usually same as username or separate password
        'to' => $phone,
        'from' => SMS_SENDER,
        'text' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'provider_response' => null
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['StrRetStatus']) && $responseData['StrRetStatus'] == 'Ok') {
        return [
            'success' => true,
            'message_id' => $responseData['RetStatus'] ?? null,
            'provider_response' => $responseData
        ];
    } else {
        $errorMsg = $responseData['StrRetStatus'] ?? 'Unknown error';
        return [
            'success' => false,
            'error' => $errorMsg,
            'provider_response' => $responseData
        ];
    }
}

/**
 * Send SMS using Payamak Panel (SOAP)
 */
function sendSMSPayamakPanel($phone, $message)
{
    if (empty(SMS_API_KEY)) {
        return [
            'success' => false,
            'error' => 'SMS API key not configured',
            'provider_response' => null
        ];
    }
    
    // Parse API key - format: "username|password" or use separate config
    $credentials = explode('|', SMS_API_KEY);
    $username = $credentials[0] ?? SMS_API_KEY;
    $password = isset($credentials[1]) ? $credentials[1] : (defined('SMS_PASSWORD') ? SMS_PASSWORD : SMS_API_KEY);
    
    try {
        ini_set("soap.wsdl_cache_enabled", 0);
        
        // Just try to use SoapClient - if it worked last night, it should work now
        $sms = new \SoapClient("http://api.payamak-panel.com/post/Send.asmx?wsdl", [
            "encoding" => "UTF-8",
        ]);
        
        $data = [
            "username" => $username,
            "password" => $password,
            "to" => [$phone],
            "from" => SMS_SENDER,
            "text" => $message,
            "isflash" => false,
        ];
        
        $result = $sms->SendSimpleSMS($data);
        
        // Check result
        if (isset($result->SendSimpleSMSResult)) {
            $resultCode = $result->SendSimpleSMSResult;
            
            // Payamak Panel typically returns a string ID on success, or error code
            if (is_numeric($resultCode) && $resultCode > 0) {
                return [
                    'success' => true,
                    'status' => 'sent',
                    'message_id' => (string)$resultCode,
                    'provider_response' => ['result' => $resultCode]
                ];
            } else {
                // Error codes from Payamak Panel
                $errorMessages = [
                    '0' => 'خطای نامشخص',
                    '1' => 'نام کاربری یا رمز عبور اشتباه است',
                    '2' => 'اعتبار کافی نیست',
                    '3' => 'محدودیت در ارسال روزانه',
                    '4' => 'محدودیت در ارسال ساعتی',
                    '5' => 'شماره فرستنده معتبر نیست',
                    '6' => 'متن پیامک خالی است',
                    '7' => 'متن پیامک بیش از حد مجاز است',
                    '8' => 'شماره گیرنده معتبر نیست',
                    '9' => 'خطای سیستم',
                ];
                
                $errorMsg = $errorMessages[$resultCode] ?? "خطا: کد {$resultCode}";
                
                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => $errorMsg,
                    'provider_response' => ['result' => $resultCode]
                ];
            }
        } else {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'پاسخ نامعتبر از سرور',
                'provider_response' => ['result' => null]
            ];
        }
    } catch (\SoapFault $e) {
        return [
            'success' => false,
            'status' => 'failed',
            'error' => 'خطای SOAP: ' . $e->getMessage(),
            'provider_response' => ['error' => $e->getMessage()]
        ];
    } catch (\Error $e) {
        // If SoapClient class doesn't exist
        if (strpos($e->getMessage(), 'SoapClient') !== false || strpos($e->getMessage(), 'Class') !== false) {
            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'افزونه SOAP در دسترس نیست. لطفاً مطمئن شوید که extension=soap در php.ini فعال است و Apache را ری‌استارت کنید.',
                'provider_response' => ['error' => $e->getMessage()]
            ];
        }
        return [
            'success' => false,
            'status' => 'failed',
            'error' => 'خطا: ' . $e->getMessage(),
            'provider_response' => ['error' => $e->getMessage()]
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'status' => 'failed',
            'error' => 'خطای عمومی: ' . $e->getMessage(),
            'provider_response' => ['error' => $e->getMessage()]
        ];
    }
}

/**
 * Send SMS using Ghasedak
 */
function sendSMSGhasedak($phone, $message)
{
    if (empty(SMS_API_KEY)) {
        return [
            'success' => false,
            'error' => 'SMS API key not configured',
            'provider_response' => null
        ];
    }
    
    $url = "https://api.ghasedak.me/v2/sms/send/simple";
    
    $headers = [
        'apikey: ' . SMS_API_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ];
    
    $data = [
        'message' => $message,
        'receptor' => $phone,
        'linenumber' => SMS_SENDER
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        return [
            'success' => false,
            'error' => 'CURL Error: ' . $curlError,
            'provider_response' => null
        ];
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && isset($responseData['result']['code']) && $responseData['result']['code'] == 200) {
        return [
            'success' => true,
            'message_id' => $responseData['result']['items'][0]['messageid'] ?? null,
            'provider_response' => $responseData
        ];
    } else {
        $errorMsg = $responseData['result']['message'] ?? 'Unknown error';
        return [
            'success' => false,
            'error' => $errorMsg,
            'provider_response' => $responseData
        ];
    }
}

/**
 * Test SMS (for development - doesn't actually send)
 */
function sendSMSTest($phone, $message)
{
    // Just log it, don't actually send
    error_log("TEST SMS - Would send to {$phone}: {$message}");
    return [
        'success' => true,
        'message_id' => 'test_' . time(),
        'provider_response' => ['test' => true, 'phone' => $phone, 'message' => $message]
    ];
}

/**
 * Main SMS sending function
 * Returns detailed result with status information
 */
function sendSMS($phone, $message)
{
    // Normalize phone number
    $phone = normalizePhone($phone);
    
    if (empty($phone) || strlen($phone) < 10) {
        logSMS($phone, $message, SMS_PROVIDER, 'invalid_phone', null, 'Invalid phone number');
        return [
            'success' => false,
            'error' => 'شماره موبایل معتبر نیست',
            'status' => 'invalid_phone'
        ];
    }
    
    // Add country code if needed (Iran: +98)
    if (strlen($phone) == 10) {
        $phone = '0' . $phone; // Add leading zero for Iranian numbers
    }
    
    // Check if SMS is enabled
    if (!SMS_ENABLED) {
        logSMS($phone, $message, SMS_PROVIDER, 'disabled', null, 'SMS sending is disabled');
        return [
            'success' => false,
            'error' => 'SMS sending is disabled',
            'status' => 'disabled'
        ];
    }
    
    // Select provider and send
    $result = null;
    $provider = SMS_PROVIDER;
    
    switch (strtolower($provider)) {
        case 'kavenegar':
            $result = sendSMSKavenegar($phone, $message);
            break;
        case 'melipayamak':
            $result = sendSMSMelipayamak($phone, $message);
            break;
        case 'ghasedak':
            $result = sendSMSGhasedak($phone, $message);
            break;
        case 'payamakpanel':
        case 'payamak-panel':
        case 'payamak':
            $result = sendSMSPayamakPanel($phone, $message);
            break;
        case 'test':
            $result = sendSMSTest($phone, $message);
            break;
        default:
            $result = [
                'success' => false,
                'error' => 'Unknown SMS provider: ' . $provider,
                'provider_response' => null
            ];
    }
    
    // Log the result
    $status = $result['success'] ? 'sent' : 'failed';
    logSMS(
        $phone,
        $message,
        $provider,
        $status,
        $result['provider_response'] ?? null,
        $result['error'] ?? null
    );
    
    // Return detailed result
    return [
        'success' => $result['success'],
        'error' => $result['error'] ?? null,
        'status' => $status,
        'message_id' => $result['message_id'] ?? null,
        'provider' => $provider,
        'provider_response' => $result['provider_response'] ?? null
    ];
}

/**
 * Get SMS sending statistics
 */
function getSMSStats($phone = null, $days = 7)
{
    if (!SMS_LOG_ENABLED) {
        return null;
    }
    
    try {
        initSMSLogTable();
        $conn = getConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM sms_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params = [$days];
        
        if ($phone) {
            $sql .= " AND phone = ?";
            $params[] = $phone;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting SMS stats: " . $e->getMessage());
        return null;
    }
}

/**
 * Get recent SMS logs
 */
function getSMSLogs($phone = null, $limit = 50)
{
    if (!SMS_LOG_ENABLED) {
        return [];
    }
    
    try {
        initSMSLogTable();
        $conn = getConnection();
        
        $sql = "SELECT * FROM sms_logs WHERE 1=1";
        $params = [];
        
        if ($phone) {
            $sql .= " AND phone = ?";
            $params[] = $phone;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting SMS logs: " . $e->getMessage());
        return [];
    }
}
?>

