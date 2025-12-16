#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to simplify vehicle picker modal in all HTML files.
Removes the form panel and "Add Vehicle" button, making it a single-step process.
"""

import os
import re
import glob

def simplify_vehicle_picker(file_path):
    """Remove form panel and Add Vehicle button from vehicle picker modal."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Step 1: Remove the "Add Vehicle" button that has data-to-panel="form"
        # This button appears in the list panel's actions
        content = re.sub(
            r'\s*<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>',
            '',
            content
        )
        
        # Step 2: Remove the entire form panel
        # The form panel starts with: <div class="vehicle-picker-modal__panel" data-panel="form">
        # And ends with: </div></div> (closing the panel and its parent)
        # We need to match everything in between, including nested divs
        
        # Count opening and closing divs to match correctly
        # Find the start of the form panel
        form_panel_start = r'<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>'
        
        # Find the pattern and remove it
        # The form panel structure is:
        # <div...data-panel="form">...content...</div></div>
        # We need to match from the opening div to the closing </div></div>
        
        # Use a more sophisticated approach: find the start, then count divs
        pattern = form_panel_start + r'.*?</div></div>(?=\s*</div></div></div>|<!-- vehicle-picker-modal / end -->)'
        content = re.sub(pattern, '', content, flags=re.DOTALL)
        
        # Alternative: simpler approach - remove everything from data-panel="form" 
        # until we find the closing tags that match the structure
        # The form panel ends with: </div></div> after the last actions div
        
        if content == original_content:
            # Try a different approach: find the form panel and count divs manually
            # Find position of form panel start
            match = re.search(form_panel_start, content)
            if match:
                start_pos = match.start()
                # From start_pos, find the matching closing divs
                # The form panel should end with </div></div> before the vehicle-picker-modal / end comment
                # Find the next occurrence of </div></div> that comes after the actions div
                remaining = content[start_pos:]
                # Look for the pattern: </div></div> followed by either </div></div></div> or <!-- vehicle-picker-modal
                end_match = re.search(r'</div></div>(?=\s*(?:</div></div></div>|<!-- vehicle-picker-modal / end -->))', remaining)
                if end_match:
                    end_pos = start_pos + end_match.end()
                    content = content[:start_pos] + content[end_pos:]
        
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        else:
            return False
            
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        import traceback
        traceback.print_exc()
        return False

def main():
    """Process all HTML files."""
    html_files = [f for f in glob.glob('*.html') if f not in ['index.html']]  # Skip index.html as it's already done
    updated_count = 0
    
    for html_file in html_files:
        if simplify_vehicle_picker(html_file):
            print(f"✓ Updated: {html_file}")
            updated_count += 1
        else:
            # Check if it needs updating
            with open(html_file, 'r', encoding='utf-8') as f:
                if 'data-to-panel="form"' in f.read():
                    print(f"✗ Failed: {html_file}")
    
    print(f"\nTotal files updated: {updated_count}")

if __name__ == '__main__':
    main()

