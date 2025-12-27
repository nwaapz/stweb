/**
 * Dynamic Block Zones Loader
 * Loads product categories from CMS and renders them as block-zone sections
 */

(function ($) {
    'use strict';

    // SVG icons (reused across all zones)
    const SVG_ICONS = {
        arrowPrev: '<svg width="7" height="11"><path d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z"/></svg>',
        arrowNext: '<svg width="7" height="11"><path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9C-0.1,9.8-0.1,10.4,0.3,10.7z"/></svg>',
        quickview: '<svg width="16" height="16"><path d="M14,15h-4v-2h3v-3h2v4C15,14.6,14.6,15,14,15z M13,3h-3V1h4c0.6,0,1,0.4,1,1v4h-2V3z M6,3H3v3H1V2c0-0.6,0.4-1,1-1h4V3z M3,13h3v2H2c-0.6,0-1-0.4-1-1v-4h2V13z"/></svg>',
        wishlist: '<svg width="16" height="16"><path d="M13.9,8.4l-5.4,5.4c-0.3,0.3-0.7,0.3-1,0L2.1,8.4c-1.5-1.5-1.5-3.8,0-5.3C2.8,2.4,3.8,2,4.8,2s1.9,0.4,2.6,1.1L8,3.7l0.6-0.6C9.3,2.4,10.3,2,11.3,2c1,0,1.9,0.4,2.6,1.1C15.4,4.6,15.4,6.9,13.9,8.4z"/></svg>',
        compare: '<svg width="16" height="16"><path d="M9,15H7c-0.6,0-1-0.4-1-1V2c0-0.6,0.4-1,1-1h2c0.6,0,1,0.4,1,1v12C10,14.6,9.6,15,9,15z"/><path d="M1,9h2c0.6,0,1,0.4,1,1v4c0,0.6-0.4,1-1,1H1c-0.6,0-1-0.4-1-1v-4C0,9.4,0.4,9,1,9z"/><path d="M15,5h-2c-0.6,0-1,0.4-1,1v8c0,0.6,0.4,1,1,1h2c0.6,0,1-0.4,1-1V6C16,5.4,15.6,5,15,5z"/></svg>',
        addToCart: '<svg width="20" height="20"><circle cx="7" cy="17" r="2"/><circle cx="15" cy="17" r="2"/><path d="M20,4.4V5l-1.8,6.3c-0.1,0.4-0.5,0.7-1,0.7H6.7c-0.4,0-0.8-0.3-1-0.7L3.3,3.9C3.1,3.3,2.6,3,2.1,3H0.4C0.2,3,0,2.8,0,2.6V1.4C0,1.2,0.2,1,0.4,1h2.5c1,0,1.8,0.6,2.1,1.6L5.1,3l2.3,6.8c0,0.1,0.2,0.2,0.3,0.2h8.6c0.1,0,0.3-0.1,0.3-0.2l1.3-4.4C17.9,5.2,17.7,5,17.5,5H9.4C9.2,5,9,4.8,9,4.6V3.4C9,3.2,9.2,3,9.4,3h9.2C19.4,3,20,3.6,20,4.4z"/></svg>',
        check: '<svg width="13" height="13"><path d="M12,4.4L5.5,11L1,6.5l1.4-1.4l3.1,3.1L10.6,3L12,4.4z"/></svg>'
    };

    /**
     * Fetch categories from CMS API
     */
    function fetchCategories() {
        return fetch('backend/api/categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    // Filter only categories with products and sort by some order (you can add order field to DB)
                    return data.data.filter(cat => cat.product_count > 0);
                }
                return [];
            })
            .catch(error => {
                console.error('Error fetching categories:', error);
                return [];
            });
    }

    /**
     * Fetch products for a category
     */
    function fetchCategoryProducts(categoryId, type = 'featured', limit = 8) {
        if (!categoryId) {
            console.error('Category ID is required');
            return Promise.resolve([]);
        }

        const params = new URLSearchParams({
            category: categoryId,
            limit: limit
        });

        if (type === 'featured') {
            params.append('featured', '1');
        } else if (type === 'bestsellers') {
            // Bestsellers should show popular products (ordered by views)
            params.append('popular', '1');
        } else if (type === 'popular') {
            // Popular products (ordered by views)
            params.append('popular', '1');
        }

        const url = `backend/api/products.php?${params.toString()}`;
        console.log('Fetching products:', url); // Debug log

        return fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    // Ensure all products belong to the requested category
                    const filteredProducts = data.data.filter(product => {
                        return product.category_id == categoryId;
                    });
                    console.log(`Fetched ${data.data.length} products, ${filteredProducts.length} match category ${categoryId}`);
                    return filteredProducts;
                }
                return [];
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                return [];
            });
    }

    /**
     * Generate product card HTML
     */
    function generateProductCard(product) {
        const productUrl = `product-full.html?id=${product.id}`;
        let productImage = product.image_url || 'images/products/product-1-245x245.jpg';
        if (productImage && productImage.startsWith('products/')) {
            productImage = 'backend/uploads/' + productImage;
        }
        const productName = product.name || 'محصول';
        const productSku = product.sku || '';
        const productPrice = product.formatted_price || '0.00';
        const rating = product.rating || 0;
        const reviewCount = product.review_count || 0;

        // Show discount if formatted_discount_price exists OR discount_percent exists
        // Don't rely on has_discount flag since it might be false if discount hasn't started yet
        let hasDiscount = false;
        let discountPrice = product.formatted_discount_price || null;

        if (discountPrice && discountPrice.trim() !== '') {
            hasDiscount = true;
        } else if (product.discount_percent && product.discount_percent > 0 && product.price) {
            // Calculate discount price if not provided
            const priceStr = product.price.toString().replace(/[^\d]/g, '');
            const originalPrice = parseFloat(priceStr);
            const discountPercent = parseFloat(product.discount_percent);

            if (!isNaN(originalPrice) && !isNaN(discountPercent) && discountPercent > 0) {
                const calculatedPrice = Math.round(originalPrice - (originalPrice * discountPercent / 100));
                discountPrice = calculatedPrice.toLocaleString('fa-IR') + ' تومان';
                hasDiscount = true;
            }
        }

        // Generate rating stars
        let ratingStars = '';
        const fullStars = Math.floor(rating);
        for (let i = 0; i < 5; i++) {
            ratingStars += `<div class="rating__star ${i < fullStars ? 'rating__star--active' : ''}"></div>`;
        }

        // Generate badges
        let badges = '';
        if (product.is_new) {
            badges += '<div class="tag-badge tag-badge--new">جدید</div>';
        }
        if (hasDiscount) {
            badges += '<div class="tag-badge tag-badge--sale">حراج</div>';
        }
        if (product.is_featured || product.is_hot) {
            badges += '<div class="tag-badge tag-badge--hot">ویژه</div>';
        }

        return `
            <div class="block-zone__carousel-item">
                <div class="product-card">
                    <div class="product-card__actions-list">
                        <button class="product-card__action product-card__action--quickview" type="button" aria-label="Quick view">
                            ${SVG_ICONS.quickview}
                        </button>
                        <button class="product-card__action product-card__action--wishlist" type="button" aria-label="Add to wish list">
                            ${SVG_ICONS.wishlist}
                        </button>
                        <button class="product-card__action product-card__action--compare" type="button" aria-label="Add to compare">
                            ${SVG_ICONS.compare}
                        </button>
                    </div>
                    <div class="product-card__image">
                        <div class="image image--type--product">
                            <a href="${productUrl}" class="image__body">
                                <img class="image__tag" src="${productImage}" alt="${productName}">
                            </a>
                        </div>
                        <div class="status-badge status-badge--style--success product-card__fit status-badge--has-icon status-badge--has-text">
                            <div class="status-badge__body">
                                <div class="status-badge__icon">${SVG_ICONS.check}</div>
                                <div class="status-badge__text">مناسب برای 2011 Ford Focus S</div>
                                <div class="status-badge__tooltip" tabindex="0" data-toggle="tooltip" title="مناسب برای 2011 Ford Focus S"></div>
                            </div>
                        </div>
                    </div>
                    <div class="product-card__info">
                        ${productSku ? `<div class="product-card__meta"><span class="product-card__meta-title">شناسه:</span> ${productSku}</div>` : ''}
                        <div class="product-card__name">
                            <div>
                                ${badges ? `<div class="product-card__badges">${badges}</div>` : ''}
                                <a href="${productUrl}">${productName}</a>
                            </div>
                        </div>
                        <div class="product-card__rating">
                            <div class="rating product-card__rating-stars">
                                <div class="rating__body">${ratingStars}</div>
                            </div>
                            <div class="product-card__rating-label">${rating} از ${reviewCount} نظر</div>
                        </div>
                    </div>
                    <div class="product-card__footer">
                        <div class="product-card__prices">
                            ${hasDiscount
                ? `<div class="product-card__price product-card__price--new">${discountPrice}</div>
                                   <div class="product-card__price product-card__price--old">${productPrice}</div>`
                : `<div class="product-card__price product-card__price--current">${productPrice}</div>`
            }
                        </div>
                        <button class="product-card__addtocart-icon" type="button" aria-label="Add to cart">
                            ${SVG_ICONS.addToCart}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Generate block-zone HTML for a category
     */
    function generateBlockZone(category, products, index) {
        const categoryUrl = `category-4-columns-sidebar.html?id=${category.id}`;
        const categoryName = category.name || 'دسته‌بندی';
        const categoryImage = category.image_url || 'images/categories/category-overlay-1.jpg';
        const categoryImageMobile = category.image_url || 'images/categories/category-overlay-1-mobile.jpg';

        // Determine spacing class (first one uses divider-lg, others use divider-sm)
        const spacingClass = index === 0 ? 'block-space--layout--divider-lg' : 'block-space--layout--divider-sm';

        // Generate category children links (if you have subcategories)
        let childrenLinks = '';
        if (category.children && category.children.length > 0) {
            category.children.forEach(child => {
                childrenLinks += `<li><a href="${categoryUrl}">${child.name}</a></li>`;
            });
        } else {
            // Default placeholder links
            childrenLinks = `
                <li><a href="${categoryUrl}">همه محصولات</a></li>
            `;
        }

        // Generate product carousel items
        let carouselItems = '';
        if (products && products.length > 0) {
            products.forEach(product => {
                carouselItems += generateProductCard(product);
            });
        } else {
            carouselItems = '<div class="block-zone__carousel-item"><p style="text-align: center; padding: 20px;">محصولی یافت نشد</p></div>';
        }

        return `
            <div class="block-space ${spacingClass}"></div>
            <div class="block block-zone" data-category-id="${category.id}">
                <div class="container">
                    <div class="block-zone__body">
                        <div class="block-zone__card category-card category-card--layout--overlay">
                            <div class="category-card__body">
                                <div class="category-card__overlay-image">
                                    <img srcset="${categoryImageMobile} 530w, ${categoryImage} 305w" 
                                         src="${categoryImage}" 
                                         sizes="(max-width: 575px) 530px, 305px" alt="${categoryName}">
                                </div>
                                <div class="category-card__overlay-image category-card__overlay-image--blur">
                                    <img srcset="${categoryImageMobile} 530w, ${categoryImage} 305w" 
                                         src="${categoryImage}" 
                                         sizes="(max-width: 575px) 530px, 305px" alt="${categoryName}">
                                </div>
                                <div class="category-card__content">
                                    <div class="category-card__info">
                                        <div class="category-card__name">
                                            <a href="${categoryUrl}">${categoryName}</a>
                                        </div>
                                        <ul class="category-card__children">
                                            ${childrenLinks}
                                        </ul>
                                        <div class="category-card__actions">
                                            <a href="${categoryUrl}" class="btn btn-primary btn-sm">فروشگاه</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="block-zone__widget">
                            <div class="block-zone__widget-header">
                                <div class="block-zone__tabs">
                                    <button class="block-zone__tabs-button block-zone__tabs-button--active" type="button" data-tab="featured">ویژه</button>
                                    <button class="block-zone__tabs-button" type="button" data-tab="bestsellers">پرفروش‌ترین</button>
                                    <button class="block-zone__tabs-button" type="button" data-tab="popular">محبوب</button>
                                </div>
                                <div class="arrow block-zone__arrow block-zone__arrow--prev arrow--prev">
                                    <button class="arrow__button" type="button">${SVG_ICONS.arrowPrev}</button>
                                </div>
                                <div class="arrow block-zone__arrow block-zone__arrow--next arrow--next">
                                    <button class="arrow__button" type="button">${SVG_ICONS.arrowNext}</button>
                                </div>
                            </div>
                            <div class="block-zone__widget-body">
                                <div class="block-zone__carousel">
                                    <div class="block-zone__carousel-loader"></div>
                                    <div class="owl-carousel">
                                        ${carouselItems}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Initialize Owl Carousel for a block-zone
     */
    function initializeCarousel(blockZone) {
        const owlCarousel = blockZone.find('.owl-carousel');

        if (owlCarousel.length === 0) {
            return;
        }

        // Check if Owl Carousel is available
        if (typeof $.fn.owlCarousel === 'undefined') {
            console.warn('Owl Carousel not loaded');
            return;
        }

        owlCarousel.owlCarousel({
            dots: false,
            margin: 20,
            loop: true,
            items: 4,
            rtl: true, // Assuming RTL for Persian
            responsive: {
                1400: { items: 4, margin: 20 },
                992: { items: 3, margin: 16 },
                460: { items: 2, margin: 16 },
                0: { items: 1 }
            }
        });

        // Attach navigation arrows
        blockZone.find('.block-zone__arrow--prev').on('click', function () {
            owlCarousel.trigger('prev.owl.carousel', [500]);
        });

        blockZone.find('.block-zone__arrow--next').on('click', function () {
            owlCarousel.trigger('next.owl.carousel', [500]);
        });

        // Tab switching is handled via event delegation at document level
        // No need to attach handlers here as they would be lost on carousel reinit
    }

    /**
     * Load and render all block zones
     */
    function loadBlockZones() {
        const container = $('.block-zones-container');

        if (container.length === 0) {
            console.warn('Block zones container not found');
            return;
        }

        // Show loading state
        container.html(`
            <div class="block-zones-loading" style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status">
                    <span class="sr-only">در حال بارگذاری...</span>
                </div>
            </div>
        `);

        // Fetch categories
        fetchCategories().then(categories => {
            if (categories.length === 0) {
                container.html('<div style="text-align: center; padding: 40px;">دسته‌بندی‌ای یافت نشد</div>');
                return;
            }

            // Clear loading
            container.empty();

            // Load each category with its products
            const promises = categories.map((category, index) => {
                return fetchCategoryProducts(category.id, 'featured', 8).then(products => {
                    const blockZoneHtml = generateBlockZone(category, products, index);
                    const $blockZone = $(blockZoneHtml);
                    container.append($blockZone);

                    // Initialize carousel after a short delay to ensure DOM is ready
                    setTimeout(() => {
                        initializeCarousel($blockZone);
                    }, 100);
                });
            });

            // Wait for all categories to load
            Promise.all(promises).then(() => {
                console.log('All block zones loaded successfully');
            }).catch(error => {
                console.error('Error loading block zones:', error);
            });
        });
    }

    // Use event delegation for tab buttons so they work even after carousel reinitialization
    $(document).on('click', '.block-zone__tabs-button', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const button = $(this);
        const blockZone = button.closest('.block-zone');
        const tabType = button.data('tab');
        const categoryId = blockZone.data('category-id');

        if (!categoryId) {
            console.error('Category ID not found for block zone');
            return false;
        }

        // Don't do anything if already active
        if (button.hasClass('block-zone__tabs-button--active')) {
            return false;
        }

        console.log(`Switching to tab: ${tabType} for category: ${categoryId}`);

        // Update active state
        blockZone.find('.block-zone__tabs-button').removeClass('block-zone__tabs-button--active');
        button.addClass('block-zone__tabs-button--active');

        // Show loading state
        const carouselContainer = blockZone.find('.block-zone__carousel');
        const carousel = blockZone.find('.owl-carousel');
        const loader = blockZone.find('.block-zone__carousel-loader');
        carouselContainer.addClass('block-zone__carousel--loading');
        loader.show();

        // Reload products for this tab type
        fetchCategoryProducts(categoryId, tabType, 8).then(products => {
            loader.hide();
            carouselContainer.removeClass('block-zone__carousel--loading');

            // Filter products to ensure they belong to this category (double check)
            const filteredProducts = products.filter(product => {
                const matches = parseInt(product.category_id) === parseInt(categoryId);
                if (!matches) {
                    console.warn(`Product ${product.id} (category: ${product.category_id}) doesn't match requested category ${categoryId}`);
                }
                return matches;
            });

            // Destroy carousel
            carousel.trigger('destroy.owl.carousel');

            // Clear and rebuild content
            carousel.empty();

            if (filteredProducts.length > 0) {
                filteredProducts.forEach(product => {
                    const productCard = $(generateProductCard(product));
                    carousel.append(productCard);
                });
            } else {
                carousel.append('<div class="block-zone__carousel-item"><p style="text-align: center; padding: 20px;">محصولی یافت نشد</p></div>');
            }

            // Reinitialize carousel
            initializeCarousel(blockZone);

            return false;
        }).catch(error => {
            loader.hide();
            carouselContainer.removeClass('block-zone__carousel--loading');
            console.error('Error loading products:', error);
        });

        return false;
    });

    // Initialize when DOM is ready
    $(document).ready(function () {
        loadBlockZones();
    });

})(jQuery);

