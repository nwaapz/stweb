#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

def fix_menu(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        original = content
        
        # Translate Menu to منو
        content = content.replace('departments__button-title">Menu</span>', 'departments__button-title">منو</span>')
        content = content.replace('>Menu<', '>منو<')
        
        # Make navigation links bold - wrap Farsi text in strong tags
        nav_items = ['خانه', 'فروشگاه', 'وبلاگ', 'حساب کاربری', 'صفحات']
        for item in nav_items:
            # Pattern: <a class="main-menu__link">خانه</a> or with SVG before
            # Make sure it's not already wrapped
            if f'<strong>{item}</strong>' not in content:
                # Replace item text with bold version in main-menu__link
                pattern = f'(<a[^>]*class="main-menu__link"[^>]*>)([^<]*?)({re.escape(item)})([^<]*?)(</a>)'
                def make_bold(m):
                    return m.group(1) + m.group(2) + f'<strong>{m.group(3)}</strong>' + m.group(4) + m.group(5)
                content = re.sub(pattern, make_bold, content)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("Fixing Menu translation and navigation fonts...")
    html_files = [os.path.join(BASE_DIR, f) for f in os.listdir(BASE_DIR) if f.endswith('.html')]
    html_files.extend([os.path.join(BASE_DIR, d, f) for d in os.listdir(BASE_DIR) if os.path.isdir(os.path.join(BASE_DIR, d)) for f in os.listdir(os.path.join(BASE_DIR, d)) if f.endswith('.html')])
    
    count = 0
    for filepath in html_files:
        if os.path.exists(filepath) and fix_menu(filepath):
            count += 1
            print(f"Updated: {os.path.basename(filepath)}")
    print(f"Done: {count} files updated")

if __name__ == '__main__':
    main()



