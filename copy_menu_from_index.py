#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Copy the menu structure from index.html to shop-grid-4-columns-sidebar.html
"""

import re

# Read both files
with open('index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

with open('shop-grid-4-columns-sidebar.html', 'r', encoding='utf-8') as f:
    shop_content = f.read()

# Extract the entire header__navbar-menu section from index.html
# This includes the main menu with all items
index_menu_pattern = re.compile(
    r'(<div\s+class="header__navbar-menu">.*?</div></div></div>)',
    re.DOTALL
)

index_match = index_menu_pattern.search(index_content)
if index_match:
    correct_menu_section = index_match.group(1)
    print(f"✓ Found menu section in index.html ({len(correct_menu_section)} chars)")
    
    # Find the same section in shop-grid-4-columns-sidebar.html
    shop_menu_pattern = re.compile(
        r'(<div\s+class="header__navbar-menu">.*?</div></div></div>)',
        re.DOTALL
    )
    
    shop_match = shop_menu_pattern.search(shop_content)
    if shop_match:
        old_menu_section = shop_match.group(1)
        shop_content = shop_content.replace(old_menu_section, correct_menu_section, 1)
        
        with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
            f.write(shop_content)
        
        print("✓ Successfully replaced menu section in shop-grid-4-columns-sidebar.html")
    else:
        print("✗ Could not find header__navbar-menu in shop-grid-4-columns-sidebar.html")
        print("  The file might have a different structure.")
else:
    print("✗ Could not find header__navbar-menu in index.html")

