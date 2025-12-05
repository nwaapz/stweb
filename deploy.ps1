# Deploy script for cPanel
# This script commits and pushes changes to GitHub
# Then you need to deploy in cPanel manually (or set up webhook for auto-deploy)

param(
    [string]$Message = "Update website"
)

Write-Host "🚀 Starting deployment process..." -ForegroundColor Cyan

# Check if we're in a git repository
if (-not (Test-Path .git)) {
    Write-Host "❌ Error: Not a git repository!" -ForegroundColor Red
    exit 1
}

# Check for changes
$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "⚠️  No changes to commit." -ForegroundColor Yellow
    Write-Host "💡 If you want to force push, use: git push origin master" -ForegroundColor Yellow
    exit 0
}

# Show status
Write-Host "`n📋 Changes to be committed:" -ForegroundColor Cyan
git status --short

# Add all changes
Write-Host "`n➕ Staging changes..." -ForegroundColor Cyan
git add .

# Commit
Write-Host "💾 Committing changes..." -ForegroundColor Cyan
git commit -m $Message

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Commit failed!" -ForegroundColor Red
    exit 1
}

# Push to GitHub
Write-Host "📤 Pushing to GitHub..." -ForegroundColor Cyan
git push origin master

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Successfully pushed to GitHub!" -ForegroundColor Green
    Write-Host "`n📝 Next steps:" -ForegroundColor Yellow
    Write-Host "   1. Go to cPanel → Git Version Control" -ForegroundColor Yellow
    Write-Host "   2. Click 'Update from Remote'" -ForegroundColor Yellow
    Write-Host "   3. Click 'Deploy HEAD Commit'" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ Push failed! Check your git remote and credentials." -ForegroundColor Red
    exit 1
}
