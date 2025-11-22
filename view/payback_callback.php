<?php
/**
 * Payment Callback Handler
 * Receives redirect from Paystack and verifies payment
 */

session_start();
require_once '../settings/core.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login/login.php');
    exit();
}

// Get payment reference from URL
$reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : null;
$customer_name = get_user_name();

// If no reference, redirect to cart
if (!$reference) {
    header('Location: cart.php?error=no_reference');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifying Payment - GhanaTunes</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2E86AB;
            --primary-hover: #1B5E7A;
            --accent-color: #F18F01;
        }
        
        body {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            background-attachment: fixed;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .verification-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            border: 8px solid #e3f2fd;
            border-top: 8px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 30px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .subtitle {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .reference-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            word-break: break-all;
        }
        
        .reference-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .reference-value {
            color: var(--primary-color);
            font-family: monospace;
            font-size: 14px;
        }
        
        .status-message {
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }
        
        .status-message.error {
            background: #fee;
            border: 2px solid #fcc;
            color: #c33;
        }
        
        .status-message.info {
            background: #e3f2fd;
            border: 2px solid #bbdefb;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <div class="spinner"></div>
        
        <h1>Verifying Payment</h1>
        <p class="subtitle">Please wait while we confirm your payment with Paystack...</p>
        
        <div class="reference-box">
            <div class="reference-label">Payment Reference:</div>
            <div class="reference-value"><?php echo $reference; ?></div>
        </div>
        
        <div class="status-message" id="statusMessage"></div>
        
        <p style="color: #6c757d; font-size: 14px; margin-top: 30px;">
            <i class="fas fa-info-circle me-1"></i>
            This may take a few seconds. Please do not close this window.
        </p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const reference = '<?php echo $reference; ?>';
            
            // Automatically verify payment on page load
            verifyPayment(reference);
            
            function verifyPayment(ref) {
                console.log('Verifying payment for reference:', ref);
                
                $.ajax({
                    url: '../actions/paystack_verify_payment.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        reference: ref
                    }),
                    dataType: 'json',
                    success: function(response) {
                        console.log('Verification response:', response);
                        
                        if (response.status === 'success' && response.verified === true) {
                            // Payment verified successfully
                            const params = new URLSearchParams({
                                invoice: response.invoice_no || 'N/A',
                                reference: response.payment_reference || ref,
                                amount: response.total_amount || '0.00',
                                date: response.order_date || '<?php echo date("F j, Y"); ?>',
                                items: response.items_count || '0'
                            });
                            
                            // Redirect to success page with order details
                            window.location.href = 'payment_success.php?' + params.toString();
                        } else {
                            // Verification failed
                            showError(response.message || 'Payment verification failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Verification error:', error);
                        console.error('Response:', xhr.responseText);
                        showError('Failed to verify payment. Please contact support.');
                    }
                });
            }
            
            function showError(message) {
                const statusMsg = $('#statusMessage');
                statusMsg.removeClass('info').addClass('error');
                statusMsg.html(`
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Verification Failed</strong><br>
                    <span style="font-size: 14px;">${message}</span>
                `);
                statusMsg.show();
                
                // Hide spinner
                $('.spinner').hide();
                
                // Redirect to cart after 5 seconds
                setTimeout(function() {
                    window.location.href = 'cart.php?error=verification_failed';
                }, 5000);
            }
        });
    </script>
</body>
</html>