#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Update support text in topbar from "تماس با ما: 09360590157" to "شماره پشتیبانی : 09360590157"
"""
import os

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
OLD_TEXT = "تماس با ما: 09360590157"
NEW_TEXT = "شماره پشتیبانی : 09360590157"

def update_support_text(filepath):
    """Update the support text in topbar"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if OLD_TEXT in content:
            content = content.replace(OLD_TEXT, NEW_TEXT)
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("=" * 70)
    print("UPDATING SUPPORT TEXT IN TOPBAR")
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
        if update_support_text(filepath):
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



