#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

def update_menu(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        original = content
        
        # 1. Translate Menu to منو
        content = content.replace('departments__button-title">Menu</span>', 'departments__button-title">منو</span>')
        content = content.replace('>Menu<', '>منو<')
        content = content.replace('departments__button-title">Menu<', 'departments__button-title">منو<')
        
        # 2. Make main navigation links bold - find patterns like:
        # <a href="index.html" class="main-menu__link">خانه</a>
        # or with SVG: <a href="..." class="main-menu__link">خانه <svg...</a>
        
        nav_items = ['خانه', 'فروشگاه', 'وبلاگ', 'حساب کاربری', 'صفحات']
        for item in nav_items:
            # Pattern 1: Simple link with just text
            pattern1 = f'(<a[^>]*class="main-menu__link"[^>]*>)({re.escape(item)})(</a>)'
            if re.search(pattern1, content) and f'<strong>{item}</strong>' not in content:
                content = re.sub(pattern1, r'\1<strong>\2</strong>\3', content)
            
            # Pattern 2: Link with SVG or other content after text
            pattern2 = f'(<a[^>]*class="main-menu__link"[^>]*>)({re.escape(item)})(\\s*<svg)'
            if re.search(pattern2, content):
                content = re.sub(pattern2, r'\1<strong>\2</strong>\3', content)
            
            # Pattern 3: Link with SVG before text
            pattern3 = f'(<a[^>]*class="main-menu__link"[^>]*>)([^<]*<svg[^>]*>[^<]*</svg>\\s*)({re.escape(item)})'
            if re.search(pattern3, content):
                content = re.sub(pattern3, r'\1\2<strong>\3</strong>', content)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error in {filepath}: {e}")
        return False

# Get all HTML files
html_files = []
for root, dirs, files in os.walk(BASE_DIR):
    for file in files:
        if file.endswith('.html'):
            html_files.append(os.path.join(root, file))

print(f"Processing {len(html_files)} files...")
count = 0
for f in html_files:
    if update_menu(f):
        count += 1
        print(f"Updated: {os.path.basename(f)}")

print(f"\nDone: {count} files updated")





