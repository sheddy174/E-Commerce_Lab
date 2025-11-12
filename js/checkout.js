/**
 * Checkout and Payment Simulation JavaScript
 * Handles payment modal and checkout process via AJAX
 */

$(document).ready(function() {
    
    /**
     * Simulate Payment Button Click
     * Shows a modal simulating payment confirmation
     */
    $('#simulatePaymentBtn').click(function() {
        const totalAmount = $('#orderTotal').text();
        
        // Show payment simulation modal
        Swal.fire({
            title: '<strong>Simulate Payment</strong>',
            html: `
                <div style="text-align: left; padding: 1rem;">
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <h5 style="color: #2E86AB; margin-bottom: 1rem;">
                            <i class="fas fa-credit-card"></i> Payment Details
                        </h5>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">
                            <span><strong>Order Total:</strong></span>
                            <span style="color: #F18F01; font-weight: 700; font-size: 1.25rem;">${totalAmount}</span>
                        </div>
                        <div style="padding: 0.5rem 0;">
                            <span><strong>Payment Method:</strong></span>
                            <span> Mobile Money (Simulated)</span>
                        </div>
                    </div>
                    
                    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-info-circle" style="color: #856404;"></i>
                        <small style="color: #856404;"> This is a <strong>simulated payment</strong> for demonstration purposes. No actual payment will be processed.</small>
                    </div>
                    
                    <p style="text-align: center; margin-top: 1.5rem; color: #6c757d;">
                        Click "Confirm Payment" to complete your order
                    </p>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check-circle me-2"></i>Confirm Payment',
            cancelButtonText: '<i class="fas fa-times me-2"></i>Cancel',
            confirmButtonColor: '#F18F01',
            cancelButtonColor: '#6c757d',
            width: '600px',
            allowOutsideClick: false,
            customClass: {
                confirmButton: 'btn-lg',
                cancelButton: 'btn-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                processCheckout();
            }
        });
    });

    /**
     * Process Checkout
     * Sends request to backend to complete the order
     */
    function processCheckout() {
        // Show processing indicator
        Swal.fire({
            title: 'Processing Payment...',
            html: `
                <div style="padding: 2rem;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p style="margin-top: 1rem; color: #6c757d;">
                        Please wait while we process your order...
                    </p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        // Send AJAX request to process checkout
        $.ajax({
            url: '../actions/process_checkout_action.php',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Payment successful
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully!',
                        html: `
                            <div style="text-align: left; padding: 1rem;">
                                <div style="background: #d1e7dd; border: 2px solid #0f5132; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 1.5rem;">
                                    <h5 style="color: #0f5132; margin-bottom: 1rem;">
                                        <i class="fas fa-check-circle"></i> Payment Confirmed
                                    </h5>
                                    <div style="padding: 0.5rem 0;">
                                        <strong>Order Reference:</strong> #${response.invoice_no}
                                    </div>
                                    <div style="padding: 0.5rem 0;">
                                        <strong>Order Date:</strong> ${response.order_date}
                                    </div>
                                    <div style="padding: 0.5rem 0;">
                                        <strong>Total Paid:</strong> <span style="color: #F18F01; font-weight: 700;">GHS ${response.total_amount}</span>
                                    </div>
                                    <div style="padding: 0.5rem 0;">
                                        <strong>Items:</strong> ${response.items_count} item${response.items_count > 1 ? 's' : ''}
                                    </div>
                                </div>
                                
                                <div style="background: #cff4fc; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                                    <i class="fas fa-envelope" style="color: #055160;"></i>
                                    <small style="color: #055160;"> A confirmation email has been sent to your registered email address.</small>
                                </div>
                                
                                <p style="text-align: center; color: #6c757d; margin-top: 1rem;">
                                    Thank you for shopping with GhanaTunes!
                                </p>
                            </div>
                        `,
                        confirmButtonText: '<i class="fas fa-home me-2"></i>Continue Shopping',
                        confirmButtonColor: '#2E86AB',
                        allowOutsideClick: false,
                        width: '600px'
                    }).then(() => {
                        // Redirect to home or orders page
                        window.location.href = '../index.php';
                    });
                } else {
                    // Payment failed
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Failed',
                        html: `
                            <div style="padding: 1rem;">
                                <p style="color: #dc3545; font-weight: 600; margin-bottom: 1rem;">
                                    ${response.message}
                                </p>
                                <p style="color: #6c757d;">
                                    Your cart items have been preserved. Please try again.
                                </p>
                            </div>
                        `,
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Checkout error:', error);
                console.error('Response:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Checkout Failed',
                    html: `
                        <div style="padding: 1rem;">
                            <p style="color: #dc3545; font-weight: 600; margin-bottom: 1rem;">
                                An error occurred while processing your order.
                            </p>
                            <p style="color: #6c757d;">
                                Please try again or contact support if the problem persists.
                            </p>
                            <div style="background: #f8f9fa; padding: 0.75rem; border-radius: 0.5rem; margin-top: 1rem;">
                                <small style="color: #6c757d; font-family: monospace;">
                                    Error: ${error}
                                </small>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#6c757d'
                });
            }
        });
    }

});