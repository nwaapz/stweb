# PowerShell script to translate remaining English text to Farsi
$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "Translating remaining English text to Farsi..." -ForegroundColor Cyan

$files = Get-ChildItem -Path $baseDir -Filter *.html -Recurse -File

# Define translations hashtable
$translations = @{
    '>Menu<' = '>منو<';
    'departments__button-title">Menu</span>' = 'departments__button-title">منو</span>';
    '>Language<' = '>زبان<';
    '>Currency<' = '>واحد پول<';
    '>Wishlist<' = '>لیست علاقه‌مندی‌ها<';
    '>Cart<' = '>سبد خرید<';
    '>Account<' = '>حساب کاربری<';
    '>Garage<' = '>گاراژ<';
    '>Free call 24/7<' = '>تماس رایگان ۲۴/۷<';
    '>Buy Theme<' = '>خرید قالب<';
    '>Add Vehicle<' = '>افزودن خودرو<';
    '>Select Year<' = '>انتخاب سال<';
    '>Select Brand<' = '>انتخاب برند<';
    '>Select Model<' = '>انتخاب مدل<';
    '>Select Engine<' = '>انتخاب موتور<';
    'placeholder="Enter VIN number"' = 'placeholder="شماره VIN را وارد کنید"';
    'aria-label="VIN number"' = 'aria-label="شماره VIN"';
    '>Cancel<' = '>لغو<';
    '>Back to list<' = '>بازگشت به لیست<';
    '>Home<' = '>خانه<';
    '>Shop<' = '>فروشگاه<';
    '>Blog<' = '>وبلاگ<';
    '>Pages<' = '>صفحات<';
    '>About Us<' = '>درباره ما<';
    '>Contact Us<' = '>تماس با ما<';
    '>Track Order<' = '>پیگیری سفارش<';
    '>Terms And Conditions<' = '>قوانین و مقررات<';
    '>FAQ<' = '>سوالات متداول<';
    '>Components<' = '>کامپوننت‌ها<';
    '>Typography<' = '>تایپوگرافی<';
    '>404<' = '>۴۰۴<';
    '>Shop Grid<' = '>فروشگاه شبکه‌ای<';
    '>Shop List<' = '>فروشگاه لیست<';
    '>Shop Table<' = '>فروشگاه جدول<';
    '>Shop Right Sidebar<' = '>فروشگاه سایدبار راست<';
    '>Full Width<' = '>تمام عرض<';
    '>Left Sidebar<' = '>سایدبار چپ<';
    '>Right Sidebar<' = '>سایدبار راست<';
    '>Product<' = '>محصول<';
    '>Post Page<' = '>صفحه پست<';
    '>Post Without Image<' = '>پست بدون تصویر<';
    '>Blog Classic<' = '>وبلاگ کلاسیک<';
    '>Blog List<' = '>لیست وبلاگ<';
    '>Blog Grid<' = '>شبکه وبلاگ<';
    '>Login & Register<' = '>ورود و ثبت نام<';
    '>Dashboard<' = '>داشبورد<';
    '>Edit Profile<' = '>ویرایش پروفایل<';
    '>Order History<' = '>تاریخچه سفارشات<';
    '>Order Details<' = '>جزئیات سفارش<';
    '>Address Book<' = '>دفترچه آدرس<';
    '>Edit Address<' = '>ویرایش آدرس<';
    '>Change Password<' = '>تغییر رمز عبور<';
    '>Logout<' = '>خروج<';
    '>Register<' = '>ثبت نام<';
    '>Compare<' = '>مقایسه<';
    '>Checkout<' = '>تسویه حساب<';
    '>Order Success<' = '>سفارش موفق<';
    '>Powered by<' = '>قدرت گرفته از<';
    '>All Rights Reserved<' = '>تمامی حقوق محفوظ است<';
    '>Contact Us v1<' = '>تماس با ما نسخه ۱<';
    '>Contact Us v2<' = '>تماس با ما نسخه ۲<';
    '>Variant One<' = '>نسخه یک<';
    '>Variant Two<' = '>نسخه دو<';
    '>Variant Three<' = '>نسخه سه<';
    '>Variant Four<' = '>نسخه چهار<';
    '>Variant Five<' = '>نسخه پنج<';
    '>Mobile Header<' = '>هدر موبایل<';
    '>Header Classic<' = '>هدر کلاسیک<';
    '>Header Spaceship<' = '>هدر فضایی<';
    '>Category<' = '>دسته‌بندی<';
    '>3 Columns Sidebar<' = '>۳ ستونه سایدبار<';
    '>4 Columns Sidebar<' = '>۴ ستونه سایدبار<';
    '>5 Columns Sidebar<' = '>۵ ستونه سایدبار<';
    '>4 Columns Full<' = '>۴ ستونه تمام عرض<';
    '>5 Columns Full<' = '>۵ ستونه تمام عرض<';
    '>6 Columns Full<' = '>۶ ستونه تمام عرض<';
    '>7 Columns Full<' = '>۷ ستونه تمام عرض<';
}

$count = 0
foreach ($file in $files) {
    try {
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        $original = $content
        
        foreach ($key in $translations.Keys) {
            $content = $content.Replace($key, $translations[$key])
        }
        
        if ($content -ne $original) {
            $utf8Bom = New-Object System.Text.UTF8Encoding $true
            [System.IO.File]::WriteAllText($file.FullName, $content, $utf8Bom)
            Write-Host "Updated: $($file.Name)" -ForegroundColor Green
            $count++
        }
    }
    catch {
        Write-Host "Error: $($file.Name) - $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`nComplete: $count files updated" -ForegroundColor Cyan





