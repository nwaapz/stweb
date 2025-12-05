TO FIX MENU AND NAVIGATION FONTS:

1. Translate "Menu" to "منو" (Farsi)
2. Make navigation links (خانه، فروشگاه، وبلاگ، حساب کاربری، صفحات) bold with better Farsi font

Run this command:
py update_menu_final.py

Or manually:
- Replace: departments__button-title">Menu</span>  →  departments__button-title">منو</span>
- Wrap navigation text in <strong> tags
- CSS is already added for Vazir font



