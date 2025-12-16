#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Fix the menu in shop-grid-4-columns-sidebar.html to match index.html
"""

import re

# Read index.html to get the correct menu structure
with open('index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

# Extract the فروشگاه menu submenu from index.html
# Pattern: find the submenu that comes after "فروشگاه" main menu link
pattern = re.compile(
    r'<a[^>]*href="shop-grid-4-columns-sidebar\.html"[^>]*class="main-menu__link">فروشگاه.*?</a>\s*(<div\s+class="main-menu__submenu">.*?</div>)',
    re.DOTALL
)

match = pattern.search(index_content)
if match:
    correct_menu = match.group(1)
    print("✓ Found correct menu structure in index.html")
    print(f"Menu length: {len(correct_menu)} characters")
    
    # Now read shop-grid-4-columns-sidebar.html
    with open('shop-grid-4-columns-sidebar.html', 'r', encoding='utf-8') as f:
        shop_content = f.read()
    
    # Try to find the فروشگاه menu in shop-grid-4-columns-sidebar.html
    # Pattern 1: Find menu after "فروشگاه" link
    shop_pattern = re.compile(
        r'(<a[^>]*href="shop-grid-4-columns-sidebar\.html"[^>]*class="main-menu__link">فروشگاه.*?</a>)\s*(<div\s+class="main-menu__submenu">.*?</div>)',
        re.DOTALL
    )
    
    match2 = shop_pattern.search(shop_content)
    if match2:
        link_part = match2.group(1)
        old_submenu = match2.group(2)
        
        # Replace the old submenu with the correct one
        replacement = link_part + correct_menu
        shop_content = shop_content.replace(match2.group(0), replacement, 1)
        
        with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
            f.write(shop_content)
        
        print("✓ Successfully updated menu in shop-grid-4-columns-sidebar.html")
    else:
        print("✗ Could not find فروشگاه menu in shop-grid-4-columns-sidebar.html")
        print("  The file might have a different structure or the menu might not exist.")
else:
    print("✗ Could not find فروشگاه menu in index.html")

