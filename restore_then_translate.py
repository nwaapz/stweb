#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Restore original English files, then translate carefully to Farsi
"""
import urllib.request
import os
import re

BASE_URL = "https://red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"
BASE_DIR = "red-parts.html.themeforest.scompiler.ru/themes/red-ltr/"

HTML_FILES = [
    "index.html", "index2.html", "404.html", "about-us.html",
    "account-addresses.html", "account-dashboard.html", "account-edit-address.html",
    "account-garage.html", "account-login.html", "account-order-details.html",
    "account-orders.html", "account-password.html", "account-profile.html",
    "blog-classic-left-sidebar.html", "blog-classic-right-sidebar.html",
    "blog-grid-left-sidebar.html", "blog-grid-right-sidebar.html",
    "blog-list-left-sidebar.html", "blog-list-right-sidebar.html",
    "cart.html", "category-3-columns-sidebar.html", "category-4-columns-full.html",
    "category-4-columns-sidebar.html", "category-5-columns-full.html",
    "category-5-columns-sidebar.html", "category-6-columns-full.html",
    "category-7-columns-full.html", "category-right-sidebar.html",
    "checkout.html", "compare.html", "components.html",
    "contact-us-v1.html", "contact-us-v2.html", "faq.html",
    "header-classic-variant-five.html", "header-classic-variant-four.html",
    "header-classic-variant-one.html", "header-classic-variant-three.html",
    "header-classic-variant-two.html", "header-spaceship-variant-one.html",
    "header-spaceship-variant-three.html", "header-spaceship-variant-two.html",
    "mobile-header-variant-one.html", "mobile-header-variant-two.html",
    "order-success.html", "post-full-width.html", "post-left-sidebar.html",
    "post-right-sidebar.html", "post-without-image.html",
    "product-full.html", "product-sidebar.html",
    "shop-grid-3-columns-sidebar.html", "shop-grid-4-columns-full.html",
    "shop-grid-4-columns-sidebar.html", "shop-grid-5-columns-full.html",
    "shop-grid-6-columns-full.html", "shop-list.html",
    "shop-right-sidebar.html", "shop-table.html",
    "terms.html", "track-order.html", "typography.html", "wishlist.html"
]

# Translation dictionary
TRANSLATIONS = {
    'Home One — Red Parts': 'خانه یک — قطعات قرمز',
    'Home Two — Red Parts': 'خانه دو — قطعات قرمز',
    'Home': 'خانه',
    'Shop': 'فروشگاه',
    'Blog': 'وبلاگ',
    'Account': 'حساب کاربری',
    'Pages': 'صفحات',
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
    'Enter Keyword or Part Number': 'کلمه کلیدی یا شماره قطعه را وارد کنید',
    'Select Vehicle': 'انتخاب وسیله نقلیه',
    'Select a vehicle to find exact fit parts': 'برای یافتن قطعات مناسب، وسیله نقلیه خود را انتخاب کنید',
    'Add A Vehicle': 'افزودن وسیله نقلیه',
    'Back to vehicles list': 'بازگشت به لیست وسایل نقلیه',
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
    'Headlights & Lighting': 'چراغ‌ها و روشنایی',
    'Interior Parts': 'لوازم داخلی',
    'Body Parts': 'قطعات بدنه',
    'Suspension': 'سیستم تعلیق',
    'Steering': 'فرمان',
    'Fuel Systems': 'سیستم سوخت',
    'Transmission': 'گیربکس',
    'Air Filters': 'فیلتر هوا',
    'Select Year': 'انتخاب سال',
    'Select Brand': 'انتخاب برند',
    'Select Model': 'انتخاب مدل',
    'Select Engine': 'انتخاب موتور',
    'Auto parts for Cars, trucks and motorcycles': 'قطعات خودرو برای اتومبیل‌ها، کامیون‌ها و موتورسیکلت‌ها',
}

def restore_and_translate(filename):
    """Restore file and translate carefully"""
    url = BASE_URL + filename
    filepath = os.path.join(BASE_DIR, filename)
    
    try:
        # Download original
        req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
        with urllib.request.urlopen(req, timeout=30) as response:
            content = response.read().decode('utf-8')
        
        # Change lang and dir
        content = content.replace('lang="en"', 'lang="fa"')
        content = content.replace('dir="ltr"', 'dir="rtl"')
        
        # Remove $ signs
        content = re.sub(r'\$(\d+\.?\d*)', r'\1', content)
        
        # Apply logo change
        logo_pattern = r'<div class="logo__image">[\s\S]*?</svg>[\s\S]*?</div>'
        new_logo = '<div class="logo__image"><img src="images/sttechLogo.png" alt="Logo" style="max-height: 52px;"></div>'
        content = re.sub(logo_pattern, new_logo, content)
        
        # Translate text (sorted by length, longest first)
        sorted_keys = sorted(TRANSLATIONS.keys(), key=len, reverse=True)
        for key in sorted_keys:
            value = TRANSLATIONS[key]
            # Only replace in text content, not in attributes
            # Replace in title tags
            content = content.replace(f'<title>{key}</title>', f'<title>{value}</title>')
            # Replace visible text between > and <
            pattern = r'(>)([^<]*?)(' + re.escape(key) + r')([^<]*?)(<)'
            def replacer(m):
                return m.group(1) + m.group(2) + value + m.group(4) + m.group(5)
            content = re.sub(pattern, replacer, content)
            # Replace in placeholder and aria-label
            content = content.replace(f'placeholder="{key}"', f'placeholder="{value}"')
            content = content.replace(f'aria-label="{key}"', f'aria-label="{value}"')
        
        # Save
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        return True
    except Exception as e:
        print(f"Error: {e}")
        return False

def main():
    print("=" * 70)
    print("RESTORING AND TRANSLATING TO FARSI")
    print("=" * 70)
    print()
    
    success = 0
    for filename in HTML_FILES:
        print(f"Processing: {filename}...", end=" ")
        if restore_and_translate(filename):
            print("✓")
            success += 1
        else:
            print("✗")
    
    print()
    print("=" * 70)
    print(f"Complete: {success} files processed")
    print("=" * 70)

if __name__ == '__main__':
    main()

