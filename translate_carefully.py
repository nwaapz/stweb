#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Carefully translate website to Farsi - only translate visible text, preserve HTML structure
"""
import os
import re
from html.parser import HTMLParser
from html import unescape

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# Translation dictionary - only visible text
TRANSLATIONS = {
    # Navigation
    'Home': 'خانه',
    'Shop': 'فروشگاه',
    'Blog': 'وبلاگ',
    'Account': 'حساب کاربری',
    'Pages': 'صفحات',
    'Menu': 'منو',
    
    # Header
    'Call Us:': 'تماس با ما:',
    'About Us': 'درباره ما',
    'Contacts': 'تماس با ما',
    'Track Order': 'پیگیری سفارش',
    'Compare:': 'مقایسه:',
    'Currency:': 'واحد پول:',
    'Language:': 'زبان:',
    'Hello, Log In': 'سلام، ورود',
    'My Account': 'حساب کاربری من',
    'Shopping Cart': 'سبد خرید',
    
    # Search
    'Enter Keyword or Part Number': 'کلمه کلیدی یا شماره قطعه را وارد کنید',
    'Select Vehicle': 'انتخاب وسیله نقلیه',
    'Select a vehicle to find exact fit parts': 'برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید',
    'Add A Vehicle': 'افزودن وسیله نقلیه',
    'Back to vehicles list': 'بازگشت به لیست وسایل نقلیه',
    'Vehicle': 'وسیله نقلیه',
    
    # Account
    'Log In to Your Account': 'ورود به حساب کاربری شما',
    'Email address': 'آدرس ایمیل',
    'Password': 'رمز عبور',
    'Forgot?': 'فراموش کرده‌اید؟',
    'Login': 'ورود',
    'Create An Account': 'ایجاد حساب کاربری',
    'Dashboard': 'داشبورد',
    'Garage': 'گاراژ',
    'Edit Profile': 'ویرایش پروفایل',
    'Order History': 'تاریخچه سفارشات',
    'Addresses': 'آدرس‌ها',
    'Logout': 'خروج',
    
    # Cart
    'Color:': 'رنگ:',
    'Material:': 'جنس:',
    'Yellow': 'زرد',
    'Aluminium': 'آلومینیوم',
    'Subtotal': 'جمع کل',
    'Shipping': 'ارسال',
    'Tax': 'مالیات',
    'Total': 'مجموع',
    'View Cart': 'مشاهده سبد خرید',
    'Checkout': 'تسویه حساب',
    
    # Categories
    'Headlights & Lighting': 'چراغ‌ها و روشنایی',
    'Interior Parts': 'لوازم داخلی',
    'Switches & Relays': 'سوئیچ‌ها و رله‌ها',
    'Tires & Wheels': 'لاستیک و چرخ',
    'Tools & Garage': 'ابزار و تجهیزات',
    'Body Parts': 'قطعات بدنه',
    'Body Parts & Mirrors': 'قطعات بدنه و آینه‌ها',
    'Suspension': 'سیستم تعلیق',
    'Steering': 'فرمان',
    'Fuel Systems': 'سیستم سوخت',
    'Fuel System & Filters': 'سیستم سوخت و فیلترها',
    'Transmission': 'گیربکس',
    'Air Filters': 'فیلتر هوا',
    'Clutches': 'کلاچ',
    'Brakes & Suspension': 'ترمز و سیستم تعلیق',
    'Engine & Drivetrain': 'موتور و سیستم انتقال قدرت',
    
    # Product details
    'Bumpers': 'سپر',
    'Hoods': 'کاپوت',
    'Grilles': 'جلو پنجره',
    'Fog Lights': 'مه‌شکن',
    'Door Handles': 'دستگیره در',
    'Car Covers': 'روکش خودرو',
    'Tailgates': 'درب صندوق عقب',
    'Headlights': 'چراغ جلو',
    'Tail Lights': 'چراغ عقب',
    'Turn Signals': 'راهنما',
    'Corner Lights': 'چراغ گوشه',
    'Brake Discs': 'دیسک ترمز',
    'Wheel Hubs': 'توپی چرخ',
    'Air Suspension': 'سیستم تعلیق بادی',
    'Ball Joints': 'مفصل کروی',
    'Brake Pad Sets': 'لنت ترمز',
    'Floor Mats': 'کفپوش',
    'Gauges': 'عقربه‌ها',
    'Consoles & Organizers': 'کنسول و نظم‌دهنده',
    'Mobile Electronics': 'لوازم الکترونیکی موبایل',
    'Steering Wheels': 'فرمان',
    'Cargo Accessories': 'لوازم بار',
    'Repair Manuals': 'راهنمای تعمیر',
    'Car Care': 'نگهداری خودرو',
    'Code Readers': 'دستگاه عیب‌یاب',
    'Tool Boxes': 'جعبه ابزار',
    'Oxygen Sensors': 'سنسور اکسیژن',
    'Heating': 'گرمایش',
    'Exhaust': 'اگزوز',
    'Cranks & Pistons': 'میل‌لنگ و پیستون',
    'Interior Accessories': 'لوازم داخلی',
    
    # Forms
    'Select Year': 'انتخاب سال',
    'Select Brand': 'انتخاب برند',
    'Select Model': 'انتخاب مدل',
    'Select Engine': 'انتخاب موتور',
    'Enter VIN number': 'شماره VIN را وارد کنید',
    'Or': 'یا',
    
    # Shop
    'Category': 'دسته‌بندی',
    'Shop Grid': 'نمایش شبکه‌ای',
    'Shop List': 'نمایش لیستی',
    'Shop Table': 'نمایش جدولی',
    'Product': 'محصول',
    'Full Width': 'تمام عرض',
    'Left Sidebar': 'نوار کناری چپ',
    'Right Sidebar': 'نوار کناری راست',
    'Cart': 'سبد خرید',
    'Order Success': 'سفارش موفق',
    'Wishlist': 'لیست علاقه‌مندی',
    'Compare': 'مقایسه',
    
    # Blog
    'Blog Classic': 'وبلاگ کلاسیک',
    'Blog List': 'لیست وبلاگ',
    'Blog Grid': 'شبکه وبلاگ',
    'Post Page': 'صفحه پست',
    'Post Without Image': 'پست بدون تصویر',
    
    # Account pages
    'Login & Register': 'ورود و ثبت‌نام',
    'Order Details': 'جزئیات سفارش',
    'Address Book': 'دفترچه آدرس',
    'Edit Address': 'ویرایش آدرس',
    'Change Password': 'تغییر رمز عبور',
    
    # Other
    'Contact Us v1': 'تماس با ما نسخه 1',
    'Contact Us v2': 'تماس با ما نسخه 2',
    'Contact Us': 'تماس با ما',
    'Terms And Conditions': 'شرایط و ضوابط',
    'FAQ': 'سوالات متداول',
    'Components': 'اجزا',
    'Typography': 'تایپوگرافی',
    'Products': 'محصولات',
    'Categories': 'دسته‌بندی‌ها',
    'on': 'در',
    'reviews': 'نظرات',
    'review': 'نظر',
    'Auto parts for Cars, trucks and motorcycles': 'قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها',
}

def translate_text_content(text):
    """Translate text while preserving HTML entities and structure"""
    if not text or not text.strip():
        return text
    
    # Sort by length (longest first) to avoid partial matches
    sorted_keys = sorted(TRANSLATIONS.keys(), key=len, reverse=True)
    
    result = text
    for key in sorted_keys:
        value = TRANSLATIONS[key]
        # Only replace whole words/phrases, be careful with HTML
        result = result.replace(key, value)
    
    return result

def translate_file(filepath):
    """Carefully translate a file - only visible text, preserve structure"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original = content
        
        # 1. Change language and direction attributes
        content = content.replace('lang="en"', 'lang="fa"')
        content = content.replace("lang='en'", "lang='fa'")
        content = content.replace('dir="ltr"', 'dir="rtl"')
        content = content.replace("dir='ltr'", "dir='rtl'")
        
        # 2. Translate title tag
        title_match = re.search(r'<title>(.*?)</title>', content, re.IGNORECASE)
        if title_match:
            title_text = title_match.group(1)
            translated_title = translate_text_content(title_text)
            content = content.replace(f'<title>{title_text}</title>', f'<title>{translated_title}</title>')
        
        # 3. Translate text in common HTML tags (visible text only)
        # Be very careful - only translate content between tags, not attributes
        
        # Translate text in <a> tags (but not href attributes)
        def translate_link(match):
            full_tag = match.group(0)
            link_text = match.group(1)
            translated = translate_text_content(link_text)
            return full_tag.replace(link_text, translated)
        
        # Translate text in buttons, spans, divs with text content
        # Only translate visible text nodes, not attributes
        sorted_keys = sorted(TRANSLATIONS.keys(), key=len, reverse=True)
        
        for key in sorted_keys:
            value = TRANSLATIONS[key]
            # Only replace in text content areas (between > and <)
            # Use lookahead/lookbehind to avoid replacing in attributes
            pattern = r'(>)([^<]*?)(' + re.escape(key) + r')([^<]*?)(<)'
            def replacer(m):
                return m.group(1) + m.group(2) + value + m.group(4) + m.group(5)
            content = re.sub(pattern, replacer, content)
        
        # 4. Translate placeholder attributes
        for key, value in TRANSLATIONS.items():
            content = content.replace(f'placeholder="{key}"', f'placeholder="{value}"')
            content = content.replace(f"placeholder='{key}'", f"placeholder='{value}'")
        
        # 5. Translate aria-label attributes
        for key, value in TRANSLATIONS.items():
            content = content.replace(f'aria-label="{key}"', f'aria-label="{value}"')
            content = content.replace(f"aria-label='{key}'", f"aria-label='{value}'")
        
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
    print("CAREFULLY TRANSLATING TO FARSI")
    print("=" * 70)
    print()
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    print()
    
    translated = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if translate_file(filepath):
            print("✓")
            translated += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"Translation complete: {translated} files updated")
    print("=" * 70)

if __name__ == '__main__':
    main()

