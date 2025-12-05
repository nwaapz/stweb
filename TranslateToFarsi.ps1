# Translate entire website from English to Farsi
# Changes: lang="en" to lang="fa", dir="ltr" to dir="rtl", all text translations

$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "TRANSLATING ENTIRE SITE TO FARSI" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Comprehensive translation dictionary
$translations = @{
    # Page titles
    'Home One — Red Parts' = 'خانه یک — قطعات قرمز'
    'Home Two — Red Parts' = 'خانه دو — قطعات قرمز'
    'Home One' = 'خانه یک'
    'Home Two' = 'خانه دو'
    
    # Navigation
    'Home' = 'خانه'
    'Shop' = 'فروشگاه'
    'Blog' = 'وبلاگ'
    'Account' = 'حساب کاربری'
    'Pages' = 'صفحات'
    'Menu' = 'منو'
    
    # Header elements
    'Call Us:' = 'تماس با ما:'
    'About Us' = 'درباره ما'
    'Contacts' = 'تماس با ما'
    'Track Order' = 'پیگیری سفارش'
    'Compare:' = 'مقایسه:'
    'Currency:' = 'واحد پول:'
    'Language:' = 'زبان:'
    'Hello, Log In' = 'سلام، ورود'
    'My Account' = 'حساب کاربری من'
    'Shopping Cart' = 'سبد خرید'
    
    # Search and vehicle
    'Enter Keyword or Part Number' = 'کلمه کلیدی یا شماره قطعه را وارد کنید'
    'Enter keyword or part number' = 'کلمه کلیدی یا شماره قطعه را وارد کنید'
    'Select Vehicle' = 'انتخاب وسیله نقلیه'
    'Select a vehicle to find exact fit parts' = 'برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید'
    'Add A Vehicle' = 'افزودن وسیله نقلیه'
    'Back to vehicles list' = 'بازگشت به لیست وسایل نقلیه'
    'Vehicle' = 'وسیله نقلیه'
    
    # Account menu
    'Log In to Your Account' = 'ورود به حساب کاربری شما'
    'Email address' = 'آدرس ایمیل'
    'Password' = 'رمز عبور'
    'Forgot?' = 'فراموش کرده‌اید؟'
    'Login' = 'ورود'
    'Create An Account' = 'ایجاد حساب کاربری'
    'Dashboard' = 'داشبورد'
    'Garage' = 'گاراژ'
    'Edit Profile' = 'ویرایش پروفایل'
    'Order History' = 'تاریخچه سفارشات'
    'Addresses' = 'آدرس‌ها'
    'Logout' = 'خروج'
    
    # Cart
    'Color:' = 'رنگ:'
    'Material:' = 'جنس:'
    'Yellow' = 'زرد'
    'Aluminium' = 'آلومینیوم'
    'Subtotal' = 'جمع کل'
    'Shipping' = 'ارسال'
    'Tax' = 'مالیات'
    'Total' = 'مجموع'
    'View Cart' = 'مشاهده سبد خرید'
    'Checkout' = 'تسویه حساب'
    
    # Product categories
    'Headlights & Lighting' = 'چراغ‌ها و روشنایی'
    'Interior Parts' = 'لوازم داخلی'
    'Switches & Relays' = 'سوئیچ‌ها و رله‌ها'
    'Tires & Wheels' = 'لاستیک و چرخ'
    'Tools & Garage' = 'ابزار و تجهیزات'
    'Body Parts' = 'قطعات بدنه'
    'Suspension' = 'سیستم تعلیق'
    'Steering' = 'فرمان'
    'Fuel Systems' = 'سیستم سوخت'
    'Transmission' = 'گیربکس'
    'Air Filters' = 'فیلتر هوا'
    'Clutches' = 'کلاچ'
    'Brakes & Suspension' = 'ترمز و سیستم تعلیق'
    'Engine & Drivetrain' = 'موتور و سیستم انتقال قدرت'
    
    # Product details
    'Bumpers' = 'سپر'
    'Hoods' = 'کاپوت'
    'Grilles' = 'جلو پنجره'
    'Fog Lights' = 'مه‌شکن'
    'Door Handles' = 'دستگیره در'
    'Car Covers' = 'روکش خودرو'
    'Tailgates' = 'درب صندوق عقب'
    'Headlights' = 'چراغ جلو'
    'Tail Lights' = 'چراغ عقب'
    'Turn Signals' = 'راهنما'
    'Corner Lights' = 'چراغ گوشه'
    'Brake Discs' = 'دیسک ترمز'
    'Wheel Hubs' = 'توپی چرخ'
    'Air Suspension' = 'سیستم تعلیق بادی'
    'Ball Joints' = 'مفصل کروی'
    'Brake Pad Sets' = 'لنت ترمز'
    'Floor Mats' = 'کفپوش'
    'Gauges' = 'عقربه‌ها'
    'Consoles & Organizers' = 'کنسول و نظم‌دهنده'
    'Mobile Electronics' = 'لوازم الکترونیکی موبایل'
    'Steering Wheels' = 'فرمان'
    'Cargo Accessories' = 'لوازم بار'
    'Repair Manuals' = 'راهنمای تعمیر'
    'Car Care' = 'نگهداری خودرو'
    'Code Readers' = 'دستگاه عیب‌یاب'
    'Tool Boxes' = 'جعبه ابزار'
    'Oxygen Sensors' = 'سنسور اکسیژن'
    'Heating' = 'گرمایش'
    'Exhaust' = 'اگزوز'
    'Cranks & Pistons' = 'میل‌لنگ و پیستون'
    
    # Form elements
    'Select Year' = 'انتخاب سال'
    'Select Brand' = 'انتخاب برند'
    'Select Model' = 'انتخاب مدل'
    'Select Engine' = 'انتخاب موتور'
    'Enter VIN number' = 'شماره VIN را وارد کنید'
    'Or' = 'یا'
    
    # Shop pages
    'Category' = 'دسته‌بندی'
    'Shop Grid' = 'نمایش شبکه‌ای'
    'Shop List' = 'نمایش لیستی'
    'Shop Table' = 'نمایش جدولی'
    'Shop Right Sidebar' = 'فروشگاه با نوار کناری راست'
    'Product' = 'محصول'
    'Full Width' = 'تمام عرض'
    'Left Sidebar' = 'نوار کناری چپ'
    'Right Sidebar' = 'نوار کناری راست'
    'Cart' = 'سبد خرید'
    'Order Success' = 'سفارش موفق'
    'Wishlist' = 'لیست علاقه‌مندی'
    'Compare' = 'مقایسه'
    
    # Blog
    'Blog Classic' = 'وبلاگ کلاسیک'
    'Blog List' = 'لیست وبلاگ'
    'Blog Grid' = 'شبکه وبلاگ'
    'Post Page' = 'صفحه پست'
    'Post Without Image' = 'پست بدون تصویر'
    
    # Account pages
    'Login & Register' = 'ورود و ثبت‌نام'
    'Order Details' = 'جزئیات سفارش'
    'Address Book' = 'دفترچه آدرس'
    'Edit Address' = 'ویرایش آدرس'
    'Change Password' = 'تغییر رمز عبور'
    
    # Other pages
    'Contact Us v1' = 'تماس با ما نسخه 1'
    'Contact Us v2' = 'تماس با ما نسخه 2'
    'Contact Us' = 'تماس با ما'
    'Terms And Conditions' = 'شرایط و ضوابط'
    'FAQ' = 'سوالات متداول'
    'Components' = 'اجزا'
    'Typography' = 'تایپوگرافی'
    
    # Products
    'Products' = 'محصولات'
    'Categories' = 'دسته‌بندی‌ها'
    'on' = 'در'
    'reviews' = 'نظرات'
    'review' = 'نظر'
    
    # Common words
    'Columns' = 'ستون'
    'Sidebar' = 'نوار کناری'
    'Full' = 'کامل'
    'Header' = 'هدر'
    'Variant' = 'نوع'
    'One' = 'یک'
    'Two' = 'دو'
    'Three' = 'سه'
    'Four' = 'چهار'
    'Five' = 'پنج'
    'Spaceship' = 'فضاپیما'
    'Classic' = 'کلاسیک'
    'Mobile Header' = 'هدر موبایل'
    
    # Auto parts specific
    'Auto parts for Cars, trucks and motorcycles' = 'قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها'
    
    # Brands (keep as is, but add for completeness)
    'Brandix Brake Kit BDX-750Z370-S' = 'کیت ترمز برندیکس BDX-750Z370-S'
    'Left Headlight Of Brandix Z54' = 'چراغ جلو چپ برندیکس Z54'
    'Glossy Gray 19" Aluminium Wheel AR-19' = 'رینگ آلومینیومی براق خاکستری 19 اینچی AR-19'
    'Twin Exhaust Pipe From Brandix Z54' = 'اگزوز دوقلو برندیکس Z54'
    'Motor Oil Level 5' = 'روغن موتور سطح 5'
    'Brandix Engine Block Z4' = 'بلوک موتور برندیکس Z4'
    'Brandix Clutch Discs Z175' = 'دیسک کلاچ برندیکس Z175'
    'Brandix Manual Five Speed Gearbox' = 'گیربکس دستی پنج دنده برندیکس'
    
    # Engine details
    'Engine' = 'موتور'
    'Gas' = 'بنزین'
    'Diesel' = 'دیزل'
    
    # Footer (will add more as needed)
    'Information' = 'اطلاعات'
    'My Account' = 'حساب کاربری من'
    'Newsletter' = 'خبرنامه'
    'Subscribe' = 'اشتراک'
}

# Get all HTML files
$files = Get-ChildItem -Path $baseDir -Filter *.html -File
Write-Host "Found $($files.Count) HTML files to translate" -ForegroundColor Yellow
Write-Host ""

$translatedCount = 0

foreach ($file in $files) {
    try {
        Write-Host "Processing: $($file.Name)" -NoNewline
        
        # Read with UTF-8 encoding
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        
        # Change language and direction
        $content = $content -replace 'lang="en"', 'lang="fa"'
        $content = $content -replace "lang='en'", "lang='fa'"
        $content = $content -replace 'dir="ltr"', 'dir="rtl"'
        $content = $content -replace "dir='ltr'", "dir='rtl'"
        
        # Apply all translations
        foreach ($key in $translations.Keys) {
            $value = $translations[$key]
            # Escape special regex characters in the key
            $escapedKey = [regex]::Escape($key)
            $content = $content -replace $escapedKey, $value
        }
        
        # Save with UTF-8 BOM
        $utf8Bom = New-Object System.Text.UTF8Encoding $true
        [System.IO.File]::WriteAllText($file.FullName, $content, $utf8Bom)
        
        Write-Host " ✓" -ForegroundColor Green
        $translatedCount++
    }
    catch {
        Write-Host " ✗ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "TRANSLATION COMPLETE!" -ForegroundColor Green
Write-Host "Translated: $translatedCount files" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Changes made:" -ForegroundColor Yellow
Write-Host "  - Changed lang='en' to lang='fa'" -ForegroundColor White
Write-Host "  - Changed dir='ltr' to dir='rtl'" -ForegroundColor White
Write-Host "  - Translated all UI text to Farsi" -ForegroundColor White



