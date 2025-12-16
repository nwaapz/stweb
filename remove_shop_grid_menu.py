#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Remove "فروشگاه Grid" menu item from all HTML files
"""

import os
import re
from pathlib import Path

def remove_shop_grid_menu_item(content):
    """Remove the فروشگاه Grid and فروشگاه List menu items from HTML content"""
    total_removed = 0
    
    # Remove فروشگاه Grid menu item (desktop menu with submenu)
    menu_item_grid = '<li class="menu__item menu__item--has-submenu"><a href="shop-grid-4-columns-sidebar.html" class="menu__link">فروشگاه Grid <span class="menu__arrow"><svg width="6px" height="9px"><path d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z"/></svg></span></a><div class="menu__submenu"><ul class="menu"><li class="menu__item"><a href="shop-grid-6-columns-full.html" class="menu__link">۶ ستونه تمام عرض</a></li><li class="menu__item"><a href="shop-grid-5-columns-full.html" class="menu__link">۵ ستونه تمام عرض</a></li><li class="menu__item"><a href="shop-grid-4-columns-full.html" class="menu__link">۴ ستونه تمام عرض</a></li><li class="menu__item"><a href="shop-grid-4-columns-sidebar.html" class="menu__link">۴ ستونه سایدبار</a></li><li class="menu__item"><a href="shop-grid-3-columns-sidebar.html" class="menu__link">۳ ستونه سایدبار</a></li></ul></div></li>'
    
    if menu_item_grid in content:
        content = content.replace(menu_item_grid, '')
        total_removed += 1
    
    # Remove فروشگاه List menu item (simple menu item)
    menu_item_list = '<li class="menu__item"><a href="shop-list.html" class="menu__link">فروشگاه List</a></li>'
    
    if menu_item_list in content:
        content = content.replace(menu_item_list, '')
        total_removed += 1
    
    # Remove فروشگاه Table menu item (simple menu item)
    menu_item_table = '<li class="menu__item"><a href="shop-table.html" class="menu__link">فروشگاه Table</a></li>'
    
    if menu_item_table in content:
        content = content.replace(menu_item_table, '')
        total_removed += 1
    
    # Remove فروشگاه Right Sidebar menu item (simple menu item)
    menu_item_right_sidebar = '<li class="menu__item"><a href="shop-right-sidebar.html" class="menu__link">فروشگاه Right Sidebar</a></li>'
    
    if menu_item_right_sidebar in content:
        content = content.replace(menu_item_right_sidebar, '')
        total_removed += 1
    
    # Remove Product menu item (with submenu)
    menu_item_product = '<li class="menu__item menu__item--has-submenu"><a href="product-full.html" class="menu__link">Product <span class="menu__arrow"><svg width="6px" height="9px"><path d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z"/></svg></span></a><div class="menu__submenu"><ul class="menu"><li class="menu__item"><a href="product-full.html" class="menu__link">تمام عرض</a></li><li class="menu__item"><a href="product-sidebar.html" class="menu__link">سایدبار چپ</a></li></ul></div></li>'
    
    if menu_item_product in content:
        content = content.replace(menu_item_product, '')
        total_removed += 1
    
    # Pattern 1: More flexible regex pattern for desktop menu Grid
    pattern1 = re.compile(
        r'<li\s+class="menu__item\s+menu__item--has-submenu"><a[^>]*href="shop-grid-4-columns-sidebar\.html"[^>]*class="menu__link">فروشگاه\s+Grid[^<]*<span[^>]*class="menu__arrow">.*?</span></a><div\s+class="menu__submenu"><ul\s+class="menu">.*?</ul></div></li>',
        re.DOTALL
    )
    
    # Pattern 2: More flexible regex pattern for List
    pattern2 = re.compile(
        r'<li\s+class="menu__item"><a[^>]*href="shop-list\.html"[^>]*class="menu__link">فروشگاه\s+List</a></li>',
        re.DOTALL
    )
    
    # Pattern 3: More flexible regex pattern for Table
    pattern3 = re.compile(
        r'<li\s+class="menu__item"><a[^>]*href="shop-table\.html"[^>]*class="menu__link">فروشگاه\s+Table</a></li>',
        re.DOTALL
    )
    
    # Pattern 4: More flexible regex pattern for Right Sidebar
    pattern4 = re.compile(
        r'<li\s+class="menu__item"><a[^>]*href="shop-right-sidebar\.html"[^>]*class="menu__link">فروشگاه\s+Right\s+Sidebar</a></li>',
        re.DOTALL
    )
    
    # Pattern 5: More flexible regex pattern for Product
    pattern5 = re.compile(
        r'<li\s+class="menu__item\s+menu__item--has-submenu"><a[^>]*href="product-full\.html"[^>]*class="menu__link">Product[^<]*<span[^>]*class="menu__arrow">.*?</span></a><div\s+class="menu__submenu"><ul\s+class="menu">.*?</ul></div></li>',
        re.DOTALL
    )
    
    # Pattern 6: Mobile menu - panel with title "فروشگاه Grid"
    pattern6 = re.compile(
        r'<div\s+class="mobile-menu__links-panel"[^>]*>.*?<div\s+class="mobile-menu__panel[^"]*">.*?<div\s+class="mobile-menu__panel-title">فروشگاه\s+Grid</div>.*?</div>\s*</div>',
        re.DOTALL
    )
    
    # Try regex patterns
    for pattern in [pattern1, pattern2, pattern3, pattern4, pattern5, pattern6]:
        content, count = pattern.subn('', content)
        total_removed += count
    
    return content, total_removed

def process_html_files():
    """Process all HTML files in the current directory"""
    html_files = list(Path('.').glob('*.html'))
    
    if not html_files:
        print("No HTML files found in current directory")
        return
    
    processed = 0
    for html_file in html_files:
        try:
            with open(html_file, 'r', encoding='utf-8') as f:
                content = f.read()
            
            original_content = content
            content, count = remove_shop_grid_menu_item(content)
            
            if count > 0:
                with open(html_file, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"✓ Processed: {html_file.name}")
                processed += 1
        except Exception as e:
            print(f"✗ Error processing {html_file.name}: {e}")
    
    print(f"\nTotal files processed: {processed}")

if __name__ == '__main__':
    process_html_files()

