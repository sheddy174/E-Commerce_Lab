/**
 * Cart Management JavaScript
 * Handles all cart interactions via AJAX
 */

$(document).ready(function() {
    
    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' :
                          type === 'info' ? 'alert-info' : 'alert-warning';
        
        const icon = type === 'success' ? 'fa-check-circle' :
                     type === 'error' ? 'fa-exclamation-triangle' :
                     type === 'info' ? 'fa-info-circle' : 'fa-exclamation-triangle';
        
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
        
        // Scroll to top to show alert
        $('html, body').animate({ scrollTop: 0 }, 300);
    }

    /**
     * Update cart totals in the UI
     */
    function updateCartTotals() {
        let subtotal = 0;
        
        $('.cart-item').each(function() {
            const priceText = $(this).find('.item-subtotal').text();
            const price = parseFloat(priceText.replace('GHS ', '').replace(',', ''));
            if (!isNaN(price)) {
                subtotal += price;
            }
        });
        
        const shipping = 0; // Free shipping
        const total = subtotal + shipping;
        
        $('#summarySubtotal').text('GHS ' + subtotal.toFixed(2));
        $('#summaryTotal').text('GHS ' + total.toFixed(2));
    }

    /**
     * Update quantity with AJAX
     */
    function updateQuantity(productId, newQty) {
        $.ajax({
            url: '../actions/update_quantity_action.php',
            type: 'POST',
            data: {
                product_id: productId,
                qty: newQty
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    if (newQty === 0) {
                        // Remove the cart item from UI
                        $(`.cart-item[data-product-id="${productId}"]`).fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if cart is empty
                            if ($('.cart-item').length === 0) {
                                location.reload(); // Reload to show empty cart message
                            } else {
                                updateCartTotals();
                            }
                        });
                    } else {
                        // Update the item subtotal
                        $(`.cart-item[data-product-id="${productId}"] .item-subtotal`)
                            .text('GHS ' + response.item_subtotal);
                        
                        // Update the quantity input
                        $(`.cart-item[data-product-id="${productId}"] .qty-input`)
                            .val(newQty);
                        
                        updateCartTotals();
                    }
                    
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Update quantity error:', error);
                showAlert('error', 'Failed to update quantity. Please try again.');
            }
        });
    }

    /**
     * Decrease quantity button
     */
    $(document).on('click', '.qty-decrease', function() {
        const productId = $(this).data('product-id');
        const input = $(`.qty-input[data-product-id="${productId}"]`);
        let currentQty = parseInt(input.val());
        
        if (currentQty > 1) {
            const newQty = currentQty - 1;
            updateQuantity(productId, newQty);
        }
    });

    /**
     * Increase quantity button
     */
    $(document).on('click', '.qty-increase', function() {
        const productId = $(this).data('product-id');
        const input = $(`.qty-input[data-product-id="${productId}"]`);
        let currentQty = parseInt(input.val());
        
        if (currentQty < 99) {
            const newQty = currentQty + 1;
            updateQuantity(productId, newQty);
        } else {
            showAlert('warning', 'Maximum quantity per product is 99');
        }
    });

    /**
     * Manual quantity input change
     */
    $(document).on('change', '.qty-input', function() {
        const productId = $(this).data('product-id');
        let newQty = parseInt($(this).val());
        
        // Validate quantity
        if (isNaN(newQty) || newQty < 1) {
            newQty = 1;
        } else if (newQty > 99) {
            newQty = 99;
            showAlert('warning', 'Maximum quantity per product is 99');
        }
        
        $(this).val(newQty);
        updateQuantity(productId, newQty);
    });

    /**
     * Remove item from cart
     */
    $(document).on('click', '.btn-remove', function() {
        const productId = $(this).data('product-id');
        const productTitle = $(`.cart-item[data-product-id="${productId}"] .product-title`).text();
        
        Swal.fire({
            title: 'Remove Item?',
            text: `Remove "${productTitle}" from your cart?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../actions/remove_from_cart_action.php',
                    type: 'POST',
                    data: { product_id: productId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $(`.cart-item[data-product-id="${productId}"]`).fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if cart is empty
                                if ($('.cart-item').length === 0) {
                                    location.reload();
                                } else {
                                    updateCartTotals();
                                }
                            });
                            
                            showAlert('success', response.message);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Remove item error:', error);
                        showAlert('error', 'Failed to remove item. Please try again.');
                    }
                });
            }
        });
    });

    /**
     * Empty cart button
     */
    $('#emptyCartBtn').click(function() {
        Swal.fire({
            title: 'Empty Cart?',
            text: 'This will remove all items from your cart. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, empty cart',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../actions/empty_cart_action.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Cart Emptied',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Empty cart error:', error);
                        showAlert('error', 'Failed to empty cart. Please try again.');
                    }
                });
            }
        });
    });

    /**
     * Add to cart functionality (for product pages)
     * Can be called from other pages
     */
    window.addToCart = function(productId, qty = 1) {
        $.ajax({
            url: '../actions/add_to_cart_action.php',
            type: 'POST',
            data: {
                product_id: productId,
                qty: qty
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Continue Shopping',
                        confirmButtonColor: '#2E86AB',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'cart.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Add to cart error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add item to cart. Please try again.'
                });
            }
        });
    };

});