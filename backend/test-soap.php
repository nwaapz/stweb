<?php
/**
 * Quick SOAP Test
 * تست سریع SOAP
 */

echo "<h2>تست SOAP</h2>";

// Check 1: Extension loaded
echo "<p><strong>1. بررسی extension_loaded('soap'):</strong> ";
if (extension_loaded('soap')) {
    echo "<span style='color: green;'>✓ فعال است</span></p>";
} else {
    echo "<span style='color: red;'>✗ فعال نیست</span></p>";
}

// Check 2: Class exists
echo "<p><strong>2. بررسی class_exists('SoapClient'):</strong> ";
if (class_exists('SoapClient')) {
    echo "<span style='color: green;'>✓ کلاس موجود است</span></p>";
} else {
    echo "<span style='color: red;'>✗ کلاس موجود نیست</span></p>";
}

// Check 3: Try to create instance
echo "<p><strong>3. تلاش برای ایجاد SoapClient:</strong> ";
try {
    ini_set("soap.wsdl_cache_enabled", 0);
    $client = new SoapClient("http://api.payamak-panel.com/post/Send.asmx?wsdl", [
        "encoding" => "UTF-8",
    ]);
    echo "<span style='color: green;'>✓ موفق بود!</span></p>";
    echo "<p>SOAP کار می‌کند. مشکل از جای دیگری است.</p>";
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ خطا: " . htmlspecialchars($e->getMessage()) . "</span></p>";
}

// Show PHP info
echo "<hr>";
echo "<h3>اطلاعات PHP:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>php.ini location:</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>Loaded extensions:</strong></p>";
echo "<pre>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo $ext . "\n";
}
echo "</pre>";

// Check if soap is in the list
if (in_array('soap', $extensions)) {
    echo "<p style='color: green;'><strong>✓ SOAP در لیست افزونه‌های بارگذاری شده است!</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ SOAP در لیست افزونه‌های بارگذاری شده نیست!</strong></p>";
}
?>

