#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Add سیستم گارانتی button to topbar in the same place and style
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
BUTTON_TEXT = "سیستم گارانتی"
BUTTON_HTML = f'<div class="topbar__item-button"><a href="#" class="topbar__button"><span class="topbar__button-label">{BUTTON_TEXT}</span></a></div>'

def add_warranty_button(filepath):
    """Add warranty button to topbar"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Check if button already exists
        if BUTTON_TEXT in content:
            return False
        
        # Find the empty topbar--spaceship-end div and add button inside it
        # Pattern: <div class="topbar topbar--spaceship-end"></div>
        pattern = r'<div class="topbar topbar--spaceship-end"></div>'
        
        if re.search(pattern, content):
            # Replace empty div with div containing the button
            replacement = f'<div class="topbar topbar--spaceship-end">{BUTTON_HTML}</div>'
            content = re.sub(pattern, replacement, content)
        else:
            # Try to find topbar--spaceship-end with any content and add button
            pattern_with_content = r'(<div class="topbar topbar--spaceship-end">)(.*?)(</div>)'
            def add_button(match):
                existing = match.group(2)
                # If empty or just whitespace, add button
                if not existing.strip():
                    return f'{match.group(1)}{BUTTON_HTML}{match.group(3)}'
                # If has content, add button before existing content
                return f'{match.group(1)}{BUTTON_HTML}{existing}{match.group(3)}'
            content = re.sub(pattern_with_content, add_button, content, flags=re.DOTALL)
        
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
    print("ADDING سیستم گارانتی BUTTON TO TOPBAR")
    print("=" * 70)
    print()
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    print()
    
    added_count = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if add_warranty_button(filepath):
            print("✓")
            added_count += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"Complete: Updated {added_count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()
