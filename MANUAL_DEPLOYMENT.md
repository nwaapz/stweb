# Manual Deployment Options for cPanel

Since the automated deployment isn't working, here are manual ways to update your website:

## Option 1: Manual Update via cPanel Git Interface

1. **In cPanel → Git Version Control:**
   - Go to your repository (`stweb`)
   - Click **"Update from Remote"** button
   - This pulls the latest code from GitHub to the cPanel repository
   - The files will be in: `/home3/startech/repositories/stweb/`

2. **Then manually copy files using File Manager:**
   - Go to **File Manager** in cPanel
   - Navigate to `/home3/startech/repositories/stweb/`
   - Select all files you need (HTML, css/, js/, images/, vendor/, backend/)
   - Copy them
   - Navigate to `/home3/startech/public_html/st1/`
   - Paste the files there

## Option 2: Use cPanel File Manager Directly

1. **Upload via File Manager:**
   - Go to **File Manager** in cPanel
   - Navigate to `public_html/st1/` (create the folder if it doesn't exist)
   - Click **Upload** button
   - Upload your files from your local machine

## Option 3: Use FTP/SFTP

1. **Get FTP credentials from cPanel:**
   - Go to **FTP Accounts** in cPanel
   - Create or use existing FTP account
   - Note the FTP host, username, and password

2. **Use an FTP client:**
   - Use FileZilla, WinSCP, or any FTP client
   - Connect to your server
   - Upload files to `/public_html/st1/` directory

## Option 4: Use cPanel Terminal (if available)

If you have SSH/Terminal access:

```bash
# Navigate to repository
cd /home3/startech/repositories/stweb

# Create deployment directory
mkdir -p /home3/startech/public_html/st1

# Copy files
cp *.html /home3/startech/public_html/st1/
cp -R css js images vendor backend /home3/startech/public_html/st1/
```

## Quick Manual Update Workflow

**Recommended approach:**
1. Push changes to GitHub: `git push origin master`
2. In cPanel → Git Version Control → Click "Update from Remote"
3. In cPanel → File Manager:
   - Go to `/repositories/stweb/`
   - Select: `index.html`, `css/`, `js/`, `images/`, `vendor/`, `backend/`
   - Copy
   - Go to `/public_html/st1/`
   - Paste

This way you still use Git for version control, but manually deploy when needed.
