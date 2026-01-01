/**
 * Dynamic Compare Loader
 * بارگذاری پویای صفحه مقایسه
 */
(function ($) {
    "use strict";

    const CompareLoader = {
        isLoading: false,
        $container: null,
        $tableBody: null
    };

    /**
     * Initialize the compare loader
     */
    function init() {
        // Find the compare container
        CompareLoader.$container = $('.compare');
        if (!CompareLoader.$container.length) {
            return; // Not on compare page
        }

        CompareLoader.$tableBody = CompareLoader.$container.find('.compare-table tbody');
        if (!CompareLoader.$tableBody.length) {
            return;
        }

        // Clear static content
        CompareLoader.$tableBody.empty();

        // Load compare on page load
        loadCompare();

        // Handle remove button clicks
        CompareLoader.$container.on('click', '.compare-table__remove', handleRemove);

        // Handle add to cart button clicks
        CompareLoader.$container.on('click', '.compare-table__add-to-cart', handleAddToCart);

        // Handle clear list button
        CompareLoader.$container.on('click', '.compare__clear-list', handleClearList);

        // Handle filter toggle (All/Different)
        CompareLoader.$container.on('change', 'input[name="compare-filter"]', handleFilterChange);
    }

    /**
     * Load compare from API
     */
    function loadCompare() {
        if (CompareLoader.isLoading) return;
        CompareLoader.isLoading = true;

        // Show loading state
        CompareLoader.$tableBody.html('<tr><td colspan="100" style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="sr-only">در حال بارگذاری...</span></div></td></tr>');

        fetch('backend/api/compare.php', {
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
                CompareLoader.isLoading = false;

                if (!data) return; // Handled redirect

                if (data.success && data.data) {
                    renderCompareTable(data.data);
                } else {
                    renderEmptyCompare();
                }
            })
            .catch(error => {
                CompareLoader.isLoading = false;
                console.error('Error loading compare:', error);
                CompareLoader.$tableBody.html('<tr><td colspan="100" style="text-align: center; padding: 40px;">خطا در بارگذاری لیست مقایسه</td></tr>');
            });
    }

    /**
     * Render compare table
     */
    function renderCompareTable(items) {
        if (items.length === 0) {
            renderEmptyCompare();
            return;
        }

        CompareLoader.$tableBody.empty();

        // Render product header row
        const $productRow = $('<tr class="compare-table__row"></tr>');
        $productRow.append('<th class="compare-table__column compare-table__column--header">محصول</th>');

        items.forEach(item => {
            const productUrl = item.slug ?
                'product-full.html?product=' + encodeURIComponent(item.slug) :
                'product-full.html?id=' + item.product_id;
            let productImage = item.image_url || 'images/products/product-1-150x150.jpg';
            if (productImage && productImage.startsWith('products/')) {
                productImage = 'backend/uploads/' + productImage;
            }
            const productName = item.name || 'بدون نام';

            const $productCell = $(`
                <td class="compare-table__column compare-table__column--product">
                    <a href="${productUrl}" class="compare-table__product">
                        <div class="compare-table__product-image image image--type--product">
                            <div class="image__body">
                                <img class="image__tag" src="${productImage}" alt="${productName}" onerror="this.src='images/products/product-1-150x150.jpg'">
                            </div>
                        </div>
                        <div class="compare-table__product-name">${productName}</div>
                    </a>
                </td>
            `);
            $productRow.append($productCell);
        });
        $productRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($productRow);

        // Render rating row
        const $ratingRow = $('<tr class="compare-table__row"></tr>');
        $ratingRow.append('<th class="compare-table__column compare-table__column--header">Rating</th>');

        items.forEach(item => {
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

            const $ratingCell = $(`
                <td class="compare-table__column compare-table__column--product">
                    <div class="compare-table__rating">
                        <div class="compare-table__rating-stars">
                            <div class="rating">
                                <div class="rating__body">${starsHtml}</div>
                            </div>
                        </div>
                        <div class="compare-table__rating-title">Based در ${reviews} نظرات</div>
                    </div>
                </td>
            `);
            $ratingRow.append($ratingCell);
        });
        $ratingRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($ratingRow);

        // Render availability row
        const $availabilityRow = $('<tr class="compare-table__row"></tr>');
        $availabilityRow.append('<th class="compare-table__column compare-table__column--header">Availability</th>');

        items.forEach(item => {
            const stockStatus = item.stock > 0 ? 'In Stock' : 'Out of Stock';
            const stockStatusClass = item.stock > 0 ? 'status-badge--style--success' : 'status-badge--style--danger';
            const stockStatusText = item.stock > 0 ? 'موجود' : 'ناموجود';

            const $availabilityCell = $(`
                <td class="compare-table__column compare-table__column--product">
                    <div class="status-badge ${stockStatusClass} status-badge--has-text">
                        <div class="status-badge__body">
                            <div class="status-badge__text">${stockStatusText}</div>
                        </div>
                    </div>
                </td>
            `);
            $availabilityRow.append($availabilityCell);
        });
        $availabilityRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($availabilityRow);

        // Render price row
        const $priceRow = $('<tr class="compare-table__row"></tr>');
        $priceRow.append('<th class="compare-table__column compare-table__column--header">Price</th>');

        items.forEach(item => {
            const price = item.formatted_effective_price || item.formatted_price || '0';
            $priceRow.append(`<td class="compare-table__column compare-table__column--product">${price}</td>`);
        });
        $priceRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($priceRow);

        // Render add to cart row
        const $addToCartRow = $('<tr class="compare-table__row"></tr>');
        $addToCartRow.append('<th class="compare-table__column compare-table__column--header">Add to cart</th>');

        items.forEach(item => {
            const $addToCartCell = $(`
                <td class="compare-table__column compare-table__column--product">
                    <button type="button" class="btn btn-sm btn-primary compare-table__add-to-cart" data-product-id="${item.product_id}" ${item.stock <= 0 ? 'disabled' : ''}>
                        Add to cart
                    </button>
                </td>
            `);
            $addToCartRow.append($addToCartCell);
        });
        $addToCartRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($addToCartRow);

        // Render SKU row
        const $skuRow = $('<tr class="compare-table__row"></tr>');
        $skuRow.append('<th class="compare-table__column compare-table__column--header">SKU</th>');

        items.forEach(item => {
            const sku = item.sku || 'N/A';
            $skuRow.append(`<td class="compare-table__column compare-table__column--product">${sku}</td>`);
        });
        $skuRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($skuRow);

        // Render weight row (if available)
        const $weightRow = $('<tr class="compare-table__row"></tr>');
        $weightRow.append('<th class="compare-table__column compare-table__column--header">Weight</th>');

        items.forEach(item => {
            const weight = item.weight ? item.weight + ' Kg' : 'N/A';
            $weightRow.append(`<td class="compare-table__column compare-table__column--product">${weight}</td>`);
        });
        $weightRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($weightRow);

        // Render color row (if available)
        const $colorRow = $('<tr class="compare-table__row"></tr>');
        $colorRow.append('<th class="compare-table__column compare-table__column--header">Color</th>');

        items.forEach(item => {
            const color = item.color || 'N/A';
            $colorRow.append(`<td class="compare-table__column compare-table__column--product">${color}</td>`);
        });
        $colorRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($colorRow);

        // Render material row (if available)
        const $materialRow = $('<tr class="compare-table__row"></tr>');
        $materialRow.append('<th class="compare-table__column compare-table__column--header">Material</th>');

        items.forEach(item => {
            const material = item.material || 'N/A';
            $materialRow.append(`<td class="compare-table__column compare-table__column--product">${material}</td>`);
        });
        $materialRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($materialRow);

        // Render remove row
        const $removeRow = $('<tr class="compare-table__row"></tr>');
        $removeRow.append('<th class="compare-table__column compare-table__column--header"></th>');

        items.forEach(item => {
            const $removeCell = $(`
                <td class="compare-table__column compare-table__column--product">
                    <button type="button" class="btn btn-sm btn-secondary compare-table__remove" data-product-id="${item.product_id}">Remove</button>
                </td>
            `);
            $removeRow.append($removeCell);
        });
        $removeRow.append('<td class="compare-table__column compare-table__column--fake"></td>');
        CompareLoader.$tableBody.append($removeRow);
    }

    /**
     * Render empty compare message
     */
    function renderEmptyCompare() {
        CompareLoader.$tableBody.html(`
            <tr>
                <td colspan="100" style="text-align: center; padding: 40px;">
                    <div>لیست مقایسه شما خالی است</div>
                    <div style="margin-top: 20px;">
                        <a href="shop-grid-4-columns-sidebar.html" class="btn btn-primary">مشاهده محصولات</a>
                    </div>
                </td>
            </tr>
        `);
    }

    /**
     * Handle remove from compare
     */
    function handleRemove(e) {
        e.preventDefault();
        const $button = $(this);
        const productId = $button.data('product-id');

        if (!productId) return;

        // Disable button during request
        $button.prop('disabled', true);

        fetch('backend/api/compare.php', {
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
                    // Reload compare table
                    loadCompare();

                    // Show success message (optional)
                    if (typeof showNotification === 'function') {
                        showNotification('از لیست مقایسه حذف شد', 'success');
                    }
                } else {
                    $button.prop('disabled', false);
                    alert(data.error || 'خطا در حذف از لیست مقایسه');
                }
            })
            .catch(error => {
                $button.prop('disabled', false);
                console.error('Error removing from compare:', error);
                alert('خطا در حذف از لیست مقایسه');
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

    /**
     * Handle clear list
     */
    function handleClearList(e) {
        e.preventDefault();

        if (!confirm('آیا مطمئن هستید که می‌خواهید لیست مقایسه را پاک کنید؟')) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true);
        const originalText = $button.text();
        $button.text('در حال پاک کردن...');

        fetch('backend/api/compare.php', {
            method: 'PUT',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                $button.prop('disabled', false);
                $button.text(originalText);

                if (data.success) {
                    // Reload compare table
                    loadCompare();

                    // Show success message
                    if (typeof showNotification === 'function') {
                        showNotification('لیست مقایسه پاک شد', 'success');
                    }
                } else {
                    alert(data.error || 'خطا در پاک کردن لیست مقایسه');
                }
            })
            .catch(error => {
                $button.prop('disabled', false);
                $button.text(originalText);
                console.error('Error clearing compare:', error);
                alert('خطا در پاک کردن لیست مقایسه');
            });
    }

    /**
     * Handle filter change (All/Different)
     */
    function handleFilterChange() {
        // This is a placeholder - implement filtering logic if needed
        // For now, just reload the table
        loadCompare();
    }

    // Initialize on document ready
    $(document).ready(function () {
        init();
    });

})(jQuery);


