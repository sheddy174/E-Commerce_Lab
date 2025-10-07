/**
 * Category Management JavaScript
 * Handles CRUD operations for categories via AJAX
 */

$(document).ready(function() {
    // Initialize DataTable
    let categoriesTable;
    
    // Load categories on page load
    loadCategories();
    
    /**
     * Load all categories from server
     */
    function loadCategories() {
        showLoading(true);
        
        $.ajax({
            url: '../actions/fetch_category_action.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                showLoading(false);
                
                if (response.status === 'success') {
                    populateTable(response.data);
                    updateStats(response.data);
                } else {
                    showAlert('error', 'Failed to load categories: ' + response.message);
                    populateTable([]);
                }
            },
            error: function(xhr, status, error) {
                showLoading(false);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error loading categories. Please refresh the page.');
                populateTable([]);
            }
        });
    }
    
    /**
     * Populate the categories table
     */
    function populateTable(categories) {
        // Destroy existing DataTable if it exists
        if (categoriesTable) {
            categoriesTable.destroy();
        }
        
        const tableBody = $('#categoriesTable tbody');
        tableBody.empty();
        
        if (categories.length === 0) {
            tableBody.append(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No categories found. Add your first category to get started.
                    </td>
                </tr>
            `);
            $('#tableContainer').show();
            return;
        }
        
        categories.forEach(function(category) {
            const row = `
                <tr>
                    <td>${category.cat_id}</td>
                    <td>
                        <strong>${escapeHtml(category.cat_name)}</strong>
                    </td>
                    <td>
                        <span class="badge bg-${category.product_count > 0 ? 'primary' : 'secondary'}">
                            ${category.product_count} products
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary edit-btn" 
                                    data-id="${category.cat_id}" 
                                    data-name="${escapeHtml(category.cat_name)}"
                                    title="Edit Category">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger delete-btn" 
                                    data-id="${category.cat_id}" 
                                    data-name="${escapeHtml(category.cat_name)}"
                                    data-count="${category.product_count}"
                                    title="Delete Category">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            tableBody.append(row);
        });
        
        // Initialize DataTable
        categoriesTable = $('#categoriesTable').DataTable({
            pageLength: 10,
            responsive: true,
            language: {
                search: "Search categories:",
                lengthMenu: "Show _MENU_ categories per page",
                info: "Showing _START_ to _END_ of _TOTAL_ categories",
                emptyTable: "No categories available"
            },
            columnDefs: [
                { orderable: false, targets: [3] } // Disable sorting for Actions column
            ]
        });
        
        $('#tableContainer').show();
    }
    
    /**
     * Update statistics cards
     */
    function updateStats(categories) {
        const totalCategories = categories.length;
        const totalProducts = categories.reduce((sum, cat) => sum + parseInt(cat.product_count), 0);
        
        $('#totalCategories').text(totalCategories);
        $('#totalProducts').text(totalProducts);
        // Note: "Added Today" would require additional backend logic to track creation dates
        $('#todayAdded').text('0');
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
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 500);
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Validate category name
     */
    function validateCategoryName(name) {
        const errors = [];
        
        if (!name || name.trim().length === 0) {
            errors.push('Category name is required');
        } else {
            name = name.trim();
            
            if (name.length < 2) {
                errors.push('Category name must be at least 2 characters long');
            }
            
            if (name.length > 100) {
                errors.push('Category name must be less than 100 characters');
            }
            
            if (!/^[a-zA-Z0-9\s\-_]+$/.test(name)) {
                errors.push('Category name can only contain letters, numbers, spaces, hyphens, and underscores');
            }
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    // Add Category Form Submission
    $('#addCategoryForm').submit(function(e) {
        e.preventDefault();
        
        const categoryName = $('#addCategoryName').val().trim();
        
        // Client-side validation
        const validation = validateCategoryName(categoryName);
        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');
        
        $.ajax({
            url: '../actions/add_category_action.php',
            type: 'POST',
            data: { cat_name: categoryName },
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    $('#addCategoryModal').modal('hide');
                    $('#addCategoryForm')[0].reset();
                    showAlert('success', response.message);
                    loadCategories(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error adding category. Please try again.');
            }
        });
    });
    
    // Edit Category Button Click
    $(document).on('click', '.edit-btn', function() {
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        
        $('#editCategoryId').val(categoryId);
        $('#editCategoryName').val(categoryName);
        $('#editCategoryModal').modal('show');
    });
    
    // Edit Category Form Submission
    $('#editCategoryForm').submit(function(e) {
        e.preventDefault();
        
        const categoryId = $('#editCategoryId').val();
        const categoryName = $('#editCategoryName').val().trim();
        
        // Client-side validation
        const validation = validateCategoryName(categoryName);
        if (!validation.isValid) {
            showAlert('error', validation.errors.join('. '));
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        $.ajax({
            url: '../actions/update_category_action.php',
            type: 'POST',
            data: { 
                cat_id: categoryId,
                cat_name: categoryName 
            },
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    $('#editCategoryModal').modal('hide');
                    showAlert('success', response.message);
                    loadCategories(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error updating category. Please try again.');
            }
        });
    });
    
    // Delete Category Button Click
    $(document).on('click', '.delete-btn', function() {
        const categoryId = $(this).data('id');
        const categoryName = $(this).data('name');
        const productCount = $(this).data('count');
        
        $('#deleteCategoryId').val(categoryId);
        $('#deleteCategoryName').text(categoryName);
        
        // Disable delete button if category has products
        if (productCount > 0) {
            $('#confirmDeleteBtn').prop('disabled', true)
                                 .html('<i class="fas fa-ban me-2"></i>Cannot Delete')
                                 .removeClass('btn-danger')
                                 .addClass('btn-secondary');
        } else {
            $('#confirmDeleteBtn').prop('disabled', false)
                                 .html('<i class="fas fa-trash me-2"></i>Delete Category')
                                 .removeClass('btn-secondary')
                                 .addClass('btn-danger');
        }
        
        $('#deleteCategoryModal').modal('show');
    });
    
    // Confirm Delete Category
    $('#confirmDeleteBtn').click(function() {
        const categoryId = $('#deleteCategoryId').val();
        
        if ($(this).prop('disabled')) {
            return;
        }
        
        // Show loading state
        const originalText = $(this).html();
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');
        
        $.ajax({
            url: '../actions/delete_category_action.php',
            type: 'POST',
            data: { cat_id: categoryId },
            dataType: 'json',
            success: function(response) {
                $('#confirmDeleteBtn').prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    $('#deleteCategoryModal').modal('hide');
                    showAlert('success', response.message);
                    loadCategories(); // Reload the table
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#confirmDeleteBtn').prop('disabled', false).html(originalText);
                console.error('AJAX Error:', error);
                showAlert('error', 'Error deleting category. Please try again.');
            }
        });
    });
    
    // Clear form data when modals are closed
    $('#addCategoryModal').on('hidden.bs.modal', function() {
        $('#addCategoryForm')[0].reset();
    });
    
    $('#editCategoryModal').on('hidden.bs.modal', function() {
        $('#editCategoryForm')[0].reset();
    });
    
    // Auto-focus on category name input when modals open
    $('#addCategoryModal').on('shown.bs.modal', function() {
        $('#addCategoryName').focus();
    });
    
    $('#editCategoryModal').on('shown.bs.modal', function() {
        $('#editCategoryName').focus().select();
    });
});