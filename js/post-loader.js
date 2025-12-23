/**
 * Post Loader - Load single blog post from CMS based on slug
 * بارگذاری پست وبلاگ از CMS بر اساس slug
 */

(function() {
    'use strict';

    const API_URL = 'backend/api/blog.php';

    /**
     * Get URL parameter by name
     */
    function getUrlParameter(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

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
     * Fetch blog post by slug from API
     */
    async function fetchBlogPost(slug) {
        try {
            const url = `${API_URL}?slug=${encodeURIComponent(slug)}`;
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            }
            return null;
        } catch (error) {
            console.error('Error fetching blog post:', error);
            return null;
        }
    }

    /**
     * Render blog post header
     */
    function renderPostHeader(post) {
        const headerImage = post.featured_image || post.image_url || 'images/posts/post-1-1903x500.jpg';
        const date = formatDateEnglish(post.published_at || post.created_at);
        const author = post.author_name || 'مدیر';
        
        const headerElement = document.querySelector('.post-view__header, .post-header');
        if (!headerElement) return;

        // Update image
        const imageElement = headerElement.querySelector('.post-header__image');
        if (imageElement) {
            imageElement.style.backgroundImage = `url('${headerImage}')`;
        }

        const headerBody = headerElement.querySelector('.post-header__body');
        if (headerBody) {
            // Update title
            const titleElement = headerBody.querySelector('.post-header__title, h1');
            if (titleElement) {
                titleElement.textContent = post.title;
            }

            // Update meta (author and date)
            const metaList = headerBody.querySelector('.post-header__meta-list');
            if (metaList) {
                metaList.innerHTML = `
                    <li class="post-header__meta-item">توسط <a href="#" class="post-header__meta-link">${author}</a></li>
                    <li class="post-header__meta-item">${date}</li>
                `;
            }
        }
    }

    /**
     * Render blog post content
     */
    function renderPostContent(post) {
        const contentElement = document.querySelector('.post__body.typography, .post__body, .typography');
        if (!contentElement) {
            // Try to find the container and create content element
            const container = document.querySelector('.post-view__item-post, .post-view__body, .container');
            if (container) {
                const newContent = document.createElement('div');
                newContent.className = 'post__body typography';
                newContent.innerHTML = post.content || '<p>محتوایی یافت نشد.</p>';
                container.appendChild(newContent);
                return;
            }
            return;
        }

        // Set the content
        contentElement.innerHTML = post.content || '<p>محتوایی یافت نشد.</p>';
    }

    /**
     * Render post author
     */
    function renderPostAuthor(post) {
        const authorElement = document.querySelector('.post__author');
        if (!authorElement) return;

        const authorName = authorElement.querySelector('.post__author-name');
        if (authorName) {
            authorName.textContent = post.author_name || 'مدیر';
        }

        const authorAbout = authorElement.querySelector('.post__author-about');
        if (authorAbout) {
            // Keep existing about text or use excerpt
            authorAbout.textContent = post.excerpt || authorAbout.textContent;
        }
    }

    /**
     * Update page title
     */
    function updatePageTitle(post) {
        if (post.title) {
            document.title = `${post.title} — Red Parts`;
        }
    }

    /**
     * Load and render blog post
     */
    async function loadBlogPost() {
        const slug = getUrlParameter('slug');
        
        if (!slug) {
            // No slug parameter, keep static content
            return;
        }

        // Show loading state
        const contentElement = document.querySelector('.post__body.typography');
        if (contentElement) {
            contentElement.innerHTML = '<div class="text-center py-5"><p>در حال بارگذاری...</p></div>';
        }

        const post = await fetchBlogPost(slug);

        if (!post) {
            // Post not found
            if (contentElement) {
                contentElement.innerHTML = `
                    <div class="text-center py-5">
                        <h2>پست یافت نشد</h2>
                        <p class="text-muted">پست وبلاگ مورد نظر یافت نشد.</p>
                        <a href="blog-classic-right-sidebar.html" class="btn btn-primary mt-3">بازگشت به وبلاگ</a>
                    </div>
                `;
            }
            return;
        }

        // Render all post sections
        renderPostHeader(post);
        renderPostContent(post);
        renderPostAuthor(post);
        updatePageTitle(post);
    }

    /**
     * Initialize post loader
     */
    function init() {
        loadBlogPost();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();

