# SMS Service Setup Guide
## راهنمای راه‌اندازی سرویس پیامک

### Overview
The SMS service has been integrated into your OTP system. It supports multiple Iranian SMS providers and includes comprehensive error handling and logging.

### Configuration

1. **Edit SMS Configuration File**
   - Open `backend/config/sms_config.php`
   - Set your SMS provider: `'kavenegar'`, `'melipayamak'`, `'ghasedak'`, or `'test'`
   - Enter your API key
   - Set your sender number/line
   - Enable/disable SMS as needed

2. **Provider Setup**

   **Kavenegar:**
   - Sign up at https://panel.kavenegar.com
   - Get your API key from the dashboard
   - Verify your sender line number
   - Set in config:
     ```php
     define('SMS_PROVIDER', 'kavenegar');
     define('SMS_API_KEY', 'YOUR_API_KEY');
     define('SMS_SENDER', 'YOUR_LINE_NUMBER');
     ```

   **Melipayamak:**
   - Sign up at https://melipayamak.com
   - Get your username/password
   - Set in config:
     ```php
     define('SMS_PROVIDER', 'melipayamak');
     define('SMS_API_KEY', 'YOUR_USERNAME');
     ```

   **Ghasedak:**
   - Sign up at https://panel.ghasedak.me
   - Get your API key
   - Set in config:
     ```php
     define('SMS_PROVIDER', 'ghasedak');
     define('SMS_API_KEY', 'YOUR_API_KEY');
     define('SMS_SENDER', 'YOUR_LINE_NUMBER');
     ```

### Testing

1. **Test Mode:**
   - Set `SMS_PROVIDER` to `'test'` in `sms_config.php`
   - SMS will be logged but not actually sent
   - Check PHP error logs for test messages

2. **Check SMS Logs:**
   - SMS attempts are logged to `sms_logs` table in database
   - View logs using: `getSMSLogs($phone)` function
   - Check statistics using: `getSMSStats($phone, $days)`

### Troubleshooting

**Problem: OTP says "sent" but no SMS received**

1. **Check SMS Logs:**
   ```php
   $logs = getSMSLogs('09123456789', 10);
   foreach ($logs as $log) {
       echo "Status: " . $log['status'] . "\n";
       echo "Error: " . $log['error_message'] . "\n";
       echo "Provider Response: " . $log['provider_response'] . "\n";
   }
   ```

2. **Check Configuration:**
   - Verify API key is correct
   - Verify sender number is correct
   - Check if SMS_ENABLED is true

3. **Check Provider Response:**
   - Look at `provider_response` in logs
   - Common errors:
     - Invalid API key
     - Insufficient credit
     - Invalid phone number format
     - Sender not verified

4. **Test SMS Sending:**
   ```php
   require_once 'backend/includes/sms_service.php';
   $result = sendSMS('09123456789', 'Test message');
   print_r($result);
   ```

### Features

- ✅ Multiple SMS provider support
- ✅ Comprehensive error handling
- ✅ Database logging of all SMS attempts
- ✅ Detailed status information
- ✅ Provider response tracking
- ✅ Test mode for development

### API Response Format

```php
[
    'success' => true/false,
    'error' => 'Error message if failed',
    'status' => 'sent' | 'failed' | 'invalid_phone' | 'disabled',
    'message_id' => 'Provider message ID',
    'provider' => 'kavenegar' | 'melipayamak' | 'ghasedak' | 'test',
    'provider_response' => [/* Full provider API response */]
]
```

### Database Schema

The `sms_logs` table is automatically created with:
- `id` - Primary key
- `phone` - Phone number
- `message` - SMS message content
- `provider` - SMS provider used
- `status` - 'sent' or 'failed'
- `provider_response` - JSON response from provider
- `error_message` - Error message if failed
- `created_at` - Timestamp

### Next Steps

1. Configure your SMS provider in `backend/config/sms_config.php`
2. Test with a real phone number
3. Monitor SMS logs to ensure delivery
4. Set up alerts for failed SMS attempts if needed


