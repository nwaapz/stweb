#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Restore English version - revert Farsi translation
"""
import urllib.request
import os
import re

BASE_URL = "https://red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# List of all HTML files to restore
HTML_FILES = [
    "index.html", "index2.html", "404.html", "about-us.html",
    "account-addresses.html", "account-dashboard.html", "account-edit-address.html",
    "account-garage.html", "account-login.html", "account-order-details.html",
    "account-orders.html", "account-password.html", "account-profile.html",
    "blog-classic-left-sidebar.html", "blog-classic-right-sidebar.html",
    "blog-grid-left-sidebar.html", "blog-grid-right-sidebar.html",
    "blog-list-left-sidebar.html", "blog-list-right-sidebar.html",
    "cart.html", "category-3-columns-sidebar.html", "category-4-columns-full.html",
    "category-4-columns-sidebar.html", "category-5-columns-full.html",
    "category-5-columns-sidebar.html", "category-6-columns-full.html",
    "category-7-columns-full.html", "category-right-sidebar.html",
    "checkout.html", "compare.html", "components.html",
    "contact-us-v1.html", "contact-us-v2.html", "faq.html",
    "header-classic-variant-five.html", "header-classic-variant-four.html",
    "header-classic-variant-one.html", "header-classic-variant-three.html",
    "header-classic-variant-two.html", "header-spaceship-variant-one.html",
    "header-spaceship-variant-three.html", "header-spaceship-variant-two.html",
    "mobile-header-variant-one.html", "mobile-header-variant-two.html",
    "order-success.html", "post-full-width.html", "post-left-sidebar.html",
    "post-right-sidebar.html", "post-without-image.html",
    "product-full.html", "product-sidebar.html",
    "shop-grid-3-columns-sidebar.html", "shop-grid-4-columns-full.html",
    "shop-grid-4-columns-sidebar.html", "shop-grid-5-columns-full.html",
    "shop-grid-6-columns-full.html", "shop-list.html",
    "shop-right-sidebar.html", "shop-table.html",
    "terms.html", "track-order.html", "typography.html", "wishlist.html"
]

def restore_file(filename):
    """Download and restore original English file, then apply logo change and remove $"""
    url = BASE_URL + filename
    filepath = os.path.join(BASE_DIR, filename)
    
    try:
        # Download original
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=30) as response:
            content = response.read().decode('utf-8')
        
        # Apply only the logo change
        logo_pattern = r'<div class="logo__image">[\s\S]*?</svg>[\s\S]*?</div>'
        new_logo = '<div class="logo__image"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></div>'
        content = re.sub(logo_pattern, new_logo, content)
        
        # Remove dollar signs
        content = re.sub(r'\$(\d+\.?\d*)', r'\1', content)
        
        # Save with UTF-8
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        return True
    except Exception as e:
        print(f"Error restoring {filename}: {e}")
        return False

def main():
    print("=" * 70)
    print("RESTORING ORIGINAL ENGLISH VERSION")
    print("=" * 70)
    print()
    
    success = 0
    failed = 0
    
    for filename in HTML_FILES:
        print(f"Restoring: {filename}...", end=" ")
        if restore_file(filename):
            print("✓")
            success += 1
        else:
            print("✗")
            failed += 1
    
    print()
    print("=" * 70)
    print(f"RESTORATION COMPLETE: {success} restored, {failed} failed")
    print("=" * 70)

if __name__ == '__main__':
    main()



