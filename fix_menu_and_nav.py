#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

def fix_all(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        original = content
        
        # 1. Translate Menu to منو
        content = content.replace('departments__button-title">Menu</span>', 'departments__button-title">منو</span>')
        content = content.replace('departments__button-title">Menu<', 'departments__button-title">منو<')
        content = content.replace('>Menu<', '>منو<')
        
        # 2. Make navigation links bold - wrap Farsi text in strong tags
        # Pattern: <a class="main-menu__link">خانه</a> or with SVG
        nav_items = ['خانه', 'فروشگاه', 'وبلاگ', 'حساب کاربری', 'صفحات']
        
        for item in nav_items:
            # Skip if already wrapped
            if f'<strong>{item}</strong>' in content:
                continue
                
            # Pattern 1: Simple - just text in link
            # <a href="..." class="main-menu__link">خانه</a>
            pattern1 = f'(<a[^>]*class="main-menu__link"[^>]*>)({re.escape(item)})(</a>)'
            content = re.sub(pattern1, r'\1<strong>\2</strong>\3', content)
            
            # Pattern 2: Text followed by SVG
            # <a class="main-menu__link">خانه <svg...</a>
            pattern2 = f'(<a[^>]*class="main-menu__link"[^>]*>)({re.escape(item)})(\\s*<svg)'
            content = re.sub(pattern2, r'\1<strong>\2</strong>\3', content)
            
            # Pattern 3: SVG before text
            # <a class="main-menu__link"><svg...>خانه</a>
            pattern3 = f'(<a[^>]*class="main-menu__link"[^>]*>)([^<]*<svg[^>]*>[^<]*</svg>\\s*)({re.escape(item)})'
            content = re.sub(pattern3, r'\1\2<strong>\3</strong>', content)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

# Process all HTML files
html_files = []
for root, dirs, files in os.walk(BASE_DIR):
    for file in files:
        if file.endswith('.html'):
            html_files.append(os.path.join(root, file))

print(f"Processing {len(html_files)} files...")
count = 0
for f in html_files:
    if fix_all(f):
        count += 1
        print(f"Updated: {os.path.basename(f)}")

print(f"\nDone: {count} files updated")





