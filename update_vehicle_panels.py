#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Update vehicle picker modal to two-phase selection (factories -> vehicles)
"""

import glob

OLD_PANEL = '<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active" data-panel="list"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div>'

NEW_PANELS = '''<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active" data-panel="factories"><div class="vehicle-picker-modal__title card-title">انتخاب کارخانه</div><div class="vehicle-picker-modal__text">ابتدا کارخانه خودروسازی را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Factories will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div><div class="vehicle-picker-modal__panel" data-panel="vehicles"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">وسیله نقلیه خود را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary" data-back-to-factories>بازگشت</button> <button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div>'''

files = [f for f in glob.glob('*.html') if f not in ['index.html']]
count = 0

for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        
        if OLD_PANEL in content:
            content = content.replace(OLD_PANEL, NEW_PANELS)
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"✓ {f}")
            count += 1
    except Exception as e:
        print(f"✗ {f}: {e}")

print(f"\nUpdated {count} files")

