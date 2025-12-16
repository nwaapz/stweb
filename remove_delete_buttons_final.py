#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Remove vehicle delete buttons from all HTML files.
"""

import glob

# The exact button HTML to remove
DELETE_BUTTON = '<button type="button" class="vehicles-list__item-remove"><svg width="16" height="16"><path d="M2,4V2h3V1h6v1h3v2H2z M13,13c0,1.1-0.9,2-2,2H5c-1.1,0-2-0.9-2-2V5h10V13z"/></svg></button>'

def remove_delete_buttons(file_path):
    """Remove vehicle delete buttons from HTML file."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if DELETE_BUTTON not in content:
            return False
        
        # Simple string replacement
        content = content.replace(DELETE_BUTTON, '')
        
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(content)
        return True
    except Exception as e:
        print(f"  Error in {file_path}: {e}")
        return False

# Get all HTML files
html_files = [f for f in glob.glob('*.html') if not f.endswith('.py')]

print(f"Removing delete buttons from {len(html_files)} HTML files...\n")
updated = 0
needs_fix = []

for f in html_files:
    # Skip index.html as it's already fixed
    if f == 'index.html':
        continue
    if remove_delete_buttons(f):
        print(f"✓ {f}")
        updated += 1
    else:
        # Check if it needs fixing
        try:
            with open(f, 'r', encoding='utf-8') as check:
                if DELETE_BUTTON in check.read():
                    needs_fix.append(f)
        except:
            pass

print(f"\n{'='*50}")
print(f"Updated {updated} files")
if needs_fix:
    print(f"Files that still need fixing: {len(needs_fix)}")
    for f in needs_fix[:10]:
        print(f"  - {f}")

