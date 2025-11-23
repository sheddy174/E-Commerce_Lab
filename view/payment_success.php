<?php
/**
 * Payment Success Page
 * Final confirmation page after successful payment verification
 */

session_start();
require_once '../settings/core.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ../login/login.php');
    exit();
}

$customer_name = get_user_name();

// Get order details from URL parameters
$invoice_no = isset($_GET['invoice']) ? htmlspecialchars($_GET['invoice']) : 'N/A';
$reference = isset($_GET['reference']) ? htmlspecialchars($_GET['reference']) : 'N/A';
$amount = isset($_GET['amount']) ? htmlspecialchars($_GET['amount']) : '0.00';
$order_date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : date('F j, Y');
$items_count = isset($_GET['items']) ? htmlspecialchars($_GET['items']) : '0';

// If no invoice number, something went wrong
if ($invoice_no === 'N/A' || $reference === 'N/A') {
    header('Location: cart.php?error=invalid_order');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - GhanaTunes</title>
    
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
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            padding: 1rem 0;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
        
        .navbar-brand {
            color: white !important;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .navbar-brand i {
            color: var(--accent-color);
        }
        
        .container {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 80px;
            color: #10b981;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        h1 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .subtitle {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 12px;
            margin: 30px 0;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        
        .detail-value {
            color: #6c757d;
            word-break: break-all;
            text-align: right;
            max-width: 60%;
        }
        
        .success-badge {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 16px 40px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            box-shadow: 0 8px 25px rgba(46, 134, 171, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(46, 134, 171, 0.4);
            color: white;
        }
        
        .btn-secondary {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-secondary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .buttons-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .confirmation-message {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border: 2px solid #6ee7b7;
            padding: 20px;
            border-radius: 12px;
            color: #065f46;
            margin-bottom: 20px;
        }
        
        .confirmation-message i {
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid px-5">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-music"></i> GhanaTunes
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="success-card">
            <div class="success-icon">🎉</div>
            
            <div class="success-badge">
                <i class="fas fa-check-circle"></i> Payment Confirmed
            </div>
            
            <h1>Order Successful!</h1>
            <p class="subtitle">Thank you for your purchase, <?php echo htmlspecialchars($customer_name); ?>!</p>
            
            <div class="confirmation-message">
                <i class="fas fa-envelope"></i>
                <strong>Confirmation Email Sent</strong><br>
                <span style="font-size: 14px;">A receipt has been sent to your registered email address.</span>
            </div>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-receipt me-2"></i>Invoice Number
                    </span>
                    <span class="detail-value"><strong><?php echo $invoice_no; ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-hashtag me-2"></i>Payment Reference
                    </span>
                    <span class="detail-value"><?php echo $reference; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar me-2"></i>Order Date
                    </span>
                    <span class="detail-value"><?php echo $order_date; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-shopping-cart me-2"></i>Items Ordered
                    </span>
                    <span class="detail-value"><?php echo $items_count; ?> item<?php echo $items_count != 1 ? 's' : ''; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-money-bill-wave me-2"></i>Amount Paid
                    </span>
                    <span class="detail-value" style="color: var(--accent-color); font-weight: 700; font-size: 1.1rem;">
                        GHS <?php echo $amount; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-check-circle me-2"></i>Status
                    </span>
                    <span class="detail-value" style="color: #10b981; font-weight: 600;">
                        <i class="fas fa-check"></i> Paid
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-credit-card me-2"></i>Payment Method
                    </span>
                    <span class="detail-value">Paystack</span>
                </div>
            </div>
            
            <div class="alert alert-info" style="margin: 20px 0;">
                <i class="fas fa-info-circle me-2"></i>
                <strong>What's Next?</strong><br>
                <small>Your order is being processed. We'll notify you once it's ready for delivery.</small>
            </div>
            
            <div class="buttons-container">
                <a href="all_product.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confetti effect
        function createConfetti() {
            const colors = ['#2E86AB', '#F18F01', '#10b981', '#3b82f6'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.cssText = `
                        position: fixed;
                        width: 10px;
                        height: 10px;
                        background: ${colors[Math.floor(Math.random() * colors.length)]};
                        left: ${Math.random() * 100}%;
                        top: -10px;
                        opacity: 1;
                        transform: rotate(${Math.random() * 360}deg);
                        z-index: 10001;
                        pointer-events: none;
                        border-radius: 2px;
                    `;
                    
                    document.body.appendChild(confetti);
                    
                    const duration = 2000 + Math.random() * 1000;
                    const startTime = Date.now();
                    
                    function animateConfetti() {
                        const elapsed = Date.now() - startTime;
                        const progress = elapsed / duration;
                        
                        if (progress < 1) {
                            const top = progress * (window.innerHeight + 50);
                            const wobble = Math.sin(progress * 10) * 50;
                            
                            confetti.style.top = top + 'px';
                            confetti.style.left = `calc(${confetti.style.left} + ${wobble}px)`;
                            confetti.style.opacity = 1 - progress;
                            confetti.style.transform = `rotate(${progress * 720}deg)`;
                            
                            requestAnimationFrame(animateConfetti);
                        } else {
                            confetti.remove();
                        }
                    }
                    
                    animateConfetti();
                }, i * 30);
            }
        }
        
        // Trigger confetti on page load
        window.addEventListener('load', createConfetti);
    </script>
</body>
</html>