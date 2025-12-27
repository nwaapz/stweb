/**
 * Dynamic Wishlist Loader
 * بارگذاری پویای لیست علاقه‌مندی
 */
(function ($) {
    "use strict";

    const WishlistLoader = {
        isLoading: false,
        $container: null,
        $tableBody: null
    };

    /**
     * Initialize the wishlist loader
     */
    function init() {
        // Find the wishlist container
        WishlistLoader.$container = $('.wishlist');
        if (!WishlistLoader.$container.length) {
            return; // Not on wishlist page
        }

        WishlistLoader.$tableBody = WishlistLoader.$container.find('.wishlist__body');
        if (!WishlistLoader.$tableBody.length) {
            return;
        }

        // Clear static content
        WishlistLoader.$tableBody.empty();

        // Load wishlist on page load
        loadWishlist();

        // Handle remove button clicks
        WishlistLoader.$container.on('click', '.wishlist__remove', handleRemove);

        // Handle add to cart button clicks
        WishlistLoader.$container.on('click', '.wishlist__add-to-cart', handleAddToCart);
    }

    /**
     * Load wishlist from API
     */
    function loadWishlist() {
        if (WishlistLoader.isLoading) return;
        WishlistLoader.isLoading = true;

        // Show loading state
        WishlistLoader.$tableBody.html('<tr><td colspan="6" style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="sr-only">در حال بارگذاری...</span></div></td></tr>');

        fetch('backend/api/wishlist.php', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (response.status === 401) {
                    // User not authenticated, redirect to login
                    window.location.href = 'account-login.html';
                    return null;
                }
                return response.json();
            })
            .then(data => {
                WishlistLoader.isLoading = false;
                
                if (!data) return; // Handled redirect

                if (data.success && data.data) {
                    renderWishlistItems(data.data);
                } else {
                    renderEmptyWishlist();
                }
            })
            .catch(error => {
                WishlistLoader.isLoading = false;
                console.error('Error loading wishlist:', error);
                WishlistLoader.$tableBody.html('<tr><td colspan="6" style="text-align: center; padding: 40px;">خطا در بارگذاری لیست علاقه‌مندی</td></tr>');
            });
    }

    /**
     * Render wishlist items
     */
    function renderWishlistItems(items) {
        WishlistLoader.$tableBody.empty();

        if (items.length === 0) {
            renderEmptyWishlist();
            return;
        }

        items.forEach(item => {
            const $row = createWishlistRow(item);
            WishlistLoader.$tableBody.append($row);
        });
    }

    /**
     * Render empty wishlist message
     */
    function renderEmptyWishlist() {
        WishlistLoader.$tableBody.html(`
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px;">
                    <div>لیست علاقه‌مندی شما خالی است</div>
                    <div style="margin-top: 20px;">
                        <a href="shop-grid-4-columns-sidebar.html" class="btn btn-primary">مشاهده محصولات</a>
                    </div>
                </td>
            </tr>
        `);
    }

    /**
     * Create a wishlist table row
     */
    function createWishlistRow(item) {
        const productUrl = item.slug ? 
            'product-full.html?product=' + encodeURIComponent(item.slug) : 
            'product-full.html?id=' + item.product_id;
        const productImage = item.image_url || 'images/products/product-1-160x160.jpg';
        const productName = item.name || 'بدون نام';
        const productPrice = item.formatted_effective_price || item.formatted_price || '0';
        const originalPrice = item.has_discount && item.formatted_price ? item.formatted_price : null;
        
        // Stock status
        const stockStatus = item.stock > 0 ? 'In Stock' : 'Out of Stock';
        const stockStatusClass = item.stock > 0 ? 'status-badge--style--success' : 'status-badge--style--danger';
        const stockStatusText = item.stock > 0 ? 'موجود' : 'ناموجود';

        // Rating (if available - you may need to fetch this from product API)
        const rating = item.rating || 0;
        const reviews = item.reviews || 0;
        const activeStars = Math.min(5, Math.max(0, Math.round(rating)));

        // Build rating stars
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= activeStars ? 
                '<div class="rating__star rating__star--active"></div>' : 
                '<div class="rating__star"></div>';
        }

        // Build price display
        let priceHtml = '';
        if (originalPrice && item.has_discount) {
            priceHtml = `<div class="wishlist__price">
                <span class="wishlist__price-new">${productPrice}</span>
                <span class="wishlist__price-old">${originalPrice}</span>
            </div>`;
        } else {
            priceHtml = `<div class="wishlist__price">${productPrice}</div>`;
        }

        const $row = $(`
            <tr class="wishlist__row wishlist__row--body">
                <td class="wishlist__column wishlist__column--body wishlist__column--image">
                    <div class="image image--type--product">
                        <a href="${productUrl}" class="image__body">
                            <img class="image__tag" src="${productImage}" alt="${productName}" onerror="this.src='images/products/product-1-160x160.jpg'">
                        </a>
                    </div>
                </td>
                <td class="wishlist__column wishlist__column--body wishlist__column--product">
                    <div class="wishlist__product-name">
                        <a href="${productUrl}">${productName}</a>
                    </div>
                    ${reviews > 0 ? `
                    <div class="wishlist__product-rating">
                        <div class="wishlist__product-rating-stars">
                            <div class="rating">
                                <div class="rating__body">${starsHtml}</div>
                            </div>
                        </div>
                        <div class="wishlist__product-rating-title">${reviews} نظرات</div>
                    </div>
                    ` : ''}
                </td>
                <td class="wishlist__column wishlist__column--body wishlist__column--stock">
                    <div class="status-badge ${stockStatusClass} status-badge--has-text">
                        <div class="status-badge__body">
                            <div class="status-badge__text">${stockStatusText}</div>
                        </div>
                    </div>
                </td>
                <td class="wishlist__column wishlist__column--body wishlist__column--price">
                    ${priceHtml}
                </td>
                <td class="wishlist__column wishlist__column--body wishlist__column--button">
                    <button type="button" class="btn btn-sm btn-primary wishlist__add-to-cart" data-product-id="${item.product_id}" ${item.stock <= 0 ? 'disabled' : ''}>
                        ${item.stock > 0 ? 'افزودن به سبد خرید' : 'ناموجود'}
                    </button>
                </td>
                <td class="wishlist__column wishlist__column--body wishlist__column--remove">
                    <button type="button" class="wishlist__remove btn btn-sm btn-muted btn-icon" data-product-id="${item.product_id}" aria-label="حذف">
                        <svg width="12" height="12">
                            <path d="M10.8,10.8L10.8,10.8c-0.4,0.4-1,0.4-1.4,0L6,7.4l-3.4,3.4c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L4.6,6L1.2,2.6
                            c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L6,4.6l3.4-3.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L7.4,6l3.4,3.4
                            C11.2,9.8,11.2,10.4,10.8,10.8z"/>
                        </svg>
                    </button>
                </td>
            </tr>
        `);

        return $row;
    }

    /**
     * Handle remove from wishlist
     */
    function handleRemove(e) {
        e.preventDefault();
        const $button = $(this);
        const productId = $button.data('product-id');

        if (!productId) return;

        // Disable button during request
        $button.prop('disabled', true);

        fetch('backend/api/wishlist.php', {
            method: 'DELETE',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_id: productId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove row from table
                    $button.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        // Check if wishlist is now empty
                        if (WishlistLoader.$tableBody.find('tr').length === 0) {
                            renderEmptyWishlist();
                        }
                    });
                    
                    // Show success message (optional)
                    if (typeof showNotification === 'function') {
                        showNotification('از لیست علاقه‌مندی حذف شد', 'success');
                    }
                } else {
                    $button.prop('disabled', false);
                    alert(data.error || 'خطا در حذف از لیست علاقه‌مندی');
                }
            })
            .catch(error => {
                $button.prop('disabled', false);
                console.error('Error removing from wishlist:', error);
                alert('خطا در حذف از لیست علاقه‌مندی');
            });
    }

    /**
     * Handle add to cart
     */
    function handleAddToCart(e) {
        e.preventDefault();
        const $button = $(this);
        const productId = $button.data('product-id');

        if (!productId) return;

        // Disable button during request
        $button.prop('disabled', true);
        const originalText = $button.text();
        $button.text('در حال افزودن...');

        fetch('backend/api/cart.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                product_id: productId,
                quantity: 1
            })
        })
            .then(response => response.json())
            .then(data => {
                $button.prop('disabled', false);
                $button.text(originalText);

                if (data.success) {
                    // Show success message
                    if (typeof showNotification === 'function') {
                        showNotification('به سبد خرید اضافه شد', 'success');
                    }
                    
                    // Update cart indicator if exists
                    if (typeof updateCartIndicator === 'function') {
                        updateCartIndicator();
                    }
                } else {
                    alert(data.error || 'خطا در افزودن به سبد خرید');
                }
            })
            .catch(error => {
                $button.prop('disabled', false);
                $button.text(originalText);
                console.error('Error adding to cart:', error);
                alert('خطا در افزودن به سبد خرید');
            });
    }

    // Initialize on document ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

