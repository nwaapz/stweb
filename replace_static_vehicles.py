#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Replace static vehicle list with empty container for dynamic loading.
"""

import glob
import re

# Pattern to match the static vehicles list
# This will match from <div class="vehicles-list"> to </div></div> containing the static vehicles
STATIC_VEHICLES_PATTERN = r'<div class="vehicles-list"><div class="vehicles-list__body">.*?</div></div>'

# Replacement - empty container
EMPTY_CONTAINER = '<div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div>'

def replace_static_vehicles(file_path):
    """Replace static vehicles with empty container."""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Find and replace the static vehicles list
        # Look for the pattern that contains static vehicle items
        # Match from vehicles-list__body to the closing divs, but only if it contains vehicles-list__item
        if 'vehicles-list__item' in content and 'vehicles-list__body' in content:
            # More specific pattern - find the vehicles-list div with static items
            pattern = r'<div class="vehicles-list"><div class="vehicles-list__body">.*?<label class="vehicles-list__item".*?</label>.*?</div></div>'
            content = re.sub(pattern, EMPTY_CONTAINER, content, flags=re.DOTALL)
        
        if content != original:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"  Error in {file_path}: {e}")
        return False

# Get all HTML files
html_files = [f for f in glob.glob('*.html') if not f.endswith('.py')]

print(f"Replacing static vehicles in {len(html_files)} HTML files...\n")
updated = 0

for f in html_files:
    if replace_static_vehicles(f):
        print(f"✓ {f}")
        updated += 1

print(f"\n{'='*50}")
print(f"Updated {updated} files")

