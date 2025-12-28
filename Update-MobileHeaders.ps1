# PowerShell script to update mobile headers in all HTML files
# This script extracts the mobile header from index.html and applies it to all other HTML files

$ErrorActionPreference = "Stop"

# Read index.html to get the target header
$indexContent = Get-Content "index.html" -Raw -Encoding UTF8

# Find the mobile header section
$startMarker = "<!-- site__mobile-header -->"
$endMarker = "<!-- site__mobile-header / end -->"

$startIdx = $indexContent.IndexOf($startMarker)
$endIdx = $indexContent.IndexOf($endMarker)

if ($startIdx -eq -1 -or $endIdx -eq -1) {
    Write-Host "Error: Could not find mobile header markers in index.html" -ForegroundColor Red
    exit 1
}

# Extract the target header (including markers)
$targetHeader = $indexContent.Substring($startIdx, $endIdx - $startIdx + $endMarker.Length)

Write-Host "Extracted mobile header from index.html" -ForegroundColor Green
Write-Host "Header length: $($targetHeader.Length) characters" -ForegroundColor Gray

# Get all HTML files (exclude header variants and test files)
$htmlFiles = Get-ChildItem -Path "." -Filter "*.html" | 
    Where-Object { 
        $name = $_.Name
        $name -notlike "header-*" -and 
        $name -notlike "mobile-header-*" -and 
        $name -ne "indexTest.html" -and 
        $name -ne "index2.html"
    }

Write-Host "`nFound $($htmlFiles.Count) HTML files to process" -ForegroundColor Cyan
Write-Host "=" * 60

$updated = 0
$skipped = 0

foreach ($file in $htmlFiles) {
    try {
        $content = Get-Content $file.FullName -Raw -Encoding UTF8
        
        $fileStartIdx = $content.IndexOf($startMarker)
        $fileEndIdx = $content.IndexOf($endMarker)
        
        if ($fileStartIdx -eq -1 -or $fileEndIdx -eq -1) {
            Write-Host "✗ $($file.Name) - header markers not found" -ForegroundColor Yellow
            $skipped++
            continue
        }
        
        # Replace the section
        $newContent = $content.Substring(0, $fileStartIdx) + 
                      $targetHeader + 
                      $content.Substring($fileEndIdx + $endMarker.Length)
        
        # Write back to file
        [System.IO.File]::WriteAllText($file.FullName, $newContent, [System.Text.Encoding]::UTF8)
        
        Write-Host "✓ $($file.Name)" -ForegroundColor Green
        $updated++
        
    } catch {
        Write-Host "✗ $($file.Name) - Error: $($_.Exception.Message)" -ForegroundColor Red
        $skipped++
    }
}

Write-Host "`n" + ("=" * 60)
Write-Host "Summary: $updated files updated, $skipped files skipped" -ForegroundColor Cyan

