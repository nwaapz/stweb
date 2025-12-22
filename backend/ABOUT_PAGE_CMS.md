# سیستم مدیریت صفحه درباره ما

## نصب و راه‌اندازی

### 1. اجرای مایگریشن پایگاه داده

برای ایجاد جداول مورد نیاز، فایل مایگریشن را اجرا کنید:

```bash
php backend/migrate_about.php
```

یا در مرورگر:
```
http://your-site.com/backend/migrate_about.php
```

این فایل جداول زیر را ایجاد می‌کند:
- `about_page` - محتوای اصلی صفحه
- `about_team` - اعضای تیم
- `about_testimonials` - نظرات مشتریان
- `about_statistics` - آمار و ارقام

### 2. دسترسی به پنل مدیریت

1. وارد پنل مدیریت شوید: `http://your-site.com/backend/admin/`
2. از منوی سمت راست، روی "صفحه درباره ما" کلیک کنید
3. یا مستقیماً به آدرس زیر بروید: `http://your-site.com/backend/admin/about.php`

## ساختار پایگاه داده

### جدول `about_page`
محتوای اصلی صفحه درباره ما:
- `id` - شناسه
- `title` - عنوان صفحه
- `description` - توضیحات
- `author_name` - نام نویسنده
- `author_title` - عنوان نویسنده
- `feature_image` - تصویر اصلی
- `is_active` - وضعیت فعال/غیرفعال
- `created_at`, `updated_at` - تاریخ ایجاد و به‌روزرسانی

### جدول `about_team`
اعضای تیم:
- `id` - شناسه
- `name` - نام
- `position` - سمت
- `description` - توضیحات (اختیاری)
- `image` - تصویر
- `sort_order` - ترتیب نمایش
- `is_active` - وضعیت فعال/غیرفعال
- `created_at`, `updated_at` - تاریخ ایجاد و به‌روزرسانی

### جدول `about_testimonials`
نظرات مشتریان:
- `id` - شناسه
- `text` - متن نظر
- `author_name` - نام نویسنده
- `author_title` - عنوان نویسنده (اختیاری)
- `rating` - امتیاز (1-5)
- `avatar` - تصویر پروفایل (اختیاری)
- `sort_order` - ترتیب نمایش
- `is_active` - وضعیت فعال/غیرفعال
- `created_at`, `updated_at` - تاریخ ایجاد و به‌روزرسانی

### جدول `about_statistics`
آمار و ارقام:
- `id` - شناسه
- `value` - مقدار (مثلاً "350" یا "80 000")
- `title` - عنوان (مثلاً "فروشگاه در سراسر جهان")
- `sort_order` - ترتیب نمایش
- `is_active` - وضعیت فعال/غیرفعال
- `created_at`, `updated_at` - تاریخ ایجاد و به‌روزرسانی

## استفاده از پنل مدیریت

### مدیریت محتوای اصلی
1. در تب "محتوای اصلی"، می‌توانید:
   - عنوان صفحه را ویرایش کنید
   - توضیحات را وارد کنید
   - نام و عنوان نویسنده را تنظیم کنید
   - تصویر اصلی را آپلود کنید
   - وضعیت فعال/غیرفعال را تغییر دهید

### مدیریت اعضای تیم
1. در تب "اعضای تیم":
   - برای افزودن عضو جدید، روی "افزودن عضو جدید" کلیک کنید
   - نام، سمت، توضیحات و تصویر را وارد کنید
   - ترتیب نمایش را تنظیم کنید
   - برای ویرایش، روی آیکون مداد کلیک کنید
   - برای حذف، روی آیکون سطل زباله کلیک کنید

### مدیریت نظرات مشتریان
1. در تب "نظرات مشتریان":
   - برای افزودن نظر جدید، روی "افزودن نظر جدید" کلیک کنید
   - متن نظر، نام نویسنده، عنوان و امتیاز (1-5) را وارد کنید
   - تصویر پروفایل را آپلود کنید (اختیاری)
   - ترتیب نمایش را تنظیم کنید

### مدیریت آمار و ارقام
1. در تب "آمار و ارقام":
   - برای افزودن آمار جدید، روی "افزودن آمار جدید" کلیک کنید
   - مقدار (مثلاً "350") و عنوان (مثلاً "فروشگاه در سراسر جهان") را وارد کنید
   - ترتیب نمایش را تنظیم کنید

## API

### دریافت اطلاعات صفحه درباره ما

```
GET /backend/api/about.php
```

**پاسخ نمونه:**
```json
{
    "success": true,
    "about": {
        "id": 1,
        "title": "درباره ما",
        "description": "توضیحات...",
        "author_name": "حسین عبدالمحمدی",
        "author_title": "CEO RedParts",
        "feature_image_url": "http://site.com/backend/uploads/about/image.jpg",
        "is_active": 1
    },
    "team": [
        {
            "id": 1,
            "name": "مایکل روسو",
            "position": "مدیر عامل",
            "description": "توضیحات...",
            "image_url": "http://site.com/backend/uploads/about/team/image.jpg",
            "sort_order": 0
        }
    ],
    "testimonials": [
        {
            "id": 1,
            "text": "متن نظر...",
            "author_name": "جسیکا مور",
            "author_title": "CEO Meblya",
            "rating": 4,
            "avatar_url": "http://site.com/backend/uploads/about/testimonials/avatar.jpg",
            "sort_order": 0
        }
    ],
    "statistics": [
        {
            "id": 1,
            "value": "350",
            "title": "فروشگاه در سراسر جهان",
            "sort_order": 1
        }
    ],
    "count": {
        "team": 5,
        "testimonials": 3,
        "statistics": 3
    }
}
```

## استفاده در فرانت‌اند

### مثال JavaScript

```javascript
// دریافت اطلاعات صفحه درباره ما
fetch('/backend/api/about.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // نمایش محتوای اصلی
            if (data.about) {
                document.querySelector('.about__card-title').textContent = data.about.title;
                document.querySelector('.about__card-text').textContent = data.about.description;
                document.querySelector('.about__card-author').textContent = 
                    data.about.author_name + ', ' + data.about.author_title;
                
                if (data.about.feature_image_url) {
                    document.querySelector('.about__image-bg').style.backgroundImage = 
                        `url('${data.about.feature_image_url}')`;
                }
            }
            
            // نمایش اعضای تیم
            const teamContainer = document.querySelector('.block-teammates__list .owl-carousel');
            data.team.forEach(member => {
                const memberHTML = `
                    <div class="block-teammates__item teammate">
                        <div class="teammate__avatar">
                            <img src="${member.image_url || 'images/placeholder.jpg'}" alt="${member.name}">
                        </div>
                        <div class="teammate__info">
                            <div class="teammate__name">${member.name}</div>
                            <div class="teammate__position">${member.position}</div>
                        </div>
                    </div>
                `;
                teamContainer.innerHTML += memberHTML;
            });
            
            // نمایش نظرات مشتریان
            const testimonialsContainer = document.querySelector('.block-reviews__list .owl-carousel');
            data.testimonials.forEach(testimonial => {
                const stars = Array(testimonial.rating).fill('<div class="rating__star rating__star--active"></div>').join('');
                const testimonialHTML = `
                    <div class="block-reviews__item">
                        <div class="block-reviews__item-avatar">
                            <img src="${testimonial.avatar_url || 'images/placeholder.jpg'}" alt="${testimonial.author_name}">
                        </div>
                        <div class="block-reviews__item-content">
                            <div class="block-reviews__item-text">${testimonial.text}</div>
                            <div class="block-reviews__item-meta">
                                <div class="block-reviews__item-rating">
                                    <div class="rating">
                                        <div class="rating__body">${stars}</div>
                                    </div>
                                </div>
                                <div class="block-reviews__item-author">
                                    ${testimonial.author_name}${testimonial.author_title ? ', ' + testimonial.author_title : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                testimonialsContainer.innerHTML += testimonialHTML;
            });
            
            // نمایش آمار
            const statsContainer = document.querySelector('.about__indicators-body');
            data.statistics.forEach(stat => {
                const statHTML = `
                    <div class="about__indicators-item">
                        <div class="about__indicators-item-value">${stat.value}</div>
                        <div class="about__indicators-item-title">${stat.title}</div>
                    </div>
                `;
                statsContainer.innerHTML += statHTML;
            });
        }
    });
```

## مسیرهای آپلود تصاویر

تصاویر در مسیرهای زیر ذخیره می‌شوند:
- تصویر اصلی: `backend/uploads/about/`
- تصاویر اعضای تیم: `backend/uploads/about/team/`
- تصاویر پروفایل نظرات: `backend/uploads/about/testimonials/`

## نکات مهم

1. **فرمت تصاویر**: فقط فرمت‌های JPEG, PNG, GIF, WebP مجاز هستند
2. **اندازه تصاویر**: توصیه می‌شود تصاویر را قبل از آپلود بهینه کنید
3. **ترتیب نمایش**: از فیلد `sort_order` برای تعیین ترتیب نمایش استفاده کنید
4. **وضعیت فعال**: فقط مواردی که `is_active = 1` دارند در API نمایش داده می‌شوند

## پشتیبانی

برای سوالات و مشکلات، با تیم توسعه تماس بگیرید.

