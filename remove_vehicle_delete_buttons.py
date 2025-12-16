#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Remove vehicle delete buttons from all HTML files.
"""

import glob
import re

def remove_delete_buttons(file_path):
    """Remove vehicle delete buttons from HTML file."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Check if file has the delete button
        if 'vehicles-list__item-remove' not in content:
            return False
        
        # Pattern 1: Exact match for minified HTML
        pattern1 = r'<button type="button" class="vehicles-list__item-remove"><svg width="16" height="16"><path d="M2,4V2h3V1h6v1h3v2H2z M13,13c0,1.1-0.9,2-2,2H5c-1.1,0-2-0.9-2-2V5h10V13z"/></svg></button>'
        content = re.sub(pattern1, '', content)
        
        # Pattern 2: More flexible pattern
        if content == original:
            pattern2 = r'<button[^>]*class="vehicles-list__item-remove"[^>]*>.*?</button>'
            content = re.sub(pattern2, '', content, flags=re.DOTALL)
        
        if content != original:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"  Error in {file_path}: {e}")
        return False

# Get all HTML files
html_files = [f for f in glob.glob('*.html') if not f.endswith('.py')]

print(f"Removing delete buttons from {len(html_files)} HTML files...\n")
updated = 0
skipped = []

for f in html_files:
    if remove_delete_buttons(f):
        print(f"✓ {f}")
        updated += 1
    else:
        # Check if it needs updating
        with open(f, 'r', encoding='utf-8') as check:
            if 'vehicles-list__item-remove' in check.read():
                skipped.append(f)

print(f"\n{'='*50}")
print(f"Updated {updated} files")
if skipped:
    print(f"Files that still need updating: {len(skipped)}")
    if len(skipped) <= 5:
        for f in skipped:
            print(f"  - {f}")

