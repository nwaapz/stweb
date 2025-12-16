#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import glob

DELETE_BUTTON = '<button type="button" class="vehicles-list__item-remove"><svg width="16" height="16"><path d="M2,4V2h3V1h6v1h3v2H2z M13,13c0,1.1-0.9,2-2,2H5c-1.1,0-2-0.9-2-2V5h10V13z"/></svg></button>'

files = [f for f in glob.glob('*.html') if f not in ['index.html', 'category-3-columns-sidebar.html']]
count = 0

for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        if DELETE_BUTTON in content:
            content = content.replace(DELETE_BUTTON, '')
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"Fixed: {f}")
            count += 1
    except Exception as e:
        print(f"Error {f}: {e}")

print(f"\nFixed {count} files")

