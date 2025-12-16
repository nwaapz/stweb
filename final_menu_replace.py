#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Final script to copy the menu structure from index.html to shop-grid-4-columns-sidebar.html
"""

import re

# Read both files
with open('index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

with open('shop-grid-4-columns-sidebar.html', 'r', encoding='utf-8') as f:
    shop_content = f.read()

# Extract the header__navbar-menu section from index.html
# This pattern finds from header__navbar-menu to the closing divs
index_pattern = re.compile(
    r'(<div\s+class="header__navbar-menu">.*?</div></div></div>)',
    re.DOTALL
)

index_match = index_pattern.search(index_content)
if index_match:
    correct_menu = index_match.group(1)
    print(f"✓ Found menu section in index.html ({len(correct_menu)} chars)")
    
    # Find the same section in shop-grid-4-columns-sidebar.html
    shop_pattern = re.compile(
        r'(<div\s+class="header__navbar-menu">.*?</div></div></div>)',
        re.DOTALL
    )
    
    shop_match = shop_pattern.search(shop_content)
    if shop_match:
        old_menu = shop_match.group(1)
        shop_content = shop_content.replace(old_menu, correct_menu, 1)
        
        with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
            f.write(shop_content)
        
        print("✓ Successfully replaced menu section in shop-grid-4-columns-sidebar.html")
        print("  The menu now matches index.html")
    else:
        print("✗ Could not find header__navbar-menu in shop-grid-4-columns-sidebar.html")
        print("  Trying to find header__navbar section...")
        
        # Try finding just the header__navbar section
        navbar_pattern = re.compile(
            r'(<div\s+class="header__navbar">.*?</div></div></div>)',
            re.DOTALL
        )
        
        navbar_match = navbar_pattern.search(shop_content)
        if navbar_match:
            # Extract the navbar-menu part from index
            index_navbar_pattern = re.compile(
                r'(<div\s+class="header__navbar">.*?</div></div></div>)',
                re.DOTALL
            )
            index_navbar_match = index_navbar_pattern.search(index_content)
            if index_navbar_match:
                correct_navbar = index_navbar_match.group(1)
                old_navbar = navbar_match.group(1)
                shop_content = shop_content.replace(old_navbar, correct_navbar, 1)
                
                with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
                    f.write(shop_content)
                
                print("✓ Successfully replaced header__navbar section")
        else:
            print("✗ Could not find header__navbar section either")
else:
    print("✗ Could not find header__navbar-menu in index.html")

