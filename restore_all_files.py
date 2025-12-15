#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Restore HTML files from original source and remove dollar signs with proper UTF-8 encoding
"""
import urllib.request
import urllib.error
import os
import re
import time

# Configuration
BASE_URL = "https://red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# List of HTML files to restore (add more as needed)
HTML_FILES = [
    "index.html",
    "index2.html",
    "404.html",
    "about-us.html",
    "account-addresses.html",
    "account-dashboard.html",
    "account-edit-address.html",
    "account-garage.html",
    "account-login.html",
    "account-order-details.html",
    "account-orders.html",
    "account-password.html",
    "account-profile.html",
    "cart.html",
    "checkout.html",
    "compare.html",
    "contact-us.html",
    "contact-us-v1.html",
    "contact-us-v2.html",
    "product-full.html",
    "product-sidebar.html",
    "wishlist.html",
]

def restore_and_fix_file(filename):
    """Download, fix dollar signs, and save with UTF-8 encoding"""
    url = BASE_URL + filename
    filepath = os.path.join(BASE_DIR, filename)
    
    print(f"Restoring: {filename}...", end=" ")
    
    try:
        # Download with proper headers
        req = urllib.request.Request(
            url,
            headers={
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            }
        )
        
        with urllib.request.urlopen(req, timeout=30) as response:
            # Read as bytes then decode as UTF-8
            content = response.read().decode('utf-8')
        
        # Remove $ signs from prices
        fixed_content = re.sub(r'\$(\d+\.?\d*)', r'\1', content)
        
        # Ensure directory exists
        os.makedirs(os.path.dirname(filepath), exist_ok=True)
        
        # Save with UTF-8 encoding
        with open(filepath, 'w', encoding='utf-8', newline='') as f:
            f.write(fixed_content)
        
        print("✓ SUCCESS")
        return True
        
    except urllib.error.HTTPError as e:
        print(f"✗ HTTP Error {e.code}")
        return False
    except urllib.error.URLError as e:
        print(f"✗ URL Error: {e.reason}")
        return False
    except Exception as e:
        print(f"✗ Error: {e}")
        return False

def main():
    print("=" * 70)
    print("RESTORING HTML FILES WITH PROPER UTF-8 ENCODING")
    print("=" * 70)
    print()
    
    success_count = 0
    failed_count = 0
    
    for filename in HTML_FILES:
        if restore_and_fix_file(filename):
            success_count += 1
        else:
            failed_count += 1
        # Small delay to avoid overwhelming the server
        time.sleep(0.5)
    
    print()
    print("=" * 70)
    print(f"SUMMARY: {success_count} files restored, {failed_count} files failed")
    print("=" * 70)
    
    if failed_count > 0:
        print("\nNote: Some files failed to download. They may not exist on the server.")
    
    print("\nFarsi text should now be restored properly!")

if __name__ == '__main__':
    main()





