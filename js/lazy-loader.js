/**
 * Progressive Section Lazy Loader
 * Loads sections and heavy content as user scrolls to them
 */
(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        rootMargin: '200px', // Start loading 200px before section enters viewport
        threshold: 0.01,
        loadOnce: true // Only load each section once
    };

    // Track which sections have been loaded
    const loadedSections = new Set();

    /**
     * Initialize lazy loading for a section
     */
    function initLazySection(section, callback) {
        const sectionId = section.getAttribute('data-lazy-id') || 
                         section.className || 
                         section.id || 
                         'section-' + Math.random().toString(36).substr(2, 9);

        // If already loaded, skip
        if (loadedSections.has(sectionId)) {
            return;
        }

        // Mark as loaded immediately to prevent duplicate loads
        loadedSections.add(sectionId);

        // Add loading class
        section.classList.add('lazy-section--loading');

        // Trigger a custom event so other scripts can react
        const loadEvent = new CustomEvent('lazySectionLoading', {
            detail: { section: section, sectionId: sectionId }
        });
        document.dispatchEvent(loadEvent);

        // Execute callback
        if (typeof callback === 'function') {
            try {
                callback(section);
            } catch (error) {
                console.error('Error loading lazy section:', error);
                section.classList.remove('lazy-section--loading');
                section.classList.add('lazy-section--error');
            }
        } else {
            // Default: just remove placeholder and show content
            const placeholder = section.querySelector('.lazy-section__placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            section.classList.remove('lazy-section--loading');
            section.classList.add('lazy-section--loaded');
            
            // Trigger loaded event
            const loadedEvent = new CustomEvent('lazySectionLoaded', {
                detail: { section: section, sectionId: sectionId }
            });
            document.dispatchEvent(loadedEvent);
        }
    }

    /**
     * Create placeholder for lazy section
     */
    function createPlaceholder(section) {
        // Check if placeholder already exists
        if (section.querySelector('.lazy-section__placeholder')) {
            return;
        }

        const placeholder = document.createElement('div');
        placeholder.className = 'lazy-section__placeholder';
        placeholder.innerHTML = `
            <div style="text-align: center; padding: 60px 20px; background: #f5f5f5; border-radius: 4px; min-height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <div class="spinner-border" role="status" style="width: 3rem; height: 3rem; border-width: 0.3em; border-color: #007bff; border-right-color: transparent;">
                    <span class="sr-only">در حال بارگذاری...</span>
                </div>
                <p style="margin-top: 20px; color: #666; font-size: 14px;">در حال بارگذاری محتوا...</p>
            </div>
        `;
        
        // Hide original content initially
        const content = section.querySelector('.lazy-section__content');
        if (content) {
            content.style.display = 'none';
        } else {
            // If no .lazy-section__content wrapper, hide all direct children except placeholder
            Array.from(section.children).forEach(child => {
                if (!child.classList.contains('lazy-section__placeholder')) {
                    child.style.display = 'none';
                }
            });
        }

        section.insertBefore(placeholder, section.firstChild);
    }

    /**
     * Load external JavaScript library
     */
    function loadScript(src, callback) {
        return new Promise((resolve, reject) => {
            // Check if already loaded
            const existingScript = document.querySelector(`script[src="${src}"]`);
            if (existingScript) {
                if (callback) callback();
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = () => {
                if (callback) callback();
                resolve();
            };
            script.onerror = () => {
                console.error('Failed to load script:', src);
                reject(new Error(`Failed to load script: ${src}`));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * Lazy load images in a section
     */
    function lazyLoadImages(section) {
        const images = section.querySelectorAll('img[data-src]');
        images.forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                // Handle loading state
                img.onload = () => {
                    img.classList.add('lazy-image--loaded');
                };
                img.onerror = () => {
                    img.classList.add('lazy-image--error');
                    // Use fallback image if provided
                    if (img.dataset.fallback) {
                        img.src = img.dataset.fallback;
                    }
                };
            }
        });

        // Also handle background images
        const bgElements = section.querySelectorAll('[data-bg-image]');
        bgElements.forEach(el => {
            if (el.dataset.bgImage) {
                el.style.backgroundImage = `url(${el.dataset.bgImage})`;
                el.removeAttribute('data-bg-image');
                el.classList.add('lazy-bg--loaded');
            }
        });
    }

    /**
     * Initialize Intersection Observer for lazy sections
     */
    function initLazySections() {
        const lazySections = document.querySelectorAll('[data-lazy-load]');
        
        if (lazySections.length === 0) {
            return;
        }

        // Create Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const section = entry.target;
                    
                    // Get load type
                    const loadType = section.getAttribute('data-lazy-load');
                    const scriptSrc = section.getAttribute('data-lazy-script');
                    const apiUrl = section.getAttribute('data-lazy-api');

                    // Load based on type
                    if (loadType === 'script' && scriptSrc) {
                        // Load external script first, then show content
                        loadScript(scriptSrc).then(() => {
                            initLazySection(section, (el) => {
                                lazyLoadImages(el);
                                el.classList.remove('lazy-section--loading');
                                el.classList.add('lazy-section--loaded');
                                const placeholder = el.querySelector('.lazy-section__placeholder');
                                if (placeholder) placeholder.style.display = 'none';
                                const content = el.querySelector('.lazy-section__content');
                                if (content) content.style.display = '';
                            });
                        }).catch(err => {
                            console.error('Script load error:', err);
                            section.classList.remove('lazy-section--loading');
                            section.classList.add('lazy-section--error');
                        });
                    } else if (loadType === 'api' && apiUrl) {
                        // Load content via API
                        initLazySection(section, (el) => {
                            fetch(apiUrl)
                                .then(response => response.json())
                                .then(data => {
                                    // Custom handler should be defined per section
                                    if (window.lazyLoadHandlers && window.lazyLoadHandlers[section.id]) {
                                        window.lazyLoadHandlers[section.id](section, data);
                                    }
                                    lazyLoadImages(el);
                                    el.classList.remove('lazy-section--loading');
                                    el.classList.add('lazy-section--loaded');
                                })
                                .catch(err => {
                                    console.error('API load error:', err);
                                    el.classList.remove('lazy-section--loading');
                                    el.classList.add('lazy-section--error');
                                });
                        });
                    } else {
                        // Standard lazy load - just show content
                        initLazySection(section, (el) => {
                            // Remove placeholder first
                            const placeholder = el.querySelector('.lazy-section__placeholder');
                            if (placeholder) {
                                placeholder.style.display = 'none';
                            }
                            
                            // Show content
                            const content = el.querySelector('.lazy-section__content');
                            if (content) {
                                content.style.display = '';
                            } else {
                                // Show all hidden children if no content wrapper
                                Array.from(el.children).forEach(child => {
                                    if (!child.classList.contains('lazy-section__placeholder')) {
                                        child.style.display = '';
                                    }
                                });
                            }
                            
                            // Lazy load images
                            lazyLoadImages(el);
                            
                            el.classList.remove('lazy-section--loading');
                            el.classList.add('lazy-section--loaded');
                            
                            // Small delay to ensure DOM is ready, then trigger loaded event
                            // This allows main.js to re-initialize carousels if needed
                            setTimeout(() => {
                                const loadedEvent = new CustomEvent('lazySectionLoaded', {
                                    detail: { section: el, sectionId: el.id || el.className }
                                });
                                document.dispatchEvent(loadedEvent);
                            }, 100);
                        });
                    }

                    // Unobserve after loading
                    observer.unobserve(section);
                }
            });
        }, {
            rootMargin: CONFIG.rootMargin,
            threshold: CONFIG.threshold
        });

        // Observe all lazy sections
        lazySections.forEach(section => {
            // Create placeholder if doesn't exist
            if (!section.querySelector('.lazy-section__placeholder')) {
                createPlaceholder(section);
            }
            observer.observe(section);
        });
    }

    /**
     * Initialize image lazy loading (native + fallback)
     */
    function initImageLazyLoading() {
        // Use native lazy loading if supported, otherwise use Intersection Observer
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[data-src]');
            images.forEach(img => {
                img.loading = 'lazy';
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        } else {
            // Fallback for older browsers
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initLazySections();
            initImageLazyLoading();
        });
    } else {
        initLazySections();
        initImageLazyLoading();
    }

    // Export API
    window.LazyLoader = {
        init: initLazySections,
        loadScript: loadScript,
        loadSection: initLazySection
    };
})();

