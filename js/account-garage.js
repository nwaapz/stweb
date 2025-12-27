(function ($) {
    "use strict";

    const API_URL = 'backend/api/';

    // UI Elements
    // Fixed selector to target the specific list container
    const $vehicleList = $('#garage-vehicles-list');
    const $brandSelect = $('.vehicle-form--layout--account select[aria-label="Brand"]');
    const $modelSelect = $('.vehicle-form--layout--account select[aria-label="Model"]');
    // Updated selector to use ID
    const $addButton = $('#user-add-vehicle-btn');

    // Custom Inputs (will be created dynamically or toggled)
    let isCustomMode = false;

    function init() {
        loadUserVehicles();
        loadFactories();
        setupEventListeners();
    }

    function setupEventListeners() {
        // Brand Change
        $brandSelect.on('change', function () {
            const val = $(this).val();

            if (val === 'other') {
                enableCustomMode();
            } else {
                disableCustomMode();
                if (val !== 'none') {
                    loadVehicles(val);
                } else {
                    $modelSelect.prop('disabled', true).html('<option value="none">انتخاب مدل</option>');
                }
            }
        });

        // Add Vehicle
        $addButton.on('click', function (e) {
            e.preventDefault();
            console.log('Add Vehicle button clicked');
            addVehicle();
        });

        // Remove Vehicle - handle both class names for compatibility
        $(document).on('click', '.vehicle-remove-btn, .remove-vehicle-btn', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (confirm('آیا از حذف این خودرو مطمئن هستید؟')) {
                removeVehicle(id);
            }
        });
    }

    // Prevent multiple simultaneous loads
    let isLoadingVehicles = false;
    
    function loadUserVehicles() {
        // Prevent duplicate calls
        if (isLoadingVehicles) {
            return;
        }
        
        isLoadingVehicles = true;
        
        $.ajax({
            url: API_URL + 'user_garage.php',
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    renderVehicles(response.data);
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = 'account/login.php';
                } else {
                    console.error('Garage Load Error:', xhr);
                    $vehicleList.html('<div class="alert alert-danger">خطا در دریافت اطلاعات خودروها: ' + (xhr.responseJSON?.error || xhr.statusText) + '</div>');
                }
            },
            complete: function() {
                isLoadingVehicles = false;
            }
        });
    }

    function renderVehicles(vehicles) {
        // Clear all content including any template elements
        $vehicleList.empty();
        
        // Remove any duplicate items that might exist (from main.js or previous renders)
        $('.vehicles-list--layout--account .vehicles-list__item').remove();

        if (vehicles.length === 0) {
            $vehicleList.html('<div class="p-4 text-center text-muted">هنوز خودرویی ثبت نکرده‌اید.</div>');
            return;
        }

        // Create a document fragment to batch DOM operations
        const fragment = document.createDocumentFragment();
        
        vehicles.forEach(vehicle => {
            const item = document.createElement('div');
            item.className = 'vehicles-list__item';
            item.setAttribute('data-id', vehicle.id);
            
            // Build parts link if vehicle_id exists
            const partsLink = vehicle.vehicle_id 
                ? `shop-grid-4-columns-sidebar.html?vehicle_id=${vehicle.vehicle_id}&user_vehicle_id=${vehicle.id}`
                : '#';
            
            const vehicleEngine = vehicle.engine ? `motor: ${vehicle.engine}` : '';
            
            item.innerHTML = `
                <div class="vehicles-list__item-info">
                    <div class="vehicles-list__item-name">${escapeHtml(vehicle.display_name)}</div>
                    ${vehicleEngine ? `<div class="vehicles-list__item-details">${escapeHtml(vehicleEngine)}</div>` : ''}
                    <div class="vehicles-list__item-links">
                        ${vehicle.vehicle_id ? `<a href="${partsLink}">نمایش قطعات</a> <span>|</span> ` : ''}
                        <a href="#" class="text-danger vehicle-remove-btn remove-vehicle-btn" data-id="${vehicle.id}">حذف</a>
                    </div>
                </div>
            `;
            fragment.appendChild(item);
        });
        
        // Append all items at once
        $vehicleList[0].appendChild(fragment);
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function loadFactories() {
        $.ajax({
            url: API_URL + 'factories.php',
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let options = '<option value="none">انتخاب برند</option>';
                    response.data.forEach(factory => {
                        options += `<option value="${factory.id}">${factory.name}</option>`;
                    });
                    options += '<option value="other">سایر (Other)</option>';

                    $brandSelect.html(options).prop('disabled', false);
                }
            }
        });
    }

    function loadVehicles(factoryId) {
        $modelSelect.prop('disabled', true).html('<option value="none">در حال بارگذاری...</option>');

        $.ajax({
            url: API_URL + 'vehicles.php',
            data: { factory_id: factoryId },
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    let options = '<option value="none">انتخاب مدل</option>';
                    response.data.forEach(vehicle => {
                        options += `<option value="${vehicle.id}">${vehicle.name}</option>`;
                    });

                    $modelSelect.html(options).prop('disabled', false);
                }
            }
        });
    }

    function enableCustomMode() {
        isCustomMode = true;

        if ($('#custom-brand-input').length === 0) {
            $('<input type="text" id="custom-brand-input" class="form-control mb-2" placeholder="نام برند (مثلا Benz)">').insertAfter($brandSelect.parent());
            $('<input type="text" id="custom-model-input" class="form-control mb-2" placeholder="نام مدل (مثلا C200)">').insertAfter($modelSelect.parent());
        }

        $brandSelect.parent().hide();
        $modelSelect.parent().hide();

        // Show Inputs
        $('#custom-brand-input').show().focus();
        $('#custom-model-input').show();

        // Add a "Cancel Custom Mode" button
        if ($('#cancel-custom-btn').length === 0) {
            $('<button type="button" id="cancel-custom-btn" class="btn btn-sm btn-link text-danger">بازگشت به انتخاب از لیست</button>')
                .insertAfter($brandSelect.closest('.vehicle-form'));

            $('#cancel-custom-btn').on('click', function () {
                disableCustomMode();
                $brandSelect.val('none').trigger('change');
            });
        }
        $('#cancel-custom-btn').show();
    }

    function disableCustomMode() {
        isCustomMode = false;
        $('#custom-brand-input').hide();
        $('#custom-model-input').hide();
        $('#cancel-custom-btn').hide();

        $brandSelect.parent().show();
        $modelSelect.parent().show();
    }

    function addVehicle() {
        let data = {};

        if (isCustomMode) {
            data.custom_brand = $('#custom-brand-input').val();
            data.custom_model = $('#custom-model-input').val();

            if (!data.custom_brand || !data.custom_model) {
                alert('لطفا نام برند و مدل را وارد کنید');
                return;
            }
        } else {
            const factoryId = $brandSelect.val();
            const vehicleId = $modelSelect.val();

            if (factoryId === 'none' || vehicleId === 'none') {
                alert('لطفا برند و مدل را انتخاب کنید');
                return;
            }

            data.factory_id = factoryId;
            data.vehicle_id = vehicleId;
        }

        // Removed VIN from data

        $.ajax({
            url: API_URL + 'user_garage.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function (response) {
                if (response.success) {
                    alert('خودرو با موفقیت افزوده شد');
                    loadUserVehicles();
                    resetForm();
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = 'account/login.php';
                    return;
                }
                alert(xhr.responseJSON?.error || 'خطا در ثبت خودرو');
            }
        });
    }

    function removeVehicle(id) {
        $.ajax({
            url: API_URL + 'user_garage.php?id=' + id,
            method: 'DELETE',
            success: function (response) {
                if (response.success) {
                    loadUserVehicles();
                }
            },
            error: function (xhr) {
                if (xhr.status === 401) {
                    window.location.href = 'account/login.php';
                }
            }
        });
    }

    function resetForm() {
        if (isCustomMode) {
            disableCustomMode();
        }
        $brandSelect.val('none');
        $modelSelect.val('none').prop('disabled', true);
    }

    // Mark that account-garage.js is handling garage functionality
    window.accountGarageInitialized = true;
    
    // Initialize
    $(document).ready(init);

})(jQuery);
