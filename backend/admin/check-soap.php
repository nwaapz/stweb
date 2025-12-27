<?php
/**
 * Check SOAP Extension Status
 * بررسی وضعیت افزونه SOAP
 */

echo "<h2>بررسی افزونه SOAP</h2>";

if (extension_loaded('soap')) {
    echo "<div style='color: green; font-size: 18px;'>✓ افزونه SOAP نصب و فعال است</div>";
    echo "<p>اطلاعات افزونه:</p>";
    echo "<pre>";
    print_r(get_loaded_extensions());
    echo "</pre>";
} else {
    echo "<div style='color: red; font-size: 18px;'>✗ افزونه SOAP نصب نشده است</div>";
    echo "<h3>راه‌حل:</h3>";
    echo "<ol>";
    echo "<li>فایل <code>php.ini</code> را باز کنید (معمولاً در <code>C:\\xampp\\php\\php.ini</code>)</li>";
    echo "<li>خط زیر را پیدا کنید: <code>;extension=soap</code></li>";
    echo "<li>سمیکالن (;) را از ابتدای خط حذف کنید تا شود: <code>extension=soap</code></li>";
    echo "<li>Apache را در XAMPP Control Panel ری‌استارت کنید</li>";
    echo "<li>این صفحه را دوباره باز کنید</li>";
    echo "</ol>";
    echo "<p><strong>مسیر فایل php.ini:</strong> " . php_ini_loaded_file() . "</p>";
}

echo "<hr>";
echo "<h3>همه افزونه‌های نصب شده:</h3>";
echo "<pre>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo $ext . "\n";
}
echo "</pre>";
?>

