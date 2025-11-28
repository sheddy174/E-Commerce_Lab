/**
 * Order Management JavaScript
 * Handles order status updates and details viewing
 */

$(document).ready(function() {
    
    // Initialize DataTable
    $('#ordersTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']], // Sort by Order ID descending
        responsive: true,
        language: {
            search: "Search orders:",
            lengthMenu: "Show _MENU_ orders per page"
        }
    });
    
    // Update Status Button Click
    $(document).on('click', '.update-status-btn', function() {
        const orderId = $(this).data('order-id');
        const currentStatus = $(this).data('current-status');
        
        $('#update_order_id').val(orderId);
        $('#update_status').val(currentStatus);
        
        // Show tracking field only for shipped/delivered status
        toggleTrackingField(currentStatus);
        
        $('#updateStatusModal').modal('show');
    });
    
    // Show/hide tracking number field based on status
    $('#update_status').change(function() {
        toggleTrackingField($(this).val());
    });
    
    function toggleTrackingField(status) {
        if (status === 'shipped' || status === 'delivered') {
            $('#trackingNumberField').slideDown();
        } else {
            $('#trackingNumberField').slideUp();
        }
    }
    
    // Update Status Form Submission
    $('#updateStatusForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        $.ajax({
            url: '../actions/update_order_status_action.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    $('#updateStatusModal').modal('hide');
                    
                    // Reload page after short delay
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                console.error('Update error:', error);
                showAlert('error', 'Error updating order status. Please try again.');
            }
        });
    });
    
    // View Order Details Button Click
    $(document).on('click', '.view-order-btn', function() {
        const orderId = $(this).data('order-id');
        
        $('#orderDetailsModal').modal('show');
        $('#orderDetailsContent').html(`
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <p class="mt-3">Loading order details...</p>
            </div>
        `);
        
        // Fetch order details
        $.ajax({
            url: '../actions/get_order_details_action.php',
            type: 'GET',
            data: { order_id: orderId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    displayOrderDetails(response.data);
                } else {
                    $('#orderDetailsContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.message}
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Fetch error:', error);
                $('#orderDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading order details. Please try again.
                    </div>
                `);
            }
        });
    });
    
    // Display order details in modal
    function displayOrderDetails(order) {
        const statusClass = 'status-' + order.order_status;
        const orderDate = new Date(order.order_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Order Information</h6>
                    <p><strong>Order ID:</strong> #${order.order_id}</p>
                    <p><strong>Date:</strong> ${orderDate}</p>
                    <p><strong>Amount:</strong> <span class="text-success fw-bold">GHS ${parseFloat(order.order_amount).toFixed(2)}</span></p>
                    <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${order.order_status.charAt(0).toUpperCase() + order.order_status.slice(1)}</span></p>
                </div>
                
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Customer Information</h6>
                    <p><strong>Name:</strong> ${escapeHtml(order.customer_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(order.customer_email)}</p>
                    <p><strong>Phone:</strong> ${escapeHtml(order.customer_contact)}</p>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">Shipping Address</h6>
                    <p>${escapeHtml(order.invoice_no)}</p>
                </div>
            </div>
        `;
        
        // Add tracking information if available
        if (order.tracking_number) {
            html += `
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Tracking Information</h6>
                        <p><strong>Tracking Number:</strong> ${escapeHtml(order.tracking_number)}</p>
                    </div>
                </div>
            `;
        }
        
        // Add dates if available
        if (order.shipped_date || order.delivered_date) {
            html += `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Delivery Timeline</h6>
            `;
            
            if (order.shipped_date) {
                const shippedDate = new Date(order.shipped_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                html += `<p><strong>Shipped:</strong> ${shippedDate}</p>`;
            }
            
            if (order.delivered_date) {
                const deliveredDate = new Date(order.delivered_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                html += `<p><strong>Delivered:</strong> ${deliveredDate}</p>`;
            }
            
            html += `
                    </div>
                </div>
            `;
        }
        
        // Add delivery notes if available
        if (order.delivery_notes) {
            html += `
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3">Delivery Notes</h6>
                        <p>${escapeHtml(order.delivery_notes)}</p>
                    </div>
                </div>
            `;
        }
        
        $('#orderDetailsContent').html(html);
    }
    
    // Show alert message
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        $('html, body').animate({ scrollTop: 0 }, 300);
        
        setTimeout(() => $('.alert').fadeOut(), 5000);
    }
    
    // Escape HTML to prevent XSS
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
});