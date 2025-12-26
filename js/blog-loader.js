/**
 * Blog Loader - Load blog posts from CMS
 * بارگذاری پست‌های وبلاگ از CMS
 */

(function($) {
    'use strict';

    const API_URL = 'backend/api/blog.php';
    let currentPage = 1;
    const postsPerPage = 10;
    let totalPosts = 0;

    /**
     * Format date to Persian format
     */
    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('fa-IR', options);
    }

    /**
     * Format date for display (e.g., "October 19, 2019")
     */
    function formatDateEnglish(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }

    /**
     * Get excerpt from content
     */
    function getExcerpt(content, maxLength = 200) {
        if (!content) return '';
        // Remove HTML tags
        const text = content.replace(/<[^>]*>/g, '');
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    /**
     * Fetch blog posts from API
     */
    async function fetchBlogPosts(page = 1, limit = postsPerPage) {
        try {
            const offset = (page - 1) * limit;
            const url = `${API_URL}?limit=${limit}&offset=${offset}&order_by=published_at&order_dir=DESC`;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success && data.data) {
                // Store total count for pagination
                totalPosts = (data.total !== undefined && data.total !== null) ? data.total : (data.count || 0);
                return data.data;
            }
            totalPosts = 0;
            return [];
        } catch (error) {
            console.error('Error fetching blog posts:', error);
            totalPosts = 0;
            return [];
        }
    }

    /**
     * Render blog post card
     */
    function renderPostCard(post) {
        const imageUrl = post.featured_image || 'images/posts/post-1-730x485.jpg';
        const excerpt = post.excerpt || getExcerpt(post.content);
        const date = formatDateEnglish(post.published_at || post.created_at);
        const author = post.author_name || 'مدیر';
        const postUrl = `post-full-width.html?slug=${post.slug}`;
        
        return `
            <div class="posts-list__item">
                <div class="post-card post-card--layout--grid">
                    <div class="post-card__image">
                        <a href="${postUrl}">
                            <img src="${imageUrl}" alt="${post.title}">
                        </a>
                    </div>
                    <div class="post-card__content">
                        <div class="post-card__category">
                            <a href="blog-classic-right-sidebar.html">وبلاگ</a>
                        </div>
                        <div class="post-card__title">
                            <h2><a href="${postUrl}">${post.title}</a></h2>
                        </div>
                        <div class="post-card__date">
                            توسط <a href="">${author}</a> در ${date}
                        </div>
                        <div class="post-card__excerpt">
                            <div class="typography">${excerpt}</div>
                        </div>
                        <div class="post-card__more">
                            <a href="${postUrl}" class="btn btn-secondary btn-sm">ادامه مطلب</a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Load and render blog posts
     */
    async function loadBlogPosts() {
        const $postsContainer = $('.posts-list__body');
        if (!$postsContainer.length) return;

        // Show loading state
        $postsContainer.html('<div class="text-center py-5"><p>در حال بارگذاری...</p></div>');

        const posts = await fetchBlogPosts(currentPage, postsPerPage);

        if (posts.length === 0) {
            $postsContainer.html(`
                <div class="text-center py-5">
                    <p class="text-muted">هیچ پستی یافت نشد.</p>
                </div>
            `);
            updatePagination();
            return;
        }

        // Render posts
        $postsContainer.html(posts.map(post => renderPostCard(post)).join(''));

        // Update pagination
        updatePagination();
    }

    /**
     * Load latest posts for sidebar
     */
    async function loadLatestPosts() {
        const $latestPostsContainer = $('.widget-posts__list');
        if (!$latestPostsContainer.length) return;

        const posts = await fetchBlogPosts(1, 4); // Get 4 latest posts

        if (posts.length === 0) {
            $latestPostsContainer.html('<li class="widget-posts__item"><p class="text-muted">هیچ پستی یافت نشد.</p></li>');
            return;
        }

        $latestPostsContainer.html(posts.map(post => {
            const imageUrl = post.featured_image || 'images/posts/post-1-70x70.jpg';
            const date = formatDateEnglish(post.published_at || post.created_at);
            const postUrl = `post-full-width.html?slug=${post.slug}`;
            
            return `
                <li class="widget-posts__item">
                    <div class="widget-posts__image">
                        <a href="${postUrl}">
                            <img src="${imageUrl}" alt="${post.title}">
                        </a>
                    </div>
                    <div class="widget-posts__info">
                        <div class="widget-posts__name">
                            <a href="${postUrl}">${post.title}</a>
                        </div>
                        <div class="widget-posts__date">${date}</div>
                    </div>
                </li>
            `;
        }).join(''));
    }

    /**
     * Build pagination URL with all current parameters
     */
    function buildPaginationUrl(page) {
        const params = new URLSearchParams();
        
        // Add page number (only if > 1)
        if (page > 1) {
            params.append('page', page);
        }
        
        // Preserve existing URL parameters (search, author, etc.)
        const urlParams = new URLSearchParams(window.location.search);
        const preserveParams = ['search', 'author_id', 'category'];
        
        preserveParams.forEach(key => {
            const value = urlParams.get(key);
            if (value && key !== 'page') {
                params.append(key, value);
            }
        });
        
        const queryString = params.toString();
        return queryString ? '?' + queryString : '';
    }

    /**
     * Update pagination
     */
    function updatePagination() {
        const $pagination = $('.posts-view__pagination');
        if (!$pagination.length) return;

        const $paginationList = $pagination.find('.pagination');
        
        // Clear static pagination HTML immediately
        $paginationList.empty();

        // Ensure totalPosts is a valid number
        if (isNaN(totalPosts) || totalPosts < 0) {
            totalPosts = 0;
        }

        const totalPages = Math.ceil(totalPosts / postsPerPage);
        
        // If only 1 page or no posts, show minimal pagination
        if (totalPages <= 1) {
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

        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        const prevHref = currentPage > 1 ? buildPaginationUrl(currentPage - 1) : '#';
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
        let startPage = Math.max(1, currentPage - Math.floor(maxPages / 2));
        let endPage = Math.min(totalPages, startPage + maxPages - 1);
        
        if (endPage - startPage < maxPages - 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }

        if (startPage > 1) {
            const firstPageUrl = buildPaginationUrl(1);
            $paginationList.append(`<li class="page-item"><a class="page-link" href="${firstPageUrl}">1</a></li>`);
            if (startPage > 2) {
                $paginationList.append(`<li class="page-item page-item--dots"><div class="pagination__dots"></div></li>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPage ? 'active' : '';
            const current = i === currentPage ? 'aria-current="page"' : '';
            const pageUrl = buildPaginationUrl(i);
            $paginationList.append(`
                <li class="page-item ${active}" ${current}>
                    ${active ? 
                        `<span class="page-link">${i} <span class="sr-only">(current)</span></span>` :
                        `<a class="page-link" href="${pageUrl}">${i}</a>`
                    }
                </li>
            `);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                $paginationList.append(`<li class="page-item page-item--dots"><div class="pagination__dots"></div></li>`);
            }
            const lastPageUrl = buildPaginationUrl(totalPages);
            $paginationList.append(`<li class="page-item"><a class="page-link" href="${lastPageUrl}">${totalPages}</a></li>`);
        }

        // Next button
        const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
        const nextHref = currentPage < totalPages ? buildPaginationUrl(currentPage + 1) : '#';
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
     * Initialize pagination click handlers
     */
    function initPagination() {
        const $pagination = $('.posts-view__pagination');
        if (!$pagination.length) return;

        $pagination.on('click', '.page-link', function(e) {
            e.preventDefault();
            const $link = $(this);
            const $pageItem = $link.closest('.page-item');

            if ($pageItem.hasClass('disabled') || $pageItem.hasClass('active')) {
                return;
            }

            const href = $link.attr('href');
            if (href && href !== '#') {
                // Parse page number from URL query string
                let pageParam = null;
                if (href.startsWith('?')) {
                    const params = new URLSearchParams(href.substring(1));
                    pageParam = params.get('page');
                }
                
                if (pageParam) {
                    currentPage = parseInt(pageParam);
                } else if ($link.attr('aria-label') === 'Previous') {
                    currentPage = Math.max(1, currentPage - 1);
                } else if ($link.attr('aria-label') === 'Next') {
                    const totalPages = Math.ceil(totalPosts / postsPerPage);
                    currentPage = Math.min(totalPages, currentPage + 1);
                } else {
                    // Try to extract page number from link text
                    const pageText = $link.text().trim();
                    const pageNum = parseInt(pageText);
                    if (!isNaN(pageNum) && pageNum > 0) {
                        currentPage = pageNum;
                    }
                }
                
                // Update URL and load posts
                updateURL();
                loadBlogPosts();
            }
        });
    }

    /**
     * Update URL without page reload
     */
    function updateURL() {
        const params = new URLSearchParams();
        if (currentPage > 1) params.append('page', currentPage);
        
        // Preserve existing URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        ['search', 'author_id', 'category'].forEach(key => {
            if (urlParams.get(key)) params.append(key, urlParams.get(key));
        });

        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        window.history.pushState({}, '', newURL);
    }

    /**
     * Initialize blog loader
     */
    function init() {
        // Get initial page from URL
        const urlParams = new URLSearchParams(window.location.search);
        currentPage = parseInt(urlParams.get('page')) || 1;
        
        // Initialize pagination handlers
        initPagination();
        
        // Load main blog posts
        loadBlogPosts();
        
        // Load latest posts for sidebar
        loadLatestPosts();
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

