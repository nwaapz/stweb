# Replace SVG logo with sttechLogo.png in all HTML files (2x bigger size)

$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "REPLACING LOGO WITH 2X BIGGER SIZE" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# The new logo HTML with 2x size (52px instead of 26px)
$newLogo = '<div class="logo__image"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></div>'

# Pattern to match both the old SVG and the previous smaller logo
$svgPattern = '<div class="logo__image">[\s\S]*?</div>'

$files = Get-ChildItem -Path $baseDir -Filter *.html -File

Write-Host "Found $($files.Count) HTML files" -ForegroundColor Yellow
Write-Host ""

$changedCount = 0

foreach ($file in $files) {
    try {
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        
        if ($content -match $svgPattern) {
            $newContent = $content -replace $svgPattern, $newLogo
            
            if ($newContent -ne $content) {
                $utf8Bom = New-Object System.Text.UTF8Encoding $true
                [System.IO.File]::WriteAllText($file.FullName, $newContent, $utf8Bom)
                Write-Host "Updated: $($file.Name)" -ForegroundColor Green
                $changedCount++
            }
        }
    }
    catch {
        Write-Host "Error with $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "COMPLETE: Updated $changedCount files" -ForegroundColor Cyan
Write-Host "Logo is now 2x bigger (52px height)" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Cyan
