#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Translate entire website from English to Farsi
Changes: lang="en" to lang="fa", dir="ltr" to dir="rtl", all text translations
"""
import os
import re
import sys

# Base directory
BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# Comprehensive translation dictionary
TRANSLATIONS = {
    # Page titles
    'Home One — Red Parts': 'خانه یک — قطعات قرمز',
    'Home Two — Red Parts': 'خانه دو — قطعات قرمز',
    'Home One': 'خانه یک',
    'Home Two': 'خانه دو',
    
    # Navigation
    'Home': 'خانه',
    'Shop': 'فروشگاه',
    'Blog': 'وبلاگ',
    'Account': 'حساب کاربری',
    'Pages': 'صفحات',
    'Menu': 'منو',
    
    # Header elements
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
    
    # Search and vehicle
    'Enter Keyword or Part Number': 'کلمه کلیدی یا شماره قطعه را وارد کنید',
    'Enter keyword or part number': 'کلمه کلیدی یا شماره قطعه را وارد کنید',
    'Select Vehicle': 'انتخاب وسیله نقلیه',
    'Select a vehicle to find exact fit parts': 'برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید',
    'Add A Vehicle': 'افزودن وسیله نقلیه',
    'Back to vehicles list': 'بازگشت به لیست وسایل نقلیه',
    'Vehicle': 'وسیله نقلیه',
    
    # Account menu
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
    'Aluminum': 'آلومینیوم',
    'Subtotal': 'جمع کل',
    'Shipping': 'ارسال',
    'Tax': 'مالیات',
    'Total': 'مجموع',
    'View Cart': 'مشاهده سبد خرید',
    'Checkout': 'تسویه حساب',
    
    # Product categories
    'Headlights & Lighting': 'چراغ‌ها و روشنایی',
    'Headlights &amp; Lighting': 'چراغ‌ها و روشنایی',
    'Interior Parts': 'لوازم داخلی',
    'Switches & Relays': 'سوئیچ‌ها و رله‌ها',
    'Switches &amp; Relays': 'سوئیچ‌ها و رله‌ها',
    'Tires & Wheels': 'لاستیک و چرخ',
    'Tires &amp; Wheels': 'لاستیک و چرخ',
    'Tools & Garage': 'ابزار و تجهیزات',
    'Tools &amp; Garage': 'ابزار و تجهیزات',
    'Body Parts': 'قطعات بدنه',
    'Body Parts & Mirrors': 'قطعات بدنه و آینه‌ها',
    'Body Parts &amp; Mirrors': 'قطعات بدنه و آینه‌ها',
    'Suspension': 'سیستم تعلیق',
    'Steering': 'فرمان',
    'Fuel Systems': 'سیستم سوخت',
    'Fuel System & Filters': 'سیستم سوخت و فیلترها',
    'Fuel System &amp; Filters': 'سیستم سوخت و فیلترها',
    'Transmission': 'گیربکس',
    'Air Filters': 'فیلتر هوا',
    'Clutches': 'کلاچ',
    'Brakes & Suspension': 'ترمز و سیستم تعلیق',
    'Brakes &amp; Suspension': 'ترمز و سیستم تعلیق',
    'Engine & Drivetrain': 'موتور و سیستم انتقال قدرت',
    'Engine &amp; Drivetrain': 'موتور و سیستم انتقال قدرت',
    
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
    'Consoles &amp; Organizers': 'کنسول و نظم‌دهنده',
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
    'Cranks &amp; Pistons': 'میل‌لنگ و پیستون',
    'Interior Accessories': 'لوازم داخلی',
    
    # Form elements
    'Select Year': 'انتخاب سال',
    'Select Brand': 'انتخاب برند',
    'Select Model': 'انتخاب مدل',
    'Select Engine': 'انتخاب موتور',
    'Enter VIN number': 'شماره VIN را وارد کنید',
    'Or': 'یا',
    
    # Shop pages
    'Category': 'دسته‌بندی',
    'Shop Grid': 'نمایش شبکه‌ای',
    'Shop List': 'نمایش لیستی',
    'Shop Table': 'نمایش جدولی',
    'Shop Right Sidebar': 'فروشگاه با نوار کناری راست',
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
    'Login &amp; Register': 'ورود و ثبت‌نام',
    'Order Details': 'جزئیات سفارش',
    'Address Book': 'دفترچه آدرس',
    'Edit Address': 'ویرایش آدرس',
    'Change Password': 'تغییر رمز عبور',
    
    # Other pages
    'Contact Us v1': 'تماس با ما نسخه 1',
    'Contact Us v2': 'تماس با ما نسخه 2',
    'Contact Us': 'تماس با ما',
    'Terms And Conditions': 'شرایط و ضوابط',
    'FAQ': 'سوالات متداول',
    'Components': 'اجزا',
    'Typography': 'تایپوگرافی',
    
    # Products
    'Products': 'محصولات',
    'Categories': 'دسته‌بندی‌ها',
    'on': 'در',
    'reviews': 'نظرات',
    'review': 'نظر',
    
    # Common words
    'Columns': 'ستون',
    'Sidebar': 'نوار کناری',
    'Full': 'کامل',
    'Header': 'هدر',
    'Variant': 'نوع',
    'One': 'یک',
    'Two': 'دو',
    'Three': 'سه',
    'Four': 'چهار',
    'Five': 'پنج',
    'Six': 'شش',
    'Seven': 'هفت',
    'Spaceship': 'فضاپیما',
    'Classic': 'کلاسیک',
    'Mobile Header': 'هدر موبایل',
    
    # Auto parts specific
    'Auto parts for Cars, trucks and motorcycles': 'قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها',
    
    # Specific products
    'Brandix Brake Kit BDX-750Z370-S': 'کیت ترمز برندیکس BDX-750Z370-S',
    'Left Headlight Of Brandix Z54': 'چراغ جلو چپ برندیکس Z54',
    'Glossy Gray 19&quot; Aluminium Wheel AR-19': 'رینگ آلومینیومی براق خاکستری 19 اینچی AR-19',
    'Glossy Gray 19" Aluminium Wheel AR-19': 'رینگ آلومینیومی براق خاکستری 19 اینچی AR-19',
    'Twin Exhaust Pipe From Brandix Z54': 'اگزوز دوقلو برندیکس Z54',
    'Motor Oil Level 5': 'روغن موتور سطح 5',
    'Brandix Engine Block Z4': 'بلوک موتور برندیکس Z4',
    'Brandix Clutch Discs Z175': 'دیسک کلاچ برندیکس Z175',
    'Brandix Manual Five Speed Gearbox': 'گیربکس دستی پنج دنده برندیکس',
    
    # Engine details
    'Engine': 'موتور',
    'Gas': 'بنزین',
    'Diesel': 'دیزل',
}

def translate_file(filepath):
    """Translate a single HTML file"""
    try:
        # Read with UTF-8 encoding
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        original_content = content
        
        # Change language and direction
        content = content.replace('lang="en"', 'lang="fa"')
        content = content.replace("lang='en'", "lang='fa'")
        content = content.replace('dir="ltr"', 'dir="rtl"')
        content = content.replace("dir='ltr'", "dir='rtl'")
        
        # Apply translations (order matters - longer phrases first)
        sorted_keys = sorted(TRANSLATIONS.keys(), key=len, reverse=True)
        for key in sorted_keys:
            value = TRANSLATIONS[key]
            content = content.replace(key, value)
        
        # Only write if content changed
        if content != original_content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    print("=" * 70)
    print("TRANSLATING ENTIRE SITE TO FARSI")
    print("=" * 70)
    print()
    
    # Find all HTML files
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files to translate")
    print()
    
    translated_count = 0
    for filepath in html_files:
        filename = os.path.basename(filepath)
        print(f"Processing: {filename}...", end=" ")
        if translate_file(filepath):
            print("✓")
            translated_count += 1
        else:
            print("(no changes)")
    
    print()
    print("=" * 70)
    print(f"TRANSLATION COMPLETE!")
    print(f"Translated: {translated_count} files")
    print("=" * 70)
    print()
    print("Changes made:")
    print("  - Changed lang='en' to lang='fa'")
    print("  - Changed dir='ltr' to dir='rtl'")
    print("  - Translated all UI text to Farsi")

if __name__ == '__main__':
    main()



