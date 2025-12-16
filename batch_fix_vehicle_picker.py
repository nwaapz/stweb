#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Batch fix vehicle picker in all HTML files using the working pattern.
"""

import re
import glob

# The pattern that works - we'll use a flexible regex version
def fix_vehicle_picker(content):
    """Remove Add Vehicle button and form panel."""
    original = content
    
    # Pattern 1: Remove Add Vehicle button (flexible)
    content = re.sub(
        r'<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>',
        '',
        content
    )
    
    # Pattern 2: Remove form panel - find from data-panel="form" to the closing divs
    # Match: <div...data-panel="form">...everything until...</div></div> before closing tags
    # Use non-greedy match with lookahead
    pattern = r'<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>.*?</div></div>(?=\s*(?:</div></div></div>|<!-- vehicle-picker-modal / end -->))'
    content = re.sub(pattern, '', content, flags=re.DOTALL)
    
    return content, content != original

# Get all HTML files
files = [f for f in glob.glob('*.html') 
         if f not in ['index.html', 'category-4-columns-sidebar.html', 'category-3-columns-sidebar.html']]

updated = 0
for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        
        new_content, changed = fix_vehicle_picker(content)
        
        if changed:
            with open(f, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f"✓ {f}")
            updated += 1
        elif 'data-panel="form"' in content:
            print(f"? {f} (pattern not matched)")
    except Exception as e:
        print(f"✗ {f}: {e}")

print(f"\nUpdated {updated} files")

