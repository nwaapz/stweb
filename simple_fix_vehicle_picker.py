#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple script to fix vehicle picker using string operations.
"""

import glob

def fix_file(filepath):
    """Fix vehicle picker in a file."""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Step 1: Remove the Add Vehicle button
        # Find the button with data-to-panel="form"
        btn_start = content.find('data-to-panel="form"')
        if btn_start > 0:
            # Find the start of this button tag
            btn_tag_start = content.rfind('<button', 0, btn_start)
            # Find the end of this button tag
            btn_tag_end = content.find('</button>', btn_start) + 8
            if btn_tag_start >= 0 and btn_tag_end > btn_tag_start:
                # Remove the button (including any whitespace before it)
                before = content[:btn_tag_start].rstrip()
                after = content[btn_tag_end:].lstrip()
                # Remove one space if it exists (between buttons)
                if before.endswith(' ') and after.startswith(' '):
                    before = before[:-1]
                content = before + after
        
        # Step 2: Remove the form panel
        # Find data-panel="form"
        panel_start = content.find('data-panel="form"')
        if panel_start > 0:
            # Find the start of the div tag
            div_start = content.rfind('<div', 0, panel_start)
            # Find where this panel ends - look for </div></div> before the comment or closing tags
            # The form panel ends with </div></div> followed by either:
            # - </div></div></div> (closing modal)
            # - <!-- vehicle-picker-modal / end -->
            search_from = panel_start
            end_marker1 = content.find('</div></div></div>', search_from)
            end_marker2 = content.find('<!-- vehicle-picker-modal / end -->', search_from)
            
            # Find the </div></div> that comes before these markers
            if end_marker1 > 0 or end_marker2 > 0:
                # Find the closest end marker
                end_marker = min([x for x in [end_marker1, end_marker2] if x > 0])
                # Now find the </div></div> that's just before this
                div_end = content.rfind('</div></div>', search_from, end_marker)
                if div_end > 0:
                    # Remove from div_start to div_end + 12 (length of </div></div>)
                    content = content[:div_start] + content[div_end + 12:]
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error in {filepath}: {e}")
        return False

# Process files
files = [f for f in glob.glob('*.html') 
         if f not in ['index.html', 'category-4-columns-sidebar.html', 'category-3-columns-sidebar.html']]

count = 0
for f in files:
    if fix_file(f):
        print(f"✓ {f}")
        count += 1
    else:
        # Check if it needs fixing
        with open(f, 'r', encoding='utf-8') as file:
            if 'data-panel="form"' in file.read():
                print(f"✗ {f} (needs manual fix)")

print(f"\nUpdated {count} files")

