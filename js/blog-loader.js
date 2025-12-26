/**
 * Blog Loader - Load blog posts from CMS
 * بارگذاری پست‌های وبلاگ از CMS
 */

(function() {
    'use strict';

    const API_URL = 'backend/api/blog.php';
    let currentPage = 1;
    const postsPerPage = 10;

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
                return data.data;
            }
            return [];
        } catch (error) {
            console.error('Error fetching blog posts:', error);
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
        const postsContainer = document.querySelector('.posts-list__body');
        if (!postsContainer) return;

        // Show loading state
        postsContainer.innerHTML = '<div class="text-center py-5"><p>در حال بارگذاری...</p></div>';

        const posts = await fetchBlogPosts(currentPage, postsPerPage);

        if (posts.length === 0) {
            postsContainer.innerHTML = `
                <div class="text-center py-5">
                    <p class="text-muted">هیچ پستی یافت نشد.</p>
                </div>
            `;
            return;
        }

        // Render posts
        postsContainer.innerHTML = posts.map(post => renderPostCard(post)).join('');

        // Update pagination if needed
        updatePagination(posts.length);
    }

    /**
     * Load latest posts for sidebar
     */
    async function loadLatestPosts() {
        const latestPostsContainer = document.querySelector('.widget-posts__list');
        if (!latestPostsContainer) return;

        const posts = await fetchBlogPosts(1, 4); // Get 4 latest posts

        if (posts.length === 0) {
            latestPostsContainer.innerHTML = '<li class="widget-posts__item"><p class="text-muted">هیچ پستی یافت نشد.</p></li>';
            return;
        }

        latestPostsContainer.innerHTML = posts.map(post => {
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
        }).join('');
    }

    /**
     * Update pagination
     */
    function updatePagination(postsCount) {
        // For now, we'll keep the existing pagination structure
        // You can enhance this later to handle actual pagination
    }

    /**
     * Initialize blog loader
     */
    function init() {
        // Load main blog posts
        loadBlogPosts();
        
        // Load latest posts for sidebar
        loadLatestPosts();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

