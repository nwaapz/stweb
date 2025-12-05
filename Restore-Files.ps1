# Restore HTML files with proper UTF-8 encoding
# This script downloads original files and removes $ signs

$baseUrl = "https://red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
$baseDir = "red-parts.html.themeforest.scompiler.ru\themes\red-ltr\"

# Important: Force UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$PSDefaultParameterValues['*:Encoding'] = 'utf8'

# List of files to restore
$files = @(
    "index.html",
    "index2.html",
    "cart.html",
    "checkout.html",
    "product-full.html",
    "product-sidebar.html"
)

function Restore-AndFixFile {
    param (
        [string]$filename
    )
    
    $url = $baseUrl + $filename
    $filepath = Join-Path $baseDir $filename
    
    Write-Host "Restoring: $filename..." -NoNewline
    
    try {
        # Download with UTF-8 encoding
        $webClient = New-Object System.Net.WebClient
        $webClient.Headers.Add("User-Agent", "Mozilla/5.0")
        $webClient.Encoding = [System.Text.Encoding]::UTF8
        
        $content = $webClient.DownloadString($url)
        
        # Remove $ signs from prices
        $content = $content -replace '\$(\d+\.?\d*)', '$1'
        
        # Save with UTF-8 encoding (with BOM to ensure proper display)
        $utf8Bom = New-Object System.Text.UTF8Encoding $true
        [System.IO.File]::WriteAllText($filepath, $content, $utf8Bom)
        
        Write-Host " SUCCESS" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host " FAILED: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RESTORING FILES WITH UTF-8 ENCODING" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$successCount = 0
$failedCount = 0

foreach ($file in $files) {
    if (Restore-AndFixFile -filename $file) {
        $successCount++
    } else {
        $failedCount++
    }
    Start-Sleep -Milliseconds 500
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SUMMARY: $successCount restored, $failedCount failed" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Farsi text should now be restored!" -ForegroundColor Green



