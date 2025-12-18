(function ($) {
    "use strict";

    let DIRECTION = null;

    function direction() {
        if (DIRECTION === null) {
            DIRECTION = getComputedStyle(document.body).direction;
        }

        return DIRECTION;
    }

    function isRTL() {
        return direction() === 'rtl';
    }


    /*
    // collapse
    */
    $(function () {
        $('[data-collapse]').each(function (i, element) {
            const collapse = element;
            const openedClass = $(element).data('collapse-opened-class');

            $('[data-collapse-trigger]', collapse).on('click', function () {
                const item = $(this).closest('[data-collapse-item]');
                const content = item.children('[data-collapse-content]');
                const itemParents = item.parents();

                itemParents.slice(0, itemParents.index(collapse) + 1).filter('[data-collapse-item]').css('height', '');

                if (item.is('.' + openedClass)) {
                    const startHeight = content.height();

                    content.css('height', startHeight + 'px');
                    content.height(); // force reflow
                    item.removeClass(openedClass);

                    content.css('height', '');
                } else {
                    const startHeight = content.height();

                    item.addClass(openedClass);

                    const endHeight = content.height();

                    content.css('height', startHeight + 'px');
                    content.height(); // force reflow
                    content.css('height', endHeight + 'px');
                }
            });

            $('[data-collapse-content]', collapse).on('transitionend', function (event) {
                if (event.originalEvent.propertyName === 'height') {
                    $(this).css('height', '');
                }
            });
        });
    });

    /*
    // .filter-price
    */
    $(function () {
        $('.filter-price').each(function (i, element) {
            const min = $(element).data('min');
            const max = $(element).data('max');
            const from = $(element).data('from');
            const to = $(element).data('to');
            const slider = element.querySelector('.filter-price__slider');

            noUiSlider.create(slider, {
                start: [from, to],
                connect: true,
                direction: isRTL() ? 'rtl' : 'ltr',
                range: {
                    'min': min,
                    'max': max
                }
            });

            const titleValues = [
                $(element).find('.filter-price__min-value')[0],
                $(element).find('.filter-price__max-value')[0]
            ];

            slider.noUiSlider.on('update', function (values, handle) {
                titleValues[handle].innerHTML = values[handle];
            });
        });
    });

    /*
    // .product-gallery
    */
    const initProductGallery = function(element, layout) {
        layout = layout !== undefined ? layout : 'standard';

        const options = {
            dots: false,
            margin: 10,
            rtl: isRTL(),
        };
        const layoutOptions = {
            'product-sidebar': {
                responsive: {
                    1400: {items: 8, margin: 10},
                    1200: {items: 6, margin: 10},
                    992: {items: 8, margin: 10},
                    768: {items: 8, margin: 10},
                    576: {items: 6, margin: 10},
                    420: {items: 5, margin: 8},
                    0: {items: 4, margin: 8}
                },
            },
            'product-full': {
                responsive: {
                    1400: {items: 6, margin: 10},
                    1200: {items: 5, margin: 8},
                    992: {items: 7, margin: 10},
                    768: {items: 5, margin: 8},
                    576: {items: 6, margin: 8},
                    420: {items: 5, margin: 8},
                    0: {items: 4, margin: 8}
                }
            },
            quickview: {
                responsive: {
                    992: {items: 5},
                    520: {items: 6},
                    440: {items: 5},
                    340: {items: 4},
                    0: {items: 3}
                }
            },
        };

        const gallery = $(element);

        const image = gallery.find('.product-gallery__featured .owl-carousel');
        const carousel = gallery.find('.product-gallery__thumbnails .owl-carousel');

        image
            .owlCarousel({items: 1, dots: false, rtl: isRTL()})
            .on('changed.owl.carousel', syncPosition);

        carousel
            .on('initialized.owl.carousel', function () {
                carousel.find('.product-gallery__thumbnails-item').eq(0).addClass('product-gallery__thumbnails-item--active');
            })
            .owlCarousel($.extend({}, options, layoutOptions[layout]));

        carousel.on('click', '.owl-item', function(e){
            e.preventDefault();

            image.data('owl.carousel').to($(this).index(), 300, true);
        });

        gallery.find('.product-gallery__zoom').on('click', function() {
            openPhotoSwipe(image.find('.owl-item.active').index());
        });

        image.on('click', '.owl-item > a', function(event) {
            event.preventDefault();

            openPhotoSwipe($(this).closest('.owl-item').index());
        });

        function getIndexDependOnDir(index) {
            // we need to invert index id direction === 'rtl' because photoswipe do not support rtl
            if (isRTL()) {
                return image.find('.owl-item img').length - 1 - index;
            }

            return index;
        }

        function openPhotoSwipe(index) {
            const photoSwipeImages = image.find('.owl-item a').toArray().map(function(element) {
                const img = $(element).find('img')[0];
                const width = $(element).data('width') || img.naturalWidth;
                const height = $(element).data('height') || img.naturalHeight;

                return {
                    src: element.href,
                    msrc: element.href,
                    w: width,
                    h: height,
                };
            });

            if (isRTL()) {
                photoSwipeImages.reverse();
            }

            const photoSwipeOptions = {
                getThumbBoundsFn: index => {
                    const imageElements = image.find('.owl-item img').toArray();
                    const dirDependentIndex = getIndexDependOnDir(index);

                    if (!imageElements[dirDependentIndex]) {
                        return null;
                    }

                    const tag = imageElements[dirDependentIndex];
                    const width = tag.naturalWidth;
                    const height = tag.naturalHeight;
                    const rect = tag.getBoundingClientRect();
                    const ration = Math.min(rect.width / width, rect.height / height);
                    const fitWidth = width * ration;
                    const fitHeight = height * ration;

                    return {
                        x: rect.left + (rect.width - fitWidth) / 2 + window.pageXOffset,
                        y: rect.top + (rect.height - fitHeight) / 2 + window.pageYOffset,
                        w: fitWidth,
                    };
                },
                index: getIndexDependOnDir(index),
                bgOpacity: .9,
                history: false
            };

            const photoSwipeGallery = new PhotoSwipe($('.pswp')[0], PhotoSwipeUI_Default, photoSwipeImages, photoSwipeOptions);

            photoSwipeGallery.listen('beforeChange', () => {
                image.data('owl.carousel').to(getIndexDependOnDir(photoSwipeGallery.getCurrentIndex()), 0, true);
            });

            photoSwipeGallery.init();
        }

        function syncPosition (el) {
            let current = el.item.index;

            carousel
                .find('.product-gallery__thumbnails-item')
                .removeClass('product-gallery__thumbnails-item--active')
                .eq(current)
                .addClass('product-gallery__thumbnails-item--active');
            const onscreen = carousel.find('.owl-item.active').length - 1;
            const start = carousel.find('.owl-item.active').first().index();
            const end = carousel.find('.owl-item.active').last().index();

            if (current > end) {
                carousel.data('owl.carousel').to(current, 100, true);
            }
            if (current < start) {
                carousel.data('owl.carousel').to(current - onscreen, 100, true);
            }
        }
    };

    $(function() {
        $('.product').each(function () {
            const gallery = $(this).find('.product-gallery');

            if (gallery.length > 0) {
                initProductGallery(gallery[0], gallery.data('layout'));
            }
        });
    });

    /*
    // .product-tabs
    */
    $(function () {
        $('.product-tabs').each(function (i, element) {
            $('.product-tabs__list', element).on('click', '.product-tabs__item a', function (event) {
                event.preventDefault();

                const tab = $(this).closest('.product-tabs__item');
                const content = $('.product-tabs__pane' + $(this).attr('href'), element);

                if (content.length) {
                    $('.product-tabs__item').removeClass('product-tabs__item--active');
                    tab.addClass('product-tabs__item--active');

                    $('.product-tabs__pane').removeClass('product-tabs__pane--active');
                    content.addClass('product-tabs__pane--active');
                }
            });

            const currentTab = $('.product-tabs__item--active', element);
            const firstTab = $('.product-tabs__item:first', element);

            if (currentTab.length) {
                currentTab.trigger('click');
            } else {
                firstTab.trigger('click');
            }
        });
    });

    /*
    // .departments
    */
    $(function() {
        $('.departments__button').on('click', function(event) {
            event.preventDefault();

            $(this).closest('.departments').toggleClass('departments--open');
        });

        $(document).on('click', function (event) {
            $('.departments')
                .not($(event.target).closest('.departments'))
                .removeClass('departments--open');
        });
    });

    /*
    // .topbar__menu
    */
    $(function() {
        $('.topbar__menu-button').on('click', function() {
            $(this).closest('.topbar__menu').toggleClass('topbar__menu--open');
        });

        $(document).on('click', function (event) {
            $('.topbar__menu')
                .not($(event.target).closest('.topbar__menu'))
                .removeClass('topbar__menu--open');
        });
    });

    /*
    // .indicator (dropcart, account-menu)
    */
    $(function() {
        $('.indicator--trigger--click .indicator__button').on('click', function(event) {
            event.preventDefault();

            const dropdown = $(this).closest('.indicator');

            if (dropdown.is('.indicator--open')) {
                dropdown.removeClass('indicator--open');
            } else {
                dropdown.addClass('indicator--open');
            }
        });

        $(document).on('click', function (event) {
            $('.indicator')
                .not($(event.target).closest('.indicator'))
                .removeClass('indicator--open');
        });
    });

    /*
    // .layout-switcher
    */
    $(function () {
        $('.layout-switcher__button').on('click', function() {
            const layoutSwitcher = $(this).closest('.layout-switcher');
            const productsView = $(this).closest('.products-view');
            const productsList = productsView.find('.products-list');

            layoutSwitcher
                .find('.layout-switcher__button')
                .removeClass('layout-switcher__button--active')
                .removeAttr('disabled');

            $(this)
                .addClass('layout-switcher__button--active')
                .attr('disabled', '');

            productsList.attr('data-layout', $(this).attr('data-layout'));
            productsList.attr('data-with-features', $(this).attr('data-with-features'));
        });
    });

    /*
    // mobile search
    */
    $(function() {
        const mobileSearch = $('.mobile-header__search');

        if (mobileSearch.length) {
            $('.mobile-indicator--search .mobile-indicator__button').on('click', function() {
                if (mobileSearch.is('.mobile-header__search--open')) {
                    mobileSearch.removeClass('mobile-header__search--open');
                } else {
                    mobileSearch.addClass('mobile-header__search--open');
                    mobileSearch.find('.mobile-search__input')[0].focus();
                }
            });

            mobileSearch.find('.mobile-search__button--close').on('click', function() {
                mobileSearch.removeClass('mobile-header__search--open');
            });

            document.addEventListener('click', function(event) {
                if (!$(event.target).closest('.mobile-indicator--search, .mobile-header__search, .modal').length) {
                    mobileSearch.removeClass('mobile-header__search--open');
                }
            }, true);

            $('.mobile-search__vehicle-picker').on('click', function () {
                $('#vehicle-picker-modal').modal('show');
            });
        }
    });

    /*
    // vehicle-picker-modal - Two-phase selection: Factory -> Vehicle
    */
    $(function() {
        let factoriesCache = null;
        let factoriesCacheTimestamp = null;
        let selectedFactoryId = null;
        let selectedFactoryName = null;
        const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

        // Update vehicle picker button text
        function updateVehiclePickerButton(vehicle) {
            if (vehicle && vehicle.name) {
                // Update mobile vehicle picker label
                $('.mobile-search__vehicle-picker-label').text(vehicle.name);
                // Update desktop vehicle picker label if it exists
                $('.search__button--start .search__button-label').text(vehicle.name);
            }
        }

        // Fetch factories from API
        function fetchFactories(forceRefresh = false) {
            if (factoriesCache && !forceRefresh) {
                const now = Date.now();
                if (factoriesCacheTimestamp && (now - factoriesCacheTimestamp) < CACHE_DURATION) {
                    return Promise.resolve(factoriesCache);
                }
            }

            return fetch('backend/api/factories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        factoriesCache = data.data;
                        factoriesCacheTimestamp = Date.now();
                        return factoriesCache;
                    }
                    return [];
                })
                .catch(error => {
                    console.error('Error fetching factories:', error);
                    return [];
                });
        }

        // Fetch vehicles for a specific factory
        function fetchVehiclesByFactory(factoryId) {
            return fetch('backend/api/vehicles.php?factory_id=' + factoryId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        return data.data;
                    }
                    return [];
                })
                .catch(error => {
                    console.error('Error fetching vehicles:', error);
                    return [];
                });
        }

        // Render factories list (Phase 1)
        function renderFactoriesList(factories, container) {
            container.empty();
            
            if (factories.length === 0) {
                container.html('<div class="vehicles-list__empty">هیچ کارخانه‌ای یافت نشد</div>');
                return;
            }

            factories.forEach(function(factory) {
                const factoryId = factory.id || '';
                const factoryName = factory.name || 'بدون نام';
                const vehicleCount = factory.vehicle_count || 0;

                const $item = $('<div class="vehicles-list__item vehicles-list__item--factory"></div>');
                $item.attr('data-factory-id', factoryId);
                $item.css('cursor', 'pointer');
                
                const $info = $('<span class="vehicles-list__item-info"></span>');
                const $name = $('<span class="vehicles-list__item-name"></span>').text(factoryName);
                const $count = $('<span class="vehicles-list__item-details"></span>').text(vehicleCount + ' وسیله نقلیه');
                
                $info.append($name).append(' ').append($count);
                $item.append($info);
                
                // Click handler to go to phase 2
                $item.on('click', function() {
                    selectedFactoryId = factoryId;
                    selectedFactoryName = factoryName;
                    showVehiclesPanel(factoryId, factoryName);
                });
                
                container.append($item);
            });
        }

        // Render vehicles list (Phase 2)
        function renderVehiclesList(vehicles, container) {
            container.empty();
            
            if (vehicles.length === 0) {
                container.html('<div class="vehicles-list__empty">هیچ وسیله نقلیه‌ای برای این کارخانه یافت نشد</div>');
                return;
            }

            vehicles.forEach(function(vehicle) {
                const vehicleId = vehicle.id || '';
                const vehicleName = vehicle.name || 'بدون نام';
                const vehicleDetails = vehicle.details || vehicle.description || vehicle.engine || '';

                const $item = $('<label class="vehicles-list__item"></label>');
                $item.attr('data-vehicle-id', vehicleId);
                
                const $radio = $('<span class="vehicles-list__item-radio input-radio"></span>');
                const $radioBody = $('<span class="input-radio__body"></span>');
                const $radioInput = $('<input class="input-radio__input" name="header-vehicle" type="radio">');
                $radioInput.attr('value', vehicleId);
                $radioInput.attr('data-vehicle-name', vehicleName);
                $radioInput.attr('data-vehicle-details', vehicleDetails);
                const $radioCircle = $('<span class="input-radio__circle"></span>');
                
                $radioBody.append($radioInput).append($radioCircle);
                $radio.append($radioBody);
                
                const $info = $('<span class="vehicles-list__item-info"></span>');
                const $name = $('<span class="vehicles-list__item-name"></span>').text(vehicleName);
                const $details = $('<span class="vehicles-list__item-details"></span>').text(vehicleDetails);
                
                $info.append($name).append(' ').append($details);
                
                $item.append($radio).append($info);
                container.append($item);
            });
        }

        // Show factories panel (Phase 1)
        function showFactoriesPanel(modal) {
            const factoriesPanel = modal.find('[data-panel="factories"]');
            const vehiclesPanel = modal.find('[data-panel="vehicles"]');
            const vehiclesContainer = vehiclesPanel.find('.vehicles-list__body');
            
            // Hide vehicles panel, show factories panel
            vehiclesPanel.removeClass('vehicle-picker-modal__panel--active');
            factoriesPanel.addClass('vehicle-picker-modal__panel--active');
            
            // Clear vehicles container
            vehiclesContainer.empty();
            
            // Reset selected factory
            selectedFactoryId = null;
            selectedFactoryName = null;
        }

        // Show vehicles panel (Phase 2)
        function showVehiclesPanel(factoryId, factoryName) {
            const modal = $('.vehicle-picker-modal').closest('.modal');
            const factoriesPanel = modal.find('[data-panel="factories"]');
            const vehiclesPanel = modal.find('[data-panel="vehicles"]');
            const vehiclesContainer = vehiclesPanel.find('.vehicles-list__body');
            const vehiclesTitle = vehiclesPanel.find('.vehicle-picker-modal__title');
            
            // Update title to show factory name
            vehiclesTitle.text('انتخاب وسیله نقلیه - ' + factoryName);
            
            // Hide factories panel, show vehicles panel
            factoriesPanel.removeClass('vehicle-picker-modal__panel--active');
            vehiclesPanel.addClass('vehicle-picker-modal__panel--active');
            
            // Show loading
            vehiclesContainer.html('<div class="vehicles-list__loading">در حال بارگذاری...</div>');
            
            // Fetch and render vehicles
            fetchVehiclesByFactory(factoryId).then(function(vehicles) {
                renderVehiclesList(vehicles, vehiclesContainer);
                // Load saved vehicle after rendering
                loadSavedVehicle();
            });
        }

        // Load saved vehicle and select it
        function loadSavedVehicle() {
            try {
                const savedVehicle = localStorage.getItem('selectedVehicle');
                if (savedVehicle) {
                    const vehicle = JSON.parse(savedVehicle);
                    // Find and select the matching vehicle radio button
                    $('input[name="header-vehicle"]').each(function() {
                        const vehicleName = $(this).attr('data-vehicle-name') || $(this).closest('.vehicles-list__item').find('.vehicles-list__item-name').text().trim();
                        if (vehicleName === vehicle.name) {
                            $(this).prop('checked', true);
                        }
                    });
                    // Update vehicle picker button text
                    updateVehiclePickerButton(vehicle);
                }
            } catch (e) {
                console.error('Error loading saved vehicle:', e);
            }
        }

        // Initialize modal
        $('.vehicle-picker-modal').closest('.modal').each(function() {
            const modal = $(this);
            const factoriesPanel = modal.find('[data-panel="factories"]');
            const vehiclesPanel = modal.find('[data-panel="vehicles"]');
            const factoriesContainer = factoriesPanel.find('.vehicles-list__body');
            let hasLoadedFactories = false;

            // Reset to factories panel when modal is hidden
            modal.on('hidden.bs.modal', function() {
                showFactoriesPanel(modal);
            });

            // Load factories when modal is shown
            modal.on('shown.bs.modal', function() {
                if (!hasLoadedFactories || !factoriesCache) {
                    hasLoadedFactories = true;
                    // Show loading state
                    factoriesContainer.html('<div class="vehicles-list__loading">در حال بارگذاری...</div>');
                    
                    fetchFactories().then(function(factories) {
                        renderFactoriesList(factories, factoriesContainer);
                    });
                }
                // Always show factories panel first
                showFactoriesPanel(modal);
            });

            // Back button handler (from vehicles to factories)
            vehiclesPanel.find('[data-back-to-factories]').on('click', function() {
                showFactoriesPanel(modal);
            });

            // Save vehicle and close modal when a vehicle is selected
            modal.on('change', 'input[name="header-vehicle"]', function() {
                const vehicleId = $(this).val();
                const vehicleName = $(this).attr('data-vehicle-name') || $(this).closest('.vehicles-list__item').find('.vehicles-list__item-name').text().trim();
                const vehicleDetails = $(this).attr('data-vehicle-details') || $(this).closest('.vehicles-list__item').find('.vehicles-list__item-details').text().trim();
                
                // Save to localStorage
                const vehicle = {
                    id: vehicleId,
                    name: vehicleName,
                    details: vehicleDetails,
                    factory_id: selectedFactoryId,
                    factory_name: selectedFactoryName,
                    timestamp: new Date().toISOString()
                };
                
                try {
                    localStorage.setItem('selectedVehicle', JSON.stringify(vehicle));
                    // Update vehicle picker button text
                    updateVehiclePickerButton(vehicle);
                } catch (e) {
                    console.error('Error saving vehicle:', e);
                }
                
                modal.modal('hide');
            });

            modal.find('.vehicle-picker-modal__close, .vehicle-picker-modal__close-button').on('click', function () {
                modal.modal('hide');
            });
        });

        // Load saved vehicle on page load (for button text)
        loadSavedVehicle();
    });

    /*
    // mobile-menu
    */
    $(function () {
        const body = $('body');
        const mobileMenu = $('.mobile-menu');
        const mobileMenuBody = mobileMenu.children('.mobile-menu__body');

        if (mobileMenu.length) {
            const open = function() {
                const bodyWidth = body.width();
                body.css('overflow', 'hidden');
                body.css('paddingRight', (body.width() - bodyWidth) + 'px');

                mobileMenu.addClass('mobile-menu--open');
            };
            const close = function() {
                body.css('overflow', 'auto');
                body.css('paddingRight', '');

                mobileMenu.removeClass('mobile-menu--open');
            };

            $('.mobile-header__menu-button').on('click', function() {
                open();
            });
            $('.mobile-menu__backdrop, .mobile-menu__close').on('click', function() {
                close();
            });
        }

        const panelsStack = [];
        let currentPanel = mobileMenuBody.children('.mobile-menu__panel');

        mobileMenu.on('click', '[data-mobile-menu-trigger]', function(event) {
            const trigger = $(this);
            const item = trigger.closest('[data-mobile-menu-item]');
            let panel = item.data('panel');

            if (!panel) {
                panel = item.children('[data-mobile-menu-panel]').children('.mobile-menu__panel');

                if (panel.length) {
                    mobileMenuBody.append(panel);
                    item.data('panel', panel);
                    panel.width(); // force reflow
                }
            }

            if (panel && panel.length) {
                event.preventDefault();

                panelsStack.push(currentPanel);
                currentPanel.addClass('mobile-menu__panel--hide');

                panel.removeClass('mobile-menu__panel--hidden');
                currentPanel = panel;
            }
        });
        mobileMenu.on('click', '.mobile-menu__panel-back', function() {
            currentPanel.addClass('mobile-menu__panel--hidden');
            currentPanel = panelsStack.pop();
            currentPanel.removeClass('mobile-menu__panel--hide');
        });
    });

    /*
    // off canvas filters
    */
    $(function () {
        const body = $('body');
        const sidebar = $('.sidebar');
        const offcanvas = sidebar.hasClass('sidebar--offcanvas--mobile') ? 'mobile' : 'always';
        const media = matchMedia('(max-width: 991px)');

        if (sidebar.length) {
            const open = function() {
                if (offcanvas === 'mobile' && !media.matches) {
                    return;
                }

                const bodyWidth = body.width();
                body.css('overflow', 'hidden');
                body.css('paddingRight', (body.width() - bodyWidth) + 'px');

                sidebar.addClass('sidebar--open');
            };
            const close = function() {
                body.css('overflow', 'auto');
                body.css('paddingRight', '');

                sidebar.removeClass('sidebar--open');
            };
            const onMediaChange = function() {
                if (offcanvas === 'mobile') {
                    if (!media.matches && sidebar.hasClass('sidebar--open')) {
                        close();
                    }
                }
            };

            if (media.addEventListener) {
                media.addEventListener('change', onMediaChange);
            } else {
                media.addListener(onMediaChange);
            }

            $('.filters-button').on('click', function() {
                open();
            });
            $('.sidebar__backdrop, .sidebar__close').on('click', function() {
                close();
            });
        }
    });

    /*
    // tooltips
    */
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    /*
    // departments megamenu
    */
    $(function () {
        let currentItem = null;
        const container = $('.departments__menu-container');

        $('.departments__item').on('mouseenter', function() {
            if (currentItem) {
                const megamenu = currentItem.data('megamenu');

                if (megamenu) {
                    megamenu.removeClass('departments__megamenu--open');
                }

                currentItem.removeClass('departments__item--hover');
                currentItem = null;
            }

            currentItem = $(this).addClass('departments__item--hover');

            if (currentItem.is('.departments__item--submenu--megamenu')) {
                let megamenu = currentItem.data('megamenu');

                if (!megamenu) {
                    megamenu = $(this).find('.departments__megamenu');

                    currentItem.data('megamenu', megamenu);

                    container.append(megamenu);
                }

                megamenu.addClass('departments__megamenu--open');
            }
        });
        $('.departments__list-padding').on('mouseenter', function() {
            if (currentItem) {
                const megamenu = currentItem.data('megamenu');

                if (megamenu) {
                    megamenu.removeClass('departments__megamenu--open');
                }

                currentItem.removeClass('departments__item--hover');
                currentItem = null;
            }
        });
        $('.departments__body').on('mouseleave', function() {
            if (currentItem) {
                const megamenu = currentItem.data('megamenu');

                if (megamenu) {
                    megamenu.removeClass('departments__megamenu--open');
                }

                currentItem.removeClass('departments__item--hover');
                currentItem = null;
            }
        });
    });

    /*
    // main menu / megamenu
    */
    $(function () {
        const megamenuArea = $('.megamenu-area');

        $('.main-menu__item--submenu--megamenu').on('mouseenter', function() {
            const megamenu = $(this).children('.main-menu__submenu');
            const offsetParent = megamenu.offsetParent();

            if (isRTL()) {
                const position = Math.max(
                    megamenuArea.offset().left,
                    Math.min(
                        $(this).offset().left + $(this).outerWidth() - megamenu.outerWidth(),
                        megamenuArea.offset().left + megamenuArea.outerWidth() - megamenu.outerWidth()
                    )
                ) - offsetParent.offset().left;

                megamenu.css('left', position + 'px');
            } else {
                const position = Math.max(
                    0,
                    Math.min(
                        $(this).offset().left,
                        megamenuArea.offset().left + megamenuArea.outerWidth() - megamenu.outerWidth()
                    )
                ) - offsetParent.offset().left;

                megamenu.css('left', position + 'px');
            }
        });
    });


    /*
    // Checkout payment methods
    */
    $(function () {
        $('[name="checkout_payment_method"]').on('change', function () {
            const currentItem = $(this).closest('.payment-methods__item');

            $(this).closest('.payment-methods__list').find('.payment-methods__item').each(function (i, element) {
                const links = $(element);
                const linksContent = links.find('.payment-methods__item-container');

                if (element !== currentItem[0]) {
                    const startHeight = linksContent.height();

                    linksContent.css('height', startHeight + 'px');
                    links.removeClass('payment-methods__item--active');

                    links.height(); // force reflow
                    linksContent.css('height', '');
                } else {
                    const startHeight = linksContent.height();

                    links.addClass('payment-methods__item--active');

                    const endHeight = linksContent.height();

                    linksContent.css('height', startHeight + 'px');
                    links.height(); // force reflow
                    linksContent.css('height', endHeight + 'px');
                }
            });
        });

        $('.payment-methods__item-container').on('transitionend', function (event) {
            if (event.originalEvent.propertyName === 'height') {
                $(this).css('height', '');
            }
        });
    });


    /*
    // add-vehicle-modal
    */
    $(function () {
        $('.filter-vehicle__button button').on('click', function () {
            $('#add-vehicle-modal').modal('show');
        });
    });


    /*
    // Quickview
    */
    const quickview = {
        cancelPreviousModal: function() {},
        clickHandler: function() {
            const modal = $('#quickview-modal');
            const button = $(this);
            const doubleClick = button.is('.product-card__action--loading');

            quickview.cancelPreviousModal();

            if (doubleClick) {
                return;
            }

            button.addClass('product-card__action--loading');

            let xhr = null;
            // timeout ONLY_FOR_DEMO!
            const timeout = setTimeout(function() {
                xhr = $.ajax({
                    url: 'quickview.html',
                    success: function(data) {
                        quickview.cancelPreviousModal = function() {};
                        button.removeClass('product-card__action--loading');

                        modal.html(data);
                        modal.find('.quickview__close').on('click', function() {
                            modal.modal('hide');
                        });
                        modal.modal('show');
                    }
                });
            }, 1000);

            quickview.cancelPreviousModal = function() {
                button.removeClass('product-card__action--loading');

                if (xhr) {
                    xhr.abort();
                }

                // timeout ONLY_FOR_DEMO!
                clearTimeout(timeout);
            };
        }
    };

    $(function () {
        const modal = $('#quickview-modal');

        modal.on('shown.bs.modal', function() {
            modal.find('.product-gallery').each(function(i, gallery) {
                initProductGallery(gallery, $(this).data('layout'));
            });

            $('.input-number', modal).customNumber();
        });

        $('.product-card__action--quickview').on('click', function() {
            quickview.clickHandler.apply(this, arguments);
        });
    });


    /*
    // .block-products-carousel
    */
    $(function () {
        const carouselOptions = {
            'grid-4': {
                items: 4,
            },
            'grid-4-sidebar': {
                items: 4,
                responsive: {
                    1400: {items: 4},
                    1200: {items: 3},
                    992: {items: 3, margin: 16},
                    768: {items: 3, margin: 16},
                    576: {items: 2, margin: 16},
                    460: {items: 2, margin: 16},
                    0: {items: 1},
                }
            },
            'grid-5': {
                items: 5,
                responsive: {
                    1400: {items: 5},
                    1200: {items: 4},
                    992: {items: 4, margin: 16},
                    768: {items: 3, margin: 16},
                    576: {items: 2, margin: 16},
                    460: {items: 2, margin: 16},
                    0: {items: 1},
                }
            },
            'grid-6': {
                items: 6,
                margin: 16,
                responsive: {
                    1400: {items: 6},
                    1200: {items: 4},
                    992: {items: 4, margin: 16},
                    768: {items: 3, margin: 16},
                    576: {items: 2, margin: 16},
                    460: {items: 2, margin: 16},
                    0: {items: 1},
                }
            },
            'horizontal': {
                items: 4,
                responsive: {
                    1400: {items: 4, margin: 14},
                    992: {items: 3, margin: 14},
                    768: {items: 2, margin: 14},
                    0: {items: 1, margin: 14},
                }
            },
            'horizontal-sidebar': {
                items: 3,
                responsive: {
                    1400: {items: 3, margin: 14},
                    768: {items: 2, margin: 14},
                    0: {items: 1, margin: 14},
                }
            }
        };

        $('.block-products-carousel').each(function() {
            const block = $(this);
            const layout = $(this).data('layout');
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel(Object.assign({}, {
                dots: false,
                margin: 20,
                loop: true,
                rtl: isRTL()
            }, carouselOptions[layout]));

            $(this).find('.section-header__arrow--prev').on('click', function() {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.section-header__arrow--next').on('click', function() {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });

            let cancelPreviousGroupChange = function() {};

            $(this).find('.section-header__groups-button').on('click', function() {
                const carousel = block.find('.block-products-carousel__carousel');

                if ($(this).is('.section-header__groups-button--active')) {
                    return;
                }

                cancelPreviousGroupChange();

                $(this).closest('.section-header__groups').find('.section-header__groups-button').removeClass('section-header__groups-button--active');
                $(this).addClass('section-header__groups-button--active');

                carousel.addClass('block-products-carousel__carousel--loading');

                // timeout ONLY_FOR_DEMO! you can replace it with an ajax request
                let timer;
                timer = setTimeout(function() {
                    let items = block.find('.owl-carousel .owl-item:not(".cloned") .block-products-carousel__column');

                    /*** this is ONLY_FOR_DEMO! / start */
                    /**/ const itemsArray = items.get();
                    /**/ const newItemsArray = [];
                    /**/
                    /**/ while (itemsArray.length > 0) {
                        /**/     const randomIndex = Math.floor(Math.random() * itemsArray.length);
                        /**/     const randomItem = itemsArray.splice(randomIndex, 1)[0];
                        /**/
                        /**/     newItemsArray.push(randomItem);
                        /**/ }
                    /**/ items = $(newItemsArray);
                    /*** this is ONLY_FOR_DEMO! / end */

                    block.find('.owl-carousel')
                        .trigger('replace.owl.carousel', [items])
                        .trigger('refresh.owl.carousel')
                        .trigger('to.owl.carousel', [0, 0]);

                    $('.product-card__action--quickview', block).on('click', function() {
                        quickview.clickHandler.apply(this, arguments);
                    });

                    carousel.removeClass('block-products-carousel__carousel--loading');
                }, 1000);
                cancelPreviousGroupChange = function() {
                    // timeout ONLY_FOR_DEMO!
                    clearTimeout(timer);
                    cancelPreviousGroupChange = function() {};
                };
            });
        });
    });

    /*
    // .block-posts-carousel
    */
    $(function () {
        const defaultOptions = {
            dots: false,
            margin: 20,
            loop: true,
            rtl: isRTL()
        };
        const options = {
            grid: {
                items: 4,
                responsive: {
                    1400: {items: 4, margin: 20},
                    1200: {items: 3, margin: 20},
                    992: {items: 3, margin: 16},
                    768: {items: 2, margin: 16},
                    0: {items: 1, margin: 16},
                },
            },
            list: {
                items: 2,
                responsive: {
                    1400: {items: 2, margin: 20},
                    992: {items: 2, margin: 16},
                    0: {items: 1, margin: 16},
                },
            },
        };

        $('.block-posts-carousel').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');
            const layout = $(this).data('layout');

            owlCarousel.owlCarousel(Object.assign({}, defaultOptions, options[layout]));

            $(this).find('.section-header__arrow--prev').on('click', function() {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.section-header__arrow--next').on('click', function() {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });
        });
    });

    /*
    // .block-teammates
    */
    $(function () {
        $('.block-teammates').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                dots: true,
                margin: 20,
                rtl: isRTL(),
                responsive: {
                    1200: {items: 5},
                    992: {items: 4},
                    768: {items: 3},
                    440: {items: 2},
                    0: {items: 1}
                }
            });
        });
    });

    /*
    // .block-reviews
    */
    $(function () {
        $('.block-reviews').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                dots: true,
                margin: 20,
                items: 1,
                loop: true,
                rtl: isRTL()
            });
        });
    });

    /*
    // .block-zone
    */
    $(function () {
        $('.block-zone').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                dots: false,
                margin: 20,
                loop: true,
                items: 4,
                rtl: isRTL(),
                responsive: {
                    1400: {items: 4, margin: 20},
                    992: {items: 3, margin: 16},
                    460: {items: 2, margin: 16},
                    0: {items: 1},
                }
            });

            $(this).find('.block-zone__arrow--prev').on('click', function() {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.block-zone__arrow--next').on('click', function() {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });

            let cancelPreviousTabChange = function() {};

            $(this).find('.block-zone__tabs-button').on('click', function() {
                const block = $(this).closest('.block-zone');
                const carousel = block.find('.block-zone__carousel');

                if ($(this).is('.block-zone__tabs-button--active')) {
                    return;
                }

                cancelPreviousTabChange();

                $(this).closest('.block-zone__tabs').find('.block-zone__tabs-button').removeClass('block-zone__tabs-button--active');
                $(this).addClass('block-zone__tabs-button--active');

                carousel.addClass('block-zone__carousel--loading');

                // timeout ONLY_FOR_DEMO! you can replace it with an ajax request
                let timer;
                timer = setTimeout(function() {
                    let items = block.find('.owl-carousel .owl-item:not(".cloned") .block-zone__carousel-item');

                    /*** this is ONLY_FOR_DEMO! / start */
                    /**/ const itemsArray = items.get();
                    /**/ const newItemsArray = [];
                    /**/
                    /**/ while (itemsArray.length > 0) {
                        /**/     const randomIndex = Math.floor(Math.random() * itemsArray.length);
                        /**/     const randomItem = itemsArray.splice(randomIndex, 1)[0];
                        /**/
                        /**/     newItemsArray.push(randomItem);
                        /**/ }
                    /**/ items = $(newItemsArray);
                    /*** this is ONLY_FOR_DEMO! / end */

                    block.find('.owl-carousel')
                        .trigger('replace.owl.carousel', [items])
                        .trigger('refresh.owl.carousel')
                        .trigger('to.owl.carousel', [0, 0]);

                    $('.product-card__action--quickview', block).on('click', function() {
                        quickview.clickHandler.apply(this, arguments);
                    });

                    carousel.removeClass('block-zone__carousel--loading');
                }, 1000);
                cancelPreviousTabChange = function() {
                    // timeout ONLY_FOR_DEMO!
                    clearTimeout(timer);
                    cancelPreviousTabChange = function() {};
                };
            });
        });
    });

    /*
    // initialize custom numbers
    */
    $(function () {
        $('.input-number').customNumber();
    });

    /*
    // header vehicle
    */
    $(function () {
        const input = $('.search__input');
        const suggestions = $('.search__dropdown--suggestions');
        const vehiclePicker = $('.search__dropdown--vehicle-picker');
        const vehiclePickerButton = $('.search__button--start');

        input.on('focus', function() {
            suggestions.addClass('search__dropdown--open');
        });
        input.on('blur', function() {
            suggestions.removeClass('search__dropdown--open');
        });

        vehiclePickerButton.on('click', function() {
            vehiclePickerButton.toggleClass('search__button--hover');
            vehiclePicker.toggleClass('search__dropdown--open');
        });

        vehiclePicker.on('transitionend', function(event) {
            if (event.originalEvent.propertyName === 'visibility' && vehiclePicker.is(event.target)) {
                vehiclePicker.find('.vehicle-picker__panel:eq(0)').addClass('vehicle-picker__panel--active');
                vehiclePicker.find('.vehicle-picker__panel:gt(0)').removeClass('vehicle-picker__panel--active');
            }
            if (event.originalEvent.propertyName === 'height' && vehiclePicker.is(event.target)) {
                vehiclePicker.css('height', '');
            }
        });

        $(document).on('click', function (event) {
            if (!$(event.target).closest('.search__dropdown--vehicle-picker, .search__button--start').length) {
                vehiclePickerButton.removeClass('search__button--hover');
                vehiclePicker.removeClass('search__dropdown--open');
            }
        });

        $('.vehicle-picker [data-to-panel]').on('click', function(event) {
            event.preventDefault();

            const toPanel = $(this).data('to-panel');
            const currentPanel = vehiclePicker.find('.vehicle-picker__panel--active');
            const nextPanel = vehiclePicker.find('[data-panel="' + toPanel + '"]');

            currentPanel.removeClass('vehicle-picker__panel--active');
            nextPanel.addClass('vehicle-picker__panel--active');
        });
    });

    /*
    // .block-sale
    */
    $(function () {
        $('.block-sale').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                items: 5,
                dots: true,
                margin: 24,
                loop: true,
                rtl: isRTL(),
                responsive: {
                    1400: {items: 5},
                    1200: {items: 4},
                    992: {items: 4, margin: 16},
                    768: {items: 3, margin: 16},
                    576: {items: 2, margin: 16},
                    460: {items: 2, margin: 16},
                    0: {items: 1},
                },
            });

            $(this).find('.block-sale__arrow--prev').on('click', function() {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.block-sale__arrow--next').on('click', function() {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });
        });
        $('.block-sale__timer').each(function() {
            const timer = $(this);
            const MINUTE = 60;
            const HOUR = MINUTE * 60;
            const DAY = HOUR * 24;

            let left = DAY * 3;

            const format = function(number) {
                let result = number.toFixed();

                if (result.length === 1) {
                    result = '0' + result;
                }

                return result;
            };

            const updateTimer = function() {
                left -= 1;

                if (left < 0) {
                    left = 0;

                    clearInterval(interval);
                }

                const leftDays = Math.floor(left / DAY);
                const leftHours = Math.floor((left - leftDays * DAY) / HOUR);
                const leftMinutes = Math.floor((left - leftDays * DAY - leftHours * HOUR) / MINUTE);
                const leftSeconds = left - leftDays * DAY - leftHours * HOUR - leftMinutes * MINUTE;

                timer.find('.timer__part-value--days').text(format(leftDays));
                timer.find('.timer__part-value--hours').text(format(leftHours));
                timer.find('.timer__part-value--minutes').text(format(leftMinutes));
                timer.find('.timer__part-value--seconds').text(format(leftSeconds));
            };

            const interval = setInterval(updateTimer, 1000);

            updateTimer();
        });
    });

    /*
    // .block-slideshow
    */
    $(function () {
        $('.block-slideshow__carousel').each(function() {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                items: 1,
                dots: true,
                loop: true,
                rtl: isRTL()
            });
        });
    });

    /*
    // .block-finder
    */
    $(function () {
        $('.block-finder__form-control--select select').on('change', function() {
            const item = $(this).closest('.block-finder__form-control--select');

            if ($(this).val() !== 'none') {
                item.find('~ .block-finder__form-control--select:eq(0) select').prop('disabled', false).val('none');
                item.find('~ .block-finder__form-control--select:gt(0) select').prop('disabled', true).val('none');
            } else {
                item.find('~ .block-finder__form-control--select select').prop('disabled', true).val('none');
            }

            item.find('~ .block-finder__form-control--select select').trigger('change.select2');
        });
    });

    /*
    // .block-header
    */
    (function(){
        // So that breadcrumbs correctly flow around the page title, we need to know its width.
        // This code simply conveys the width of the page title in CSS.

        const media = matchMedia('(min-width: 1200px)');
        const updateTitleWidth = function() {
            const width = $('.block-header__title').outerWidth();
            const titleSafeArea = $('.breadcrumb__title-safe-area').get(0);

            if (titleSafeArea && width) {
                titleSafeArea.style.setProperty('--block-header-title-width', width+'px');
            }
        };

        if (media.matches) {
            updateTitleWidth();
        }

        if (media.addEventListener) {
            media.addEventListener('change', updateTitleWidth);
        } else {
            media.addListener(updateTitleWidth);
        }
    })();

    /*
    // select2
    */
    $(function () {
        $('.form-control-select2, .block-finder__form-control--select select').select2({width: ''});
    });

    /*
    // .vehicle-form
    */
    $(function () {
        $('.vehicle-form__item--select select').on('change', function() {
            const item = $(this).closest('.vehicle-form__item--select');

            if ($(this).val() !== 'none') {
                item.find('~ .vehicle-form__item--select:eq(0) select').prop('disabled', false).val('none');
                item.find('~ .vehicle-form__item--select:gt(0) select').prop('disabled', true).val('none');
            } else {
                item.find('~ .vehicle-form__item--select select').prop('disabled', true).val('none');
            }

            item.find('~ .vehicle-form__item--select select').trigger('change.select2');
        });
    });

    /*
    // Vehicle storage utility functions
    */
    window.getSelectedVehicle = function() {
        try {
            const savedVehicle = localStorage.getItem('selectedVehicle');
            if (savedVehicle) {
                return JSON.parse(savedVehicle);
            }
        } catch (e) {
            console.error('Error getting selected vehicle:', e);
        }
        return null;
    };

    window.clearSelectedVehicle = function() {
        try {
            localStorage.removeItem('selectedVehicle');
            // Reset vehicle picker button text
            $('.mobile-search__vehicle-picker-label').text('وسیله نقلیه شما');
            $('.search__button--start .search__button-label').text('Vehicle');
        } catch (e) {
            console.error('Error clearing selected vehicle:', e);
        }
    };
    /*
    // Dynamic Breadcrumb for Product Pages
    */
    $(function() {
        const productTitle = $('.product__title').text().trim();
        if (productTitle) {
            // Update the last breadcrumb item
            const lastBreadcrumbItem = $('.breadcrumb__item--last .breadcrumb__item-link');
            if (lastBreadcrumbItem.length) {
                lastBreadcrumbItem.text(productTitle);
            }
        }
    });

})(jQuery);
