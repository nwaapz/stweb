<?php
/**
 * SMS Configuration
 * تنظیمات سرویس پیامک
 * 
 * Configure your SMS provider settings here
 */

// SMS Provider: 'kavenegar', 'melipayamak', 'ghasedak', 'payamakpanel', or 'test'
define('SMS_PROVIDER', 'payamakpanel'); // Change to your provider

// SMS API Key (get from your SMS provider dashboard)
// For Payamak Panel: Use format "username|password" or just username (password will use SMS_PASSWORD)
define('SMS_API_KEY', '09121777039|4thvahdati@FB'); // Enter your API key here

// SMS Password (for Payamak Panel, if not included in API_KEY)
define('SMS_PASSWORD', '4thvahdati@FB'); // Optional: separate password field

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

