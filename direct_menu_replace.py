#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Directly replace the menu structure using the exact HTML provided by the user
"""

import re

# The menu structure the user wants to replace (from their message)
old_menu_start = '<div class="main-menu__submenu"><ul class="menu"><li class="menu__item menu__item--has-submenu"><a href="category-4-columns-sidebar.html" class="menu__link">دسته‌بندی'

# The correct menu structure from index.html
new_menu = '''<div class="main-menu__submenu"><ul class="menu"><li class="menu__item menu__item--has-submenu" data-dynamic-categories><a href="category-4-columns-sidebar.html" class="menu__link">محصولات <span class="menu__arrow"><svg width="6px" height="9px"><path d="M0.3,7.4l3-2.9l-3-2.9c-0.4-0.3-0.4-0.9,0-1.3l0,0c0.4-0.3,0.9-0.4,1.3,0L6,4.5L1.6,8.7c-0.4,0.4-0.9,0.4-1.3,0l0,0C-0.1,8.4-0.1,7.8,0.3,7.4z"/></svg></span></a><div class="menu__submenu"><ul class="menu" data-categories-list></ul></div></li><li class="menu__item"><a href="cart.html" class="menu__link">سبد خرید</a></li><li class="menu__item"><a href="checkout.html" class="menu__link">تسویه حساب</a></li><li class="menu__item"><a href="order-success.html" class="menu__link">سفارش موفق</a></li><li class="menu__item"><a href="wishlist.html" class="menu__link">لیست علاقه‌مندی‌ها</a></li><li class="menu__item"><a href="compare.html" class="menu__link">مقایسه</a></li><li class="menu__item"><a href="track-order.html" class="menu__link">پیگیری سفارش</a></li></ul></div>'''

with open('shop-grid-4-columns-sidebar.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Try to find the menu that starts with the pattern the user mentioned
# Look for menu containing "دسته‌بندی" followed by menu items
pattern = re.compile(
    r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?دسته‌بندی.*?<li[^>]*>.*?shop-grid-4-columns-sidebar\.html.*?نمایش\s+شبکه‌ای.*?</ul></div>',
    re.DOTALL
)

match = pattern.search(content)
if match:
    old_menu = match.group(0)
    content = content.replace(old_menu, new_menu, 1)
    print("✓ Found and replaced menu using pattern with دسته‌بندی and نمایش شبکه‌ای")
    with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
        f.write(content)
    print("✓ Menu updated successfully!")
else:
    # Try a simpler pattern - just find menu with "دسته‌بندی"
    pattern2 = re.compile(
        r'<div\s+class="main-menu__submenu"><ul\s+class="menu">.*?دسته‌بندی.*?</ul></div>',
        re.DOTALL
    )
    match2 = pattern2.search(content)
    if match2:
        old_menu = match2.group(0)
        # Check if it's the فروشگاه menu by looking at context
        start = match2.start()
        context = content[max(0, start-300):start+50]
        if 'فروشگاه' in context or 'shop-grid-4-columns-sidebar.html' in context:
            content = content.replace(old_menu, new_menu, 1)
            print("✓ Found and replaced menu with دسته‌بندی")
            with open('shop-grid-4-columns-sidebar.html', 'w', encoding='utf-8') as f:
                f.write(content)
            print("✓ Menu updated successfully!")
        else:
            print("✗ Found menu with دسته‌بندی but it's not the فروشگاه menu")
    else:
        print("✗ Could not find the menu structure to replace")
        print("  The menu might already be correct, or the structure is different.")
        print("  Please check if the menu in shop-grid-4-columns-sidebar.html matches index.html")

