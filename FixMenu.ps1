# Translate Menu to Farsi and make navigation bold
$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "Fixing Menu and Navigation..." -ForegroundColor Cyan

$files = Get-ChildItem -Path $baseDir -Filter *.html -Recurse -File

$count = 0
foreach ($file in $files) {
    try {
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        $original = $content
        
        # Translate Menu to منو
        $content = $content -replace 'departments__button-title">Menu</span>', 'departments__button-title">منو</span>'
        $content = $content -replace '>Menu<', '>منو<'
        
        # Make navigation links bold
        $navItems = @('خانه', 'فروشگاه', 'وبلاگ', 'حساب کاربری', 'صفحات')
        foreach ($item in $navItems) {
            if ($content -notmatch "<strong>$item</strong>") {
                # Wrap in strong tags if in main-menu__link
                $pattern = "(<a[^>]*class=`"main-menu__link`"[^>]*>)([^<]*?)($item)([^<]*?)(</a>)"
                $replacement = '$1$2<strong>$3</strong>$4$5'
                $content = $content -replace $pattern, $replacement
            }
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



