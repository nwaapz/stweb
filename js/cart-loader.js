/**
 * Cart Loader
 * Dynamically loads cart data and renders the cart dropdown
 */

(function () {
    'use strict';

    const CART_API_URL = 'backend/api/cart.php';

    /**
     * Find cart indicator element
     */
    function findCartIndicator() {
        const indicators = document.querySelectorAll('.indicator--trigger--click');
        for (let indicator of indicators) {
            const titleEl = indicator.querySelector('.indicator__title');
            if (titleEl && titleEl.textContent.includes('سبد خرید')) {
                return indicator;
            }
        }
        return null;
    }

    /**
     * Format price with Persian number formatting
     */
    function formatPrice(price) {
        if (!price && price !== 0) return '0';
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        let formatted = Math.round(price).toString();
        return formatted.replace(/\d/g, (digit) => persianDigits[parseInt(digit)]);
    }

    /**
     * Format number with commas
     */
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Get cart data from API
     */
    async function fetchCartData() {
        try {
            const response = await fetch(CART_API_URL, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                if (response.status === 401) {
                    // User not logged in - return empty cart
                    return {
                        success: true,
                        data: {
                            items: [],
                            total_items: 0,
                            subtotal: 0,
                            shipping: 0,
                            tax: 0,
                            total: 0
                        }
                    };
                }
                throw new Error('Failed to fetch cart data');
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Error fetching cart data:', error);
            return {
                success: false,
                data: {
                    items: [],
                    total_items: 0,
                    subtotal: 0,
                    shipping: 0,
                    tax: 0,
                    total: 0
                }
            };
        }
    }

    /**
     * Render cart items
     */
    function renderCartItems(items) {
        if (!items || items.length === 0) {
            return '<li class="dropcart__empty"><div class="dropcart__empty-message">سبد خرید شما خالی است</div></li>';
        }

        let html = '';
        items.forEach((item, index) => {
            const productUrl = item.slug ? `product-full.html?slug=${item.slug}` : `product-full.html?id=${item.product_id}`;
            let imageUrl = item.image_url || item.image || 'images/products/product-placeholder-70x70.jpg';
            if (imageUrl && imageUrl.startsWith('products/')) {
                imageUrl = 'backend/uploads/' + imageUrl;
            }
            const productName = item.product_name || item.name || 'محصول';
            const quantity = item.quantity || 1;
            const price = item.effective_price || item.price || 0;
            const totalPrice = price * quantity;

            // Build features list if available
            let featuresHtml = '';
            if (item.color || item.material || item.weight) {
                featuresHtml = '<ul class="dropcart__item-features">';
                if (item.color) featuresHtml += `<li>رنگ: ${item.color}</li>`;
                if (item.material) featuresHtml += `<li>جنس: ${item.material}</li>`;
                if (item.weight) featuresHtml += `<li>وزن: ${item.weight}</li>`;
                featuresHtml += '</ul>';
            }

            html += `
                <li class="dropcart__item">
                    <div class="dropcart__item-image image image--type--product">
                        <a class="image__body" href="${productUrl}">
                            <img class="image__tag" src="${imageUrl}" alt="${productName}">
                        </a>
                    </div>
                    <div class="dropcart__item-info">
                        <div class="dropcart__item-name">
                            <a href="${productUrl}">${productName}</a>
                        </div>
                        ${featuresHtml}
                        <div class="dropcart__item-meta">
                            <div class="dropcart__item-quantity">${quantity}</div>
                            <div class="dropcart__item-price">${formatNumber(totalPrice)}</div>
                        </div>
                    </div>
                    <button type="button" class="dropcart__item-remove" data-product-id="${item.product_id}">
                        <svg width="10" height="10">
                            <path d="M8.8,8.8L8.8,8.8c-0.4,0.4-1,0.4-1.4,0L5,6.4L2.6,8.8c-0.4,0.4-1,0.4-1.4,0l0,0c-0.4-0.4-0.4-1,0-1.4L3.6,5L1.2,2.6
c-0.4-0.4-0.4-1,0-1.4l0,0c0.4-0.4,1-0.4,1.4,0L5,3.6l2.4-2.4c0.4-0.4,1-0.4,1.4,0l0,0c0.4,0.4,0.4,1,0,1.4L6.4,5l2.4,2.4
C9.2,7.8,9.2,8.4,8.8,8.8z"/>
                        </svg>
                    </button>
                </li>
            `;

            // Add divider after each item except the last
            if (index < items.length - 1) {
                html += '<li class="dropcart__divider" role="presentation"></li>';
            }
        });

        return html;
    }

    /**
     * Render cart totals
     */
    function renderCartTotals(data) {
        const subtotal = data.subtotal || 0;
        const shipping = data.shipping || 0;
        const tax = data.tax || 0;
        const total = data.total || subtotal + shipping + tax;

        return `
            <table>
                <tbody>
                    <tr>
                        <th>جمع کل</th>
                        <td>${formatNumber(subtotal)}</td>
                    </tr>
                    <tr>
                        <th>ارسال</th>
                        <td>${formatNumber(shipping)}</td>
                    </tr>
                    <tr>
                        <th>مالیات</th>
                        <td>${formatNumber(tax)}</td>
                    </tr>
                    <tr>
                        <th>مجموع</th>
                        <td>${formatNumber(total)}</td>
                    </tr>
                </tbody>
            </table>
        `;
    }

    /**
     * Update cart indicator (counter and value)
     */
    function updateCartIndicator(data) {
        const indicator = findCartIndicator();
        if (!indicator) return;

        const counterEl = indicator.querySelector('.indicator__counter');
        const valueEl = indicator.querySelector('.indicator__value');

        const totalItems = data.total_items || (data.items ? data.items.length : 0);

        // Update desktop counter
        if (counterEl) {
            counterEl.textContent = totalItems;
            if (totalItems > 0) {
                // Show counter - remove inline style to use default CSS
                counterEl.style.display = '';
                counterEl.style.visibility = 'visible';
            } else {
                // Hide counter when cart is empty
                counterEl.style.display = 'none';
            }
        }

        // Update mobile counter (if exists)
        const mobileCounterEl = document.querySelector('.mobile-indicator__counter');
        if (mobileCounterEl) {
            mobileCounterEl.textContent = totalItems;
            if (totalItems > 0) {
                mobileCounterEl.style.display = '';
                mobileCounterEl.style.visibility = 'visible';
            } else {
                mobileCounterEl.style.display = 'none';
            }
        }

        // Update cart value
        if (valueEl) {
            const total = data.total || data.subtotal || 0;
            valueEl.textContent = formatNumber(total);
        }
    }

    /**
     * Load and render cart
     */
    async function loadCart() {
        const indicator = findCartIndicator();
        if (!indicator) return;

        const contentEl = indicator.querySelector('.indicator__content');
        if (!contentEl) return;

        const dropcartEl = contentEl.querySelector('.dropcart');
        if (!dropcartEl) return;

        // Show loading state
        const listEl = dropcartEl.querySelector('.dropcart__list');
        if (listEl) {
            listEl.innerHTML = '<li class="dropcart__loading"><div class="dropcart__loading-message">در حال بارگذاری...</div></li>';
        }

        // Fetch cart data
        const result = await fetchCartData();
        const cartData = result.data || {};

        // Update indicator
        updateCartIndicator(cartData);

        // Render items
        if (listEl) {
            listEl.innerHTML = renderCartItems(cartData.items || []);
        }

        // Render totals
        const totalsEl = dropcartEl.querySelector('.dropcart__totals');
        if (totalsEl) {
            totalsEl.innerHTML = renderCartTotals(cartData);
        }

        // Attach remove button handlers
        attachRemoveHandlers();
    }

    /**
     * Attach event handlers to remove buttons
     */
    function attachRemoveHandlers() {
        const removeButtons = document.querySelectorAll('.dropcart__item-remove[data-product-id]');
        removeButtons.forEach(button => {
            button.addEventListener('click', async function () {
                const productId = this.getAttribute('data-product-id');
                if (!productId) return;

                // Disable button during request
                this.disabled = true;

                try {
                    const response = await fetch(CART_API_URL, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ product_id: productId })
                    });

                    if (response.ok) {
                        // Reload cart
                        await loadCart();
                    } else {
                        alert('خطا در حذف محصول از سبد خرید');
                    }
                } catch (error) {
                    console.error('Error removing item from cart:', error);
                    alert('خطا در حذف محصول از سبد خرید');
                } finally {
                    this.disabled = false;
                }
            });
        });
    }

    /**
     * Initialize cart loader
     */
    function init() {
        // Load cart on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadCart);
        } else {
            loadCart();
        }

        // Reload cart when indicator is opened (for real-time updates)
        const indicator = findCartIndicator();
        if (indicator) {
            const button = indicator.querySelector('.indicator__button');
            if (button) {
                button.addEventListener('click', function () {
                    // Small delay to ensure dropdown is visible
                    setTimeout(loadCart, 100);
                });
            }
        }

        // Expose loadCart function globally for manual refresh
        window.loadCart = loadCart;
    }

    // Initialize
    init();
})();

