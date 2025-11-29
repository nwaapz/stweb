#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Script to restore HTML files and remove dollar signs while preserving UTF-8 encoding
This script will attempt to download files from the original source if available,
or you can manually restore them using HTTrack first.
"""
import os
import re
import sys
import urllib.request
import urllib.parse
from pathlib import Path

def download_file(url, filepath):
    """Download a file with proper UTF-8 encoding"""
    try:
        req = urllib.request.Request(url)
        req.add_header('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
        
        with urllib.request.urlopen(req) as response:
            content = response.read()
            # Try to decode as UTF-8
            try:
                text = content.decode('utf-8')
            except UnicodeDecodeError:
                # Try other encodings
                text = content.decode('utf-8', errors='ignore')
            
            # Ensure directory exists
            os.makedirs(os.path.dirname(filepath), exist_ok=True)
            
            # Write with UTF-8 encoding
            with open(filepath, 'w', encoding='utf-8', newline='') as f:
                f.write(text)
            
            return True
    except Exception as e:
        print(f"Error downloading {url}: {e}")
        return False

def fix_dollar_signs_in_file(filepath):
    """Remove dollar signs from prices in a file while preserving UTF-8 encoding"""
    try:
        # Read file with UTF-8 encoding
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Replace $ followed by numbers (prices) with just the numbers
        # Pattern: $ followed by digits and optional decimal point and more digits
        original_content = content
        content = re.sub(r'\$(\d+\.?\d*)', r'\1', content)
        
        # Only write if there were changes
        if content != original_content:
            # Write back with UTF-8 encoding
            with open(filepath, 'w', encoding='utf-8', newline='') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    print("=" * 60)
    print("File Restoration and Dollar Sign Removal Tool")
    print("=" * 60)
    print()
    print("OPTION 1: Restore files using HTTrack (Recommended)")
    print("  - Open HTTrack Website Copier")
    print("  - Navigate to: D:\\StartechWeb\\st1")
    print("  - Click 'Update existing mirror' or re-run the mirror")
    print("  - This will restore all files with proper UTF-8 encoding")
    print()
    print("OPTION 2: Fix dollar signs in existing files")
    print("  - This will remove $ from prices in all HTML files")
    print("  - Note: If files are corrupted, they need to be restored first")
    print()
    
    if len(sys.argv) > 1 and sys.argv[1] == '--fix-only':
        # Just fix dollar signs
        directory = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr"
        
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
        print("Removing dollar signs from prices...")
        
        # Process each file
        success_count = 0
        changed_count = 0
        for filepath in html_files:
            if fix_dollar_signs_in_file(filepath):
                changed_count += 1
            success_count += 1
        
        print(f"Processed {success_count} files")
        print(f"Modified {changed_count} files (removed dollar signs)")
    else:
        print("Please restore files first using HTTrack, then run:")
        print("  python restore_and_fix.py --fix-only")

if __name__ == '__main__':
    main()

