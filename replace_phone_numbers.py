#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Replace all phone numbers with 09360590157
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
NEW_PHONE = "09360590157"

# Common phone number patterns
PHONE_PATTERNS = [
    r'\(800\)\s*060-0730',  # (800) 060-0730
    r'800-060-0730',         # 800-060-0730
    r'800\s*060\s*0730',     # 800 060 0730
    r'\(800\)\s*060\s*0730', # (800) 060 0730
    r'Call Us:\s*\(?800\)?\s*[-.\s]?\d{3}[-.\s]?\d{4}',  # Call Us: (800) 060-0730
    r'تماس با ما:\s*\(?800\)?\s*[-.\s]?\d{3}[-.\s]?\d{4}',  # Farsi version
]

def replace_phone_numbers(filepath):
    """Replace all phone numbers in a file"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # Replace common patterns
        for pattern in PHONE_PATTERNS:
            content = re.sub(pattern, NEW_PHONE, content, flags=re.IGNORECASE)
        
        # Also replace any phone number after "Phone Number", "Phone", "Phدرe Number" (corrupted text)
        content = re.sub(r'(Phone\s+Number|Phone|Phدرe\s+Number)[^<]*?(\d{3}[-.\s]?\d{3}[-.\s]?\d{4})', 
                        r'\1>' + NEW_PHONE, content, flags=re.IGNORECASE)
        
        # Replace phone numbers in footer contacts
        content = re.sub(r'<dt>Phone\s+Number</dt>\s*<dd>[^<]*?</dd>', 
                        f'<dt>Phone Number</dt><dd>{NEW_PHONE}</dd>', content, flags=re.IGNORECASE)
        
        # Replace any remaining (800) patterns
        content = re.sub(r'\(800\)\s*[-.\s]?\d{3}[-.\s]?\d{4}', NEW_PHONE, content)
        
        # Replace any 800-xxx-xxxx patterns
        content = re.sub(r'800[-.\s]?\d{3}[-.\s]?\d{4}', NEW_PHONE, content)
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("=" * 70)
    print("REPLACING ALL PHONE NUMBERS WITH 09360590157")
    print("=" * 70)
    print()
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    print()
    
    replaced_count = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if replace_phone_numbers(filepath):
            print("✓")
            replaced_count += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"Complete: Updated {replaced_count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()

