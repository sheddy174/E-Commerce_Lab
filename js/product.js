/**
 * Product Management JavaScript
 * Handles CRUD operations for products via AJAX with image uploads
 * Includes Source column & filter (Admin vs Artisan)
 */

$(document).ready(function () {
    let productsTable = null;   // DataTable instance

    // Initial load
    loadCategories();
    loadBrands();
    loadProducts();

    // ==========================================
    // CATEGORY / BRAND LOADERS
    // ==========================================

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

    // ==========================================
    // LOAD & RENDER PRODUCTS
    // ==========================================

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
     * Build table rows and initialise DataTable
     */
    function populateTable(products) {
        // If a DataTable already exists, fully destroy it and clear rows
        if (productsTable) {
            productsTable.clear().destroy();
            productsTable = null;
        }

        const tableBody = $('#productsTable tbody');
        tableBody.empty();

        // No products: show friendly message, DO NOT initialise DataTable
        if (!products || products.length === 0) {
            tableBody.append(`
                <tr class="no-data-row">
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No products found. Add your first product to get started.
                    </td>
                </tr>
            `);
            $('#totalProducts').text(0);
            $('#totalValue').text('GHS 0.00');
            $('#tableContainer').show();
            return;
        }

        // Build rows (MUST have exactly 8 <td> to match 8 <th>)
        products.forEach(function (product) {
            const imageUrl = product.product_image
                ? '../../' + product.product_image
                : 'https://placehold.co/200x200/E3F2FD/2E86AB?text=No+Image';

            const price = parseFloat(product.product_price).toFixed(2);

            // Source badge
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

            const rowHtml = `
                <tr data-source="${product.artisan_id ? 'artisan' : 'admin'}">
                    <td>${product.product_id}</td>
                    <td>
                        <img src="${imageUrl}" class="product-image-preview" alt="${escapeHtml(product.product_title)}"
                             onerror="this.src='https://placehold.co/200x200/E3F2FD/2E86AB?text=No+Image'">
                    </td>
                    <td>
                        <strong>${escapeHtml(product.product_title)}</strong><br>
                        <small class="text-muted">
                            ${escapeHtml(product.product_desc || '').substring(0, 50)}${product.product_desc && product.product_desc.length > 50 ? '...' : ''}
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
            tableBody.append(rowHtml);
        });

        // Debug: confirm each row has 8 cells
        $('#productsTable tbody tr').each(function (idx) {
            const count = $(this).children('td').length;
            console.log('Row', idx, 'cell count:', count);
        });

        // Now safely initialise DataTable
        productsTable = $('#productsTable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products"
            },
            columnDefs: [
                { orderable: false, targets: [1, 7] } // image + actions
            ],
            order: [[0, 'desc']],
            destroy: true // extra safety on re-init
        });

        $('#tableContainer').show();

        // Apply current source filter (in case the user changed it)
        applySourceFilter();
    }

    // ==========================================
    // STATS / UI HELPERS
    // ==========================================

    function updateStats(products) {
        const totalProducts = products.length;
        const totalValue = products.reduce((sum, product) => {
            const p = parseFloat(product.product_price);
            return sum + (isNaN(p) ? 0 : p);
        }, 0);

        $('#totalProducts').text(totalProducts);
        $('#totalValue').text('GHS ' + totalValue.toFixed(2));
    }

    function showLoading(show) {
        if (show) {
            $('#loadingSpinner').show();
            $('#tableContainer').hide();
        } else {
            $('#loadingSpinner').hide();
        }
    }

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

    // ==========================================
    // VALIDATION
    // ==========================================

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

    function validateImageFile(file) {
        const errors = [];

        if (!file) {
            return { isValid: true, errors: [] };
        }

        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            errors.push('Image file is too large. Maximum size is 5MB');
        }

        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type.toLowerCase())) {
            errors.push('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    // ==========================================
    // IMAGE PREVIEW
    // ==========================================

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

    // ==========================================
    // ADD PRODUCT
    // ==========================================

    $('#addProductForm').submit(function (e) {
        e.preventDefault();

        const formData = new FormData(this);

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
        const imageValidation = validateImageFile(imageFile);
        if (!imageValidation.isValid) {
            showAlert('error', imageValidation.errors.join('. '));
            return;
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
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#addProductModal').modal('hide');
                    $('#addProductForm')[0].reset();
                    $('#addImagePreview').hide();
                    $('#addFileName').text('No file chosen');
                    showAlert('success', response.message);
                    loadProducts();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

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

    // ==========================================
    // EDIT PRODUCT
    // ==========================================

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
        const imageValidation = validateImageFile(imageFile);
        if (!imageValidation.isValid) {
            showAlert('error', imageValidation.errors.join('. '));
            return;
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
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    $('#editProductModal').modal('hide');
                    showAlert('success', response.message);
                    loadProducts();
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function (xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });

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

    // ==========================================
    // DELETE PRODUCT
    // ==========================================

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

    // ==========================================
    // SOURCE FILTER (All / Admin / Artisan)
    // ==========================================

    // Custom DataTables search: filter by data-source attribute
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        // Only apply to our productsTable
        if (settings.nTable.getAttribute('id') !== 'productsTable') {
            return true;
        }

        const filterValue = $('#sourceFilter').val();
        if (!filterValue || filterValue === 'all') {
            return true;
        }

        const row = $(settings.nTable).find('tbody tr').eq(dataIndex);
        const rowSource = row.attr('data-source'); // 'admin' or 'artisan'

        return rowSource === filterValue;
    });

    function applySourceFilter() {
        if (productsTable) {
            productsTable.draw();
        }
    }

    $('#sourceFilter').change(function () {
        applySourceFilter();
    });

    // ==========================================
    // MODAL RESET
    // ==========================================

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
