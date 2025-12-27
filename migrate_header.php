<?php
// migrate_header.php
// Copies header from index.html to all other .html files.
$root = __DIR__;
$indexFile = $root . '/index.html';
$headerMobileStart = '<header class="site__mobile-header">';
$headerMobileEnd = '</header>'; // closing tag for mobile header
$headerDesktopStart = '<header class="site__header">';
$headerDesktopEnd = '</header>'; // closing tag for desktop header

// Load index.html and extract header blocks
$indexContent = file_get_contents($indexFile);
if ($indexContent === false) {
    die("Failed to read index.html\n");
}
// Mobile header extraction
$mobilePosStart = strpos($indexContent, $headerMobileStart);
$mobilePosEnd = strpos($indexContent, $headerMobileEnd, $mobilePosStart) + strlen($headerMobileEnd);
$mobileHeader = substr($indexContent, $mobilePosStart, $mobilePosEnd - $mobilePosStart);
// Desktop header extraction (first occurrence after mobile header)
$desktopPosStart = strpos($indexContent, $headerDesktopStart, $mobilePosEnd);
$desktopPosEnd = strpos($indexContent, $headerDesktopEnd, $desktopPosStart) + strlen($headerDesktopEnd);
$desktopHeader = substr($indexContent, $desktopPosStart, $desktopPosEnd - $desktopPosStart);

// Find all .html files except index.html
$files = glob($root . '/*.html');
foreach ($files as $file) {
    if (basename($file) === 'index.html')
        continue;
    $content = file_get_contents($file);
    if ($content === false)
        continue;
    // Replace mobile header if present
    $newContent = preg_replace('#<header class="site__mobile-header">.*?</header>#s', $mobileHeader, $content);
    // Replace desktop header if present
    $newContent = preg_replace('#<header class="site__header">.*?</header>#s', $desktopHeader, $newContent);
    // Ensure required JS files are included before </body>
    if (strpos($newContent, 'js/main.js') === false) {
        $newContent = str_replace('</body>', "    <script src=\"js/main.js\"></script>\n</body>", $newContent);
    }
    if (strpos($newContent, 'js/cart-loader.js') === false) {
        $newContent = str_replace('</body>', "    <script src=\"js/cart-loader.js\"></script>\n</body>", $newContent);
    }
    file_put_contents($file, $newContent);
    echo "Migrated $file\n";
}
?>