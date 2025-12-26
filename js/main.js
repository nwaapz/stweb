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
            let min = parseFloat($(element).data('min'));
            let max = parseFloat($(element).data('max'));
            let from = parseFloat($(element).data('from'));
            let to = parseFloat($(element).data('to'));
            const slider = element.querySelector('.filter-price__slider');

            if (!slider) {
                return; // Slider element not found
            }

            // Validate and set defaults for min/max
            if (isNaN(min) || min < 0) min = 0;
            if (isNaN(max) || max <= min) max = min + 1000;

            // Validate and clamp from/to values
            if (isNaN(from) || from < min) from = min;
            if (from > max) from = max;
            if (isNaN(to) || to > max) to = max;
            if (to < min) to = min;
            if (from > to) {
                // If from > to, swap them or set to defaults
                from = min;
                to = max;
            }

            // Ensure from and to are within valid range
            from = Math.max(min, Math.min(max, from));
            to = Math.max(min, Math.min(max, to));
            if (from > to) {
                from = min;
                to = max;
            }

            // Destroy existing slider if it exists
            if (slider.noUiSlider) {
                slider.noUiSlider.destroy();
            }

            // Ensure slider element has dimensions before initialization
            function initWhenReady() {
                if (slider.offsetWidth === 0 || slider.offsetParent === null) {
                    // Wait for element to be visible
                    requestAnimationFrame(initWhenReady);
                } else {
                    initializeSlider(slider, min, max, from, to, element);
                }
            }
            initWhenReady();
        });

        function initializeSlider(slider, min, max, from, to, element) {
            try {
                // Ensure all values are numbers
                min = Number(min);
                max = Number(max);
                from = Number(from);
                to = Number(to);

                // Final validation
                if (isNaN(min) || min < 0) min = 0;
                if (isNaN(max) || max <= min) max = min + 1000;
                if (isNaN(from) || from < min) from = min;
                if (from > max) from = max;
                if (isNaN(to) || to > max) to = max;
                if (to < min) to = min;
                if (from > to) {
                    from = min;
                    to = max;
                }

                noUiSlider.create(slider, {
                    start: [from, to],
                    connect: true,
                    direction: 'ltr',
                    behaviour: 'tap-drag',
                    step: 1,
                    range: {
                        'min': min,
                        'max': max
                    },
                    format: {
                        to: function (value) {
                            return Math.round(Number(value));
                        },
                        from: function (value) {
                            return Number(value);
                        }
                    }
                });

                const titleValues = [
                    $(element).find('.filter-price__min-value')[0],
                    $(element).find('.filter-price__max-value')[0]
                ];

                // Always update displayed values to show current handle positions
                // This handler will run every time the slider values change
                slider.noUiSlider.on('update', function (values, handle) {
                    // Always show current handle positions (from/to values)
                    // Format value as integer (remove decimals)
                    const formattedValue = Math.round(parseFloat(values[handle]));
                    if (titleValues[handle]) {
                        titleValues[handle].innerHTML = formattedValue.toLocaleString('fa-IR');
                        // Mark that this is showing handle position, not filtered range
                        $(titleValues[handle]).data('is-filtered-range', false);
                    }
                });
                
                // Also update on slide (while dragging) for real-time feedback
                slider.noUiSlider.on('slide', function (values, handle) {
                    const formattedValue = Math.round(parseFloat(values[handle]));
                    if (titleValues[handle]) {
                        titleValues[handle].innerHTML = formattedValue.toLocaleString('fa-IR');
                        $(titleValues[handle]).data('is-filtered-range', false);
                    }
                });
                
                // Track user interaction to prevent filtered range from overriding handle positions
                slider.noUiSlider.on('start', function() {
                    $(element).data('user-interacted', true);
                    // Ensure values will show handle positions
                    const $minValue = $(element).find('.filter-price__min-value');
                    const $maxValue = $(element).find('.filter-price__max-value');
                    if ($minValue.length) {
                        $minValue.data('is-filtered-range', false);
                    }
                    if ($maxValue.length) {
                        $maxValue.data('is-filtered-range', false);
                    }
                });
                
                // Also update on set (when values are programmatically changed)
                slider.noUiSlider.on('set', function (values, handle) {
                    // Only update if user has interacted (to avoid conflicts with initial setup)
                    if ($(element).data('user-interacted')) {
                        const formattedValue = Math.round(parseFloat(values[handle]));
                        if (titleValues[handle]) {
                            titleValues[handle].innerHTML = formattedValue.toLocaleString('fa-IR');
                            $(titleValues[handle]).data('is-filtered-range', false);
                        }
                    }
                });

                // Force slider to update positions after initialization
                // Use multiple timeouts to ensure proper positioning
                setTimeout(function() {
                    if (slider.noUiSlider) {
                        const currentValues = slider.noUiSlider.get();
                        // Force re-render by setting values
                        slider.noUiSlider.set(currentValues);
                        
                        // Fix handle positioning for RTL layout
                        const handles = slider.querySelectorAll('.noUi-handle');
                        handles.forEach(function(handle) {
                            const origin = handle.closest('.noUi-origin');
                            if (origin) {
                                // Ensure proper positioning
                                origin.style.right = 'auto';
                                origin.style.left = 'auto';
                            }
                        });
                    }
                }, 50);
                
                // Additional fix after a longer delay to ensure layout is complete
                setTimeout(function() {
                    if (slider.noUiSlider) {
                        const currentValues = slider.noUiSlider.get();
                        slider.noUiSlider.set(currentValues);
                    }
                }, 200);
            } catch (error) {
                console.error('Error initializing price slider:', error);
            }
        }
    });

    /*
    // .product-gallery
    */
    const initProductGallery = function (element, layout) {
        layout = layout !== undefined ? layout : 'standard';

        const options = {
            dots: false,
            margin: 10,
            rtl: isRTL(),
        };
        const layoutOptions = {
            'product-sidebar': {
                responsive: {
                    1400: { items: 8, margin: 10 },
                    1200: { items: 6, margin: 10 },
                    992: { items: 8, margin: 10 },
                    768: { items: 8, margin: 10 },
                    576: { items: 6, margin: 10 },
                    420: { items: 5, margin: 8 },
                    0: { items: 4, margin: 8 }
                },
            },
            'product-full': {
                responsive: {
                    1400: { items: 6, margin: 10 },
                    1200: { items: 5, margin: 8 },
                    992: { items: 7, margin: 10 },
                    768: { items: 5, margin: 8 },
                    576: { items: 6, margin: 8 },
                    420: { items: 5, margin: 8 },
                    0: { items: 4, margin: 8 }
                }
            },
            quickview: {
                responsive: {
                    992: { items: 5 },
                    520: { items: 6 },
                    440: { items: 5 },
                    340: { items: 4 },
                    0: { items: 3 }
                }
            },
        };

        const gallery = $(element);

        const image = gallery.find('.product-gallery__featured .owl-carousel');
        const carousel = gallery.find('.product-gallery__thumbnails .owl-carousel');

        image
            .owlCarousel({ items: 1, dots: false, rtl: isRTL() })
            .on('changed.owl.carousel', syncPosition);

        carousel
            .on('initialized.owl.carousel', function () {
                carousel.find('.product-gallery__thumbnails-item').eq(0).addClass('product-gallery__thumbnails-item--active');
            })
            .owlCarousel($.extend({}, options, layoutOptions[layout]));

        carousel.on('click', '.owl-item', function (e) {
            e.preventDefault();

            image.data('owl.carousel').to($(this).index(), 300, true);
        });

        gallery.find('.product-gallery__zoom').on('click', function () {
            openPhotoSwipe(image.find('.owl-item.active').index());
        });

        image.on('click', '.owl-item > a', function (event) {
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
            const photoSwipeImages = image.find('.owl-item a').toArray().map(function (element) {
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

        function syncPosition(el) {
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

    $(function () {
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
    $(function () {
        $('.departments__button').on('click', function (event) {
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
    $(function () {
        $('.topbar__menu-button').on('click', function () {
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
    $(function () {
        $('.indicator--trigger--click .indicator__button').on('click', function (event) {
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
        $('.layout-switcher__button').on('click', function () {
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
    $(function () {
        const mobileSearch = $('.mobile-header__search');

        if (mobileSearch.length) {
            $('.mobile-indicator--search .mobile-indicator__button').on('click', function () {
                if (mobileSearch.is('.mobile-header__search--open')) {
                    mobileSearch.removeClass('mobile-header__search--open');
                } else {
                    mobileSearch.addClass('mobile-header__search--open');
                    mobileSearch.find('.mobile-search__input')[0].focus();
                }
            });

            mobileSearch.find('.mobile-search__button--close').on('click', function () {
                mobileSearch.removeClass('mobile-header__search--open');
            });

            document.addEventListener('click', function (event) {
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
    $(function () {
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

            factories.forEach(function (factory) {
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
                $item.on('click', function () {
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

            vehicles.forEach(function (vehicle) {
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
            fetchVehiclesByFactory(factoryId).then(function (vehicles) {
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
                    $('input[name="header-vehicle"]').each(function () {
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
        $('.vehicle-picker-modal').closest('.modal').each(function () {
            const modal = $(this);
            const factoriesPanel = modal.find('[data-panel="factories"]');
            const vehiclesPanel = modal.find('[data-panel="vehicles"]');
            const factoriesContainer = factoriesPanel.find('.vehicles-list__body');
            let hasLoadedFactories = false;

            // Reset to factories panel when modal is hidden
            modal.on('hidden.bs.modal', function () {
                showFactoriesPanel(modal);
            });

            // Load factories when modal is shown
            modal.on('shown.bs.modal', function () {
                if (!hasLoadedFactories || !factoriesCache) {
                    hasLoadedFactories = true;
                    // Show loading state
                    factoriesContainer.html('<div class="vehicles-list__loading">در حال بارگذاری...</div>');

                    fetchFactories().then(function (factories) {
                        renderFactoriesList(factories, factoriesContainer);
                    });
                }
                // Always show factories panel first
                showFactoriesPanel(modal);
            });

            // Back button handler (from vehicles to factories)
            vehiclesPanel.find('[data-back-to-factories]').on('click', function () {
                showFactoriesPanel(modal);
            });

            // Save vehicle and close modal when a vehicle is selected
            modal.on('change', 'input[name="header-vehicle"]', function () {
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
            const open = function () {
                const bodyWidth = body.width();
                body.css('overflow', 'hidden');
                body.css('paddingRight', (body.width() - bodyWidth) + 'px');

                mobileMenu.addClass('mobile-menu--open');
            };
            const close = function () {
                body.css('overflow', 'auto');
                body.css('paddingRight', '');

                mobileMenu.removeClass('mobile-menu--open');
            };

            $('.mobile-header__menu-button').on('click', function () {
                open();
            });
            $('.mobile-menu__backdrop, .mobile-menu__close').on('click', function () {
                close();
            });
        }

        const panelsStack = [];
        let currentPanel = mobileMenuBody.children('.mobile-menu__panel');

        mobileMenu.on('click', '[data-mobile-menu-trigger]', function (event) {
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
        mobileMenu.on('click', '.mobile-menu__panel-back', function () {
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
            const open = function () {
                if (offcanvas === 'mobile' && !media.matches) {
                    return;
                }

                const bodyWidth = body.width();
                body.css('overflow', 'hidden');
                body.css('paddingRight', (body.width() - bodyWidth) + 'px');

                sidebar.addClass('sidebar--open');
            };
            const close = function () {
                body.css('overflow', 'auto');
                body.css('paddingRight', '');

                sidebar.removeClass('sidebar--open');
            };
            const onMediaChange = function () {
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

            $('.filters-button').on('click', function () {
                open();
            });
            $('.sidebar__backdrop, .sidebar__close').on('click', function () {
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

        $('.departments__item').on('mouseenter', function () {
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
        $('.departments__list-padding').on('mouseenter', function () {
            if (currentItem) {
                const megamenu = currentItem.data('megamenu');

                if (megamenu) {
                    megamenu.removeClass('departments__megamenu--open');
                }

                currentItem.removeClass('departments__item--hover');
                currentItem = null;
            }
        });
        $('.departments__body').on('mouseleave', function () {
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

        $('.main-menu__item--submenu--megamenu').on('mouseenter', function () {
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
        cancelPreviousModal: function () { },
        clickHandler: function () {
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
            const timeout = setTimeout(function () {
                xhr = $.ajax({
                    url: 'quickview.html',
                    success: function (data) {
                        quickview.cancelPreviousModal = function () { };
                        button.removeClass('product-card__action--loading');

                        modal.html(data);
                        modal.find('.quickview__close').on('click', function () {
                            modal.modal('hide');
                        });
                        modal.modal('show');
                    }
                });
            }, 1000);

            quickview.cancelPreviousModal = function () {
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

        modal.on('shown.bs.modal', function () {
            modal.find('.product-gallery').each(function (i, gallery) {
                initProductGallery(gallery, $(this).data('layout'));
            });

            $('.input-number', modal).customNumber();
        });

        $('.product-card__action--quickview').on('click', function () {
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
                    1400: { items: 4 },
                    1200: { items: 3 },
                    992: { items: 3, margin: 16 },
                    768: { items: 3, margin: 16 },
                    576: { items: 2, margin: 16 },
                    460: { items: 2, margin: 16 },
                    0: { items: 1 },
                }
            },
            'grid-5': {
                items: 5,
                responsive: {
                    1400: { items: 5 },
                    1200: { items: 4 },
                    992: { items: 4, margin: 16 },
                    768: { items: 3, margin: 16 },
                    576: { items: 2, margin: 16 },
                    460: { items: 2, margin: 16 },
                    0: { items: 1 },
                }
            },
            'grid-6': {
                items: 6,
                margin: 16,
                responsive: {
                    1400: { items: 6 },
                    1200: { items: 4 },
                    992: { items: 4, margin: 16 },
                    768: { items: 3, margin: 16 },
                    576: { items: 2, margin: 16 },
                    460: { items: 2, margin: 16 },
                    0: { items: 1 },
                }
            },
            'horizontal': {
                items: 4,
                responsive: {
                    1400: { items: 4, margin: 14 },
                    992: { items: 3, margin: 14 },
                    768: { items: 2, margin: 14 },
                    0: { items: 1, margin: 14 },
                }
            },
            'horizontal-sidebar': {
                items: 3,
                responsive: {
                    1400: { items: 3, margin: 14 },
                    768: { items: 2, margin: 14 },
                    0: { items: 1, margin: 14 },
                }
            }
        };

        $('.block-products-carousel').each(function () {
            const block = $(this);
            const layout = $(this).data('layout');
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel(Object.assign({}, {
                dots: false,
                margin: 20,
                loop: true,
                rtl: isRTL()
            }, carouselOptions[layout]));

            $(this).find('.section-header__arrow--prev').on('click', function () {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.section-header__arrow--next').on('click', function () {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });

            let cancelPreviousGroupChange = function () { };

            $(this).find('.section-header__groups-button').on('click', function () {
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
                timer = setTimeout(function () {
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
                        /**/
                    }
                    /**/ items = $(newItemsArray);
                    /*** this is ONLY_FOR_DEMO! / end */

                    block.find('.owl-carousel')
                        .trigger('replace.owl.carousel', [items])
                        .trigger('refresh.owl.carousel')
                        .trigger('to.owl.carousel', [0, 0]);

                    $('.product-card__action--quickview', block).on('click', function () {
                        quickview.clickHandler.apply(this, arguments);
                    });

                    carousel.removeClass('block-products-carousel__carousel--loading');
                }, 1000);
                cancelPreviousGroupChange = function () {
                    // timeout ONLY_FOR_DEMO!
                    clearTimeout(timer);
                    cancelPreviousGroupChange = function () { };
                };
            });
        });

        // Load latest products dynamically for "تازه‌ها" section
        $('.block-products-carousel[data-layout="horizontal"] .owl-carousel[data-latest-products-carousel]').each(function () {
            const owlCarousel = $(this);
            const block = owlCarousel.closest('.block-products-carousel');
            const carousel = block.find('.block-products-carousel__carousel');

            // Show loading state
            carousel.addClass('block-products-carousel__carousel--loading');

            // Function to render latest products
            function renderLatestProducts(products) {
                if (products.length === 0) {
                    carousel.removeClass('block-products-carousel__carousel--loading');
                    owlCarousel.html('<div style="text-align: center; padding: 40px;">محصول جدیدی یافت نشد</div>');
                    return;
                }

                // Destroy existing carousel if initialized
                if (owlCarousel.data('owl.carousel')) {
                    owlCarousel.trigger('destroy.owl.carousel');
                    owlCarousel.removeClass('owl-loaded owl-drag');
                }

                owlCarousel.empty();

                // Group products in columns (2 products per column for horizontal layout)
                for (let i = 0; i < products.length; i += 2) {
                    const column = $('<div class="block-products-carousel__column"></div>');
                    let productsAdded = 0;

                    // Add up to 2 products in this column
                    for (let j = 0; j < 2 && (i + j) < products.length; j++) {
                        const product = products[i + j];

                        // Skip if product data is invalid
                        if (!product || !product.id) {
                            continue;
                        }

                        const productUrl = product.slug ? 'product-full.html?product=' + encodeURIComponent(product.slug) : 'product-full.html?id=' + product.id;
                        const productImage = product.image_url || 'images/products/product-1-245x245.jpg';
                        const productName = (product.name && product.name.trim()) ? product.name.trim() : 'بدون نام';
                        const productPrice = product.formatted_price || '0';
                        const discountPrice = product.formatted_discount_price || null;
                        const productRating = parseFloat(product.rating) || 0;
                        const productReviews = parseInt(product.reviews) || 0;

                        // Calculate number of active stars (0-5)
                        const activeStars = Math.min(5, Math.max(0, Math.round(productRating)));

                        // Build badges container
                        const badgesContainer = $('<div class="product-card__badges"></div>');
                        if (product.has_discount) {
                            badgesContainer.append('<div class="tag-badge tag-badge--sale">حراج</div>');
                        }
                        // Check if product is new (created in last 30 days)
                        if (product.created_at) {
                            try {
                                const createdDate = new Date(product.created_at);
                                const daysDiff = (new Date() - createdDate) / (1000 * 60 * 60 * 24);
                                if (daysDiff <= 30) {
                                    badgesContainer.append('<div class="tag-badge tag-badge--new">جدید</div>');
                                }
                            } catch (e) {
                                // Invalid date, skip
                            }
                        }
                        if (product.is_featured) {
                            badgesContainer.append('<div class="tag-badge tag-badge--hot">ویژه</div>');
                        }

                        // Build rating stars
                        const ratingBody = $('<div class="rating__body"></div>');
                        for (let star = 1; star <= 5; star++) {
                            const starDiv = $('<div class="rating__star"></div>');
                            if (star <= activeStars) {
                                starDiv.addClass('rating__star--active');
                            }
                            ratingBody.append(starDiv);
                        }

                        // Build price container
                        const pricesContainer = $('<div class="product-card__prices"></div>');
                        if (discountPrice && product.has_discount) {
                            pricesContainer.append($('<div class="product-card__price product-card__price--new"></div>').text(discountPrice));
                            pricesContainer.append($('<div class="product-card__price product-card__price--old"></div>').text(productPrice));
                        } else {
                            pricesContainer.append($('<div class="product-card__price product-card__price--current"></div>').text(productPrice));
                        }

                        // Build product card using jQuery
                        const cell = $('<div class="block-products-carousel__cell"></div>');
                        const productCard = $('<div class="product-card product-card--layout--horizontal"></div>');

                        // Actions
                        const actionsList = $('<div class="product-card__actions-list"></div>');
                        const quickviewBtn = $('<button class="product-card__action product-card__action--quickview" type="button" aria-label="Quick view"></button>');
                        quickviewBtn.html('<svg width="16" height="16"><path d="M14,15h-4v-2h3v-3h2v4C15,14.6,14.6,15,14,15z M13,3h-3V1h4c0.6,0,1,0.4,1,1v4h-2V3z M6,3H3v3H1V2c0-0.6,0.4-1,1-1h4V3z M3,13h3v2H2c-0.6,0-1-0.4-1-1v-4h2V13z"/></svg>');
                        actionsList.append(quickviewBtn);
                        productCard.append(actionsList);

                        // Image
                        const imageContainer = $('<div class="product-card__image"></div>');
                        const imageDiv = $('<div class="image image--type--product"></div>');
                        const imageLink = $('<a href="' + productUrl + '" class="image__body"></a>');
                        const productImg = $('<img class="image__tag">');
                        productImg.attr('src', productImage);
                        productImg.attr('alt', productName);
                        productImg.attr('onerror', "this.src='images/products/product-1-245x245.jpg'");
                        imageLink.append(productImg);
                        imageDiv.append(imageLink);
                        imageContainer.append(imageDiv);
                        productCard.append(imageContainer);

                        // Info
                        const infoContainer = $('<div class="product-card__info"></div>');
                        const nameContainer = $('<div class="product-card__name"></div>');
                        const nameDiv = $('<div></div>');
                        if (badgesContainer.children().length > 0) {
                            nameDiv.append(badgesContainer);
                        }
                        nameDiv.append($('<a href="' + productUrl + '"></a>').text(productName));
                        nameContainer.append(nameDiv);
                        infoContainer.append(nameContainer);

                        // Rating
                        const ratingContainer = $('<div class="product-card__rating"></div>');
                        const ratingStars = $('<div class="rating product-card__rating-stars"></div>');
                        ratingStars.append(ratingBody);
                        ratingContainer.append(ratingStars);
                        ratingContainer.append($('<div class="product-card__rating-label"></div>').text(productRating.toFixed(1) + ' از ' + productReviews + ' نظر'));
                        infoContainer.append(ratingContainer);
                        productCard.append(infoContainer);

                        // Footer
                        const footerContainer = $('<div class="product-card__footer"></div>');
                        footerContainer.append(pricesContainer);
                        productCard.append(footerContainer);

                        cell.append(productCard);
                        column.append(cell);
                        productsAdded++;
                    }

                    // If last column has only 1 product, add empty placeholder cell to maintain height consistency
                    if (productsAdded === 1 && (i + 1) >= products.length) {
                        const emptyCell = $('<div class="block-products-carousel__cell"></div>');
                        // Create empty card structure to maintain layout height - keep visible but empty
                        const emptyCard = $('<div class="product-card product-card--layout--horizontal"></div>');
                        emptyCard.css('opacity', '0');
                        emptyCell.append(emptyCard);
                        column.append(emptyCell);
                    }

                    // Only append column if it has at least one product
                    if (column.children().length > 0 && productsAdded > 0) {
                        owlCarousel.append(column);
                    }
                }

                // Re-initialize carousel with new content (horizontal layout settings)
                owlCarousel.owlCarousel({
                    dots: false,
                    margin: 14,
                    loop: products.length > 4,
                    rtl: isRTL(),
                    items: 4,
                    responsive: {
                        1400: { items: 4 },
                        1200: { items: 3 },
                        992: { items: 3 },
                        768: { items: 2 },
                        576: { items: 2 },
                        0: { items: 1 }
                    }
                });

                carousel.removeClass('block-products-carousel__carousel--loading');

                // Re-bind arrow handlers
                block.find('.section-header__arrow--prev').off('click').on('click', function () {
                    owlCarousel.trigger('prev.owl.carousel', [500]);
                });
                block.find('.section-header__arrow--next').off('click').on('click', function () {
                    owlCarousel.trigger('next.owl.carousel', [500]);
                });

                // Re-initialize quickview handlers
                $('.product-card__action--quickview', block).off('click').on('click', function () {
                    if (typeof quickview !== 'undefined' && quickview.clickHandler) {
                        quickview.clickHandler.apply(this, arguments);
                    }
                });
            }

            // Fetch latest products from API (ordered by created_at DESC)
            const apiUrl = 'backend/api/products.php?order_by=created_at&order_dir=DESC&limit=10';

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        renderLatestProducts(data.data);
                    } else {
                        carousel.removeClass('block-products-carousel__carousel--loading');
                        owlCarousel.html('<div style="text-align: center; padding: 40px;">محصول جدیدی یافت نشد</div>');
                    }
                })
                .catch(error => {
                    console.error('Error fetching latest products:', error);
                    carousel.removeClass('block-products-carousel__carousel--loading');
                    owlCarousel.html('<div style="text-align: center; padding: 40px;">خطا در بارگذاری محصولات</div>');
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
                    1400: { items: 4, margin: 20 },
                    1200: { items: 3, margin: 20 },
                    992: { items: 3, margin: 16 },
                    768: { items: 2, margin: 16 },
                    0: { items: 1, margin: 16 },
                },
            },
            list: {
                items: 2,
                responsive: {
                    1400: { items: 2, margin: 20 },
                    992: { items: 2, margin: 16 },
                    0: { items: 1, margin: 16 },
                },
            },
        };

        $('.block-posts-carousel').each(function () {
            const owlCarousel = $(this).find('.owl-carousel');
            const layout = $(this).data('layout');

            owlCarousel.owlCarousel(Object.assign({}, defaultOptions, options[layout]));

            $(this).find('.section-header__arrow--prev').on('click', function () {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.section-header__arrow--next').on('click', function () {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });
        });
    });

    /*
    // .block-teammates
    */
    $(function () {
        $('.block-teammates').each(function () {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                dots: true,
                margin: 20,
                rtl: isRTL(),
                responsive: {
                    1200: { items: 5 },
                    992: { items: 4 },
                    768: { items: 3 },
                    440: { items: 2 },
                    0: { items: 1 }
                }
            });
        });
    });

    /*
    // .block-reviews
    */
    $(function () {
        $('.block-reviews').each(function () {
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
        $('.block-zone').each(function () {
            const owlCarousel = $(this).find('.owl-carousel');

            owlCarousel.owlCarousel({
                dots: false,
                margin: 20,
                loop: true,
                items: 4,
                rtl: isRTL(),
                responsive: {
                    1400: { items: 4, margin: 20 },
                    992: { items: 3, margin: 16 },
                    460: { items: 2, margin: 16 },
                    0: { items: 1 },
                }
            });

            $(this).find('.block-zone__arrow--prev').on('click', function () {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.block-zone__arrow--next').on('click', function () {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });

            let cancelPreviousTabChange = function () { };

            $(this).find('.block-zone__tabs-button').on('click', function () {
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
                timer = setTimeout(function () {
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
                        /**/
                    }
                    /**/ items = $(newItemsArray);
                    /*** this is ONLY_FOR_DEMO! / end */

                    block.find('.owl-carousel')
                        .trigger('replace.owl.carousel', [items])
                        .trigger('refresh.owl.carousel')
                        .trigger('to.owl.carousel', [0, 0]);

                    $('.product-card__action--quickview', block).on('click', function () {
                        quickview.clickHandler.apply(this, arguments);
                    });

                    carousel.removeClass('block-zone__carousel--loading');
                }, 1000);
                cancelPreviousTabChange = function () {
                    // timeout ONLY_FOR_DEMO!
                    clearTimeout(timer);
                    cancelPreviousTabChange = function () { };
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

        input.on('focus', function () {
            suggestions.addClass('search__dropdown--open');
        });
        input.on('blur', function () {
            suggestions.removeClass('search__dropdown--open');
        });

        vehiclePickerButton.on('click', function () {
            vehiclePickerButton.toggleClass('search__button--hover');
            vehiclePicker.toggleClass('search__dropdown--open');
        });

        vehiclePicker.on('transitionend', function (event) {
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

        $('.vehicle-picker [data-to-panel]').on('click', function (event) {
            event.preventDefault();

            const toPanel = $(this).data('to-panel');
            const currentPanel = vehiclePicker.find('.vehicle-picker__panel--active');
            const nextPanel = vehiclePicker.find('[data-panel="' + toPanel + '"]');

            currentPanel.removeClass('vehicle-picker__panel--active');
            nextPanel.addClass('vehicle-picker__panel--active');
        });
    });

    /*
    // .block-sale - Load discounted products from CMS
    */
    $(function () {
        $('.block-sale').each(function () {
            const block = $(this);
            const owlCarousel = $(this).find('.owl-carousel');
            const initialProductsHtml = owlCarousel.html(); // Store static products as fallback

            // Function to render discounted products in sale carousel
            function renderSaleProducts(products) {
                // Filter to only show products with active discounts
                const discountedProducts = products.filter(function (product) {
                    return product.has_discount && product.formatted_discount_price;
                });

                if (discountedProducts.length === 0) {
                    // If no discounted products, keep static products or show message
                    return;
                }

                // Destroy existing carousel if initialized
                if (owlCarousel.data('owl.carousel')) {
                    owlCarousel.trigger('destroy.owl.carousel');
                    owlCarousel.removeClass('owl-loaded owl-drag');
                }

                owlCarousel.empty();
                owlCarousel.css('opacity', '0'); // Hide while loading

                discountedProducts.forEach(function (product) {
                    const productUrl = product.slug ? 'product-full.html?product=' + encodeURIComponent(product.slug) : 'product-full.html?id=' + product.id;
                    const productImage = product.image_url || 'images/products/product-1-245x245.jpg';
                    const productName = product.name || 'بدون نام';
                    const productPrice = product.formatted_price || '0';
                    const discountPrice = product.formatted_discount_price || null;
                    const productSku = product.sku || '';
                    const vehicleName = product.vehicle_name || '';

                    // Create sale item
                    const saleItem = $('<div class="block-sale__item"></div>');
                    const productCard = $('<div class="product-card"></div>');

                    // Product actions
                    const actionsList = $('<div class="product-card__actions-list"></div>');
                    actionsList.append('<button class="product-card__action product-card__action--quickview" type="button" aria-label="Quick view"><svg width="16" height="16"><path d="M14,15h-4v-2h3v-3h2v4C15,14.6,14.6,15,14,15z M13,3h-3V1h4c0.6,0,1,0.4,1,1v4h-2V3z M6,3H3v3H1V2c0-0.6,0.4-1,1-1h4V3z M3,13h3v2H2c-0.6,0-1-0.4-1-1v-4h2V13z"/></svg></button>');
                    actionsList.append('<button class="product-card__action product-card__action--wishlist" type="button" aria-label="Add to wish list"><svg width="16" height="16"><path d="M13.9,8.4l-5.4,5.4c-0.3,0.3-0.7,0.3-1,0L2.1,8.4c-1.5-1.5-1.5-3.8,0-5.3C2.8,2.4,3.8,2,4.8,2s1.9,0.4,2.6,1.1L8,3.7l0.6-0.6C9.3,2.4,10.3,2,11.3,2c1,0,1.9,0.4,2.6,1.1C15.4,4.6,15.4,6.9,13.9,8.4z"/></svg></button>');
                    actionsList.append('<button class="product-card__action product-card__action--compare" type="button" aria-label="Add to compare"><svg width="16" height="16"><path d="M9,15H7c-0.6,0-1-0.4-1-1V2c0-0.6,0.4-1,1-1h2c0.6,0,1,0.4,1,1v12C10,14.6,9.6,15,9,15z"/><path d="M1,9h2c0.6,0,1,0.4,1,1v4c0,0.6-0.4,1-1,1H1c-0.6,0-1-0.4-1-1v-4C0,9.4,0.4,9,1,9z"/><path d="M15,5h-2c-0.6,0-1,0.4-1,1v8c0,0.6,0.4,1,1,1h2c0.6,0,1-0.4,1-1V6C16,5.4,15.6,5,15,5z"/></svg></button>');

                    // Product image
                    const imageDiv = $('<div class="product-card__image"></div>');
                    const image = $('<div class="image image--type--product"></div>');
                    const imageLink = $('<a href="' + productUrl + '" class="image__body"></a>');
                    imageLink.append('<img class="image__tag" src="' + productImage + '" alt="' + productName + '" onerror="this.src=\'images/products/product-1-245x245.jpg\'">');
                    image.append(imageLink);
                    imageDiv.append(image);

                    // Vehicle badge if available
                    if (vehicleName) {
                        const statusBadge = $('<div class="status-badge status-badge--style--success product-card__fit status-badge--has-icon status-badge--has-text"></div>');
                        const badgeBody = $('<div class="status-badge__body"></div>');
                        badgeBody.append('<div class="status-badge__icon"><svg width="13" height="13"><path d="M12,4.4L5.5,11L1,6.5l1.4-1.4l3.1,3.1L10.6,3L12,4.4z"/></svg></div>');
                        badgeBody.append('<div class="status-badge__text">مناسب برای ' + vehicleName + '</div>');
                        badgeBody.append('<div class="status-badge__tooltip" tabindex="0" data-toggle="tooltip" title="" data-original-title="مناسب برای ' + vehicleName + '"></div>');
                        statusBadge.append(badgeBody);
                        imageDiv.append(statusBadge);
                    }

                    // Product info
                    const infoDiv = $('<div class="product-card__info"></div>');
                    if (productSku) {
                        infoDiv.append('<div class="product-card__meta"><span class="product-card__meta-title">شناسه:</span> ' + productSku + '</div>');
                    }
                    const nameDiv = $('<div class="product-card__name"></div>');
                    nameDiv.append('<div><a href="' + productUrl + '">' + productName + '</a></div>');
                    infoDiv.append(nameDiv);

                    // Product footer with discount prices
                    const footerDiv = $('<div class="product-card__footer"></div>');
                    const pricesDiv = $('<div class="product-card__prices"></div>');
                    // Always show discount price as new price and original as old price
                    pricesDiv.append('<div class="product-card__price product-card__price--new">' + discountPrice + '</div>');
                    pricesDiv.append('<div class="product-card__price product-card__price--old">' + productPrice + '</div>');
                    footerDiv.append(pricesDiv);
                    footerDiv.append('<button class="product-card__addtocart-icon" type="button" aria-label="Add to cart"><svg width="20" height="20"><circle cx="7" cy="17" r="2"/><circle cx="15" cy="17" r="2"/><path d="M20,4.4V5l-1.8,6.3c-0.1,0.4-0.5,0.7-1,0.7H6.7c-0.4,0-0.8-0.3-1-0.7L3.3,3.9C3.1,3.3,2.6,3,2.1,3H0.4C0.2,3,0,2.8,0,2.6V1.4C0,1.2,0.2,1,0.4,1h2.5c1,0,1.8,0.6,2.1,1.6L5.1,3l2.3,6.8c0,0.1,0.2,0.2,0.3,0.2h8.6c0.1,0,0.3-0.1,0.3-0.2l1.3-4.4C17.9,5.2,17.7,5,17.5,5H9.4C9.2,5,9,4.8,9,4.6V3.4C9,3.2,9.2,3,9.4,3h9.2C19.4,3,20,3.6,20,4.4z"/></svg></button>');

                    // Assemble product card
                    productCard.append(actionsList);
                    productCard.append(imageDiv);
                    productCard.append(infoDiv);
                    productCard.append(footerDiv);

                    saleItem.append(productCard);
                    owlCarousel.append(saleItem);
                });

                // Re-initialize carousel with new content
                owlCarousel.owlCarousel({
                    items: 5,
                    dots: true,
                    margin: 24,
                    loop: discountedProducts.length > 5,
                    rtl: isRTL(),
                    responsive: {
                        1400: { items: 5 },
                        1200: { items: 4 },
                        992: { items: 4, margin: 16 },
                        768: { items: 3, margin: 16 },
                        576: { items: 2, margin: 16 },
                        460: { items: 2, margin: 16 },
                        0: { items: 1 },
                    },
                });

                owlCarousel.css('opacity', '1'); // Show carousel

                // Re-bind arrow handlers
                block.find('.block-sale__arrow--prev').off('click').on('click', function () {
                    owlCarousel.trigger('prev.owl.carousel', [500]);
                });
                block.find('.block-sale__arrow--next').off('click').on('click', function () {
                    owlCarousel.trigger('next.owl.carousel', [500]);
                });

                // Re-initialize quickview handlers
                $('.product-card__action--quickview', block).off('click').on('click', function () {
                    if (typeof quickview !== 'undefined' && quickview.clickHandler) {
                        quickview.clickHandler.apply(this, arguments);
                    }
                });
            }

            // Fetch discounted products from API
            const apiUrl = 'backend/api/products.php?discounted=1&limit=20';

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        renderSaleProducts(data.data);
                    } else {
                        // If no discounted products, keep static products
                        owlCarousel.css('opacity', '1');
                    }
                })
                .catch(error => {
                    console.error('Error fetching discounted products:', error);
                    // On error, keep static products
                    owlCarousel.css('opacity', '1');
                });

            // Initialize carousel with static products first (will be replaced if API call succeeds)
            owlCarousel.owlCarousel({
                items: 5,
                dots: true,
                margin: 24,
                loop: true,
                rtl: isRTL(),
                responsive: {
                    1400: { items: 5 },
                    1200: { items: 4 },
                    992: { items: 4, margin: 16 },
                    768: { items: 3, margin: 16 },
                    576: { items: 2, margin: 16 },
                    460: { items: 2, margin: 16 },
                    0: { items: 1 },
                },
            });

            $(this).find('.block-sale__arrow--prev').on('click', function () {
                owlCarousel.trigger('prev.owl.carousel', [500]);
            });
            $(this).find('.block-sale__arrow--next').on('click', function () {
                owlCarousel.trigger('next.owl.carousel', [500]);
            });
        });
        $('.block-sale__timer').each(function () {
            const timer = $(this);
            const MINUTE = 60;
            const HOUR = MINUTE * 60;
            const DAY = HOUR * 24;

            let left = DAY * 3;

            const format = function (number) {
                let result = number.toFixed();

                if (result.length === 1) {
                    result = '0' + result;
                }

                return result;
            };

            const updateTimer = function () {
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
        $('.block-slideshow__carousel').each(function () {
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
    // .block-finder - Dynamic loading from CMS
    */
    $(function () {
        const $makeSelect = $('#block-finder-make');
        const $modelSelect = $('#block-finder-model');
        const $blockFinderForm = $('.block-finder__form');

        // Load factories (brands) from CMS API
        function loadFactories() {
            $.ajax({
                url: 'backend/api/factories.php',
                method: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        // Clear existing options except the default
                        $makeSelect.find('option:not([value="none"])').remove();

                        // Add factories to dropdown
                        response.data.forEach(function (factory) {
                            $makeSelect.append($('<option>', {
                                value: factory.id,
                                text: factory.name
                            }));
                        });

                        // Enable the make select
                        $makeSelect.prop('disabled', false);
                        $makeSelect.trigger('change.select2');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading factories:', error);
                }
            });
        }

        // Load vehicles (models) for selected factory
        function loadVehicles(factoryId) {
            if (!factoryId || factoryId === 'none') {
                $modelSelect.prop('disabled', true).val('none');
                $modelSelect.find('option:not([value="none"])').remove();
                $modelSelect.trigger('change.select2');
                return;
            }

            $.ajax({
                url: 'backend/api/vehicles.php',
                method: 'GET',
                data: { factory_id: factoryId },
                dataType: 'json',
                success: function (response) {
                    if (response.success && response.data) {
                        // Clear existing options except the default
                        $modelSelect.find('option:not([value="none"])').remove();

                        // Add vehicles to dropdown
                        response.data.forEach(function (vehicle) {
                            $modelSelect.append($('<option>', {
                                value: vehicle.id,
                                text: vehicle.name || (vehicle.make + ' ' + vehicle.model)
                            }));
                        });

                        // Enable the model select
                        $modelSelect.prop('disabled', false);
                        $modelSelect.trigger('change.select2');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error loading vehicles:', error);
                    $modelSelect.prop('disabled', true);
                }
            });
        }

        // Handle make (brand) selection change
        $makeSelect.on('change', function () {
            const selectedFactoryId = $(this).val();
            loadVehicles(selectedFactoryId);
        });

        // Handle form submission
        $blockFinderForm.on('submit', function (e) {
            e.preventDefault();

            const makeId = $makeSelect.val();
            const modelId = $modelSelect.val();

            if (makeId === 'none' || modelId === 'none') {
                alert('لطفاً برند و مدل را انتخاب کنید');
                return false;
            }

            // Redirect to search/category page with selected vehicle
            // You can customize this URL based on your routing structure
            const searchUrl = 'category.html?make=' + makeId + '&model=' + modelId;
            window.location.href = searchUrl;

            return false;
        });

        // Initialize: Load factories on page load
        loadFactories();
    });

    /*
    // .block-header
    */
    (function () {
        // So that breadcrumbs correctly flow around the page title, we need to know its width.
        // This code simply conveys the width of the page title in CSS.

        const media = matchMedia('(min-width: 1200px)');
        const updateTitleWidth = function () {
            const width = $('.block-header__title').outerWidth();
            const titleSafeArea = $('.breadcrumb__title-safe-area').get(0);

            if (titleSafeArea && width) {
                titleSafeArea.style.setProperty('--block-header-title-width', width + 'px');
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
        $('.form-control-select2, .block-finder__form-control--select select').select2({ width: '' });
    });

    /*
    // .vehicle-form
    */
    $(function () {
        $('.vehicle-form__item--select select').on('change', function () {
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
    window.getSelectedVehicle = function () {
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

    window.clearSelectedVehicle = function () {
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
    $(function () {
        console.log("Breadcrumb Sync Script Started");

        // Product Title
        const productTitle = $('.product__title').text().trim();
        if (productTitle) {
            const lastBreadcrumbItem = $('.breadcrumb__item--last .breadcrumb__item-link');
            if (lastBreadcrumbItem.length) {
                lastBreadcrumbItem.text(productTitle);
            }
        }

        // Product Category (from Tags)
        /*
        // Disabled as per user request to use "فروشگاه" (Shop) statically
        const firstTagRef = $('.product__tags .tags__list a').first();
        const firstTag = firstTagRef.text().trim();

        console.log("First Tag Found:", firstTag);

        if (firstTag) {
            const categoryBreadcrumbItem = $('.breadcrumb__item--category');
            console.log("Category Breadcrumb Item Length:", categoryBreadcrumbItem.length);

            if (categoryBreadcrumbItem.length) {
                categoryBreadcrumbItem.text(firstTag);
                console.log("Updated category to:", firstTag);
            }
        }
        */
    });

    /*
    // Dynamic Analogs (Related Products)
    */
    $(function () {
        const analogsTab = $('#product-tab-analogs');

        if (analogsTab.length) {
            const tbody = analogsTab.find('.analogs-table tbody');

            // Get product ID or Slug from URL
            const urlParams = new URLSearchParams(window.location.search);
            // Check 'product' param (slug) or fallback to 'id'
            const productParam = urlParams.get('product') || urlParams.get('id') || '1';

            if (productParam) {
                // Show loading state
                tbody.html('<tr><td colspan="4" style="text-align:center;">در حال بارگذاری...</td></tr>');

                // Step 1: Determine API endpoint (ID vs Slug)
                let apiEndpoint = 'backend/api/products.php?id=' + encodeURIComponent(productParam);
                if (isNaN(productParam)) {
                    apiEndpoint = 'backend/api/products.php?slug=' + encodeURIComponent(productParam);
                }

                // Step 1: Fetch current product
                fetch(apiEndpoint)
                    .then(response => response.json())
                    .then(productData => {
                        if (productData.success && productData.data) {
                            const categoryId = productData.data.category_id;
                            const currentProductId = productData.data.id;

                            // Step 2: Fetch related products by category ID
                            if (categoryId) {
                                // Pass current ID to filter it out in next step if needed, 
                                // though we filter client side below.
                                return fetch('backend/api/products.php?category=' + categoryId + '&limit=4') // Get 4 to have buffer for filtering
                                    .then(res => res.json())
                                    .then(data => ({
                                        ...data,
                                        currentId: currentProductId
                                    }));
                            }
                        }
                        throw new Error('Product or category not found');
                    })
                    .then(data => {
                        tbody.empty();

                        // Step 3: Display related products
                        if (data.success && data.data && data.data.length > 0) {
                            // Filter out current product
                            const relatedProducts = data.data.filter(p => p.id != data.currentId).slice(0, 3);

                            if (relatedProducts.length > 0) {
                                relatedProducts.forEach(product => {
                                    const tr = $('<tr>');

                                    // Name & SKU
                                    const nameCol = $('<td class="analogs-table__column analogs-table__column--name">');
                                    const nameLink = $('<a>')
                                        .addClass('analogs-table__product-name')
                                        // Use Slug in URL
                                        .attr('href', 'product-full.html?product=' + encodeURIComponent(product.slug))
                                        .text(product.name);
                                    const skuDiv = $('<div>')
                                        .addClass('analogs-table__sku')
                                        .attr('data-title', 'SKU')
                                        .text(product.sku || 'N/A');
                                    nameCol.append(nameLink, '<br>', skuDiv);

                                    // Rating
                                    const ratingCol = $('<td class="analogs-table__column analogs-table__column--rating">');
                                    const ratingDiv = $('<div class="analogs-table__rating">');
                                    const starsHtml = getStarsHtml(product.rating || 5);
                                    const ratingStars = $('<div class="analogs-table__rating-stars">')
                                        .html('<div class="rating"><div class="rating__body">' + starsHtml + '</div></div>');
                                    const ratingLabel = $('<div class="analogs-table__rating-label">')
                                        .text((product.reviews || 0) + ' نظرات');
                                    ratingDiv.append(ratingStars, ratingLabel);
                                    ratingCol.append(ratingDiv);

                                    // Vendor
                                    const vendorCol = $('<td class="analogs-table__column analogs-table__column--vendor" data-title="Vendor">');
                                    const vendorName = product.vehicle_name || 'Brandix'; // Default or from DB
                                    const country = product.country || 'Germany'; // Default or from DB
                                    vendorCol.html(vendorName + ' <div class="analogs-table__country">(' + country + ')</div>');

                                    // Price
                                    const priceCol = $('<td class="analogs-table__column analogs-table__column--price">')
                                        .text(product.formatted_price);

                                    tr.append(nameCol, ratingCol, vendorCol, priceCol);
                                    tbody.append(tr);
                                });
                            } else {
                                tbody.html('<tr><td colspan="4" style="text-align:center;">محصول مشابهی یافت نشد</td></tr>');
                            }

                        } else {
                            tbody.html('<tr><td colspan="4" style="text-align:center;">محصول مشابهی یافت نشد</td></tr>');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching analogs:', error);
                        tbody.html('<tr><td colspan="4" style="text-align:center;">محصول مشابهی یافت نشد</td></tr>');
                    });
            } else {
                tbody.html('<tr><td colspan="4" style="text-align:center;">محصول مشخص نیست</td></tr>');
            }
        }

        function getStarsHtml(rating) {
            let html = '';
            for (let i = 0; i < 5; i++) {
                if (i < rating) {
                    html += '<div class="rating__star rating__star--active"></div>';
                } else {
                    html += '<div class="rating__star"></div>';
                }
            }
            return html;
        }
    });

    /*
    // Dynamic Main Product Rating
    */
    $(function () {
        const ratingContainer = $('.product__rating');
        if (ratingContainer.length) {
            // Get product ID or Slug from URL
            const urlParams = new URLSearchParams(window.location.search);
            const productParam = urlParams.get('product') || urlParams.get('id');

            if (productParam) {
                let apiEndpoint = 'backend/api/products.php?id=' + encodeURIComponent(productParam);
                if (isNaN(productParam)) {
                    apiEndpoint = 'backend/api/products.php?slug=' + encodeURIComponent(productParam);
                }

                fetch(apiEndpoint)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const product = data.data;
                            const rating = parseFloat(product.rating) || 0;
                            const reviews = parseInt(product.reviews) || 0;

                            // Helper for stars
                            let starsHtml = '';
                            for (let i = 0; i < 5; i++) {
                                if (i < Math.round(rating)) {
                                    starsHtml += '<div class="rating__star rating__star--active"></div>';
                                } else {
                                    starsHtml += '<div class="rating__star"></div>';
                                }
                            }

                            ratingContainer.find('.rating__body').html(starsHtml);
                            // Update product tags dynamically
                            const tagsList = $('.product__tags .tags__list');
                            if (tagsList.length) {
                                const category = product.category_name || '';
                                const vehicle = product.vehicle_name || '';
                                let tagsHtml = '';
                                if (category) tagsHtml += `<a href="">${category}</a>`;
                                if (vehicle) tagsHtml += ` <a href="">${vehicle}</a>`;
                                tagsList.html(tagsHtml);
                            }
                            ratingContainer.find('.product__rating-label a').text(`${reviews} نظرات`);
                        }
                    })
                    .catch(e => console.error('Error fetching product rating:', e));
            }
        }
    });

    /*
    // Load factories (brands) section dynamically from CMS
    */
    $(function () {
        const $brandsList = $('#factories-brands-list');

        if ($brandsList.length) {
            // Fetch factories from API
            fetch('backend/api/factories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        // Clear loading state
                        $brandsList.empty();

                        // Render factories
                        const factories = data.data.filter(f => f.is_active && f.logo_url); // Only active factories with logos

                        if (factories.length === 0) {
                            $brandsList.html('<li style="text-align: center; padding: 40px; width: 100%;">هیچ کارخانه‌ای یافت نشد</li>');
                            return;
                        }

                        factories.forEach(function (factory, index) {
                            // Create factory item
                            const $item = $('<li>').addClass('block-brands__item');
                            const $link = $('<a>')
                                .addClass('block-brands__item-link')
                                .attr('href', factory.slug ? 'category.html?factory=' + factory.id : '#')
                                .attr('title', factory.name);

                            // Create image - handle both absolute and relative URLs
                            let logoUrl = factory.logo_url;
                            if (logoUrl && !logoUrl.startsWith('http') && !logoUrl.startsWith('/')) {
                                // If relative path doesn't start with /, make it relative to root
                                logoUrl = '/' + logoUrl;
                            }

                            const $img = $('<img>')
                                .attr('src', logoUrl || '')
                                .attr('alt', factory.name)
                                .css('max-width', '100%')
                                .css('height', 'auto')
                                .on('error', function () {
                                    // Hide image if it fails to load
                                    $(this).hide();
                                });

                            // Create name span
                            const $name = $('<span>')
                                .addClass('block-brands__item-name')
                                .text(factory.name);

                            $link.append($img, $name);
                            $item.append($link);
                            $brandsList.append($item);

                            // Add divider after each item except the last one
                            if (index < factories.length - 1) {
                                const $divider = $('<li>')
                                    .addClass('block-brands__divider')
                                    .attr('role', 'presentation');
                                $brandsList.append($divider);
                            }
                        });
                    } else {
                        $brandsList.html('<li style="text-align: center; padding: 40px; width: 100%;">هیچ کارخانه‌ای یافت نشد</li>');
                    }
                })
                .catch(error => {
                    console.error('Error loading factories for brands section:', error);
                    $brandsList.html('<li style="text-align: center; padding: 40px; width: 100%;">خطا در بارگذاری کارخانجات</li>');
                });
        }
    });

})(jQuery);
