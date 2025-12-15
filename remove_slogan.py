#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Remove the slogan text from all HTML files
"""
import os

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
TEXT_TO_REMOVE = "قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها"

def remove_slogan(filepath):
    """Remove the slogan text from a file"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if TEXT_TO_REMOVE in content:
            # Remove the text and any surrounding div/span tags if it's in a tag
            content = content.replace(TEXT_TO_REMOVE, '')
            
            # Also try to remove the div tag if it's in a logo__slogan div
            import re
            # Pattern to match div with class logo__slogan containing the text
            pattern = r'<div[^>]*class="logo__slogan"[^>]*>[\s]*' + re.escape(TEXT_TO_REMOVE) + r'[\s]*</div>'
            content = re.sub(pattern, '', content)
            
            # Also try without the class attribute
            pattern2 = r'<div[^>]*>[\s]*' + re.escape(TEXT_TO_REMOVE) + r'[\s]*</div>'
            content = re.sub(pattern2, '', content)
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("=" * 70)
    print("REMOVING SLOGAN TEXT")
    print("=" * 70)
    print()
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    print()
    
    removed_count = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if remove_slogan(filepath):
            print("✓")
            removed_count += 1
        else:
            print("(not found)")
    
    print()
    print("=" * 70)
    print(f"Complete: Removed from {removed_count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()





