# SMS Service Setup for cPanel Host
## راه‌اندازی سرویس پیامک در cPanel

### Step 1: Upload Files

Upload these files to your cPanel host:
- `backend/includes/sms_service.php`
- `backend/config/sms_config.php` (create from example)
- `backend/admin/sms-diagnostics.php`
- `backend/admin/check-soap.php`

### Step 2: Configure SMS Settings

1. **Create SMS Config File:**
   - Copy `backend/config/sms_config.example.php` to `backend/config/sms_config.php`
   - Edit `sms_config.php` with your credentials

2. **Set Your Credentials:**
   ```php
   define('SMS_PROVIDER', 'payamakpanel');
   define('SMS_API_KEY', '09121777039|4thvahdati@FB');
   define('SMS_SENDER', '20001390');
   define('SMS_ENABLED', true);
   ```

### Step 3: Check SOAP Extension (cPanel)

cPanel hosts usually have SOAP enabled by default, but verify:

1. **Via cPanel:**
   - Go to cPanel → Select PHP Version
   - Check if `soap` extension is enabled
   - If not, enable it from the extensions list

2. **Via PHP Info:**
   - Create a file `phpinfo.php`:
     ```php
     <?php phpinfo(); ?>
     ```
   - Visit it in browser
   - Search for "soap"
   - Should see "soap" in the list

3. **Via Check Script:**
   - Visit: `yourdomain.com/backend/admin/check-soap.php`
   - Should show "✓ افزونه SOAP نصب و فعال است"

### Step 4: Database Setup

The SMS service will automatically create the `sms_logs` table when first used. Make sure:
- Database connection is configured in `backend/config/database.php`
- Your database user has CREATE TABLE permissions

### Step 5: Test SMS Sending

1. **Access Diagnostics Page:**
   - Login to admin panel
   - Go to: `yourdomain.com/backend/admin/sms-diagnostics.php`

2. **Test SMS:**
   - Enter a phone number
   - Click "ارسال پیامک تست"
   - Check the result

3. **Check Logs:**
   - View recent SMS logs on the diagnostics page
   - Check provider responses
   - Verify delivery status

### Step 6: File Permissions

Make sure these files are readable:
```bash
chmod 644 backend/config/sms_config.php
chmod 644 backend/includes/sms_service.php
```

### Troubleshooting

#### SOAP Not Available
If SOAP is not available on cPanel:
1. Contact your hosting provider to enable it
2. Or switch to a provider that uses REST API (Kavenegar, Melipayamak, Ghasedak)

#### Database Connection Issues
- Check `backend/config/database.php` settings
- Verify database credentials
- Ensure database exists

#### SMS Not Sending
1. Check SMS logs in diagnostics page
2. Verify API credentials are correct
3. Check provider response for error codes
4. Ensure SMS_ENABLED is true

#### Path Issues
If you get "file not found" errors:
- Check file paths are correct
- Use relative paths (already configured)
- Verify file structure matches local setup

### Security Notes

1. **Protect Config File:**
   - Keep `sms_config.php` outside web root if possible
   - Or add to `.htaccess`:
     ```apache
     <Files "sms_config.php">
         Order allow,deny
         Deny from all
     </Files>
     ```

2. **Database Credentials:**
   - Never commit `sms_config.php` to version control
   - Use strong passwords
   - Restrict database user permissions

### Alternative: Use REST API Providers

If SOAP is not available, you can use:
- **Kavenegar** (REST API) - Recommended
- **Melipayamak** (REST API)
- **Ghasedak** (REST API)

Just change `SMS_PROVIDER` in config file.

### Testing Checklist

- [ ] Files uploaded to cPanel
- [ ] `sms_config.php` created and configured
- [ ] SOAP extension enabled
- [ ] Database connection working
- [ ] Test SMS sent successfully
- [ ] SMS logs showing in database
- [ ] OTP system sending real SMS

### Support

If you encounter issues:
1. Check SMS diagnostics page for detailed errors
2. Review SMS logs in database
3. Check cPanel error logs
4. Verify provider credentials

