#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Simple script to replace mobile header in all HTML files
"""

import os
from pathlib import Path

# Read the target header from index.html and modify it
with open('index.html', 'r', encoding='utf-8') as f:
    index_content = f.read()

# Find the mobile header in index.html
start_marker = '<!-- site__mobile-header -->'
end_marker = '<!-- site__mobile-header / end -->'

start_idx = index_content.find(start_marker)
end_idx = index_content.find(end_marker)

if start_idx == -1 or end_idx == -1:
    print("Could not find mobile header in index.html")
    exit(1)

# Extract the header (including the markers)
target_header = index_content[start_idx:end_idx + len(end_marker)]

# Replace "وسیله نقلیه شما" with "پرشیا" 
target_header = target_header.replace('وسیله نقلیه\n\t\t\t\t\t\tشما', 'پرشیا')
target_header = target_header.replace('وسیله نقلیه شما', 'پرشیا')

# Get all HTML files
html_files = [f for f in Path('.').glob('*.html') 
              if not any(x in f.name for x in ['header-', 'mobile-header-', 'indexTest', 'index2'])]

print(f"Found {len(html_files)} HTML files to process\n")

updated = 0
for html_file in sorted(html_files):
    try:
        with open(html_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Find the mobile header section
        start_idx = content.find(start_marker)
        end_idx = content.find(end_marker)
        
        if start_idx == -1 or end_idx == -1:
            print(f"✗ {html_file.name} - header markers not found")
            continue
        
        # Replace the section
        new_content = content[:start_idx] + target_header + content[end_idx + len(end_marker):]
        
        with open(html_file, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        print(f"✓ {html_file.name}")
        updated += 1
        
    except Exception as e:
        print(f"✗ {html_file.name} - Error: {e}")

print(f"\nUpdated {updated} files successfully!")

