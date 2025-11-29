#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Script to remove dollar signs from prices while preserving UTF-8 encoding
"""
import os
import re
import sys

def fix_dollar_signs_in_file(filepath):
    """Remove dollar signs from prices in a file while preserving UTF-8 encoding"""
    try:
        # Read file with UTF-8 encoding
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Replace $ followed by numbers (prices) with just the numbers
        # Pattern: $ followed by digits and optional decimal point and more digits
        content = re.sub(r'\$(\d+\.?\d*)', r'\1', content)
        
        # Write back with UTF-8 encoding and BOM to ensure proper display
        with open(filepath, 'w', encoding='utf-8', newline='') as f:
            f.write(content)
        
        return True
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    if len(sys.argv) < 2:
        print("Usage: python fix_dollar_signs.py <directory>")
        sys.exit(1)
    
    directory = sys.argv[1]
    
    if not os.path.isdir(directory):
        print(f"Error: {directory} is not a valid directory")
        sys.exit(1)
    
    # Find all HTML files
    html_files = []
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    
    # Process each file
    success_count = 0
    for filepath in html_files:
        if fix_dollar_signs_in_file(filepath):
            success_count += 1
    
    print(f"Successfully processed {success_count} files")

if __name__ == '__main__':
    main()

