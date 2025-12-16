#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import glob
import re

files = [f for f in glob.glob('*.html') if f not in ['index.html', 'category-4-columns-sidebar.html']]
count = 0

for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        
        original = content
        
        # Fix 1: Update factories panel title and text if wrong
        content = content.replace(
            'data-panel="factories"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید</div>',
            'data-panel="factories"><div class="vehicle-picker-modal__title card-title">انتخاب کارخانه</div><div class="vehicle-picker-modal__text">ابتدا کارخانه خودروسازی را انتخاب کنید</div>'
        )
        
        # Fix 2: Remove "Add Vehicle" button from factories panel
        content = re.sub(
            r'<button type="button" class="btn btn-sm btn-primary" data-to-panel="form">افزودن وسیله نقلیه</button>',
            '',
            content
        )
        
        # Fix 3: Add vehicles panel if it doesn't exist (after factories panel, before form panel)
        if 'data-panel="vehicles"' not in content:
            # Find the closing of factories panel
            factories_close = '</div></div><div class="vehicle-picker-modal__panel" data-panel="form"'
            vehicles_panel = '</div></div><div class="vehicle-picker-modal__panel" data-panel="vehicles"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">وسیله نقلیه خود را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary" data-back-to-factories>بازگشت</button> <button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div><div class="vehicle-picker-modal__panel" data-panel="form"'
            content = content.replace(factories_close, vehicles_panel)
        
        # Fix 4: Update factories container comment
        content = content.replace(
            'data-panel="factories">.*?<!-- Vehicles will be loaded dynamically from API -->',
            'data-panel="factories">.*?<!-- Factories will be loaded dynamically from API -->',
            flags=re.DOTALL
        )
        
        if content != original:
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"✓ {f}")
            count += 1
    except Exception as e:
        print(f"✗ {f}: {e}")

print(f"\nUpdated {count} files")

