# Remove dollar signs from ALL HTML files while preserving UTF-8
# Run this AFTER restoring files with HTTrack

$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"

# Force UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "REMOVING DOLLAR SIGNS FROM PRICES" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Find all HTML files
$files = Get-ChildItem -Path $baseDir -Filter *.html -Recurse

Write-Host "Found $($files.Count) HTML files" -ForegroundColor Yellow
Write-Host ""

$changedCount = 0

foreach ($file in $files) {
    try {
        # Read with UTF-8 encoding
        $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
        
        # Check if file has dollar signs
        if ($content -match '\$\d') {
            # Remove dollar signs from prices
            $newContent = $content -replace '\$(\d+\.?\d*)', '$1'
            
            # Save with UTF-8 encoding (with BOM)
            $utf8Bom = New-Object System.Text.UTF8Encoding $true
            [System.IO.File]::WriteAllText($file.FullName, $newContent, $utf8Bom)
            
            Write-Host "✓ Fixed: $($file.Name)" -ForegroundColor Green
            $changedCount++
        }
    }
    catch {
        Write-Host "✗ Error with $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "COMPLETE: Modified $changedCount files" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan



