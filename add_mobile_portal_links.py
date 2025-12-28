#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to add پرتال نمایندگان and سیستم گارانتی links to mobile header indicators
"""

import os
import re
from pathlib import Path

# The new indicators HTML to add (with proper indentation - 6 tabs)
NEW_INDICATORS = '''\t\t\t\t\t\t<div class="mobile-indicator d-none d-md-block"><a href="BranchPortal.html"
\t\t\t\t\t\t\tclass="mobile-indicator__button" style="display: flex; flex-direction: column; align-items: center; gap: 2px;"><span class="mobile-indicator__icon"><svg
\t\t\t\t\t\t\t\twidth="20" height="20">
\t\t\t\t\t\t\t\t<path d="M18,20H2c-1.1,0-2-0.9-2-2V2c0-1.1,0.9-2,2-2h16c1.1,0,2,0.9,2,2v16C20,19.1,19.1,20,18,20z M18,2H2v16h16V2z" />
\t\t\t\t\t\t\t\t<rect x="4" y="4" width="4" height="4" />
\t\t\t\t\t\t\t\t<rect x="10" y="4" width="4" height="4" />
\t\t\t\t\t\t\t\t<rect x="16" y="4" width="2" height="4" />
\t\t\t\t\t\t\t\t<rect x="4" y="10" width="4" height="4" />
\t\t\t\t\t\t\t\t<rect x="10" y="10" width="4" height="4" />
\t\t\t\t\t\t\t\t<rect x="16" y="10" width="2" height="4" />
\t\t\t\t\t\t\t\t<rect x="4" y="16" width="12" height="2" />
\t\t\t\t\t\t\t</svg></span><span style="font-size: 10px; line-height: 1; margin-top: 2px;">پرتال نمایندگان</span></a></div>
\t\t\t\t\t\t<div class="mobile-indicator d-none d-md-block"><a href="account-guaranty.html"
\t\t\t\t\t\t\tclass="mobile-indicator__button" style="display: flex; flex-direction: column; align-items: center; gap: 2px;"><span class="mobile-indicator__icon"><svg
\t\t\t\t\t\t\t\twidth="20" height="20">
\t\t\t\t\t\t\t\t<path d="M10,0L2,4v6c0,5.5,3.8,10.7,8,12c4.2-1.3,8-6.5,8-12V4L10,0z M10,2.2L16,5.3v4.7c0,4.6-3.1,9-6,10.1c-2.9-1.1-6-5.5-6-10.1V5.3L10,2.2z" />
\t\t\t\t\t\t\t\t<path d="M10,6c-1.1,0-2,0.9-2,2v2c0,1.1,0.9,2,2,2s2-0.9,2-2V8C12,6.9,11.1,6,10,6z M10,10c-0.6,0-1-0.4-1-1V8c0-0.6,0.4-1,1-1s1,0.4,1,1v1C11,9.6,10.6,10,10,10z" />
\t\t\t\t\t\t\t</svg></span><span style="font-size: 10px; line-height: 1; margin-top: 2px;">سیستم گارانتی</span></a></div>'''

def update_file(file_path):
    """Update a single HTML file"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Check if the file already has the new indicators
        if 'پرتال نمایندگان' in content and 'mobile-indicator__button' in content:
            # Check if it's already in the mobile indicators section
            if 'mobile-header__indicators' in content:
                # Check if the indicators are already added
                pattern = r'<div class="mobile-indicator d-none d-md-block"><a href="BranchPortal\.html"'
                if re.search(pattern, content):
                    print(f"  ✓ Already updated: {file_path.name}")
                    return False
        
        # Find the position after the cart indicator and before the closing </div> of mobile-header__indicators
        # Look for the cart indicator closing tag, then find the next </div> that's likely the closing of mobile-header__indicators
        cart_pattern = r'<div class="mobile-indicator"><a href="cart\.html".*?</a></div>'
        cart_match = re.search(cart_pattern, content, flags=re.DOTALL)
        
        if not cart_match:
            print(f"  ✗ Could not find cart indicator in {file_path.name}")
            return False
        
        # Find the end position of cart indicator
        cart_end = cart_match.end()
        
        # Look for the closing </div> after cart indicator (should be on a new line with proper indentation)
        # Find the next </div> that appears after some whitespace
        remaining_content = content[cart_end:]
        closing_match = re.search(r'\s*(</div>)', remaining_content)
        
        if not closing_match:
            print(f"  ✗ Could not find closing div after cart indicator in {file_path.name}")
            return False
        
        # Insert the new indicators between cart indicator and closing div
        insert_pos = cart_end + closing_match.start()
        new_content = content[:insert_pos] + '\n' + NEW_INDICATORS + '\n\t\t\t\t\t' + content[insert_pos:]
        
        if new_content != content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"  ✓ Updated: {file_path.name}")
            return True
        else:
            print(f"  ✗ Pattern not found: {file_path.name}")
            return False
            
    except Exception as e:
        print(f"  ✗ Error processing {file_path.name}: {e}")
        return False

def main():
    """Main function"""
    base_dir = Path('.')
    html_files = list(base_dir.glob('*.html'))
    
    # Exclude header and mobile-header variant files as they might have different structure
    html_files = [f for f in html_files if not f.name.startswith(('header-', 'mobile-header-'))]
    
    print(f"Found {len(html_files)} HTML files to process...")
    print()
    
    updated_count = 0
    processed_count = 0
    for html_file in sorted(html_files):
        try:
            file_content = html_file.read_text(encoding='utf-8')
            if 'mobile-header__indicators' in file_content:
                processed_count += 1
                print(f"Processing: {html_file.name}")
                if update_file(html_file):
                    updated_count += 1
                print()
        except Exception as e:
            print(f"Error reading {html_file.name}: {e}")
    
    print(f"\nCompleted! Processed {processed_count} files, updated {updated_count} files.")

if __name__ == '__main__':
    main()

