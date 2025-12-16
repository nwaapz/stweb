#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Replace the menu structure in shop-grid-4-columns-sidebar.html to match index.html
"""

import re
from pathlib import Path

# The desired menu structure from index.html
new_menu = '''<div class="main-menu__submenu"><ul class="menu"><li class="menu__item menu__item--has-submenu" data-dynamic-categories><a href="category-4-columns-sidebar.html" class="menu__link">محصولات <span class="menu__arrow"><svg width="6px" height="9px"><path d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z"/></svg></span></a><div class="menu__submenu"><ul class="menu" data-categories-list></ul></div></li><li class="menu__item"><a href="cart.html" class="menu__link">سبد خرید</a></li><li class="menu__item"><a href="checkout.html" class="menu__link">تسویه حساب</a></li><li class="menu__item"><a href="order-success.html" class="menu__link">سفارش موفق</a></li><li class="menu__item"><a href="wishlist.html" class="menu__link">لیست علاقه‌مندی‌ها</a></li><li class="menu__item"><a href="compare.html" class="menu__link">مقایسه</a></li><li class="menu__item"><a href="track-order.html" class="menu__link">پیگیری سفارش</a></li></ul></div>'''

def replace_menu(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original_content = content
    
    # Pattern 1: Find menu that contains "دسته‌بندی" (the old structure user mentioned)
    pattern1 = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?دسته‌بندی.*?</ul></div>',
        re.DOTALL
    )
    
    match = pattern1.search(content)
    if match:
        old_menu = match.group(0)
        # Verify it's the فروشگاه menu by checking context
        start = match.start()
        context_start = max(0, start - 200)
        context = content[context_start:start + 100]
        if 'فروشگاه' in context or 'shop-grid-4-columns-sidebar.html' in context:
            content = content.replace(old_menu, new_menu, 1)
            print(f"✓ Found and replaced menu with 'دسته‌بندی' pattern")
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
    
    # Pattern 2: Find menu that contains "نمایش شبکه‌ای" (another item from old structure)
    pattern2 = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?نمایش\s+شبکه‌ای.*?</ul></div>',
        re.DOTALL
    )
    
    match = pattern2.search(content)
    if match:
        old_menu = match.group(0)
        start = match.start()
        context_start = max(0, start - 200)
        context = content[context_start:start + 100]
        if 'فروشگاه' in context or 'shop-grid-4-columns-sidebar.html' in context:
            content = content.replace(old_menu, new_menu, 1)
            print(f"✓ Found and replaced menu with 'نمایش شبکه‌ای' pattern")
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
    
    # Pattern 3: Find menu that comes after "فروشگاه" main menu link
    pattern3 = re.compile(
        r'(<a[^>]*href="shop-grid-4-columns-sidebar\.html"[^>]*class="main-menu__link">فروشگاه[^<]*</a>)\s*(<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?</ul></div>)',
        re.DOTALL
    )
    
    match = pattern3.search(content)
    if match:
        link_part = match.group(1)
        old_submenu = match.group(2)
        replacement = link_part + '\n' + new_menu
        content = content.replace(match.group(0), replacement, 1)
        print(f"✓ Found and replaced menu after 'فروشگاه' link")
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    
    # Pattern 4: Find any main-menu__submenu and check if it needs replacement
    # Look for menus that have items like "shop-list", "shop-table", "product-full" etc.
    pattern4 = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?(?:shop-list|shop-table|shop-right-sidebar|product-full).*?</ul></div>',
        re.DOTALL
    )
    
    match = pattern4.search(content)
    if match:
        old_menu = match.group(0)
        start = match.start()
        context_start = max(0, start - 300)
        context = content[context_start:start + 50]
        if 'فروشگاه' in context:
            content = content.replace(old_menu, new_menu, 1)
            print(f"✓ Found and replaced menu containing shop/product links")
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
    
    print(f"✗ Could not find menu structure to replace")
    print(f"  The file might already have the correct menu, or the structure is different.")
    return False

if __name__ == '__main__':
    file_path = Path('shop-grid-4-columns-sidebar.html')
    if file_path.exists():
        success = replace_menu(file_path)
        if success:
            print(f"\n✓ Menu updated successfully!")
        else:
            print(f"\n✗ Menu structure not found or already correct.")
    else:
        print(f"File {file_path} not found")

