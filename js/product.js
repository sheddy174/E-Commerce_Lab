/**
 * Product Management JavaScript
 * Handles CRUD operations for products via AJAX with image uploads
 * Includes Source column (Admin vs Artisan) and Source filter
 */

$(document).ready(function () {
    // Initialize DataTable reference
    let productsTable;

    // Make sure filter starts on "all"
    $('#sourceFilter').val('all');

    // Load initial data
    loadCategories();
    loadBrands();
    loadProducts();

    // =========================================================
    // CATEGORY & BRAND LOADING
    // =========================================================

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
                    $('#totalCategories').text(response.data.length);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading categories:', error);
            }
        });
    }

    /**
     * Populate category dropdown menus
     */
    function populateCategoryDropdowns(categories) {
        const addDropdown = $('#addProductCategory');
        const editDropdown = $('#editProductCategory');

        addDropdown.find('option:not(:first)').remove();
        editDropdown.find('option:not(:first)').remove();

        categories.forEach(function (category) {
            const option = `<option value="${category.cat_id}">${escapeHtml(category.cat_name)}</option>`;
            addDropdown.append(option);
            editDropdown.append(option);
        });
    }

    /**
     * Load brands for dropdown menus
     */
    function loadBrands() {
        $.ajax({
            url: '../actions/fetch_brand_action.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data.length > 0) {
                    populateBrandDropdowns(response.data);
                    $('#totalBrands').text(response.data.length);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading brands:', error);
            }
        });
    }

    /**
     * Populate brand dropdown menus
     */
    function populateBrandDropdowns(brands) {
        const addDropdown = $('#addProductBrand');
        const editDropdown = $('#editProductBrand');

        addDropdown.find('option:not(:first)').remove();
        editDropdown.find('option:not(:first)').remove();

        brands.forEach(function (brand) {
            const option = `<option value="${brand.brand_id}">${escapeHtml(brand.brand_name)} (${escapeHtml(brand.cat_name)})</option>`;
            addDropdown.append(option);
            editDropdown.append(option);
        });
    }

    // =========================================================
    // LOAD & RENDER PRODUCTS
    // =========================================================

    /**
     * Load all products from server
     */
    function loadProducts() {
        showLoading(true);

        $.ajax({
            url: '../actions/fetch_product_action.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                showLoading(false);

                if (response.status === 'success') {
                    populateTable(response.data);
                    updateStats(response.data);

                    if (response.added_today !== undefined) {
                        $('#todayAdded').text(response.added_today);
                    }
                } else {
                    showAlert('error', 'Failed to load products: ' + response.message);
                    populateTable([]);
                }
            },
            error: function (xhr, status, error) {
                showLoading(false);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error loading products. Please refresh the page.');
                populateTable([]);
            }
        });
    }

    /**
     * Populate the products table
     * Includes Source column and data-source attribute for filtering
     */
    function populateTable(products) {
        if (productsTable) {
            productsTable.destroy();
        }

        const tableBody = $('#productsTable tbody');
        tableBody.empty();

        if (products.length === 0) {
            tableBody.append(`
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No products found. Add your first product to get started.
                    </td>
                </tr>
            `);
            $('#tableContainer').show();
            return;
        }

        products.forEach(function (product) {
            // Image path construction for shared server
            const imageUrl = product.product_image
                ? '../../' + product.product_image
                : 'https://placehold.co/200x200/E3F2FD/2E86AB?text=No+Image';

            const price = parseFloat(product.product_price).toFixed(2);

            // Build source badge
            let sourceBadge = '';
            if (product.artisan_id) {
                const shopName = escapeHtml(product.shop_name || 'Artisan');
                const artisanName = escapeHtml(product.artisan_name || '');
                sourceBadge = `
                    <span class="badge bg-warning text-dark" title="${artisanName}">
                        <i class="fas fa-hammer"></i> ${shopName}
                    </span>
                `;
            } else {
                sourceBadge = `
                    <span class="badge bg-primary">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                `;
            }

            const row = `
                <tr data-source="${product.artisan_id ? 'artisan' : 'admin'}">
                    <td>${product.product_id}</td>
                    <td>
                        <img src="${imageUrl}" class="product-image-preview" alt="${escapeHtml(product.product_title)}" 
                             onerror="this.src='https://placehold.co/200x200/E3F2FD/2E86AB?text=No+Image'">
                    </td>
                    <td>
                        <strong>${escapeHtml(product.product_title)}</strong><br>
                        <small class="text-muted">
                            ${escapeHtml(product.product_desc || '').substring(0, 50)}
                            ${product.product_desc && product.product_desc.length > 50 ? '...' : ''}
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            ${escapeHtml(product.cat_name || 'No Category')}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-success">
                            ${escapeHtml(product.brand_name || 'No Brand')}
                        </span>
                    </td>
                    <td><strong>GHS ${price}</strong></td>
                    <td>${sourceBadge}</td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary edit-btn" 
                                    data-id="${product.product_id}"
                                    title="Edit Product">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger delete-btn" 
                                    data-id="${product.product_id}"
                                    data-title="${escapeHtml(product.product_title)}"
                                    title="Delete Product">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });

        productsTable = $('#productsTable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products"
            },
            columnDefs: [
                { orderable: false, targets: [1, 7] } // Image and Actions columns
            ],
            order: [[0, 'desc']]
        });

        $('#tableContainer').show();

        // After table is (re)built, immediately apply current filter
        if ($('#sourceFilter').val()) {
            productsTable.draw();
        }
    }

    /**
     * Update statistics cards
     */
    function updateStats(products) {
        const totalProducts = products.length;
        const totalValue = products.reduce((sum, product) => sum + parseFloat(product.product_price), 0);

        $('#totalProducts').text(totalProducts);
        $('#totalValue').text('GHS ' + totalValue.toFixed(2));
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

    // =========================================================
    // UTILITIES
    // =========================================================

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success'
            : type === 'error' ? 'alert-danger'
            : 'alert-warning';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('#alertContainer').html(alertHtml);
        setTimeout(() => $('.alert').alert('close'), 5000);
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
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Validate product data
     */
    function validateProduct(data) {
        const errors = [];

        if (!data.product_title || data.product_title.trim().length < 3) {
            errors.push('Product title must be at least 3 characters');
        }

        if (!data.product_cat || data.product_cat <= 0) {
            errors.push('Please select a category');
        }

        if (!data.product_brand || data.product_brand <= 0) {
            errors.push('Please select a brand');
        }

        if (!data.product_price || data.product_price < 0) {
            errors.push('Please enter a valid price');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Validate image file before upload
     */
    function validateImageFile(file) {
        const errors = [];

        if (!file) {
            return { isValid: true, errors: [] }; // No file is okay
        }

        // Check file size (5MB max)
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (file.size > maxSize) {
            errors.push('Image file is too large. Maximum size is 5MB');
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type.toLowerCase())) {
            errors.push('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    // =========================================================
    // IMAGE PREVIEW HANDLERS
    // =========================================================

    $('#addProductImage').change(function () {
        previewImage(this, '#addImagePreview', '#addFileName');
    });

    $('#editProductImage').change(function () {
        previewImage(this, '#editImagePreview', '#editFileName');
    });

    function previewImage(input, previewSelector, fileNameSelector) {
        const file = input.files[0];

        if (file) {
            $(fileNameSelector).text(file.name);

            const validation = validateImageFile(file);
            if (!validation.isValid) {
                showAlert('error', validation.errors.join('. '));
                input.value = '';
                $(previewSelector).hide();
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                $(previewSelector).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            $(fileNameSelector).text('No file chosen');
            $(previewSelector).hide();
        }
    }

    // =========================================================
    // ADD PRODUCT
    // =========================================================

    $('#addProductForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        console.log('Submitting product with data:');
        for (let pair of formData.entries()) {
            if (pair[0] === 'product_image') {
                console.log(pair[0] + ': ' + (pair[1].name || 'No file'));
            } else {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }

        const validation = validateProduct({
            product_title: formData.get('product_title'),
            product_cat: formData.get('product_cat'),
            product_brand: formData.get('product_brand'),
            product_price: formData.get('product_price')
        });

        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }

        const imageFile = $('#addProductImage')[0].files[0];
        if (imageFile) {
            console.log('Image file selected:', imageFile.name, 'Size:', imageFile.size, 'Type:', imageFile.type);
        } else {
            console.log('No image file selected');
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');

        $.ajax({
            url: '../actions/add_product_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            success: function (response) {
                console.log('Server response:', response);
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();
                    $('#addImagePreview').hide();
                    $('#addFileName').text('No file chosen');
                    showAlert('success', response.message);

                    if (response.debug) {
                        console.warn('Image upload debug info:', response.debug);
                    }

                    loadProducts();
                } else {
                    console.error('Product add failed:', response.message);
                    showAlert('error', response.message);

                    if (response.debug) {
                        console.error('Debug info:', response.debug);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

                submitBtn.prop('disabled', false).html(originalText);

                let errorMessage = 'Error adding product. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    if (xhr.status === 413) {
                        errorMessage = 'File too large. Maximum size is 5MB.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Check your error logs for details.';
                    }
                }

                showAlert('error', errorMessage);
            }
        });
    });

    // =========================================================
    // EDIT PRODUCT
    // =========================================================

    $(document).on('click', '.edit-btn', function () {
        const productId = $(this).data('id');

        $.ajax({
            url: '../actions/fetch_product_action.php',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    const product = response.data.find(p => p.product_id == productId);
                    if (product) {
                        populateEditForm(product);
                        $('#editProductModal').modal('show');
                    }
                }
            }
        });
    });

    function populateEditForm(product) {
        $('#editProductId').val(product.product_id);
        $('#editProductTitle').val(product.product_title);
        $('#editProductCategory').val(product.product_cat);
        $('#editProductBrand').val(product.product_brand);
        $('#editProductPrice').val(parseFloat(product.product_price).toFixed(2));
        $('#editProductDesc').val(product.product_desc);
        $('#editProductKeywords').val(product.product_keywords);

        const imageUrl = product.product_image
            ? '../../' + product.product_image
            : 'https://placehold.co/200x200/E3F2FD/2E86AB?text=No+Image';

        $('#editCurrentImage').attr('src', imageUrl);
        $('#editImagePreview').hide();
        $('#editFileName').text('No file chosen');
    }

    $('#editProductForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        console.log('Updating product with data:');
        for (let pair of formData.entries()) {
            if (pair[0] === 'product_image') {
                console.log(pair[0] + ': ' + (pair[1].name || 'No file'));
            } else {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }

        const validation = validateProduct({
            product_title: formData.get('product_title'),
            product_cat: formData.get('product_cat'),
            product_brand: formData.get('product_brand'),
            product_price: formData.get('product_price')
        });

        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }

        const imageFile = $('#editProductImage')[0].files[0];
        if (imageFile) {
            console.log('New image file selected:', imageFile.name, 'Size:', imageFile.size, 'Type:', imageFile.type);
        } else {
            console.log('No new image file selected (keeping existing image)');
        }

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');

        $.ajax({
            url: '../actions/update_product_action.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            success: function (response) {
                console.log('Update response:', response);
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#editProductModal').modal('hide');
                    showAlert('success', response.message);
                    loadProducts();
                } else {
                    console.error('Product update failed:', response.message);
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

                submitBtn.prop('disabled', false).html(originalText);

                let errorMessage = 'Error updating product. Please try again.';
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    if (errorResponse.message) {
                        errorMessage = errorResponse.message;
                    }
                } catch (e) {
                    if (xhr.status === 413) {
                        errorMessage = 'File too large. Maximum size is 5MB.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Check your error logs for details.';
                    }
                }

                showAlert('error', errorMessage);
            }
        });
    });

    // =========================================================
    // DELETE PRODUCT
    // =========================================================

    $(document).on('click', '.delete-btn', function () {
        const productId = $(this).data('id');
        const productTitle = $(this).data('title');

        Swal.fire({
            title: 'Are you sure?',
            html: `You are about to delete "<strong>${productTitle}</strong>".<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteProduct(productId);
            }
        });
    });

    function deleteProduct(productId) {
        $.ajax({
            url: '../actions/delete_product_action.php',
            type: 'POST',
            data: { product_id: productId },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    loadProducts();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                showAlert('error', 'Error deleting product. Please try again.');
            }
        });
    }

    // =========================================================
    // SOURCE FILTER (All / Admin / Artisan)
    // =========================================================

    // Custom DataTables filter for source
    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            // Only apply to our products table
            if (settings.nTable.getAttribute('id') !== 'productsTable') {
                return true;
            }

            // Default to "all" if value is empty/undefined
            const filterValue = $('#sourceFilter').val() || 'all';

            if (filterValue === 'all') {
                return true;
            }

            const row = $('#productsTable tbody tr').eq(dataIndex);
            const rowSource = row.attr('data-source') || 'admin';

            return rowSource === filterValue;
        }
    );

    // When filter changes, redraw table
    $('#sourceFilter').change(function () {
        if (productsTable) {
            productsTable.draw();
        }
    });

    // =========================================================
    // MODAL CLEANUP
    // =========================================================

    $('#addProductModal').on('hidden.bs.modal', function () {
        $('#addProductForm')[0].reset();
        $('#addImagePreview').hide();
        $('#addFileName').text('No file chosen');
    });

    $('#editProductModal').on('hidden.bs.modal', function () {
        $('#editProductForm')[0].reset();
        $('#editImagePreview').hide();
        $('#editFileName').text('No file chosen');
    });
});
