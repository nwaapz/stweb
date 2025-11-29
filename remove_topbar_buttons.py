#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Remove Compare, Currency, and Language buttons from top right
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

def remove_topbar_buttons(filepath):
    """Remove the three buttons from topbar"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Remove Compare button - simple div with مقایسه or Compare
        # Pattern: <div class="topbar__item-button"><a...>مقایسه:...</a></div>
        compare_pattern = r'<div class="topbar__item-button"><a[^>]*class="topbar__button"[^>]*><span[^>]*>مقایسه:</span>[^<]*<span[^>]*>[^<]*</span></a></div>'
        content = re.sub(compare_pattern, '', content)
        compare_pattern_en = r'<div class="topbar__item-button"><a[^>]*class="topbar__button"[^>]*><span[^>]*>Compare:</span>[^<]*<span[^>]*>[^<]*</span></a></div>'
        content = re.sub(compare_pattern_en, '', content)
        
        # Remove Currency button with menu - need to match the entire structure including menu-body
        # This is tricky because the menu-body contains multiple items
        # Let's match from the div start to the closing </div> after menu-body
        currency_pattern = r'<div class="topbar__item-button topbar__menu"><button[^>]*class="topbar__button[^"]*topbar__menu-button"[^>]*><span[^>]*>واحد پول:</span>[^<]*<span[^>]*>[^<]*</span>[^<]*<span[^>]*>[^<]*</span></button><div class="topbar__menu-body">[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a></div></div>'
        content = re.sub(currency_pattern, '', content)
        currency_pattern_en = r'<div class="topbar__item-button topbar__menu"><button[^>]*class="topbar__button[^"]*topbar__menu-button"[^>]*><span[^>]*>Currency:</span>[^<]*<span[^>]*>[^<]*</span>[^<]*<span[^>]*>[^<]*</span></button><div class="topbar__menu-body">[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a></div></div>'
        content = re.sub(currency_pattern_en, '', content)
        
        # Remove Language button with menu
        language_pattern = r'<div class="topbar__menu"><button[^>]*class="topbar__button[^"]*topbar__menu-button"[^>]*><span[^>]*>زبان:</span>[^<]*<span[^>]*>[^<]*</span>[^<]*<span[^>]*>[^<]*</span></button><div class="topbar__menu-body">[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a></div></div>'
        content = re.sub(language_pattern, '', content)
        language_pattern_en = r'<div class="topbar__menu"><button[^>]*class="topbar__button[^"]*topbar__menu-button"[^>]*><span[^>]*>Language:</span>[^<]*<span[^>]*>[^<]*</span>[^<]*<span[^>]*>[^<]*</span></button><div class="topbar__menu-body">[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a>[^<]*<a[^>]*>[^<]*</a></div></div>'
        content = re.sub(language_pattern_en, '', content)
        
        # More flexible approach - find the topbar--spaceship-end section and remove these specific buttons
        # Match the entire topbar__item-button or topbar__menu divs that contain these labels
        # Using non-greedy matching with lookahead to find the closing div
        
        # Try a different approach - find patterns that include the button and its menu
        patterns_to_remove = [
            # Compare button
            r'<div class="topbar__item-button"><a[^>]*>.*?مقایسه:.*?</a></div>',
            r'<div class="topbar__item-button"><a[^>]*>.*?Compare:.*?</a></div>',
            # Currency button with menu
            r'<div class="topbar__item-button topbar__menu">.*?واحد پول:.*?topbar__menu-body.*?</div></div>',
            r'<div class="topbar__item-button topbar__menu">.*?Currency:.*?topbar__menu-body.*?</div></div>',
            # Language button with menu  
            r'<div class="topbar__menu">.*?زبان:.*?topbar__menu-body.*?</div></div>',
            r'<div class="topbar__menu">.*?Language:.*?topbar__menu-body.*?</div></div>',
        ]
        
        for pattern in patterns_to_remove:
            content = re.sub(pattern, '', content, flags=re.DOTALL)
        
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
    print("REMOVING TOPBAR BUTTONS (Compare, Currency, Language)")
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
        if remove_topbar_buttons(filepath):
            print("✓")
            removed_count += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"Complete: Updated {removed_count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()
