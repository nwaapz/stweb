#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Update the menu in shop-grid-4-columns-sidebar.html to match index.html
"""

import re
from pathlib import Path

def update_menu_in_file(file_path):
    """Update the فروشگاه menu submenu to match index.html"""
    
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # The menu structure from index.html (current/desired structure)
    new_menu_structure = '''<div class="main-menu__submenu"><ul class="menu"><li class="menu__item menu__item--has-submenu" data-dynamic-categories><a href="category-4-columns-sidebar.html" class="menu__link">محصولات <span class="menu__arrow"><svg width="6px" height="9px"><path d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z"/></svg></span></a><div class="menu__submenu"><ul class="menu" data-categories-list></ul></div></li><li class="menu__item"><a href="cart.html" class="menu__link">سبد خرید</a></li><li class="menu__item"><a href="checkout.html" class="menu__link">تسویه حساب</a></li><li class="menu__item"><a href="order-success.html" class="menu__link">سفارش موفق</a></li><li class="menu__item"><a href="wishlist.html" class="menu__link">لیست علاقه‌مندی‌ها</a></li><li class="menu__item"><a href="compare.html" class="menu__link">مقایسه</a></li><li class="menu__item"><a href="track-order.html" class="menu__link">پیگیری سفارش</a></li></ul></div>'''
    
    # Pattern to find the main-menu__submenu for فروشگاه
    # This pattern finds the submenu that comes after the فروشگاه main menu item
    pattern = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?</ul></div>',
        re.DOTALL
    )
    
    # Find all matches
    matches = list(pattern.finditer(content))
    
    # We need to find the one that's under the فروشگاه menu item
    # Look for the pattern that comes after "فروشگاه" main menu link
    shop_menu_pattern = re.compile(
        r'<a\s+href="shop-grid-4-columns-sidebar\.html"[^>]*class="main-menu__link">فروشگاه[^<]*<svg[^>]*>.*?</svg></a>\s*<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?</ul></div>',
        re.DOTALL
    )
    
    match = shop_menu_pattern.search(content)
    if match:
        # Extract the full match
        full_match = match.group(0)
        
        # Replace the submenu part
        old_submenu = re.search(r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?</ul></div>', full_match, re.DOTALL).group(0)
        new_full_match = full_match.replace(old_submenu, new_menu_structure)
        content = content.replace(full_match, new_full_match, 1)
        
        print(f"✓ Updated menu in {file_path.name}")
        return content, True
    
    # Alternative: try to find any main-menu__submenu that contains the old menu items
    # Pattern for the old menu structure (the one user wants to replace)
    old_menu_pattern = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?<li[^>]*class="menu__item[^"]*menu__item--has-submenu[^"]*"[^>]*><a[^>]*href="category-4-columns-sidebar\.html"[^>]*class="menu__link">دسته‌بندی.*?</ul></div>',
        re.DOTALL
    )
    
    match = old_menu_pattern.search(content)
    if match:
        old_menu = match.group(0)
        # Find where this menu is (should be after فروشگاه main menu link)
        # Replace the entire submenu
        content = content.replace(old_menu, new_menu_structure, 1)
        print(f"✓ Updated menu in {file_path.name}")
        return content, True
    
    # Try a more flexible pattern - find menu with "دسته‌بندی" or "نمایش شبکه‌ای"
    flexible_pattern = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?(?:دسته‌بندی|نمایش\s+شبکه‌ای|نمایش\s+لیستی|نمایش\s+جدولی|فروشگاه\s+با\s+نوار\s+کناری\s+راست|محصول).*?</ul></div>',
        re.DOTALL
    )
    
    match = flexible_pattern.search(content)
    if match:
        # Check if this is the فروشگاه menu by looking at context
        start_pos = match.start()
        # Look backwards to see if there's a فروشگاه link before this
        context_start = max(0, start_pos - 500)
        context = content[context_start:start_pos + len(match.group(0))]
        
        if 'فروشگاه' in context and 'main-menu__link' in context:
            old_menu = match.group(0)
            content = content.replace(old_menu, new_menu_structure, 1)
            print(f"✓ Updated menu in {file_path.name}")
            return content, True
    
    print(f"✗ Could not find menu structure to replace in {file_path.name}")
    return content, False

if __name__ == '__main__':
    file_path = Path('shop-grid-4-columns-sidebar.html')
    if file_path.exists():
        content, updated = update_menu_in_file(file_path)
        if updated:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"\nMenu updated successfully in {file_path.name}")
        else:
            print(f"\nMenu structure not found. The file might already have the correct menu or use a different structure.")
    else:
        print(f"File {file_path} not found")

