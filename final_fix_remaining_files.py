#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Final script to fix remaining HTML files - uses exact string replacement.
"""

import glob
import re

# Files already fixed
fixed_files = {'index.html', 'category-4-columns-sidebar.html', 'category-3-columns-sidebar.html'}

def fix_file(filepath):
    """Fix vehicle picker using exact pattern matching."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Remove Add Vehicle button - find the exact pattern
        # Pattern: <button...data-to-panel="form"...>افزودن وسیله نقلیه</button>
        content = re.sub(
            r'<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>',
            '',
            content
        )
        
        # Remove form panel - find from data-panel="form" to closing divs
        # The pattern is: <div...data-panel="form">...content...</div></div>
        # We need to match until we hit </div></div> that's followed by closing tags or comment
        
        # Find the start
        start_idx = content.find('data-panel="form"')
        if start_idx > 0:
            # Find the opening <div tag
            div_start = content.rfind('<div', 0, start_idx)
            
            # Find the end - look for </div></div> followed by </div></div></div> or comment
            search_from = start_idx
            end1 = content.find('</div></div></div>', search_from)
            end2 = content.find('<!-- vehicle-picker-modal / end -->', search_from)
            
            if end1 > 0 or end2 > 0:
                # Find the </div></div> just before the end marker
                end_marker = min([x for x in [end1, end2] if x > 0])
                div_end = content.rfind('</div></div>', search_from, end_marker)
                
                if div_end > 0 and div_start >= 0:
                    # Remove from div_start to div_end + 12 (length of </div></div>)
                    content = content[:div_start] + content[div_end + 12:]
        
        # Clean up any double spaces
        content = content.replace('  ', ' ')
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"  Error: {e}")
        return False

# Get all HTML files
all_files = glob.glob('*.html')
files_to_fix = [f for f in all_files if f not in fixed_files and not f.endswith('.py')]

print(f"Fixing {len(files_to_fix)} files...\n")
updated = 0
failed = []

for f in files_to_fix:
    try:
        # Check if it needs fixing first
        with open(f, 'r', encoding='utf-8') as check:
            has_form = 'data-panel="form"' in check.read()
        
        if has_form:
            if fix_file(f):
                print(f"✓ {f}")
                updated += 1
            else:
                failed.append(f)
                print(f"✗ {f} (fix failed)")
    except Exception as e:
        print(f"✗ {f}: {e}")

print(f"\n{'='*50}")
print(f"Updated {updated} out of {len(files_to_fix)} files")
if failed:
    print(f"Failed: {len(failed)} files")

