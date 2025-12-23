/**
 * Dynamic Departments Menu
 * Loads categories from CMS and populates the departments menu
 */

(function($) {
    'use strict';

    let categoriesLoaded = false;
    let categoriesData = [];

    /**
     * Fetch categories from CMS API
     */
    function fetchCategories() {
        return fetch('backend/api/categories.php?tree=1')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    categoriesData = data.data;
                    return categoriesData;
                }
                return [];
            })
            .catch(error => {
                console.error('Error fetching categories:', error);
                return [];
            });
    }

    /**
     * Build category URL
     */
    function getCategoryUrl(category) {
        if (category.slug) {
            return `category-4-columns-sidebar.html?category=${encodeURIComponent(category.slug)}`;
        }
        return `category-4-columns-sidebar.html?category=${category.id}`;
    }

    /**
     * Render a single category item for departments menu
     */
    function renderCategoryItem(category, hasChildren) {
        const itemClass = hasChildren 
            ? 'departments__item departments__item--submenu--megamenu departments__item--has-submenu'
            : 'departments__item';
        
        let html = `<li class="${itemClass}">`;
        html += `<a href="${getCategoryUrl(category)}" class="departments__item-link">${category.name}`;
        
        if (hasChildren) {
            html += ` <span class="departments__item-arrow"><svg width="7" height="11">
                <path d="M0.3,10.7L0.3,10.7c0.4,0.4,0.9,0.4,1.3,0L7,5.5L1.6,0.3C1.2-0.1,0.7,0,0.3,0.3l0,0c-0.4,0.4-0.4,1,0,1.3l4,3.9l-4,3.9C-0.1,9.8-0.1,10.4,0.3,10.7z" />
            </svg></span>`;
        }
        
        html += `</a>`;
        
        // If has children, add megamenu
        if (hasChildren && category.children && category.children.length > 0) {
            html += renderMegamenu(category);
        }
        
        html += `</li>`;
        return html;
    }

    /**
     * Render megamenu for category with children
     */
    function renderMegamenu(category) {
        const children = category.children || [];
        if (children.length === 0) {
            return '';
        }
        
        // Determine megamenu size based on number of children
        let megamenuSize = 'departments__megamenu--size--lg';
        let colClass = 'col-4';
        let numColumns = 3;
        
        if (children.length > 15) {
            megamenuSize = 'departments__megamenu--size--xl';
            colClass = 'col-1of5';
            numColumns = 5;
        } else if (children.length > 9) {
            megamenuSize = 'departments__megamenu--size--lg';
            colClass = 'col-3';
            numColumns = 4;
        } else if (children.length > 6) {
            megamenuSize = 'departments__megamenu--size--md';
            colClass = 'col-4';
            numColumns = 3;
        } else if (children.length <= 3) {
            megamenuSize = 'departments__megamenu--size--sm';
            colClass = 'col-12';
            numColumns = 1;
        }
        
        const itemsPerColumn = Math.ceil(children.length / numColumns);
        let html = `<div class="departments__item-menu">`;
        html += `<div class="megamenu departments__megamenu ${megamenuSize}">`;
        
        // Add category image if available
        if (category.image) {
            html += `<div class="megamenu__image"><img src="backend/uploads/categories/${category.image}" alt="${category.name}"></div>`;
        }
        
        html += `<div class="row">`;
        
        // Split children into columns
        for (let col = 0; col < numColumns; col++) {
            const startIdx = col * itemsPerColumn;
            const endIdx = Math.min(startIdx + itemsPerColumn, children.length);
            const columnChildren = children.slice(startIdx, endIdx);
            
            if (columnChildren.length > 0) {
                html += `<div class="${colClass}">`;
                html += `<ul class="megamenu__links megamenu-links megamenu-links--root">`;
                
                columnChildren.forEach(child => {
                    const hasGrandChildren = child.children && child.children.length > 0;
                    html += `<li class="megamenu-links__item ${hasGrandChildren ? 'megamenu-links__item--has-submenu' : ''}">`;
                    html += `<a class="megamenu-links__item-link" href="${getCategoryUrl(child)}">${child.name}</a>`;
                    
                    if (hasGrandChildren) {
                        html += `<ul class="megamenu-links">`;
                        child.children.forEach(grandChild => {
                            html += `<li class="megamenu-links__item">`;
                            html += `<a class="megamenu-links__item-link" href="${getCategoryUrl(grandChild)}">${grandChild.name}</a>`;
                            html += `</li>`;
                        });
                        html += `</ul>`;
                    }
                    
                    html += `</li>`;
                });
                
                html += `</ul>`;
                html += `</div>`;
            }
        }
        
        html += `</div>`;
        html += `</div>`;
        html += `</div>`;
        return html;
    }

    /**
     * Populate departments menu with categories
     */
    function populateDepartmentsMenu() {
        const $departmentsList = $('.departments__list');
        if ($departmentsList.length === 0) {
            console.warn('Departments list not found');
            return;
        }

        // Clear existing items except padding
        $departmentsList.find('li:not(.departments__list-padding)').remove();

        // Only render active categories
        if (categoriesData && categoriesData.length > 0) {
            categoriesData.forEach(category => {
                // Only show active categories
                if (category.is_active !== 0 && category.is_active !== false) {
                    const hasChildren = category.children && category.children.length > 0;
                    const itemHtml = renderCategoryItem(category, hasChildren);
                    $departmentsList.append(itemHtml);
                }
            });
        }

        // Reinitialize menu handlers if needed
        if (typeof window.initDepartmentsMenu === 'function') {
            window.initDepartmentsMenu();
        }
    }

    /**
     * Initialize departments menu
     */
    function initDepartmentsMenu() {
        const $departmentsButton = $('.departments__button');
        const $departmentsMenu = $('.departments__menu');

        if ($departmentsButton.length === 0 || $departmentsMenu.length === 0) {
            return;
        }

        // Load categories when button is clicked
        $departmentsButton.on('click', function() {
            if (!categoriesLoaded) {
                // Clear any existing items except padding
                const $list = $('.departments__list');
                $list.find('li:not(.departments__list-padding)').remove();

                // Fetch and populate
                fetchCategories().then(() => {
                    categoriesLoaded = true;
                    populateDepartmentsMenu();
                });
            }
        });

        // Also load on page load if menu is visible
        if ($departmentsMenu.is(':visible')) {
            if (!categoriesLoaded) {
                fetchCategories().then(() => {
                    categoriesLoaded = true;
                    populateDepartmentsMenu();
                });
            }
        }
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initDepartmentsMenu();
    });

    // Export for manual initialization if needed
    window.DepartmentsMenu = {
        init: initDepartmentsMenu,
        reload: function() {
            categoriesLoaded = false;
            fetchCategories().then(() => {
                categoriesLoaded = true;
                populateDepartmentsMenu();
            });
        }
    };

})(jQuery);

