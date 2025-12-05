#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Translate remaining English text to Farsi in all HTML files.
"""
import os
import re

BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

# Dictionary of English to Farsi translations
TRANSLATIONS = {
    # Mobile Menu & Header
    r'>Menu<': '>منو<',
    r'departments__button-title">Menu</span>': 'departments__button-title">منو</span>',
    r'>Language<': '>زبان<',
    r'>Currency<': '>واحد پول<',
    r'>Wishlist<': '>لیست علاقه‌مندی‌ها<',
    r'>Cart<': '>سبد خرید<',
    r'>Account<': '>حساب کاربری<',
    r'>Garage<': '>گاراژ<',
    r'>Free call 24/7<': '>تماس رایگان ۲۴/۷<',
    r'>Buy Theme<': '>خرید قالب<',
    
    # Vehicle Picker
    r'>Add Vehicle<': '>افزودن خودرو<',
    r'>Select Year<': '>انتخاب سال<',
    r'>Select Brand<': '>انتخاب برند<',
    r'>Select Model<': '>انتخاب مدل<',
    r'>Select Engine<': '>انتخاب موتور<',
    r'placeholder="Enter VIN number"': 'placeholder="شماره VIN را وارد کنید"',
    r'aria-label="VIN number"': 'aria-label="شماره VIN"',
    r'>Cancel<': '>لغو<',
    r'>Back to list<': '>بازگشت به لیست<',
    
    # Navigation & Pages
    r'>Home<': '>خانه<',
    r'>Shop<': '>فروشگاه<',
    r'>Blog<': '>وبلاگ<',
    r'>Pages<': '>صفحات<',
    r'>About Us<': '>درباره ما<',
    r'>Contact Us<': '>تماس با ما<',
    r'>Track Order<': '>پیگیری سفارش<',
    r'>Terms And Conditions<': '>قوانین و مقررات<',
    r'>FAQ<': '>سوالات متداول<',
    r'>Components<': '>کامپوننت‌ها<',
    r'>Typography<': '>تایپوگرافی<',
    r'>404<': '>۴۰۴<',
    
    # Shop Related
    r'>Shop Grid<': '>فروشگاه شبکه‌ای<',
    r'>Shop List<': '>فروشگاه لیست<',
    r'>Shop Table<': '>فروشگاه جدول<',
    r'>Shop Right Sidebar<': '>فروشگاه سایدبار راست<',
    r'>Full Width<': '>تمام عرض<',
    r'>Left Sidebar<': '>سایدبار چپ<',
    r'>Right Sidebar<': '>سایدبار راست<',
    r'>Product<': '>محصول<',
    
    # Blog Related
    r'>Post Page<': '>صفحه پست<',
    r'>Post Without Image<': '>پست بدون تصویر<',
    r'>Blog Classic<': '>وبلاگ کلاسیک<',
    r'>Blog List<': '>لیست وبلاگ<',
    r'>Blog Grid<': '>شبکه وبلاگ<',
    
    # Account Related
    r'>Login & Register<': '>ورود و ثبت نام<',
    r'>Dashboard<': '>داشبورد<',
    r'>Edit Profile<': '>ویرایش پروفایل<',
    r'>Order History<': '>تاریخچه سفارشات<',
    r'>Order Details<': '>جزئیات سفارش<',
    r'>Address Book<': '>دفترچه آدرس<',
    r'>Edit Address<': '>ویرایش آدرس<',
    r'>Change Password<': '>تغییر رمز عبور<',
    r'>Logout<': '>خروج<',
    r'>Register<': '>ثبت نام<',
    
    # Misc
    r'>Compare<': '>مقایسه<',
    r'>Checkout<': '>تسویه حساب<',
    r'>Order Success<': '>سفارش موفق<',
    
    # Footer / Copyright
    r'>Powered by<': '>قدرت گرفته از<',
    r'>All Rights Reserved<': '>تمامی حقوق محفوظ است<',
    r'>Contact Us v1<': '>تماس با ما نسخه ۱<',
    r'>Contact Us v2<': '>تماس با ما نسخه ۲<',
    r'>Variant One<': '>نسخه یک<',
    r'>Variant Two<': '>نسخه دو<',
    r'>Variant Three<': '>نسخه سه<',
    r'>Variant Four<': '>نسخه چهار<',
    r'>Variant Five<': '>نسخه پنج<',
    r'>Mobile Header<': '>هدر موبایل<',
    r'>Header Classic<': '>هدر کلاسیک<',
    r'>Header Spaceship<': '>هدر فضایی<',
    r'>Category<': '>دسته‌بندی<',
    r'>3 Columns Sidebar<': '>۳ ستونه سایدبار<',
    r'>4 Columns Sidebar<': '>۴ ستونه سایدبار<',
    r'>5 Columns Sidebar<': '>۵ ستونه سایدبار<',
    r'>4 Columns Full<': '>۴ ستونه تمام عرض<',
    r'>5 Columns Full<': '>۵ ستونه تمام عرض<',
    r'>6 Columns Full<': '>۶ ستونه تمام عرض<',
    r'>7 Columns Full<': '>۷ ستونه تمام عرض<',
}

def translate_files(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        original = content
        
        for eng, fa in TRANSLATIONS.items():
            # Check if it's a regex replacement (simple string replacements used here mostly)
            if 'placeholder' in eng or 'aria-label' in eng:
                content = content.replace(eng, fa)
            else:
                # Simple replace for text inside tags
                content = content.replace(eng, fa)
        
        # Specific fix for Menu translation if missed
        content = content.replace('departments__button-title">Menu</span>', 'departments__button-title">منو</span>')
        content = content.replace('departments__button-title">Menu<', 'departments__button-title">منو<')
        
        if content != original:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            return True
        return False
    except Exception as e:
        print(f"Error processing {filepath}: {e}")
        return False

def main():
    print("=" * 70)
    print("TRANSLATING REMAINING ENGLISH TEXT TO FARSI")
    print("=" * 70)
    
    html_files = []
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith('.html'):
                html_files.append(os.path.join(root, file))
    
    print(f"Found {len(html_files)} HTML files")
    
    count = 0
    for filepath in html_files:
        if translate_files(filepath):
            count += 1
            print(f"Updated: {os.path.basename(filepath)}")
            
    print("=" * 70)
    print(f"Complete: Updated {count} files")
    print("=" * 70)

if __name__ == '__main__':
    main()



