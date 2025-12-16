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
        
        # Pattern 1: Remove the "Add Vehicle" button and the entire form panel
        # This pattern matches from the actions div with the Add Vehicle button to the end of the form panel
        pattern1 = r'(<div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button>)\s*<button type="button" class="btn btn-sm btn-primary" data-to-panel="form">افزودن وسیله نقلیه</button></div></div>(<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>.*?</div></div>)'
        
        # Try to match and replace
        content = re.sub(pattern1, r'\1</div></div>', content, flags=re.DOTALL)
        
        # Pattern 2: Alternative pattern if the structure is slightly different
        # Match the actions div with Add Vehicle button, then remove everything until the closing divs of the form panel
        pattern2 = r'(<div class="vehicle-picker-modal__actions"><button[^>]*class="btn btn-sm btn-secondary vehicle-picker-modal__close-button"[^>]*>لغو</button>)\s*<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button></div></div><div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>.*?<div class="vehicle-picker-modal__actions"><button[^>]*data-to-panel="list"[^>]*>بازگشت به لیست</button>.*?</div></div>'
        
        if content == original_content:
            # Try pattern 2
            content = re.sub(pattern2, r'\1</div></div>', content, flags=re.DOTALL)
        
        # Pattern 3: More flexible pattern - find the Add Vehicle button and remove it, then remove the entire form panel
        if content == original_content:
            # Remove Add Vehicle button
            content = re.sub(r'<button[^>]*data-to-panel="form"[^>]*>افزودن وسیله نقلیه</button>', '', content)
            
            # Remove the entire form panel
            content = re.sub(r'<div class="vehicle-picker-modal__panel"[^>]*data-panel="form"[^>]*>.*?</div></div>', '', content, flags=re.DOTALL)
        
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
    
    for html_file in html_files:
        if simplify_vehicle_picker(html_file):
            print(f"Updated: {html_file}")
            updated_count += 1
        else:
            print(f"No changes needed: {html_file}")
    
    print(f"\nTotal files updated: {updated_count}")

if __name__ == '__main__':
    main()

