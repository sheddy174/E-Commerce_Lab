/**
 * Checkout and Paystack Payment JavaScript
 * Handles real payment via Paystack gateway
 */

$(document).ready(function() {
    
    /**
     * Proceed to Payment Button Click
     * Shows payment modal with Paystack integration
     */
    $('#simulatePaymentBtn').click(function() {
        const totalAmount = $('#orderTotal').text();
        
        // Show Paystack payment modal
        Swal.fire({
            title: '<strong>Secure Payment</strong>',
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
                            <span> Paystack (Card, Mobile Money)</span>
                        </div>
                    </div>
                    
                    <div style="background: linear-gradient(135deg, #2E86AB 0%, #1B5E7A 100%); color: white; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                        <div style="text-align: center;">
                            <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                            <div style="font-size: 18px; letter-spacing: 1px; margin-bottom: 0.5rem;">🔒 Powered by Paystack</div>
                            <div style="font-size: 12px; opacity: 0.9;">Your payment is 100% secure and encrypted</div>
                        </div>
                    </div>
                    
                    <p style="text-align: center; margin-top: 1.5rem; color: #6c757d;">
                        Click "Pay Now" to proceed to secure payment gateway
                    </p>
                </div>
            `,
            icon: null,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-lock me-2"></i>Pay Now',
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
                initializePaystackPayment();
            }
        });
    });

    /**
     * Initialize Paystack Payment
     */
    function initializePaystackPayment() {
        // Prompt user for email (clean approach - always works)
        Swal.fire({
            title: 'Email for Receipt',
            input: 'email',
            inputLabel: 'Enter your email address to receive payment confirmation',
            inputPlaceholder: 'your.email@example.com',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            confirmButtonColor: '#2E86AB',
            cancelButtonColor: '#6c757d',
            inputValidator: (value) => {
                if (!value) {
                    return 'Email is required'
                }
                if (!validateEmail(value)) {
                    return 'Please enter a valid email address'
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                proceedToPaystack(result.value);
            }
        });
    }
    
    /**
     * Proceed to Paystack after email confirmation
     */
    function proceedToPaystack(customerEmail) {
        // Show loading
        Swal.fire({
            title: 'Initializing Payment...',
            html: `
                <div style="padding: 2rem;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p style="margin-top: 1rem; color: #6c757d;">
                        Connecting to Paystack secure gateway...
                    </p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        
        // Initialize transaction with backend
        $.ajax({
            url: '../actions/paystack_init_transaction.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                email: customerEmail
            }),
            dataType: 'json',
            success: function(response) {
                console.log('Paystack init response:', response);
                
                if (response.status === 'success') {
                    // Redirect to Paystack payment page
                    Swal.fire({
                        icon: 'success',
                        title: 'Redirecting to Payment...',
                        text: 'Taking you to Paystack secure gateway',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect to Paystack
                        window.location.href = response.authorization_url;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Initialization Failed',
                        text: response.message || 'Failed to initialize payment',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Init error:', error);
                console.error('Response:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Error',
                    text: 'Failed to connect to payment gateway. Please try again.',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }
    
    /**
     * Validate email address
     */
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

});