#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
1. Translate "Menu" to Farsi "منو"
2. Make Farsi navigation links bold with better font
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

def improve_menu_farsi(filepath):
    """Translate Menu and improve Farsi navigation fonts"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # 1. Translate "Menu" to "منو"
        content = content.replace('>Menu<', '>منو<')
        content = content.replace('departments__button-title">Menu</span>', 'departments__button-title">منو</span>')
        content = content.replace('<span class="departments__button-title">Menu</span>', '<span class="departments__button-title">منو</span>')
        
        # 2. Make Farsi navigation links bold with better font
        # Find main menu links and add inline styles or classes
        farsi_nav_items = ['خانه', 'فروشگاه', 'وبلاگ', 'حساب کاربری', 'صفحات']
        
        for item in farsi_nav_items:
            # Pattern: <a href="..." class="main-menu__link">خانه</a>
            pattern = f'(<a[^>]*class="main-menu__link"[^>]*>)({re.escape(item)})(</a>)'
            replacement = r'\1<strong style="font-weight: 700; font-family: \'Vazir\', \'Tahoma\', \'Arial\', sans-serif;">\2</strong>\3'
            content = re.sub(pattern, replacement, content)
            
            # Also handle if there's SVG or other content inside
            pattern2 = f'(<a[^>]*class="main-menu__link"[^>]*>)([^<]*?)({re.escape(item)})([^<]*?)(</a>)'
            replacement2 = r'\1\2<strong style="font-weight: 700; font-family: \'Vazir\', \'Tahoma\', \'Arial\', sans-serif;">\3</strong>\4\5'
            content = re.sub(pattern2, replacement2, content)
        
        # 3. Add Vazir font to head if not present (better Farsi font for e-commerce)
        if 'fonts.googleapis.com/css?family=Vazir' not in content:
            # Find the head section and add Vazir font
            if '<head>' in content or '<head ' in content:
                # Add Vazir font link after other font links
                font_link = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazir:wght@400;500;700&display=swap">'
                # Insert after existing font links or in head
                if 'fonts.googleapis.com' in content:
                    # Insert after last font link
                    content = re.sub(
                        r'(<link[^>]*fonts\.googleapis\.com[^>]*>)',
                        r'\1\n' + font_link,
                        content,
                        count=1
                    )
                else:
                    # Insert in head section
                    content = re.sub(
                        r'(<head[^>]*>)',
                        r'\1\n' + font_link,
                        content
                    )
        
        # 4. Add CSS for better Farsi navigation styling
        # Add style tag if not exists or update existing
        css_style = '''
<style>
.main-menu__link strong {
    font-weight: 700 !important;
    font-family: 'Vazir', 'Tahoma', 'Arial', sans-serif !important;
    letter-spacing: 0;
}
.main-menu__link {
    font-family: 'Vazir', 'Tahoma', 'Arial', sans-serif;
}
.departments__button-title {
    font-family: 'Vazir', 'Tahoma', 'Arial', sans-serif;
    font-weight: 600;
}
</style>
'''
        
        # Add style before closing head tag
        if '</head>' in content and css_style.strip() not in content:
            content = content.replace('</head>', css_style + '\n</head>')
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("=" * 70)
    print("IMPROVING MENU: Translate to Farsi & Better Fonts")
    print("=" * 70)
    print()
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    print()
    
    updated_count = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if improve_menu_farsi(filepath):
            print("✓")
            updated_count += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"Complete: Updated {updated_count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()



