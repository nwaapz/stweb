#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import glob

# Old single panel
old = '<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active" data-panel="list"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div>'

# New two panels
new = '<div class="vehicle-picker-modal__panel vehicle-picker-modal__panel--active" data-panel="factories"><div class="vehicle-picker-modal__title card-title">انتخاب کارخانه</div><div class="vehicle-picker-modal__text">ابتدا کارخانه خودروسازی را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Factories will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div><div class="vehicle-picker-modal__panel" data-panel="vehicles"><div class="vehicle-picker-modal__title card-title">انتخاب وسیله نقلیه</div><div class="vehicle-picker-modal__text">وسیله نقلیه خود را انتخاب کنید</div><div class="vehicles-list"><div class="vehicles-list__body"><!-- Vehicles will be loaded dynamically from API --></div></div><div class="vehicle-picker-modal__actions"><button type="button" class="btn btn-sm btn-secondary" data-back-to-factories>بازگشت</button> <button type="button" class="btn btn-sm btn-secondary vehicle-picker-modal__close-button">لغو</button></div></div>'

files = [f for f in glob.glob('*.html') if f not in ['index.html', 'category-4-columns-sidebar.html']]
count = 0

for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            content = file.read()
        if old in content:
            content = content.replace(old, new)
            with open(f, 'w', encoding='utf-8') as file:
                file.write(content)
            print(f"✓ {f}")
            count += 1
    except Exception as e:
        print(f"✗ {f}: {e}")

print(f"\nUpdated {count} files")

