/**
 * Dynamic Products Loader for Shop Pages
 * بارگذاری پویای محصولات برای صفحات فروشگاه
 */
(function ($) {
    "use strict";

    // Product loader state
    const ProductLoader = {
        currentPage: 1,
        limit: 16,
        sortBy: 'created_at',
        sortDir: 'DESC',
        layout: 'grid',
        withFeatures: false,
        filters: {},
        totalProducts: 0,
        isLoading: false,
        $container: null,
        $productsList: null,
        $pagination: null,
        $legend: null,
        $sortSelect: null,
        $limitSelect: null,
        $layoutSwitcher: null
    };

    /**
     * Initialize the product loader
     */
    function init() {
        // Find the products view container
        ProductLoader.$container = $('.products-view');
        if (!ProductLoader.$container.length) {
            return; // Not on a shop page
        }

        ProductLoader.$productsList = ProductLoader.$container.find('.products-list');
        ProductLoader.$pagination = ProductLoader.$container.find('.products-view__pagination');
        ProductLoader.$legend = ProductLoader.$container.find('.view-options__legend');
        ProductLoader.$sortSelect = ProductLoader.$container.find('#view-option-sort');
        ProductLoader.$limitSelect = ProductLoader.$container.find('#view-option-limit');
        ProductLoader.$layoutSwitcher = ProductLoader.$container.find('.layout-switcher');
        
        // Show pagination initially (will be updated after products load)
        if (ProductLoader.$pagination.length) {
            ProductLoader.$pagination.show();
        }

        // Get initial values from URL params
        const urlParams = new URLSearchParams(window.location.search);
        ProductLoader.currentPage = parseInt(urlParams.get('page')) || 1;
        ProductLoader.limit = parseInt(urlParams.get('limit')) || 16;
        ProductLoader.sortBy = urlParams.get('sort') || 'created_at';
        ProductLoader.sortDir = urlParams.get('dir') || 'DESC';

        // Get category/vehicle from URL
        if (urlParams.get('category')) {
            ProductLoader.filters.category_id = parseInt(urlParams.get('category'));
        }
        if (urlParams.get('vehicle')) {
            ProductLoader.filters.vehicle_id = parseInt(urlParams.get('vehicle'));
        }
        if (urlParams.get('search')) {
            ProductLoader.filters.search = urlParams.get('search');
        }
        if (urlParams.get('featured')) {
            ProductLoader.filters.is_featured = true;
        }
        if (urlParams.get('discounted')) {
            ProductLoader.filters.has_discount = true;
        }
        if (urlParams.get('price_min')) {
            ProductLoader.filters.price_min = parseFloat(urlParams.get('price_min'));
        }
        if (urlParams.get('price_max')) {
            ProductLoader.filters.price_max = parseFloat(urlParams.get('price_max'));
        }

        // Initialize UI elements
        initSortSelect();
        initLimitSelect();
        initLayoutSwitcher();
        initPagination();

        // Load products on page load
        loadProducts();
    }

    /**
     * Initialize sort select dropdown
     */
    function initSortSelect() {
        if (!ProductLoader.$sortSelect.length) return;

        // Populate sort options
        const sortOptions = [
            { value: 'created_at', text: 'جدیدترین' },
            { value: 'price', text: 'قیمت: کم به زیاد' },
            { value: 'price_desc', text: 'قیمت: زیاد به کم' },
            { value: 'name', text: 'نام: الف-ی' },
            { value: 'views', text: 'پربازدیدترین' }
        ];

        ProductLoader.$sortSelect.empty();
        sortOptions.forEach(option => {
            const selected = (option.value === ProductLoader.sortBy || 
                            (option.value === 'price_desc' && ProductLoader.sortBy === 'price' && ProductLoader.sortDir === 'DESC')) ? 'selected' : '';
            ProductLoader.$sortSelect.append(`<option value="${option.value}" ${selected}>${option.text}</option>`);
        });

        ProductLoader.$sortSelect.on('change', function() {
            const value = $(this).val();
            if (value === 'price_desc') {
                ProductLoader.sortBy = 'price';
                ProductLoader.sortDir = 'DESC';
            } else {
                ProductLoader.sortBy = value;
                ProductLoader.sortDir = value === 'price' ? 'ASC' : 'DESC';
            }
            ProductLoader.currentPage = 1;
            loadProducts();
            updateURL();
        });
    }

    /**
     * Initialize limit select dropdown
     */
    function initLimitSelect() {
        if (!ProductLoader.$limitSelect.length) return;

        const limitOptions = [4, 12, 16, 24, 32, 48];
        ProductLoader.$limitSelect.empty();
        limitOptions.forEach(limit => {
            const selected = limit === ProductLoader.limit ? 'selected' : '';
            ProductLoader.$limitSelect.append(`<option value="${limit}" ${selected}>${limit}</option>`);
        });

        ProductLoader.$limitSelect.on('change', function() {
            ProductLoader.limit = parseInt($(this).val());
            ProductLoader.currentPage = 1;
            loadProducts();
            updateURL();
        });
    }

    /**
     * Initialize layout switcher
     */
    function initLayoutSwitcher() {
        if (!ProductLoader.$layoutSwitcher.length) return;

        ProductLoader.$layoutSwitcher.find('.layout-switcher__button').on('click', function() {
            const $button = $(this);
            ProductLoader.layout = $button.data('layout');
            ProductLoader.withFeatures = $button.data('with-features') === true;

            // Update active state
            ProductLoader.$layoutSwitcher.find('.layout-switcher__button')
                .removeClass('layout-switcher__button--active')
                .removeAttr('disabled');
            $button.addClass('layout-switcher__button--active').attr('disabled', '');

            // Update products list attributes
            ProductLoader.$productsList.attr('data-layout', ProductLoader.layout);
            ProductLoader.$productsList.attr('data-with-features', ProductLoader.withFeatures);
        });
    }

    /**
     * Initialize pagination
     */
    function initPagination() {
        if (!ProductLoader.$pagination.length) return;

        ProductLoader.$pagination.on('click', '.page-link', function(e) {
            e.preventDefault();
            const $link = $(this);
            const $pageItem = $link.closest('.page-item');

            if ($pageItem.hasClass('disabled') || $pageItem.hasClass('active')) {
                return;
            }

            const href = $link.attr('href');
            if (href && href !== '#') {
                const pageMatch = href.match(/page=(\d+)/);
                if (pageMatch) {
                    ProductLoader.currentPage = parseInt(pageMatch[1]);
                } else if ($link.attr('aria-label') === 'Previous') {
                    ProductLoader.currentPage = Math.max(1, ProductLoader.currentPage - 1);
                } else if ($link.attr('aria-label') === 'Next') {
                    ProductLoader.currentPage = ProductLoader.currentPage + 1;
                }
                loadProducts();
                updateURL();
            }
        });
    }

    /**
     * Build API URL with current filters
     */
    function buildAPIUrl() {
        const params = new URLSearchParams();
        
        // Add filters
        if (ProductLoader.filters.category_id) {
            params.append('category', ProductLoader.filters.category_id);
        }
        if (ProductLoader.filters.vehicle_id) {
            params.append('vehicle', ProductLoader.filters.vehicle_id);
        }
        if (ProductLoader.filters.search) {
            params.append('search', ProductLoader.filters.search);
        }
        if (ProductLoader.filters.is_featured) {
            params.append('featured', '1');
        }
        if (ProductLoader.filters.has_discount) {
            params.append('discounted', '1');
        }
        if (ProductLoader.filters.price_min) {
            params.append('price_min', ProductLoader.filters.price_min);
        }
        if (ProductLoader.filters.price_max) {
            params.append('price_max', ProductLoader.filters.price_max);
        }

        // Add sorting
        params.append('order_by', ProductLoader.sortBy);
        params.append('order_dir', ProductLoader.sortDir);

        // Add pagination
        const offset = (ProductLoader.currentPage - 1) * ProductLoader.limit;
        params.append('limit', ProductLoader.limit);
        params.append('offset', offset);

        return 'backend/api/products.php?' + params.toString();
    }

    /**
     * Load products from API
     */
    function loadProducts() {
        if (ProductLoader.isLoading) return;
        ProductLoader.isLoading = true;

        const $content = ProductLoader.$productsList.find('.products-list__content');
        if (!$content.length) {
            // Create content container if it doesn't exist
            const $contentDiv = $('<div class="products-list__content"></div>');
            ProductLoader.$productsList.append($contentDiv);
            ProductLoader.$productsList = ProductLoader.$productsList; // Update reference
        }

        const $contentContainer = ProductLoader.$productsList.find('.products-list__content');
        
        // Show loading state
        $contentContainer.html('<div style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="sr-only">در حال بارگذاری...</span></div></div>');

        fetch(buildAPIUrl())
            .then(response => response.json())
            .then(data => {
                ProductLoader.isLoading = false;
                
                if (data.success && data.data) {
                    ProductLoader.totalProducts = data.total || data.count || data.data.length;
                    renderProducts(data.data);
                    updatePagination();
                    updateLegend();
                    
                    // Update price filter range if available
                    if (data.price_range) {
                        updatePriceFilterRange(data.price_range);
                    }
                } else {
                    $contentContainer.html('<div style="text-align: center; padding: 40px;">محصولی یافت نشد</div>');
                }
            })
            .catch(error => {
                ProductLoader.isLoading = false;
                console.error('Error loading products:', error);
                $contentContainer.html('<div style="text-align: center; padding: 40px;">خطا در بارگذاری محصولات</div>');
            });
    }

    /**
     * Render products
     */
    function renderProducts(products) {
        const $contentContainer = ProductLoader.$productsList.find('.products-list__content');
        $contentContainer.empty();

        if (products.length === 0) {
            $contentContainer.html('<div style="text-align: center; padding: 40px;">محصولی یافت نشد</div>');
            return;
        }

        products.forEach(product => {
            const $item = createProductCard(product);
            $contentContainer.append($item);
        });

        // Re-initialize quickview handlers
        $('.product-card__action--quickview', ProductLoader.$container).off('click').on('click', function() {
            if (typeof quickview !== 'undefined' && quickview.clickHandler) {
                quickview.clickHandler.apply(this, arguments);
            }
        });
    }

    /**
     * Create a product card element
     */
    function createProductCard(product) {
        const productUrl = product.slug ? 
            'product-full.html?product=' + encodeURIComponent(product.slug) : 
            'product-full.html?id=' + product.id;
        const productImage = product.image_url || 'images/products/product-1-245x245.jpg';
        const productName = product.name || 'بدون نام';
        const productPrice = product.formatted_price || '0';
        const discountPrice = product.formatted_discount_price || null;
        const productSku = product.sku || '';
        const vehicleName = product.vehicle_name || '';
        const productRating = parseFloat(product.rating) || 0;
        const productReviews = parseInt(product.reviews) || 0;
        const activeStars = Math.min(5, Math.max(0, Math.round(productRating)));

        // Build badges
        let badgesHtml = '';
        if (product.has_discount) {
            badgesHtml += '<div class="tag-badge tag-badge--sale">حراج</div>';
        }
        if (product.created_at) {
            try {
                const createdDate = new Date(product.created_at);
                const daysDiff = (new Date() - createdDate) / (1000 * 60 * 60 * 24);
                if (daysDiff <= 30) {
                    badgesHtml += '<div class="tag-badge tag-badge--new">جدید</div>';
                }
            } catch (e) {}
        }
        if (product.is_featured) {
            badgesHtml += '<div class="tag-badge tag-badge--hot">ویژه</div>';
        }

        // Build rating stars
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= activeStars ? 
                '<div class="rating__star rating__star--active"></div>' : 
                '<div class="rating__star"></div>';
        }

        // Build price
        let priceHtml = '';
        if (discountPrice && product.has_discount) {
            priceHtml = `<div class="product-card__price product-card__price--new">${discountPrice}</div>
                        <div class="product-card__price product-card__price--old">${productPrice}</div>`;
        } else {
            priceHtml = `<div class="product-card__price product-card__price--current">${productPrice}</div>`;
        }

        // Build vehicle badge
        let vehicleBadgeHtml = '';
        if (vehicleName) {
            vehicleBadgeHtml = `
                <div class="status-badge status-badge--style--success product-card__fit status-badge--has-icon status-badge--has-text">
                    <div class="status-badge__body">
                        <div class="status-badge__icon">
                            <svg width="13" height="13"><path d="M12,4.4L5.5,11L1,6.5l1.4-1.4l3.1,3.1L10.6,3L12,4.4z"></path></svg>
                        </div>
                        <div class="status-badge__text">مناسب برای ${vehicleName}</div>
                        <div class="status-badge__tooltip" tabindex="0" data-toggle="tooltip" title="" data-original-title="مناسب برای ${vehicleName}"></div>
                    </div>
                </div>`;
        }

        const $item = $(`
            <div class="products-list__item">
                <div class="product-card">
                    <div class="product-card__actions-list">
                        <button class="product-card__action product-card__action--quickview" type="button" aria-label="Quick view">
                            <svg width="16" height="16"><path d="M14,15h-4v-2h3v-3h2v4C15,14.6,14.6,15,14,15z M13,3h-3V1h4c0.6,0,1,0.4,1,1v4h-2V3z M6,3H3v3H1V2c0-0.6,0.4-1,1-1h4V3z M3,13h3v2H2c-0.6,0-1-0.4-1-1v-4h2V13z"/></svg>
                        </button>
                        <button class="product-card__action product-card__action--wishlist" type="button" aria-label="Add to wish list">
                            <svg width="16" height="16"><path d="M13.9,8.4l-5.4,5.4c-0.3,0.3-0.7,0.3-1,0L2.1,8.4c-1.5-1.5-1.5-3.8,0-5.3C2.8,2.4,3.8,2,4.8,2s1.9,0.4,2.6,1.1L8,3.7l0.6-0.6C9.3,2.4,10.3,2,11.3,2c1,0,1.9,0.4,2.6,1.1C15.4,4.6,15.4,6.9,13.9,8.4z"/></svg>
                        </button>
                        <button class="product-card__action product-card__action--compare" type="button" aria-label="Add to compare">
                            <svg width="16" height="16"><path d="M9,15H7c-0.6,0-1-0.4-1-1V2c0-0.6,0.4-1,1-1h2c0.6,0,1,0.4,1,1v12C10,14.6,9.6,15,9,15z"/><path d="M1,9h2c0.6,0,1,0.4,1,1v4c0,0.6-0.4,1-1,1H1c-0.6,0-1-0.4-1-1v-4C0,9.4,0.4,9,1,9z"/><path d="M15,5h-2c-0.6,0-1,0.4-1,1v8c0,0.6,0.4,1,1,1h2c0.6,0,1-0.4,1-1V6C16,5.4,15.6,5,15,5z"/></svg>
                        </button>
                    </div>
                    <div class="product-card__image">
                        <div class="image image--type--product">
                            <a href="${productUrl}" class="image__body">
                                <img class="image__tag" src="${productImage}" alt="${productName}" onerror="this.src='images/products/product-1-245x245.jpg'">
                            </a>
                        </div>
                        ${vehicleBadgeHtml}
                    </div>
                    <div class="product-card__info">
                        ${productSku ? `<div class="product-card__meta"><span class="product-card__meta-title">SKU:</span> ${productSku}</div>` : ''}
                        <div class="product-card__name">
                            <div>
                                ${badgesHtml ? `<div class="product-card__badges">${badgesHtml}</div>` : ''}
                                <a href="${productUrl}">${productName}</a>
                            </div>
                        </div>
                        <div class="product-card__rating">
                            <div class="rating product-card__rating-stars">
                                <div class="rating__body">${starsHtml}</div>
                            </div>
                            <div class="product-card__rating-label">${productRating.toFixed(1)} از ${productReviews} نظر</div>
                        </div>
                        ${ProductLoader.withFeatures ? '<div class="product-card__features"><ul><li>Speed: 750 RPM</li><li>Power Source: Cordless-Electric</li><li>Battery Cell Type: Lithium</li><li>Voltage: 20 Volts</li><li>Battery Capacity: 2 Ah</li></ul></div>' : ''}
                    </div>
                    <div class="product-card__footer">
                        <div class="product-card__prices">${priceHtml}</div>
                        <button class="product-card__addtocart-icon" type="button" aria-label="Add to cart">
                            <svg width="20" height="20"><circle cx="7" cy="17" r="2"></circle><circle cx="15" cy="17" r="2"></circle><path d="M20,4.4V5l-1.8,6.3c-0.1,0.4-0.5,0.7-1,0.7H6.7c-0.4,0-0.8-0.3-1-0.7L3.3,3.9C3.1,3.3,2.6,3,2.1,3H0.4C0.2,3,0,2.8,0,2.6V1.4C0,1.2,0.2,1,0.4,1h2.5c1,0,1.8,0.6,2.1,1.6L5.1,3l2.3,6.8c0,0.1,0.2,0.2,0.3,0.2h8.6c0.1,0,0.3-0.1,0.3-0.2l1.3-4.4C17.9,5.2,17.7,5,17.5,5H9.4C9.2,5,9,4.8,9,4.6V3.4C9,3.2,9.2,3,9.4,3h9.2C19.4,3,20,3.6,20,4.4z"/></svg>
                        </button>
                        <button class="product-card__addtocart-full" type="button">افزودن به سبد خرید</button>
                        <button class="product-card__wishlist" type="button">
                            <svg width="16" height="16"><path d="M13.9,8.4l-5.4,5.4c-0.3,0.3-0.7,0.3-1,0L2.1,8.4c-1.5-1.5-1.5-3.8,0-5.3C2.8,2.4,3.8,2,4.8,2s1.9,0.4,2.6,1.1L8,3.7l0.6-0.6C9.3,2.4,10.3,2,11.3,2c1,0,1.9,0.4,2.6,1.1C15.4,4.6,15.4,6.9,13.9,8.4z"/></svg>
                            <span>افزودن به علاقه‌مندی‌ها</span>
                        </button>
                        <button class="product-card__compare" type="button">
                            <svg width="16" height="16"><path d="M9,15H7c-0.6,0-1-0.4-1-1V2c0-0.6,0.4-1,1-1h2c0.6,0,1,0.4,1,1v12C10,14.6,9.6,15,9,15z"/><path d="M1,9h2c0.6,0,1,0.4,1,1v4c0,0.6-0.4,1-1,1H1c-0.6,0-1-0.4-1-1v-4C0,9.4,0.4,9,1,9z"/><path d="M15,5h-2c-0.6,0-1,0.4-1,1v8c0,0.6,0.4,1,1,1h2c0.6,0,1-0.4,1-1V6C16,5.4,15.6,5,15,5z"/></svg>
                            <span>مقایسه</span>
                        </button>
                    </div>
                </div>
            </div>
        `);

        return $item;
    }

    /**
     * Update pagination
     */
    function updatePagination() {
        if (!ProductLoader.$pagination.length) return;

        const totalPages = Math.ceil(ProductLoader.totalProducts / ProductLoader.limit);
        // Always show pagination, even if there's only 1 page
        ProductLoader.$pagination.show();
        
        if (totalPages <= 1) {
            // If only 1 page, just show page 1 as active
            const $paginationList = ProductLoader.$pagination.find('.pagination');
            $paginationList.empty();
            $paginationList.append(`
                <li class="page-item disabled">
                    <a class="page-link page-link--with-arrow" href="#" aria-label="Previous">
                        <span class="page-link__arrow page-link__arrow--left" aria-hidden="true">
                            <svg width="7" height="11"><path d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z"></path></svg>
                        </span>
                    </a>
                </li>
                <li class="page-item active" aria-current="page">
                    <span class="page-link">1 <span class="sr-only">(current)</span></span>
                </li>
                <li class="page-item disabled">
                    <a class="page-link page-link--with-arrow" href="#" aria-label="Next">
                        <span class="page-link__arrow page-link__arrow--right" aria-hidden="true">
                            <svg width="7" height="11"><path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9C-0.1,9.8-0.1,10.4,0.3,10.7z"></path></svg>
                        </span>
                    </a>
                </li>
            `);
            return;
        }
        const $paginationList = ProductLoader.$pagination.find('.pagination');
        $paginationList.empty();

        // Previous button
        const prevDisabled = ProductLoader.currentPage === 1 ? 'disabled' : '';
        const prevHref = ProductLoader.currentPage > 1 ? `?page=${ProductLoader.currentPage - 1}` : '#';
        $paginationList.append(`
            <li class="page-item ${prevDisabled}">
                <a class="page-link page-link--with-arrow" href="${prevHref}" aria-label="Previous">
                    <span class="page-link__arrow page-link__arrow--left" aria-hidden="true">
                        <svg width="7" height="11"><path d="M6.7,0.3L6.7,0.3c-0.4-0.4-0.9-0.4-1.3,0L0,5.5l5.4,5.2c0.4,0.4,0.9,0.3,1.3,0l0,0c0.4-0.4,0.4-1,0-1.3l-4-3.9l4-3.9C7.1,1.2,7.1,0.6,6.7,0.3z"></path></svg>
                    </span>
                </a>
            </li>
        `);

        // Page numbers
        const maxPages = 7;
        let startPage = Math.max(1, ProductLoader.currentPage - Math.floor(maxPages / 2));
        let endPage = Math.min(totalPages, startPage + maxPages - 1);
        
        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }

        if (startPage > 1) {
            $paginationList.append(`<li class="page-item"><a class="page-link" href="?page=1">1</a></li>`);
            if (startPage > 2) {
                $paginationList.append(`<li class="page-item page-item--dots"><div class="pagination__dots"></div></li>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === ProductLoader.currentPage ? 'active' : '';
            const current = i === ProductLoader.currentPage ? 'aria-current="page"' : '';
            $paginationList.append(`
                <li class="page-item ${active}" ${current}>
                    ${active ? 
                        `<span class="page-link">${i} <span class="sr-only">(current)</span></span>` :
                        `<a class="page-link" href="?page=${i}">${i}</a>`
                    }
                </li>
            `);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                $paginationList.append(`<li class="page-item page-item--dots"><div class="pagination__dots"></div></li>`);
            }
            $paginationList.append(`<li class="page-item"><a class="page-link" href="?page=${totalPages}">${totalPages}</a></li>`);
        }

        // Next button
        const nextDisabled = ProductLoader.currentPage >= totalPages ? 'disabled' : '';
        const nextHref = ProductLoader.currentPage < totalPages ? `?page=${ProductLoader.currentPage + 1}` : '#';
        $paginationList.append(`
            <li class="page-item ${nextDisabled}">
                <a class="page-link page-link--with-arrow" href="${nextHref}" aria-label="Next">
                    <span class="page-link__arrow page-link__arrow--right" aria-hidden="true">
                        <svg width="7" height="11"><path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9C-0.1,9.8-0.1,10.4,0.3,10.7z"></path></svg>
                    </span>
                </a>
            </li>
        `);
    }

    /**
     * Update price filter range
     */
    function updatePriceFilterRange(priceRange) {
        if (!priceRange || priceRange.min === undefined || priceRange.max === undefined) {
            return;
        }
        
        const minPrice = Math.floor(priceRange.min);
        const maxPrice = Math.ceil(priceRange.max);
        
        $('.filter-price').each(function() {
            const $filterPrice = $(this);
            const $slider = $filterPrice.find('.filter-price__slider');
            const $minValue = $filterPrice.find('.filter-price__min-value');
            const $maxValue = $filterPrice.find('.filter-price__max-value');
            
            // Update data attributes
            $filterPrice.attr('data-min', minPrice);
            $filterPrice.attr('data-max', maxPrice);
            
            // Mark that these values should show filtered products range, not slider position
            $filterPrice.data('show-filtered-range', true);
            $filterPrice.data('filtered-min', minPrice);
            $filterPrice.data('filtered-max', maxPrice);
            
            // Update displayed min/max values directly (based on filtered products range)
            if ($minValue.length) {
                $minValue.text(minPrice.toLocaleString('fa-IR'));
                $minValue.data('is-filtered-range', true);
            }
            if ($maxValue.length) {
                $maxValue.text(maxPrice.toLocaleString('fa-IR'));
                $maxValue.data('is-filtered-range', true);
            }
            
            // Get current values or use defaults
            const currentFrom = parseFloat($filterPrice.data('from')) || minPrice;
            const currentTo = parseFloat($filterPrice.data('to')) || maxPrice;
            
            // Clamp to new range
            const newFrom = Math.max(minPrice, Math.min(maxPrice, currentFrom));
            const newTo = Math.max(minPrice, Math.min(maxPrice, currentTo));
            
            $filterPrice.attr('data-from', newFrom);
            $filterPrice.attr('data-to', newTo);
            
            // Reinitialize slider if it exists
            if ($slider.length && $slider[0].noUiSlider) {
                try {
                    const slider = $slider[0].noUiSlider;
                    const currentValues = slider.get();
                    
                    // Update slider range
                    slider.updateOptions({
                        range: {
                            'min': minPrice,
                            'max': maxPrice
                        }
                    }, false);
                    
                    // Set values within new range
                    const clampedFrom = Math.max(minPrice, Math.min(maxPrice, parseFloat(currentValues[0])));
                    const clampedTo = Math.max(minPrice, Math.min(maxPrice, parseFloat(currentValues[1])));
                    slider.set([clampedFrom, clampedTo]);
                    
                    // Force update displayed values to show min/max of filtered products
                    // These values represent the range of filtered products, not the slider position
                    setTimeout(function() {
                        if ($minValue.length) {
                            $minValue.text(minPrice.toLocaleString('fa-IR'));
                        }
                        if ($maxValue.length) {
                            $maxValue.text(maxPrice.toLocaleString('fa-IR'));
                        }
                    }, 50);
                    
                    // Also update after a longer delay to ensure it sticks
                    setTimeout(function() {
                        if ($minValue.length) {
                            $minValue.text(minPrice.toLocaleString('fa-IR'));
                        }
                        if ($maxValue.length) {
                            $maxValue.text(maxPrice.toLocaleString('fa-IR'));
                        }
                    }, 200);
                } catch(e) {
                    console.error('Error updating price slider:', e);
                    // If update fails, destroy and let main.js reinitialize
                    $slider[0].noUiSlider.destroy();
                    // Trigger reinitialization
                    setTimeout(function() {
                        if (typeof window.initPriceSliders === 'function') {
                            window.initPriceSliders();
                        }
                    }, 100);
                }
            } else {
                // If slider doesn't exist yet, update data attributes so it initializes correctly
                // The displayed values are already updated above
            }
        });
    }

    /**
     * Update legend text
     */
    function updateLegend() {
        if (!ProductLoader.$legend.length) return;

        const start = (ProductLoader.currentPage - 1) * ProductLoader.limit + 1;
        const end = Math.min(ProductLoader.currentPage * ProductLoader.limit, ProductLoader.totalProducts);
        ProductLoader.$legend.text(`نمایش ${start}-${end} از ${ProductLoader.totalProducts} محصول`);

        // Update pagination legend too
        const $paginationLegend = ProductLoader.$pagination.find('.products-view__pagination-legend');
        if ($paginationLegend.length) {
            $paginationLegend.text(`نمایش ${start}-${end} از ${ProductLoader.totalProducts} محصول`);
        }
    }

    /**
     * Update URL without page reload
     */
    function updateURL() {
        const params = new URLSearchParams();
        if (ProductLoader.currentPage > 1) params.append('page', ProductLoader.currentPage);
        if (ProductLoader.limit !== 16) params.append('limit', ProductLoader.limit);
        if (ProductLoader.sortBy !== 'created_at') params.append('sort', ProductLoader.sortBy);
        if (ProductLoader.sortDir !== 'DESC') params.append('dir', ProductLoader.sortDir);
        
        // Add existing URL params
        const urlParams = new URLSearchParams(window.location.search);
        ['category', 'vehicle', 'search', 'featured', 'discounted', 'price_min', 'price_max'].forEach(key => {
            if (urlParams.get(key)) params.append(key, urlParams.get(key));
        });

        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newURL);
    }

    // Initialize on document ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

