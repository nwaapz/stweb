#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to simplify vehicle picker modal in all HTML files.
"""

import re
import glob

def fix_file(file_path):
    """Remove form panel and Add Vehicle button."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Step 1: Remove the "Add Vehicle" button
        content = re.sub(
            r'\s*<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>',
            '',
            content
        )
        
        # Step 2: Find and remove the form panel
        # Find the start marker
        start_pattern = r'<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>'
        start_match = re.search(start_pattern, content)
        
        if start_match:
            # Find the end marker - look for </div></div> followed by </div></div></div> or comment
            # The form panel ends with: </div></div> before the closing tags
            remaining = content[start_match.end():]
            
            # Find the pattern that ends the form panel
            # It should be: </div></div> followed by either:
            # - </div></div></div> (closing modal structure)
            # - <!-- vehicle-picker-modal / end -->
            end_pattern = r'</div></div>(?=\s*(?:</div></div></div>|<!-- vehicle-picker-modal / end -->))'
            end_match = re.search(end_pattern, remaining)
            
            if end_match:
                # Remove from start to end
                start_pos = start_match.start()
                end_pos = start_match.end() + end_match.end()
                content = content[:start_pos] + content[end_pos:]
        
        if content != original:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
        
    except Exception as e:
        print(f"Error in {file_path}: {e}")
        return False

# Process all HTML files except index.html (already done)
files = [f for f in glob.glob('*.html') if f != 'index.html' and f != 'category-4-columns-sidebar.html']
count = 0

for f in files:
    if fix_file(f):
        print(f"✓ {f}")
        count += 1
    else:
        # Check if it still has the form panel
        with open(f, 'r', encoding='utf-8') as file:
            if 'data-panel="form"' in file.read():
                print(f"✗ {f} (still has form panel)")

print(f"\nUpdated {count} files")

