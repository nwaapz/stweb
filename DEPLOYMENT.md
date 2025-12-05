# Deployment Guide for cPanel

This guide explains how to deploy your e-commerce website to cPanel using Git.

## Setup (One-time)

1. **Push `.cpanel.yml` to GitHub:**
   ```bash
   git add .cpanel.yml
   git commit -m "Add cPanel deployment configuration"
   git push origin master
   ```

2. **In cPanel Git Version Control:**
   - Click "Update from Remote" to pull the latest changes
   - Click "Deploy HEAD Commit" to deploy

## Daily Workflow

### Option 1: Manual Deployment (Recommended for now)

1. **Make changes locally** in your code
2. **Commit and push to GitHub:**
   ```bash
   git add .
   git commit -m "Update products page"
   git push origin master
   ```
3. **In cPanel:**
   - Go to Git Version Control → Your Repository
   - Click "Update from Remote" (pulls latest from GitHub)
   - Click "Deploy HEAD Commit" (deploys to public_html)

### Option 2: Automatic Deployment (Future)

You can set up a webhook in GitHub to automatically trigger deployment when you push:
- In cPanel, you'll get a webhook URL
- Add it to your GitHub repository settings → Webhooks

## What Gets Deployed

- ✅ All HTML files
- ✅ CSS, JS, images, vendor files
- ✅ Backend PHP files (to `public_html/backend/`)
- ❌ Python scripts (excluded)
- ❌ PowerShell scripts (excluded)
- ❌ Development files (excluded)

## Troubleshooting

**"The system cannot deploy" error:**
- Make sure `.cpanel.yml` exists in your repository root
- Ensure there are no uncommitted changes on the server
- Click "Update from Remote" first, then deploy

**Files not updating:**
- Check that you pushed to GitHub
- Click "Update from Remote" before deploying
- Verify `.cpanel.yml` is in the repository
