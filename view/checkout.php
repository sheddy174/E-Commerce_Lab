<?php
/**
 * Checkout View
 * Order review and Paystack payment page
 */

session_start();
require_once '../settings/core.php';
require_once '../controllers/cart_controller.php';

// Require login
if (!is_logged_in()) {
    header("Location: ../login/login.php?redirect=checkout");
    exit();
}

$customer_id = get_user_id();
$customer_name = get_user_name();
$customer_email = get_user_email();

// Get cart items
$cart_items = get_user_cart_ctr($customer_id);

// Redirect to cart if empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['product_price'] * $item['qty'];
}

$shipping = 0; // Free shipping
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GhanaTunes</title>
    
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
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(46, 134, 171, 0.3);
        }
        
        .checkout-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-right: 1rem;
        }
        
        .item-details {
            flex-grow: 1;
        }
        
        .item-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .item-meta {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .item-price {
            text-align: right;
            min-width: 120px;
        }
        
        .price-value {
            font-weight: 700;
            color: var(--accent-color);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            padding-top: 1rem;
        }
        
        .customer-info {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .info-row {
            display: flex;
            padding: 0.5rem 0;
        }
        
        .info-label {
            font-weight: 600;
            min-width: 100px;
            color: #495057;
        }
        
        .btn-payment {
            background: var(--accent-color);
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-payment:hover {
            background: #d97e01;
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(241, 143, 1, 0.3);
            color: white;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: white;
            border-radius: 2rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        }
        
        .step i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .step-label {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .paystack-badge {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover) 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-top: 1rem;
        }
        
        .paystack-badge i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-credit-card me-3"></i>Checkout
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Review your order and complete payment</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="cart.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step">
                <i class="fas fa-check-circle"></i>
                <span class="step-label">Review Order → Complete Payment</span>
            </div>
        </div>

        <div class="row">
            <!-- Order Details -->
            <div class="col-lg-8">
                <!-- Customer Information -->
                <div class="checkout-card">
                    <h5 class="section-title">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                    <div class="customer-info">
                        <div class="info-row">
                            <span class="info-label">Name:</span>
                            <span><?php echo htmlspecialchars($customer_name); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span><?php echo htmlspecialchars($customer_email); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="checkout-card">
                    <h5 class="section-title">
                        <i class="fas fa-list me-2"></i>Order Items (<?php echo count($cart_items); ?>)
                    </h5>

                    <?php foreach ($cart_items as $item): ?>
                        <div class="order-item">
                            <?php 
                            $image_url = !empty($item['product_image']) 
                                ? '../../' . $item['product_image'] 
                                : 'https://placehold.co/150x150/E3F2FD/2E86AB?text=Product';
                            ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 class="item-image" 
                                 alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                 onerror="this.src='https://placehold.co/150x150/E3F2FD/2E86AB?text=Product'">
                            
                            <div class="item-details">
                                <div class="item-title">
                                    <?php echo htmlspecialchars($item['product_title']); ?>
                                </div>
                                <div class="item-meta">
                                    <i class="fas fa-folder me-1"></i>
                                    <?php echo htmlspecialchars($item['cat_name']); ?>
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-tag me-1"></i>
                                    <?php echo htmlspecialchars($item['brand_name']); ?>
                                    <span class="mx-2">|</span>
                                    <strong>Qty:</strong> <?php echo $item['qty']; ?>
                                </div>
                            </div>
                            
                            <div class="item-price">
                                <div class="price-value">
                                    GHS <?php echo number_format($item['product_price'] * $item['qty'], 2); ?>
                                </div>
                                <small class="text-muted">
                                    GHS <?php echo number_format($item['product_price'], 2); ?> each
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Summary & Payment -->
            <div class="col-lg-4">
                <div class="checkout-card">
                    <h5 class="section-title">
                        <i class="fas fa-receipt me-2"></i>Order Summary
                    </h5>

                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>GHS <?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span class="text-success">
                            <?php echo $shipping == 0 ? 'FREE' : 'GHS ' . number_format($shipping, 2); ?>
                        </span>
                    </div>

                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span id="orderTotal">GHS <?php echo number_format($total, 2); ?></span>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-credit-card me-2"></i>
                        <small><strong>Secure Payment via Paystack</strong><br>
                        Pay with Card or Mobile Money</small>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="button" 
                                class="btn btn-payment" 
                                id="simulatePaymentBtn"
                                data-customer-email="<?php echo htmlspecialchars($customer_email); ?>">
                            <i class="fas fa-lock me-2"></i>Proceed to Payment
                        </button>
                    </div>

                    <div class="paystack-badge">
                        <i class="fas fa-shield-alt"></i>
                        <strong>Powered by Paystack</strong><br>
                        <small style="opacity: 0.9;">100% Secure & Encrypted</small>
                    </div>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>
                            Your payment information is secure
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/checkout.js"></script>
</body>
</html>