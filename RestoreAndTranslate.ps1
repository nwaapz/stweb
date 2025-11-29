# Restore English files and translate carefully to Farsi

$baseUrl = "https://red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$files = @(
    "index.html", "index2.html", "404.html", "about-us.html",
    "account-addresses.html", "account-dashboard.html", "account-edit-address.html",
    "account-garage.html", "account-login.html", "account-order-details.html",
    "account-orders.html", "account-password.html", "account-profile.html",
    "blog-classic-left-sidebar.html", "blog-classic-right-sidebar.html",
    "blog-grid-left-sidebar.html", "blog-grid-right-sidebar.html",
    "blog-list-left-sidebar.html", "blog-list-right-sidebar.html",
    "cart.html", "category-3-columns-sidebar.html", "category-4-columns-full.html",
    "category-4-columns-sidebar.html", "category-5-columns-full.html",
    "category-5-columns-sidebar.html", "category-6-columns-full.html",
    "category-7-columns-full.html", "category-right-sidebar.html",
    "checkout.html", "compare.html", "components.html",
    "contact-us-v1.html", "contact-us-v2.html", "faq.html",
    "header-classic-variant-five.html", "header-classic-variant-four.html",
    "header-classic-variant-one.html", "header-classic-variant-three.html",
    "header-classic-variant-two.html", "header-spaceship-variant-one.html",
    "header-spaceship-variant-three.html", "header-spaceship-variant-two.html",
    "mobile-header-variant-one.html", "mobile-header-variant-two.html",
    "order-success.html", "post-full-width.html", "post-left-sidebar.html",
    "post-right-sidebar.html", "post-without-image.html",
    "product-full.html", "product-sidebar.html",
    "shop-grid-3-columns-sidebar.html", "shop-grid-4-columns-full.html",
    "shop-grid-4-columns-sidebar.html", "shop-grid-5-columns-full.html",
    "shop-grid-6-columns-full.html", "shop-list.html",
    "shop-right-sidebar.html", "shop-table.html",
    "terms.html", "track-order.html", "typography.html", "wishlist.html"
)

Write-Host "Restoring and translating files..." -ForegroundColor Cyan
Write-Host ""

$success = 0
foreach ($file in $files) {
    $url = $baseUrl + $file
    $filepath = Join-Path $baseDir $file
    
    Write-Host "Processing: $file..." -NoNewline
    
    try {
        $webClient = New-Object System.Net.WebClient
        $webClient.Headers.Add("User-Agent", "Mozilla/5.0")
        $webClient.Encoding = [System.Text.Encoding]::UTF8
        
        $content = $webClient.DownloadString($url)
        
        # Change lang and dir
        $content = $content -replace 'lang="en"', 'lang="fa"'
        $content = $content -replace 'dir="ltr"', 'dir="rtl"'
        
        # Remove $ signs
        $content = $content -replace '\$(\d+\.?\d*)', '$1'
        
        # Apply logo change
        $logoPattern = '<div class="logo__image">[\s\S]*?</svg>[\s\S]*?</div>'
        $newLogo = '<div class="logo__image"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></div>'
        $content = $content -replace $logoPattern, $newLogo
        
        # Simple text translations (only visible text, not attributes)
        $translations = @{
            '>Home<' = '>خانه<'
            '>Shop<' = '>فروشگاه<'
            '>Blog<' = '>وبلاگ<'
            '>Account<' = '>حساب کاربری<'
            '>Pages<' = '>صفحات<'
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
            'Enter Keyword or Part Number' = 'کلمه کلیدی یا شماره قطعه را وارد کنید'
            'Select Vehicle' = 'انتخاب وسیله نقلیه'
            'Add A Vehicle' = 'افزودن وسیله نقلیه'
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
            'Headlights & Lighting' = 'چراغ‌ها و روشنایی'
            'Interior Parts' = 'لوازم داخلی'
            'Body Parts' = 'قطعات بدنه'
            'Suspension' = 'سیستم تعلیق'
            'Steering' = 'فرمان'
            'Fuel Systems' = 'سیستم سوخت'
            'Transmission' = 'گیربکس'
            'Air Filters' = 'فیلتر هوا'
            'Select Year' = 'انتخاب سال'
            'Select Brand' = 'انتخاب برند'
            'Select Model' = 'انتخاب مدل'
            'Select Engine' = 'انتخاب موتور'
            'Auto parts for Cars, trucks and motorcycles' = 'قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها'
        }
        
        foreach ($key in $translations.Keys) {
            $value = $translations[$key]
            $content = $content -replace [regex]::Escape($key), $value
        }
        
        $utf8Bom = New-Object System.Text.UTF8Encoding $true
        [System.IO.File]::WriteAllText($filepath, $content, $utf8Bom)
        
        Write-Host " ✓" -ForegroundColor Green
        $success++
    }
    catch {
        Write-Host " ✗" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Complete: $success files processed" -ForegroundColor Cyan

