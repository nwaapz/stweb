<?php
/**
 * SMS Configuration Example
 * کپی این فایل به sms_config.php و تنظیمات خود را وارد کنید
 * 
 * Copy this file to sms_config.php and enter your settings
 */

// SMS Provider: 'kavenegar', 'melipayamak', 'ghasedak', 'payamakpanel', or 'test'
define('SMS_PROVIDER', 'payamakpanel'); // Change to your provider

// SMS API Key (get from your SMS provider dashboard)
// For Payamak Panel: Use format "username|password" (e.g., "09121777039|4thvahdati@FB")
// For other providers: Just the API key
define('SMS_API_KEY', 'YOUR_USERNAME|YOUR_PASSWORD'); // Enter your API key here

// SMS Password (for Payamak Panel, if not included in API_KEY)
// Optional: You can use this instead of including password in SMS_API_KEY
define('SMS_PASSWORD', 'YOUR_PASSWORD'); // Optional: separate password field

// SMS Sender Number/Line (usually provided by your SMS provider)
define('SMS_SENDER', '20001390'); // Change to your sender number

// Enable/Disable SMS sending
define('SMS_ENABLED', true); // Set to false to disable SMS (for testing)

// Enable SMS logging to database
define('SMS_LOG_ENABLED', true);

/**
 * Provider-specific notes:
 * 
 * Payamak Panel:
 * - Provider: 'payamakpanel'
 * - API Key: Format "username|password" (e.g., "09121777039|4thvahdati@FB")
 * - Or use SMS_API_KEY for username and SMS_PASSWORD for password
 * - Sender: Your line number (e.g., "20001390")
 * - Uses SOAP API: http://api.payamak-panel.com/post/Send.asmx?wsdl
 * - Note: SOAP extension must be enabled in PHP (usually enabled by default on cPanel)
 * 
 * Kavenegar:
 * - API Key: Get from https://panel.kavenegar.com/client/membership/panel
 * - Sender: Your verified line number
 * 
 * Melipayamak:
 * - API Key: Your username
 * - Password: Usually same as username or separate
 * 
 * Ghasedak:
 * - API Key: Get from https://panel.ghasedak.me
 * - Sender: Your line number
 */


