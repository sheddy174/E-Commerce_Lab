/**
 * Brand Management JavaScript
 * Handles CRUD operations for brands via AJAX
 */

$(document).ready(function () {
    // Initialize DataTable
    let brandsTable;

    // Load brands and categories on page load
    loadCategories();
    loadBrands();

    /**
     * Load categories for dropdown menus
     */
    function loadCategories() {
        $.ajax({
            url: '../actions/fetch_categories_for_dropdown.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data.length > 0) {
                    populateCategoryDropdowns(response.data);
                } else {
                    console.warn('No categories available');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading categories:', error);
                showAlert('error', 'Failed to load categories. Please refresh the page.');
            }
        });
    }

    /**
     * Populate category dropdown menus
     */
    function populateCategoryDropdowns(categories) {
        const addDropdown = $('#addBrandCategory');
        const editDropdown = $('#editBrandCategory');

        // Clear existing options (except the first "Select" option)
        addDropdown.find('option:not(:first)').remove();
        editDropdown.find('option:not(:first)').remove();

        // Add category options
        categories.forEach(function (category) {
            const option = `<option value="${category.cat_id}">${escapeHtml(category.cat_name)}</option>`;
            addDropdown.append(option);
            editDropdown.append(option);
        });

        // Update stats
        $('#totalCategories').text(categories.length);
    }

    /**
     * Load all brands from server
     */
    function loadBrands() {
        showLoading(true);

        $.ajax({
            url: '../actions/fetch_brand_action.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                showLoading(false);

                if (response.status === 'success') {
                    populateTable(response.data);
                    updateStats(response.data);
                    // Update "Added Today" stat
                    if (response.added_today !== undefined) {
                        $('#todayAdded').text(response.added_today);
                    }
                } else {
                    showAlert('error', 'Failed to load brands: ' + response.message);
                    populateTable([]);
                }
            },
            error: function (xhr, status, error) {
                showLoading(false);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error loading brands. Please refresh the page.');
                populateTable([]);
            }
        });
    }

    /**
     * Populate the brands table
     */
    function populateTable(brands) {
        // Destroy existing DataTable if it exists
        if (brandsTable) {
            brandsTable.destroy();
        }

        const tableBody = $('#brandsTable tbody');
        tableBody.empty();

        if (brands.length === 0) {
            tableBody.append(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No brands found. Add your first brand to get started.
                    </td>
                </tr>
            `);
            $('#tableContainer').show();
            return;
        }

        brands.forEach(function (brand) {
            const row = `
                <tr>
                    <td>${brand.brand_id}</td>
                    <td>
                        <strong>${escapeHtml(brand.brand_name)}</strong>
                    </td>
                    <td>
                        <span class="badge bg-info category-badge">
                            <i class="fas fa-folder me-1"></i>${escapeHtml(brand.cat_name || 'No Category')}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${brand.product_count > 0 ? 'primary' : 'secondary'}">
                            ${brand.product_count} products
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary edit-btn" 
                                    data-id="${brand.brand_id}" 
                                    data-name="${escapeHtml(brand.brand_name)}"
                                    data-cat="${brand.brand_cat}"
                                    data-catname="${escapeHtml(brand.cat_name || '')}"
                                    title="Edit Brand">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger delete-btn" 
                                    data-id="${brand.brand_id}" 
                                    data-name="${escapeHtml(brand.brand_name)}"
                                    data-catname="${escapeHtml(brand.cat_name || '')}"
                                    data-count="${brand.product_count}"
                                    title="Delete Brand">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });

        // Initialize DataTable
        brandsTable = $('#brandsTable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "Search brands:",
                lengthMenu: "Show _MENU_ brands per page",
                info: "Showing _START_ to _END_ of _TOTAL_ brands",
                emptyTable: "No brands available"
            },
            columnDefs: [
                { orderable: false, targets: [4] } // Disable sorting for Actions column
            ],
            order: [[2, 'asc'], [1, 'asc']] // Sort by category, then brand name
        });

        $('#tableContainer').show();
    }

    // /**
    //  * Update statistics cards
    //  */
    // function updateStats(brands) {
    //     const totalBrands = brands.length;
    //     const totalProducts = brands.reduce((sum, brand) => sum + parseInt(brand.product_count), 0);

    //     $('#totalBrands').text(totalBrands);
    //     $('#totalProducts').text(totalProducts);
    //     // Note: "Added Today" would require additional backend logic to track creation dates
    //     $('#todayAdded').text('0');
    // }

    /**
     * Update statistics cards
     */
    function updateStats(brands) {
        const totalBrands = brands.length;
        const totalProducts = brands.reduce((sum, brand) => sum + parseInt(brand.product_count), 0);

        $('#totalBrands').text(totalBrands);
        $('#totalProducts').text(totalProducts);
        // todayAdded will be updated when we receive the fetch response
    }

    /**
     * Show/hide loading spinner
     */
    function showLoading(show) {
        if (show) {
            $('#loadingSpinner').show();
            $('#tableContainer').hide();
        } else {
            $('#loadingSpinner').hide();
        }
    }

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
            type === 'error' ? 'alert-danger' :
                type === 'warning' ? 'alert-warning' : 'alert-info';

        const icon = type === 'success' ? 'fa-check-circle' :
            type === 'error' ? 'fa-exclamation-triangle' :
                type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('#alertContainer').html(alertHtml);

        // Auto-dismiss after 5 seconds
        setTimeout(function () {
            $('.alert').alert('close');
        }, 5000);

        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 500);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
    }

    /**
     * Validate brand information
     */
    function validateBrand(name, category) {
        const errors = [];

        if (!name || name.trim().length === 0) {
            errors.push('Brand name is required');
        } else {
            name = name.trim();

            if (name.length < 2) {
                errors.push('Brand name must be at least 2 characters long');
            }

            if (name.length > 100) {
                errors.push('Brand name must be less than 100 characters');
            }

            if (!/^[a-zA-Z0-9\s\-_]+$/.test(name)) {
                errors.push('Brand name can only contain letters, numbers, spaces, hyphens, and underscores');
            }
        }

        if (!category || category === '') {
            errors.push('Category is required');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    // Add Brand Form Submission
    $('#addBrandForm').submit(function (e) {
        e.preventDefault();

        const brandName = $('#addBrandName').val().trim();
        const brandCategory = $('#addBrandCategory').val();

        // Client-side validation
        const validation = validateBrand(brandName, brandCategory);
        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');

        $.ajax({
            url: '../actions/add_brand_action.php',
            type: 'POST',
            data: {
                brand_name: brandName,
                brand_cat: brandCategory
            },
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#addBrandModal').modal('hide');
                    $('#addBrandForm')[0].reset();
                    showAlert('success', response.message);
                    loadBrands(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error adding brand. Please try again.');
            }
        });
    });

    // Edit Brand Button Click
    $(document).on('click', '.edit-btn', function () {
        const brandId = $(this).data('id');
        const brandName = $(this).data('name');
        const brandCat = $(this).data('cat');

        $('#editBrandId').val(brandId);
        $('#editBrandName').val(brandName);
        $('#editBrandCategory').val(brandCat);
        $('#editBrandModal').modal('show');
    });

    // Edit Brand Form Submission
    $('#editBrandForm').submit(function (e) {
        e.preventDefault();

        const brandId = $('#editBrandId').val();
        const brandName = $('#editBrandName').val().trim();
        const brandCategory = $('#editBrandCategory').val();

        // Client-side validation
        const validation = validateBrand(brandName, brandCategory);
        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

        $.ajax({
            url: '../actions/update_brand_action.php',
            type: 'POST',
            data: {
                brand_id: brandId,
                brand_name: brandName,
                brand_cat: brandCategory
            },
            dataType: 'json',
            success: function (response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#editBrandModal').modal('hide');
                    showAlert('success', response.message);
                    loadBrands(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error updating brand. Please try again.');
            }
        });
    });

    // Delete Brand Button Click
    $(document).on('click', '.delete-btn', function () {
        const brandId = $(this).data('id');
        const brandName = $(this).data('name');
        const brandCatName = $(this).data('catname');
        const productCount = $(this).data('count');

        $('#deleteBrandId').val(brandId);
        $('#deleteBrandName').text(brandName);
        $('#deleteBrandCategory').text(brandCatName);

        // Disable delete button if brand has products
        if (productCount > 0) {
            $('#confirmDeleteBtn').prop('disabled', true)
                .html('<i class="fas fa-ban me-2"></i>Cannot Delete')
                .removeClass('btn-danger')
                .addClass('btn-secondary');
        } else {
            $('#confirmDeleteBtn').prop('disabled', false)
                .html('<i class="fas fa-trash me-2"></i>Delete Brand')
                .removeClass('btn-secondary')
                .addClass('btn-danger');
        }

        $('#deleteBrandModal').modal('show');
    });

    // Confirm Delete Brand
    $('#confirmDeleteBtn').click(function () {
        const brandId = $('#deleteBrandId').val();

        if ($(this).prop('disabled')) {
            return;
        }

        // Show loading state
        const originalText = $(this).html();
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');

        $.ajax({
            url: '../actions/delete_brand_action.php',
            type: 'POST',
            data: { brand_id: brandId },
            dataType: 'json',
            success: function (response) {
                $('#confirmDeleteBtn').prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#deleteBrandModal').modal('hide');
                    showAlert('success', response.message);
                    loadBrands(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                $('#confirmDeleteBtn').prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error deleting brand. Please try again.');
            }
        });
    });

    // Clear form data when modals are closed
    $('#addBrandModal').on('hidden.bs.modal', function () {
        $('#addBrandForm')[0].reset();
    });

    $('#editBrandModal').on('hidden.bs.modal', function () {
        $('#editBrandForm')[0].reset();
    });

    // Auto-focus on brand name input when modals open
    $('#addBrandModal').on('shown.bs.modal', function () {
        $('#addBrandCategory').focus();
    });

    $('#editBrandModal').on('shown.bs.modal', function () {
        $('#editBrandCategory').focus();
    });
});