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
        
        # Step 1: Remove the "Add Vehicle" button (data-to-panel="form")
        # Match: <button...data-to-panel="form"...>افزودن وسیله نقلیه</button>
        content = re.sub(r'<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>', '', content)
        
        # Step 2: Remove the entire form panel
        # Match from <div class="vehicle-picker-modal__panel" data-panel="form"> to </div></div> (closing the panel)
        # We need to be careful to match the correct closing tags
        # The form panel structure: <div...data-panel="form">...content...</div></div>
        # We'll match from data-panel="form" to the closing divs that end the panel
        
        # More precise pattern: find the form panel div and remove everything until we hit the closing tags
        # The form panel ends with: </div></div> after the actions div
        pattern = r'<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>.*?</div></div>'
        content = re.sub(pattern, '', content, flags=re.DOTALL)
        
        # Step 3: Clean up any extra whitespace around the actions div
        # Make sure there's no double spaces or newlines
        content = re.sub(r'(</div></div>)\s*(<!--)', r'\1\2', content)
        
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        else:
            return False
            
    except Exception as e:
        print(f"Error processing {file_path}: {e}")
        return False

def main():
    """Process all HTML files."""
    html_files = glob.glob('*.html')
    updated_count = 0
    skipped = []
    
    for html_file in html_files:
        if simplify_vehicle_picker(html_file):
            print(f"✓ Updated: {html_file}")
            updated_count += 1
        else:
            skipped.append(html_file)
    
    print(f"\n{'='*50}")
    print(f"Total files updated: {updated_count}")
    if skipped:
        print(f"Files with no changes: {len(skipped)}")
        if len(skipped) <= 10:
            for f in skipped:
                print(f"  - {f}")

if __name__ == '__main__':
    main()

